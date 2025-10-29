<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class QueueService
{
    protected Database $db;
    protected ProductService $productService;
    protected MediaService $mediaService;

    public function __construct(
        Database $db,
        ProductService $productService,
        MediaService $mediaService
    ) {
        $this->db = $db;
        $this->productService = $productService;
        $this->mediaService = $mediaService;
    }

    /**
     * Add job to queue
     */
    public function push(string $jobType, array $payload, int $priority = 5): int
    {
        $data = [
            'job_type' => $jobType,
            'payload' => json_encode($payload),
            'status' => 'pending',
            'priority' => $priority,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return $this->db->insert('queue_jobs', $data);
    }

    /**
     * Process pending jobs
     */
    public function processJobs(int $limit = 50): array
    {
        // Get pending jobs ordered by priority
        $jobs = $this->db->query(
            "SELECT * FROM queue_jobs
             WHERE status = 'pending'
             AND (scheduled_at IS NULL OR scheduled_at <= NOW())
             ORDER BY priority DESC, created_at ASC
             LIMIT ?",
            [$limit]
        );

        $results = [];

        foreach ($jobs as $job) {
            $result = $this->processJob((int) $job['id']);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Process single job
     */
    public function processJob(int $jobId): array
    {
        // Get job
        $job = $this->db->query(
            "SELECT * FROM queue_jobs WHERE id = ? LIMIT 1",
            [$jobId]
        );

        if (empty($job)) {
            return ['job_id' => $jobId, 'status' => 'not_found'];
        }

        $job = $job[0];

        // Update status to processing
        $this->db->update('queue_jobs', [
            'status' => 'processing',
            'started_at' => date('Y-m-d H:i:s'),
        ], ['id' => $jobId]);

        try {
            $payload = json_decode($job['payload'], true);

            // Process based on job type
            $result = $this->processJobType($job['job_type'], $payload);

            // Update job status to completed
            $this->db->update('queue_jobs', [
                'status' => 'completed',
                'result' => json_encode($result),
                'completed_at' => date('Y-m-d H:i:s'),
            ], ['id' => $jobId]);

            return [
                'job_id' => $jobId,
                'job_type' => $job['job_type'],
                'status' => 'success',
                'result' => $result,
            ];
        } catch (\Exception $e) {
            // Update job status
            $attempts = $job['attempts'] + 1;
            $maxAttempts = (int) env('QUEUE_MAX_ATTEMPTS', '3');
            $status = $attempts >= $maxAttempts ? 'failed' : 'pending';

            $this->db->update('queue_jobs', [
                'status' => $status,
                'attempts' => $attempts,
                'error_message' => $e->getMessage(),
                'failed_at' => $status === 'failed' ? date('Y-m-d H:i:s') : null,
            ], ['id' => $jobId]);

            return [
                'job_id' => $jobId,
                'job_type' => $job['job_type'],
                'status' => 'error',
                'error' => $e->getMessage(),
                'attempts' => $attempts,
            ];
        }
    }

    /**
     * Process job by type
     */
    protected function processJobType(string $jobType, array $payload): array
    {
        switch ($jobType) {
            case 'import_product':
                return $this->processImportProduct($payload);

            case 'download_image':
                return $this->processDownloadImage($payload);

            case 'import_category':
                return $this->processImportCategory($payload);

            case 'send_email':
                return $this->processSendEmail($payload);

            case 'process_webhook':
                return $this->processWebhook($payload);

            case 'generate_sitemap':
                return $this->processGenerateSitemap($payload);

            case 'cleanup_old_files':
                return $this->processCleanupOldFiles($payload);

            case 'sync_inventory':
                return $this->processSyncInventory($payload);

            default:
                throw new \Exception("Unknown job type: {$jobType}");
        }
    }

    /**
     * Process import product job
     */
    protected function processImportProduct(array $payload): array
    {
        $externalSource = $payload['external_source'] ?? 'local-crm';
        $externalId = $payload['external_id'] ?? null;

        if (!$externalId) {
            throw new \Exception('Missing external_id in payload');
        }

        // Check if product already exists
        $existingProduct = $this->productService->findByExternalId($externalSource, $externalId);

        $productData = [
            'sku' => $payload['sku'] ?? null,
            'skud' => $payload['skud'] ?? null,
            'category_id' => $payload['category_id'] ?? null,
            'brand_id' => $payload['brand_id'] ?? null,
            'type' => $payload['type'] ?? 'simple',
            'status' => $payload['status'] ?? 'active',
            'stock_status' => $payload['stock_status'] ?? 'in_stock',
            'stock_quantity' => $payload['stock_quantity'] ?? 0,
            'translations' => $payload['translations'] ?? [],
            'prices' => $payload['prices'] ?? [],
            'external_id' => $externalId,
            'external_source' => $externalSource,
        ];

        if ($existingProduct) {
            // Update existing product
            $productId = (int) $existingProduct['id'];
            $this->productService->update($productId, $productData);
            $operation = 'updated';
        } else {
            // Create new product
            $productId = $this->productService->create($productData);
            $operation = 'created';
        }

        return [
            'product_id' => $productId,
            'operation' => $operation,
            'sku' => $productData['sku'],
        ];
    }

    /**
     * Process download image job
     */
    protected function processDownloadImage(array $payload): array
    {
        $url = $payload['url'] ?? null;
        $externalId = $payload['external_id'] ?? null;
        $externalSource = $payload['external_source'] ?? 'local-crm';

        if (!$url) {
            throw new \Exception('Missing url in payload');
        }

        // Check if image already exists
        if ($externalId) {
            $existingMedia = $this->mediaService->findByExternalId($externalSource, $externalId);
            if ($existingMedia) {
                return [
                    'media_id' => $existingMedia['id'],
                    'operation' => 'already_exists',
                ];
            }
        }

        // Download and create media
        $metadata = [
            'translations' => $payload['translations'] ?? [],
            'tags' => $payload['tags'] ?? [],
            'categories' => $payload['categories'] ?? [],
            'external_id' => $externalId,
            'external_source' => $externalSource,
        ];

        $mediaId = $this->mediaService->downloadFromUrl($url, $metadata);

        // Attach to products if SKU tags provided
        $attachedCount = 0;
        if (!empty($payload['tags'])) {
            foreach ($payload['tags'] as $tag) {
                if ($tag['type'] === 'sku') {
                    $count = $this->mediaService->attachToProductsBySku($tag['name'], $mediaId);
                    $attachedCount += $count;
                }
            }
        }

        return [
            'media_id' => $mediaId,
            'operation' => 'downloaded',
            'url' => $url,
            'attached_to_products' => $attachedCount,
        ];
    }

    /**
     * Process import category job
     */
    protected function processImportCategory(array $payload): array
    {
        // Basic category import logic
        // This would be expanded based on actual category structure
        $externalId = $payload['external_id'] ?? null;
        $externalSource = $payload['external_source'] ?? 'local-crm';

        if (!$externalId) {
            throw new \Exception('Missing external_id in payload');
        }

        // Check if category exists
        $existing = $this->db->query(
            "SELECT c.id FROM categories c
             INNER JOIN external_entity_mapping e
                ON e.local_entity_type = 'category'
                AND e.local_entity_id = c.id
             WHERE e.external_source = ?
             AND e.external_id = ?
             LIMIT 1",
            [$externalSource, $externalId]
        );

        $categoryData = [
            'parent_id' => $payload['parent_id'] ?? null,
            'type' => $payload['type'] ?? 'product',
            'status' => $payload['status'] ?? 'active',
            'sort_order' => $payload['sort_order'] ?? 0,
        ];

        if (!empty($existing)) {
            // Update
            $categoryId = (int) $existing[0]['id'];
            $this->db->update('categories', $categoryData, ['id' => $categoryId]);
            $operation = 'updated';
        } else {
            // Create
            $categoryData['created_at'] = date('Y-m-d H:i:s');
            $categoryId = $this->db->insert('categories', $categoryData);

            // Map external entity
            $this->db->insert('external_entity_mapping', [
                'local_entity_type' => 'category',
                'local_entity_id' => $categoryId,
                'external_source' => $externalSource,
                'external_entity_type' => 'category',
                'external_id' => $externalId,
                'sync_status' => 'synced',
                'last_synced_at' => date('Y-m-d H:i:s'),
            ]);

            $operation = 'created';
        }

        // Update translations
        if (!empty($payload['translations'])) {
            foreach ($payload['translations'] as $lang => $translation) {
                $this->db->query(
                    "INSERT INTO category_lang (category_id, lang, name, slug, description)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        name = VALUES(name),
                        slug = VALUES(slug),
                        description = VALUES(description)",
                    [
                        $categoryId,
                        $lang,
                        $translation['name'] ?? '',
                        $translation['slug'] ?? '',
                        $translation['description'] ?? null,
                    ]
                );
            }
        }

        return [
            'category_id' => $categoryId,
            'operation' => $operation,
        ];
    }

    /**
     * Process send email job
     */
    protected function processSendEmail(array $payload): array
    {
        $to = $payload['to'] ?? null;
        $subject = $payload['subject'] ?? '';
        $body = $payload['body'] ?? '';
        $from = $payload['from'] ?? env('MAIL_FROM_ADDRESS');

        if (!$to) {
            throw new \Exception('Missing recipient email address');
        }

        // Basic email sending (in production, use PHPMailer or similar)
        $headers = [
            'From' => $from,
            'Content-Type' => 'text/html; charset=UTF-8',
        ];

        $success = mail($to, $subject, $body, $headers);

        if (!$success) {
            throw new \Exception('Failed to send email');
        }

        return [
            'to' => $to,
            'subject' => $subject,
            'sent' => true,
        ];
    }

    /**
     * Process webhook job
     */
    protected function processWebhook(array $payload): array
    {
        // Generic webhook processing
        // This would be expanded based on webhook type
        return [
            'webhook_id' => $payload['webhook_id'] ?? null,
            'processed' => true,
        ];
    }

    /**
     * Process generate sitemap job
     */
    protected function processGenerateSitemap(array $payload): array
    {
        // Sitemap generation logic would go here
        // This is a placeholder
        return [
            'sitemap' => 'generated',
            'urls_count' => 0,
        ];
    }

    /**
     * Process cleanup old files job
     */
    protected function processCleanupOldFiles(array $payload): array
    {
        $days = $payload['days'] ?? 30;
        $deleted = 0;

        // Clean old cache files
        $cacheDir = cache_path();
        $files = glob($cacheDir . '/*');

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < time() - ($days * 86400)) {
                unlink($file);
                $deleted++;
            }
        }

        return [
            'deleted_files' => $deleted,
            'days_threshold' => $days,
        ];
    }

    /**
     * Process sync inventory job
     */
    protected function processSyncInventory(array $payload): array
    {
        // Inventory sync logic would go here
        // This is a placeholder
        return [
            'synced' => true,
            'products_updated' => 0,
        ];
    }

    /**
     * Get job status
     */
    public function getJobStatus(int $jobId): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM queue_jobs WHERE id = ? LIMIT 1",
            [$jobId]
        );

        return $result[0] ?? null;
    }

    /**
     * Get pending jobs count
     */
    public function getPendingCount(): int
    {
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM queue_jobs WHERE status = 'pending'"
        );

        return (int) $result[0]['count'];
    }

    /**
     * Get failed jobs
     */
    public function getFailedJobs(int $limit = 100): array
    {
        return $this->db->query(
            "SELECT * FROM queue_jobs
             WHERE status = 'failed'
             ORDER BY failed_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Retry failed job
     */
    public function retryJob(int $jobId): array
    {
        // Reset job status
        $this->db->update('queue_jobs', [
            'status' => 'pending',
            'attempts' => 0,
            'error_message' => null,
            'failed_at' => null,
        ], ['id' => $jobId]);

        // Process immediately
        return $this->processJob($jobId);
    }

    /**
     * Delete old completed jobs
     */
    public function cleanupCompletedJobs(int $daysOld = 7): int
    {
        $result = $this->db->query(
            "DELETE FROM queue_jobs
             WHERE status = 'completed'
             AND completed_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$daysOld]
        );

        // Return affected rows (this would need to be implemented in Database class)
        return 0; // Placeholder
    }

    /**
     * Get queue statistics
     */
    public function getStatistics(): array
    {
        $stats = [
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'failed' => 0,
        ];

        $results = $this->db->query(
            "SELECT status, COUNT(*) as count
             FROM queue_jobs
             GROUP BY status"
        );

        foreach ($results as $row) {
            $stats[$row['status']] = (int) $row['count'];
        }

        return $stats;
    }

    /**
     * Schedule job for later
     */
    public function schedule(string $jobType, array $payload, string $scheduledAt, int $priority = 5): int
    {
        $data = [
            'job_type' => $jobType,
            'payload' => json_encode($payload),
            'status' => 'pending',
            'priority' => $priority,
            'scheduled_at' => $scheduledAt,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return $this->db->insert('queue_jobs', $data);
    }

    /**
     * Cancel pending job
     */
    public function cancelJob(int $jobId): bool
    {
        return $this->db->update('queue_jobs', [
            'status' => 'cancelled',
        ], ['id' => $jobId]);
    }
}
