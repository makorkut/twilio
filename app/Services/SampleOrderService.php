<?php

namespace App\Services;

use App\Core\Database;

class SampleOrderService
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new sample order request
     */
    public function create(array $data): int
    {
        // Validate required fields
        $required = ['user_id', 'product_id', 'quantity'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Check if user is eligible for samples
        if (!$this->isEligibleForSamples((int)$data['user_id'])) {
            throw new \RuntimeException('Bu kullanıcı numune siparişi için uygun değil');
        }

        // Check sample limits
        $product = $this->db->fetchOne(
            "SELECT * FROM products WHERE id = ?",
            [$data['product_id']]
        );

        if (!$product) {
            throw new \RuntimeException('Ürün bulunamadı');
        }

        // Check if product allows samples
        if (!$product['allow_samples']) {
            throw new \RuntimeException('Bu ürün için numune siparişi yapılamaz');
        }

        // Check quantity limits
        $maxSampleQty = $product['max_sample_quantity'] ?? 5;
        if ($data['quantity'] > $maxSampleQty) {
            throw new \RuntimeException("Maksimum numune miktarı: {$maxSampleQty}");
        }

        // Generate sample order number
        $orderNumber = $this->generateSampleOrderNumber();

        // Calculate shipping cost (samples may have special shipping rates)
        $shippingCost = $this->calculateSampleShipping($data);

        // Insert sample order
        $sampleOrderId = $this->db->insert('sample_orders', [
            'order_number' => $orderNumber,
            'user_id' => $data['user_id'],
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'status' => 'pending',
            'purpose' => $data['purpose'] ?? null,
            'project_details' => $data['project_details'] ?? null,
            'shipping_cost' => $shippingCost,
            'notes' => $data['notes'] ?? null,
            'shipping_address' => json_encode($data['shipping_address'] ?? []),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Log status history
        $this->addStatusHistory($sampleOrderId, 'pending', 'Numune talebi oluşturuldu');

        return $sampleOrderId;
    }

    /**
     * Check if user is eligible for sample orders
     */
    public function isEligibleForSamples(int $userId): bool
    {
        // Get user details
        $user = $this->db->fetchOne(
            "SELECT u.*, cg.allow_samples, cg.max_samples_per_month
             FROM users u
             LEFT JOIN customer_groups cg ON u.customer_group_id = cg.id
             WHERE u.id = ?",
            [$userId]
        );

        if (!$user) {
            return false;
        }

        // Check if user's group allows samples
        if (!$user['allow_samples']) {
            return false;
        }

        // Check monthly limit
        $maxPerMonth = $user['max_samples_per_month'] ?? 10;
        $currentMonthCount = $this->getMonthlyRequestCount($userId);

        return $currentMonthCount < $maxPerMonth;
    }

    /**
     * Get monthly sample request count for user
     */
    public function getMonthlyRequestCount(int $userId): int
    {
        $startOfMonth = date('Y-m-01 00:00:00');
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count
             FROM sample_orders
             WHERE user_id = ? AND created_at >= ?",
            [$userId, $startOfMonth]
        );

        return (int)($result['count'] ?? 0);
    }

    /**
     * Generate sample order number
     */
    private function generateSampleOrderNumber(): string
    {
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        return "SAMPLE-{$date}-{$random}";
    }

    /**
     * Calculate sample shipping cost
     */
    private function calculateSampleShipping(array $data): float
    {
        // Get product weight/dimensions for shipping calculation
        $product = $this->db->fetchOne(
            "SELECT weight, length, width, height FROM products WHERE id = ?",
            [$data['product_id']]
        );

        // Base sample shipping cost
        $baseCost = 25.00; // 25 TL base cost

        // Add weight-based cost
        $weight = ($product['weight'] ?? 0) * $data['quantity'];
        if ($weight > 1) { // Over 1 kg
            $baseCost += ($weight - 1) * 5; // 5 TL per kg over 1kg
        }

        return $baseCost;
    }

    /**
     * Update sample order status
     */
    public function updateStatus(int $sampleOrderId, string $status, ?string $note = null): bool
    {
        $validStatuses = ['pending', 'approved', 'preparing', 'shipped', 'delivered', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        $updated = $this->db->update(
            'sample_orders',
            [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            ['id' => $sampleOrderId]
        );

        if ($updated) {
            $this->addStatusHistory($sampleOrderId, $status, $note);
        }

        return $updated;
    }

    /**
     * Add status history entry
     */
    private function addStatusHistory(int $sampleOrderId, string $status, ?string $note = null): void
    {
        $this->db->insert('sample_order_history', [
            'sample_order_id' => $sampleOrderId,
            'status' => $status,
            'note' => $note,
            'created_by' => $_SESSION['user_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get sample order by ID with details
     */
    public function getById(int $sampleOrderId): ?array
    {
        $order = $this->db->fetchOne(
            "SELECT so.*,
                    u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                    p.name as product_name, p.sku as product_sku, p.image as product_image,
                    cg.name as customer_group_name
             FROM sample_orders so
             LEFT JOIN users u ON so.user_id = u.id
             LEFT JOIN products p ON so.product_id = p.id
             LEFT JOIN customer_groups cg ON u.customer_group_id = cg.id
             WHERE so.id = ?",
            [$sampleOrderId]
        );

        if (!$order) {
            return null;
        }

        // Decode JSON fields
        $order['shipping_address'] = json_decode($order['shipping_address'], true);

        // Get status history
        $order['status_history'] = $this->db->fetchAll(
            "SELECT sh.*, u.name as created_by_name
             FROM sample_order_history sh
             LEFT JOIN users u ON sh.created_by = u.id
             WHERE sh.sample_order_id = ?
             ORDER BY sh.created_at DESC",
            [$sampleOrderId]
        );

        return $order;
    }

    /**
     * Search sample orders
     */
    public function search(array $filters, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT so.*,
                       u.name as customer_name, u.email as customer_email,
                       p.name as product_name, p.sku as product_sku
                FROM sample_orders so
                LEFT JOIN users u ON so.user_id = u.id
                LEFT JOIN products p ON so.product_id = p.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (so.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR p.name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND so.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND so.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['product_id'])) {
            $sql .= " AND so.product_id = ?";
            $params[] = $filters['product_id'];
        }

        $sql .= " ORDER BY so.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get sample order statistics
     */
    public function getStatistics(): array
    {
        $stats = [
            'total' => 0,
            'by_status' => [],
            'pending_approval' => 0,
            'recent_requests' => []
        ];

        // Total count
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM sample_orders");
        $stats['total'] = $result['count'] ?? 0;

        // Count by status
        $byStatus = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count
             FROM sample_orders
             GROUP BY status"
        );
        foreach ($byStatus as $row) {
            $stats['by_status'][$row['status']] = $row['count'];
        }

        // Pending approval count
        $stats['pending_approval'] = $stats['by_status']['pending'] ?? 0;

        // Recent requests
        $stats['recent_requests'] = $this->search([], 10, 0);

        return $stats;
    }

    /**
     * Approve sample order and convert to shipment
     */
    public function approve(int $sampleOrderId, ?string $trackingNumber = null): bool
    {
        $order = $this->getById($sampleOrderId);
        if (!$order) {
            return false;
        }

        // Update status
        $this->updateStatus($sampleOrderId, 'approved', 'Numune talebi onaylandı');

        // If tracking number provided, update and mark as shipped
        if ($trackingNumber) {
            $this->db->update(
                'sample_orders',
                ['tracking_number' => $trackingNumber],
                ['id' => $sampleOrderId]
            );
            $this->updateStatus($sampleOrderId, 'shipped', "Kargo takip no: {$trackingNumber}");
        }

        return true;
    }

    /**
     * Reject sample order
     */
    public function reject(int $sampleOrderId, string $reason): bool
    {
        return $this->updateStatus($sampleOrderId, 'rejected', "Ret nedeni: {$reason}");
    }

    /**
     * Get status label
     */
    public static function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'Beklemede',
            'approved' => 'Onaylandı',
            'preparing' => 'Hazırlanıyor',
            'shipped' => 'Kargoda',
            'delivered' => 'Teslim Edildi',
            'rejected' => 'Reddedildi',
            default => ucfirst($status)
        };
    }

    /**
     * Get status icon
     */
    public static function getStatusIcon(string $status): string
    {
        return match($status) {
            'pending' => '⏳',
            'approved' => '✅',
            'preparing' => '📦',
            'shipped' => '🚚',
            'delivered' => '✔️',
            'rejected' => '❌',
            default => '📋'
        };
    }
}
