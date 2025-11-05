<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class WebhookService
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Send webhook to external endpoint
     */
    public function send(string $endpoint, string $eventType, array $payload, ?string $secret = null): int
    {
        $this->db->beginTransaction();

        try {
            // Create webhook event record
            $idempotencyKey = $this->generateIdempotencyKey($eventType, $payload);

            $webhookData = [
                'direction' => 'outgoing',
                'endpoint' => $endpoint,
                'event_type' => $eventType,
                'payload' => json_encode($payload),
                'idempotency_key' => $idempotencyKey,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $webhookId = $this->db->insert('webhook_events', $webhookData);

            // Send HTTP request
            $result = $this->sendHttpRequest($endpoint, $eventType, $payload, $secret);

            // Update webhook record
            $updateData = [
                'status' => $result['success'] ? 'success' : 'failed',
                'http_status_code' => $result['http_code'],
                'response_body' => $result['response'],
                'response_headers' => json_encode($result['headers'] ?? []),
                'sent_at' => date('Y-m-d H:i:s'),
            ];

            if (!$result['success']) {
                $updateData['error_message'] = $result['error'] ?? 'Unknown error';
            }

            $this->db->update('webhook_events', $updateData, 'id = ?', [$webhookId]);

            $this->db->commit();

            return $webhookId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Receive and process webhook
     */
    public function receive(string $source, string $eventType, array $payload, ?string $signature = null, ?string $secret = null): array
    {
        $this->db->beginTransaction();

        try {
            // Validate signature if provided
            if ($signature && $secret) {
                if (!$this->validateSignature($payload, $signature, $secret)) {
                    throw new \Exception('Invalid webhook signature');
                }
            }

            // Check idempotency
            $idempotencyKey = $payload['idempotency_key'] ?? $this->generateIdempotencyKey($eventType, $payload);

            $existing = $this->db->query(
                "SELECT id, status FROM webhook_events
                 WHERE direction = 'incoming'
                 AND idempotency_key = ?
                 LIMIT 1",
                [$idempotencyKey]
            );

            if (!empty($existing)) {
                // Already processed
                $this->db->commit();
                return [
                    'status' => 'duplicate',
                    'webhook_id' => $existing[0]['id'],
                    'message' => 'Webhook already processed',
                ];
            }

            // Create webhook event record
            $webhookData = [
                'direction' => 'incoming',
                'endpoint' => $source,
                'event_type' => $eventType,
                'payload' => json_encode($payload),
                'idempotency_key' => $idempotencyKey,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $webhookId = $this->db->insert('webhook_events', $webhookData);

            // Process webhook based on event type
            $result = $this->processWebhook($webhookId, $eventType, $payload);

            // Update webhook record
            $updateData = [
                'status' => $result['success'] ? 'success' : 'failed',
                'processed_at' => date('Y-m-d H:i:s'),
            ];

            if (!$result['success']) {
                $updateData['error_message'] = $result['error'] ?? 'Processing failed';
            }

            $this->db->update('webhook_events', $updateData, 'id = ?', [$webhookId]);

            $this->db->commit();

            return [
                'status' => 'processed',
                'webhook_id' => $webhookId,
                'result' => $result,
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();

            // Log error webhook
            try {
                $this->db->insert('webhook_events', [
                    'direction' => 'incoming',
                    'endpoint' => $source,
                    'event_type' => $eventType,
                    'payload' => json_encode($payload),
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $logError) {
                // Silent fail
            }

            throw $e;
        }
    }

    /**
     * Process webhook based on event type
     */
    protected function processWebhook(int $webhookId, string $eventType, array $payload): array
    {
        try {
            switch ($eventType) {
                case 'product.created':
                case 'product.updated':
                    return $this->processProductWebhook($payload);

                case 'product.deleted':
                    return $this->processProductDeletion($payload);

                case 'image.created':
                case 'image.updated':
                    return $this->processImageWebhook($payload);

                case 'category.created':
                case 'category.updated':
                    return $this->processCategoryWebhook($payload);

                case 'order.status_changed':
                    return $this->processOrderStatusWebhook($payload);

                default:
                    // Queue for manual processing
                    $this->queueWebhook($webhookId, $eventType, $payload);
                    return ['success' => true, 'message' => 'Queued for processing'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process product webhook (create/update)
     */
    protected function processProductWebhook(array $payload): array
    {
        // Queue for background processing to avoid timeout
        $jobData = [
            'job_type' => 'import_product',
            'payload' => json_encode($payload),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $jobId = $this->db->insert('queue_jobs', $jobData);

        return [
            'success' => true,
            'message' => 'Product queued for processing',
            'job_id' => $jobId,
        ];
    }

    /**
     * Process product deletion
     */
    protected function processProductDeletion(array $payload): array
    {
        $externalId = $payload['external_id'] ?? null;
        $externalSource = $payload['external_source'] ?? 'local-crm';

        if (!$externalId) {
            throw new \Exception('Missing external_id in payload');
        }

        // Find product
        $result = $this->db->query(
            "SELECT local_entity_id FROM external_entity_mapping
             WHERE external_source = ?
             AND external_entity_type = 'product'
             AND external_id = ?
             LIMIT 1",
            [$externalSource, $externalId]
        );

        if (empty($result)) {
            return ['success' => true, 'message' => 'Product not found, nothing to delete'];
        }

        $productId = (int) $result[0]['local_entity_id'];

        // Soft delete
        $this->db->update('products', [
            'status' => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$productId]);

        return ['success' => true, 'product_id' => $productId];
    }

    /**
     * Process image webhook (create/update)
     */
    protected function processImageWebhook(array $payload): array
    {
        // Queue for background processing
        $jobData = [
            'job_type' => 'download_image',
            'payload' => json_encode($payload),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $jobId = $this->db->insert('queue_jobs', $jobData);

        return [
            'success' => true,
            'message' => 'Image queued for download',
            'job_id' => $jobId,
        ];
    }

    /**
     * Process category webhook
     */
    protected function processCategoryWebhook(array $payload): array
    {
        // Queue for background processing
        $jobData = [
            'job_type' => 'import_category',
            'payload' => json_encode($payload),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $jobId = $this->db->insert('queue_jobs', $jobData);

        return [
            'success' => true,
            'message' => 'Category queued for processing',
            'job_id' => $jobId,
        ];
    }

    /**
     * Process order status webhook
     */
    protected function processOrderStatusWebhook(array $payload): array
    {
        $orderId = $payload['order_id'] ?? null;
        $newStatus = $payload['status'] ?? null;

        if (!$orderId || !$newStatus) {
            throw new \Exception('Missing order_id or status in payload');
        }

        // Update order status
        $this->db->update('orders', [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$orderId]);

        // Log status change
        $this->db->insert('order_status_history', [
            'order_id' => $orderId,
            'status' => $newStatus,
            'notes' => 'Updated via webhook',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'order_id' => $orderId, 'new_status' => $newStatus];
    }

    /**
     * Queue webhook for manual processing
     */
    protected function queueWebhook(int $webhookId, string $eventType, array $payload): void
    {
        $jobData = [
            'job_type' => 'process_webhook',
            'payload' => json_encode([
                'webhook_id' => $webhookId,
                'event_type' => $eventType,
                'payload' => $payload,
            ]),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('queue_jobs', $jobData);
    }

    /**
     * Send HTTP request
     */
    protected function sendHttpRequest(string $endpoint, string $eventType, array $payload, ?string $secret = null): array
    {
        $data = [
            'event_type' => $eventType,
            'timestamp' => time(),
            'payload' => $payload,
        ];

        $jsonData = json_encode($data);

        $headers = [
            'Content-Type: application/json',
            'User-Agent: PolyurethaneEcommerce/1.0',
        ];

        // Add signature if secret provided
        if ($secret) {
            $signature = $this->generateSignature($data, $secret);
            $headers[] = 'X-Webhook-Signature: ' . $signature;
        }

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $error = curl_error($ch);

        curl_close($ch);

        $responseHeaders = substr($response, 0, $headerSize);
        $responseBody = substr($response, $headerSize);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $responseBody,
            'headers' => $this->parseHeaders($responseHeaders),
            'error' => $error ?: null,
        ];
    }

    /**
     * Generate webhook signature (HMAC SHA256)
     */
    protected function generateSignature(array $data, string $secret): string
    {
        $payload = json_encode($data);
        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Validate webhook signature
     */
    protected function validateSignature(array $data, string $signature, string $secret): bool
    {
        $expectedSignature = $this->generateSignature($data, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Generate idempotency key
     */
    protected function generateIdempotencyKey(string $eventType, array $payload): string
    {
        // Use event type + payload hash
        $payloadString = json_encode($payload);
        return $eventType . '_' . hash('sha256', $payloadString);
    }

    /**
     * Parse HTTP headers
     */
    protected function parseHeaders(string $headerText): array
    {
        $headers = [];
        $lines = explode("\r\n", $headerText);

        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$name, $value] = explode(':', $line, 2);
                $headers[trim($name)] = trim($value);
            }
        }

        return $headers;
    }

    /**
     * Retry failed webhooks
     */
    public function retryFailed(int $limit = 50): array
    {
        // Get failed webhooks that haven't exceeded max retries
        $webhooks = $this->db->query(
            "SELECT * FROM webhook_events
             WHERE status = 'failed'
             AND direction = 'outgoing'
             AND retry_count < ?
             ORDER BY created_at ASC
             LIMIT ?",
            [3, $limit]
        );

        $results = [];

        foreach ($webhooks as $webhook) {
            try {
                $payload = json_decode($webhook['payload'], true);
                $secret = env('LOCAL_CRM_WEBHOOK_SECRET');

                // Retry sending
                $result = $this->sendHttpRequest($webhook['endpoint'], $webhook['event_type'], $payload, $secret);

                // Update webhook record
                $updateData = [
                    'status' => $result['success'] ? 'success' : 'failed',
                    'http_status_code' => $result['http_code'],
                    'response_body' => $result['response'],
                    'retry_count' => $webhook['retry_count'] + 1,
                    'last_retry_at' => date('Y-m-d H:i:s'),
                ];

                if ($result['success']) {
                    $updateData['sent_at'] = date('Y-m-d H:i:s');
                } else {
                    $updateData['error_message'] = $result['error'] ?? 'Retry failed';
                }

                $this->db->update('webhook_events', $updateData, 'id = ?', [$webhook['id']]);

                $results[] = [
                    'webhook_id' => $webhook['id'],
                    'status' => $result['success'] ? 'success' : 'failed',
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'webhook_id' => $webhook['id'],
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get webhook by ID
     */
    public function findById(int $webhookId): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM webhook_events WHERE id = ? LIMIT 1",
            [$webhookId]
        );

        return $result[0] ?? null;
    }

    /**
     * Get webhooks by event type
     */
    public function getByEventType(string $eventType, int $limit = 100): array
    {
        return $this->db->query(
            "SELECT * FROM webhook_events
             WHERE event_type = ?
             ORDER BY created_at DESC
             LIMIT ?",
            [$eventType, $limit]
        );
    }

    /**
     * Log sync event
     */
    public function logSync(string $entityType, int $entityId, string $operation, bool $success, ?string $error = null): int
    {
        $data = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'operation' => $operation,
            'status' => $success ? 'success' : 'failed',
            'error_message' => $error,
            'synced_at' => date('Y-m-d H:i:s'),
        ];

        return $this->db->insert('sync_logs', $data);
    }
}
