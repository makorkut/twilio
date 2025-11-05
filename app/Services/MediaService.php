<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class MediaService
{
    protected Database $db;
    protected string $uploadPath;
    protected string $publicPath;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->uploadPath = STORAGE_PATH . '/uploads/media';
        $this->publicPath = PUBLIC_PATH . '/uploads/media';
    }

    /**
     * Create media record
     */
    public function create(array $data): int
    {
        $this->db->beginTransaction();

        try {
            // Insert main media record
            $mediaData = [
                'filename' => $data['filename'],
                'mime_type' => $data['mime_type'],
                'file_size' => $data['file_size'],
                'width' => $data['width'] ?? null,
                'height' => $data['height'] ?? null,
                'file_path' => $data['file_path'],
                'thumbnail_path' => $data['thumbnail_path'] ?? null,
                'media_type' => $data['media_type'] ?? 'image',
                'status' => $data['status'] ?? 'active',
                'uploaded_by' => $data['uploaded_by'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $mediaId = $this->db->insert('media', $mediaData);

            // Insert multi-language data
            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $langCode => $translation) {
                    $this->createTranslation($mediaId, $langCode, $translation);
                }
            }

            // Attach tags (including SKU tags)
            if (!empty($data['tags'])) {
                foreach ($data['tags'] as $tagData) {
                    $this->attachTag($mediaId, $tagData);
                }
            }

            // Attach to categories
            if (!empty($data['categories'])) {
                foreach ($data['categories'] as $categoryId => $level) {
                    $this->attachCategory($mediaId, $categoryId, $level);
                }
            }

            // Map external entity if provided
            if (!empty($data['external_id']) && !empty($data['external_source'])) {
                $this->mapExternalEntity($mediaId, $data['external_source'], $data['external_id']);
            }

            $this->db->commit();

            return $mediaId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Upload file and create media record
     */
    public function upload(array $file, array $metadata = []): int
    {
        // Validate file
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \Exception('Invalid file upload');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('media_', true) . '.' . $extension;

        // Organize by date
        $dateDir = date('Y/m');
        $uploadDir = $this->uploadPath . '/' . $dateDir;

        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new \Exception('Failed to move uploaded file');
        }

        // Get image dimensions if image
        $dimensions = null;
        if (strpos($file['type'], 'image/') === 0) {
            $dimensions = getimagesize($filePath);
        }

        // Prepare data
        $data = [
            'filename' => $file['name'],
            'mime_type' => $file['type'],
            'file_size' => $file['size'],
            'width' => $dimensions ? $dimensions[0] : null,
            'height' => $dimensions ? $dimensions[1] : null,
            'file_path' => $dateDir . '/' . $filename,
            'media_type' => $this->getMediaType($file['type']),
            'uploaded_by' => $metadata['uploaded_by'] ?? null,
            'translations' => $metadata['translations'] ?? [],
            'tags' => $metadata['tags'] ?? [],
            'categories' => $metadata['categories'] ?? [],
            'external_id' => $metadata['external_id'] ?? null,
            'external_source' => $metadata['external_source'] ?? null,
        ];

        // Generate thumbnail for images
        if ($data['media_type'] === 'image') {
            $data['thumbnail_path'] = $this->generateThumbnail($filePath, $dateDir);
        }

        return $this->create($data);
    }

    /**
     * Download and create media from URL
     */
    public function downloadFromUrl(string $url, array $metadata = []): int
    {
        // Download file
        $tempFile = tempnam(sys_get_temp_dir(), 'media_download_');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FILE, fopen($tempFile, 'w'));

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            unlink($tempFile);
            throw new \Exception("Failed to download file from URL: {$url}");
        }

        // Get file info
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tempFile);
        finfo_close($finfo);

        $fileSize = filesize($tempFile);
        $filename = basename(parse_url($url, PHP_URL_PATH));

        // Prepare file array
        $file = [
            'tmp_name' => $tempFile,
            'name' => $filename,
            'type' => $mimeType,
            'size' => $fileSize,
        ];

        // Upload
        try {
            $mediaId = $this->upload($file, $metadata);
            unlink($tempFile);
            return $mediaId;
        } catch (\Exception $e) {
            unlink($tempFile);
            throw $e;
        }
    }

    /**
     * Attach media to entity (product, post, page, etc.)
     */
    public function attachToEntity(int $mediaId, string $entityType, int $entityId, string $usageType = 'gallery', int $sortOrder = 0): void
    {
        $data = [
            'media_id' => $mediaId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'usage_type' => $usageType,
            'sort_order' => $sortOrder,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Check if already exists
        $existing = $this->db->query(
            "SELECT id FROM media_usage
             WHERE media_id = ? AND entity_type = ? AND entity_id = ? AND usage_type = ?
             LIMIT 1",
            [$mediaId, $entityType, $entityId, $usageType]
        );

        if (empty($existing)) {
            $this->db->insert('media_usage', $data);
        }
    }

    /**
     * Detach media from entity
     */
    public function detachFromEntity(int $mediaId, string $entityType, int $entityId, ?string $usageType = null): void
    {
        $where = 'media_id = ? AND entity_type = ? AND entity_id = ?';
        $params = [$mediaId, $entityType, $entityId];

        if ($usageType) {
            $where .= ' AND usage_type = ?';
            $params[] = $usageType;
        }

        $this->db->delete('media_usage', $where, $params);
    }

    /**
     * Get media by entity
     */
    public function getByEntity(string $entityType, int $entityId, ?string $usageType = null): array
    {
        $query = "SELECT m.*, mu.usage_type, mu.sort_order
                  FROM media m
                  INNER JOIN media_usage mu ON mu.media_id = m.id
                  WHERE mu.entity_type = ? AND mu.entity_id = ?";

        $params = [$entityType, $entityId];

        if ($usageType) {
            $query .= " AND mu.usage_type = ?";
            $params[] = $usageType;
        }

        $query .= " ORDER BY mu.sort_order ASC, m.id DESC";

        return $this->db->query($query, $params);
    }

    /**
     * Attach tag to media
     */
    public function attachTag(int $mediaId, array $tagData): void
    {
        // Get or create tag
        $tagId = $this->getOrCreateTag($tagData['name'], $tagData['type'] ?? 'general');

        // Attach to media
        $data = [
            'media_id' => $mediaId,
            'tag_id' => $tagId,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Check if already exists
        $existing = $this->db->query(
            "SELECT id FROM media_tag_relations WHERE media_id = ? AND tag_id = ? LIMIT 1",
            [$mediaId, $tagId]
        );

        if (empty($existing)) {
            $this->db->insert('media_tag_relations', $data);

            // NOTE: MySQL trigger 'auto_attach_media_to_product_by_sku' will fire here
            // If tag_type is 'sku', it will automatically attach this media to matching products
        }
    }

    /**
     * Detach tag from media
     */
    public function detachTag(int $mediaId, int $tagId): void
    {
        $this->db->delete('media_tag_relations', 'media_id = ? AND tag_id = ?', [$mediaId, $tagId]);
    }

    /**
     * Get or create tag
     */
    protected function getOrCreateTag(string $name, string $type = 'general'): int
    {
        // Check if exists
        $existing = $this->db->query(
            "SELECT id FROM media_tags WHERE name = ? AND tag_type = ? LIMIT 1",
            [$name, $type]
        );

        if (!empty($existing)) {
            return (int) $existing[0]['id'];
        }

        // Create new tag
        $slug = $this->generateSlug($name);
        $data = [
            'name' => $name,
            'slug' => $slug,
            'tag_type' => $type,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return $this->db->insert('media_tags', $data);
    }

    /**
     * Get media by SKU tag
     */
    public function getBySku(string $sku): array
    {
        return $this->db->query(
            "SELECT m.*
             FROM media m
             INNER JOIN media_tag_relations mtr ON mtr.media_id = m.id
             INNER JOIN media_tags mt ON mt.id = mtr.tag_id
             WHERE mt.tag_type = 'sku' AND mt.name = ?
             ORDER BY m.id DESC",
            [$sku]
        );
    }

    /**
     * Attach category to media
     */
    public function attachCategory(int $mediaId, int $categoryId, string $level = 'main'): void
    {
        $data = [
            'media_id' => $mediaId,
            'category_id' => $categoryId,
            'level' => $level,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Check if already exists
        $existing = $this->db->query(
            "SELECT id FROM media_category_relations WHERE media_id = ? AND category_id = ? AND level = ? LIMIT 1",
            [$mediaId, $categoryId, $level]
        );

        if (empty($existing)) {
            $this->db->insert('media_category_relations', $data);
        }
    }

    /**
     * Create media translation
     */
    protected function createTranslation(int $mediaId, string $langCode, array $translation): void
    {
        $slug = $this->generateSlug($translation['title'] ?? '', $langCode, $mediaId);

        $data = [
            'media_id' => $mediaId,
            'lang' => $langCode,
            'title' => $translation['title'] ?? '',
            'slug' => $slug,
            'alt_text' => $translation['alt_text'] ?? null,
            'caption' => $translation['caption'] ?? null,
            'description' => $translation['description'] ?? null,
        ];

        $this->db->insert('media_lang', $data);
    }

    /**
     * Map media to external entity
     */
    protected function mapExternalEntity(int $mediaId, string $externalSource, string $externalId): void
    {
        $data = [
            'local_entity_type' => 'media',
            'local_entity_id' => $mediaId,
            'external_source' => $externalSource,
            'external_entity_type' => 'image',
            'external_id' => $externalId,
            'sync_status' => 'synced',
            'last_synced_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->query(
            "INSERT INTO external_entity_mapping
                (local_entity_type, local_entity_id, external_source, external_entity_type, external_id, sync_status, last_synced_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                local_entity_id = VALUES(local_entity_id),
                sync_status = VALUES(sync_status),
                last_synced_at = VALUES(last_synced_at)",
            [
                $data['local_entity_type'],
                $data['local_entity_id'],
                $data['external_source'],
                $data['external_entity_type'],
                $data['external_id'],
                $data['sync_status'],
                $data['last_synced_at'],
            ]
        );
    }

    /**
     * Generate thumbnail
     */
    protected function generateThumbnail(string $sourcePath, string $dateDir): string
    {
        $thumbDir = $this->uploadPath . '/' . $dateDir . '/thumbs';

        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        $filename = 'thumb_' . basename($sourcePath);
        $thumbPath = $thumbDir . '/' . $filename;

        // Simple thumbnail generation (basic implementation)
        // In production, use Intervention Image or similar library
        $sourceImage = @imagecreatefromstring(file_get_contents($sourcePath));

        if (!$sourceImage) {
            return null;
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        $thumbWidth = 300;
        $thumbHeight = (int) ($sourceHeight * ($thumbWidth / $sourceWidth));

        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);

        imagejpeg($thumbImage, $thumbPath, 85);

        imagedestroy($sourceImage);
        imagedestroy($thumbImage);

        return $dateDir . '/thumbs/' . $filename;
    }

    /**
     * Get media type from MIME type
     */
    protected function getMediaType(string $mimeType): string
    {
        if (strpos($mimeType, 'image/') === 0) {
            return 'image';
        }
        if (strpos($mimeType, 'video/') === 0) {
            return 'video';
        }
        if (strpos($mimeType, 'audio/') === 0) {
            return 'audio';
        }
        if (in_array($mimeType, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
            return 'document';
        }

        return 'other';
    }

    /**
     * Generate unique slug
     */
    protected function generateSlug(string $text, string $langCode = '', int $mediaId = 0): string
    {
        // Turkish character map
        $charMap = [
            'Ç' => 'C', 'ç' => 'c',
            'Ğ' => 'G', 'ğ' => 'g',
            'İ' => 'I', 'ı' => 'i',
            'Ö' => 'O', 'ö' => 'o',
            'Ş' => 'S', 'ş' => 's',
            'Ü' => 'U', 'ü' => 'u',
        ];

        $slug = strtr($text, $charMap);
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        if (empty($slug)) {
            $slug = 'media-' . uniqid();
        }

        // Check uniqueness for media_lang
        if ($langCode) {
            $originalSlug = $slug;
            $counter = 1;

            while (true) {
                $existing = $this->db->query(
                    "SELECT media_id FROM media_lang WHERE slug = ? AND lang = ? AND media_id != ? LIMIT 1",
                    [$slug, $langCode, $mediaId]
                );

                if (empty($existing)) {
                    break;
                }

                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        } else {
            // For media_tags
            $originalSlug = $slug;
            $counter = 1;

            while (true) {
                $existing = $this->db->query(
                    "SELECT id FROM media_tags WHERE slug = ? LIMIT 1",
                    [$slug]
                );

                if (empty($existing)) {
                    break;
                }

                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        return $slug;
    }

    /**
     * Find media by ID
     */
    public function findById(int $mediaId): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM media WHERE id = ? LIMIT 1",
            [$mediaId]
        );

        return $result[0] ?? null;
    }

    /**
     * Find media by external ID
     */
    public function findByExternalId(string $externalSource, string $externalId): ?array
    {
        $result = $this->db->query(
            "SELECT m.* FROM media m
             INNER JOIN external_entity_mapping e
                ON e.local_entity_type = 'media'
                AND e.local_entity_id = m.id
             WHERE e.external_source = ? AND e.external_id = ?
             LIMIT 1",
            [$externalSource, $externalId]
        );

        return $result[0] ?? null;
    }

    /**
     * Delete media (soft delete)
     */
    public function delete(int $mediaId): bool
    {
        $updateData = [
            'status' => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s'),
        ];

        return $this->db->update('media', $updateData, 'id = ?', [$mediaId]);
    }

    /**
     * Get full URL for media file
     */
    public function getUrl(array $media): string
    {
        return env('APP_URL') . '/uploads/media/' . $media['file_path'];
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrl(array $media): ?string
    {
        if (empty($media['thumbnail_path'])) {
            return null;
        }

        return env('APP_URL') . '/uploads/media/' . $media['thumbnail_path'];
    }

    /**
     * Batch attach media by SKU (for automation)
     * This is an alternative to the trigger-based approach
     */
    public function attachToProductsBySku(string $sku, int $mediaId, string $usageType = 'gallery'): int
    {
        // Find products with matching SKU
        $products = $this->db->query(
            "SELECT id FROM products WHERE sku = ? OR skud = ?",
            [$sku, $sku]
        );

        $count = 0;
        foreach ($products as $product) {
            $this->attachToEntity($mediaId, 'product', (int) $product['id'], $usageType);
            $count++;
        }

        return $count;
    }

    /**
     * Process media queue (for background processing)
     */
    public function processQueue(int $limit = 50): array
    {
        // Get pending media processing jobs
        $jobs = $this->db->query(
            "SELECT * FROM queue_jobs
             WHERE job_type = 'process_media'
             AND status = 'pending'
             ORDER BY created_at ASC
             LIMIT ?",
            [$limit]
        );

        $results = [];

        foreach ($jobs as $job) {
            try {
                $payload = json_decode($job['payload'], true);

                // Download and process media
                $mediaId = $this->downloadFromUrl($payload['url'], $payload['metadata'] ?? []);

                // Update job status
                $this->db->update('queue_jobs', [
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s'),
                    'result' => json_encode(['media_id' => $mediaId]),
                ], 'id = ?', [$job['id']]);

                $results[] = ['job_id' => $job['id'], 'media_id' => $mediaId, 'status' => 'success'];
            } catch (\Exception $e) {
                // Update job status
                $attempts = $job['attempts'] + 1;
                $status = $attempts >= 3 ? 'failed' : 'pending';

                $this->db->update('queue_jobs', [
                    'status' => $status,
                    'attempts' => $attempts,
                    'error_message' => $e->getMessage(),
                    'failed_at' => $status === 'failed' ? date('Y-m-d H:i:s') : null,
                ], 'id = ?', [$job['id']]);

                $results[] = ['job_id' => $job['id'], 'status' => 'error', 'error' => $e->getMessage()];
            }
        }

        return $results;
    }
}
