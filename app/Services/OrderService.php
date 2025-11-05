<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class OrderService
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Create order
     */
    public function create(array $data): int
    {
        $this->db->beginTransaction();

        try {
            // Generate order number
            $orderNumber = $this->generateOrderNumber();

            $orderData = [
                'order_number' => $orderNumber,
                'user_id' => $data['user_id'] ?? null,
                'customer_group_id' => $data['customer_group_id'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'payment_status' => $data['payment_status'] ?? 'pending',
                'payment_method' => $data['payment_method'] ?? null,
                'currency_code' => $data['currency_code'] ?? 'TRY',
                'exchange_rate' => $data['exchange_rate'] ?? 1.0,

                // Amounts
                'subtotal' => $data['subtotal'] ?? 0,
                'tax_total' => $data['tax_total'] ?? 0,
                'shipping_total' => $data['shipping_total'] ?? 0,
                'discount_total' => $data['discount_total'] ?? 0,
                'total' => $data['total'] ?? 0,

                // Addresses
                'billing_address_json' => isset($data['billing_address']) ? json_encode($data['billing_address']) : null,
                'shipping_address_json' => isset($data['shipping_address']) ? json_encode($data['shipping_address']) : null,

                // Customer info (for guest checkouts)
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,

                // Other
                'notes' => $data['notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'coupon_id' => $data['coupon_id'] ?? null,

                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $orderId = $this->db->insert('orders', $orderData);

            // Insert order items
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $this->addOrderItem($orderId, $item);
                }
            }

            // Create status history
            $this->addStatusHistory($orderId, $orderData['status'], 'Order created');

            $this->db->commit();

            return $orderId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Add item to order
     */
    protected function addOrderItem(int $orderId, array $item): int
    {
        $itemData = [
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'variant_id' => $item['variant_id'] ?? null,
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'total_price' => $item['quantity'] * $item['unit_price'],
            'tax_rate' => $item['tax_rate'] ?? 0,
            'tax_amount' => $item['tax_amount'] ?? 0,
            'discount_amount' => $item['discount_amount'] ?? 0,
            'options_json' => isset($item['options']) ? json_encode($item['options']) : null,
            'product_snapshot_json' => isset($item['product_snapshot']) ? json_encode($item['product_snapshot']) : null,
        ];

        return $this->db->insert('order_items', $itemData);
    }

    /**
     * Update order status
     */
    public function updateStatus(int $orderId, string $status, ?string $note = null): bool
    {
        $this->db->beginTransaction();

        try {
            // Update order
            $this->db->update('orders',
                [
                    'status' => $status,
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                'id = ?',
                [$orderId]
            );

            // Add status history
            $this->addStatusHistory($orderId, $status, $note);

            $this->db->commit();

            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Add status history entry
     */
    protected function addStatusHistory(int $orderId, string $status, ?string $note = null): int
    {
        return $this->db->insert('order_status_history', [
            'order_id' => $orderId,
            'status' => $status,
            'note' => $note,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(int $orderId, string $paymentStatus, ?array $paymentData = null): bool
    {
        $updateData = [
            'payment_status' => $paymentStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($paymentStatus === 'paid') {
            $updateData['paid_at'] = date('Y-m-d H:i:s');
        }

        if ($paymentData) {
            $updateData['payment_data_json'] = json_encode($paymentData);
        }

        return $this->db->update('orders', $updateData, 'id = ?', [$orderId]);
    }

    /**
     * Find order by ID
     */
    public function findById(int $orderId): ?array
    {
        $order = $this->db->fetch(
            "SELECT * FROM orders WHERE id = ?",
            [$orderId]
        );

        if (!$order) {
            return null;
        }

        // Get items
        $order['items'] = $this->getOrderItems($orderId);

        // Get status history
        $order['status_history'] = $this->getStatusHistory($orderId);

        // Decode JSON fields
        if ($order['billing_address_json']) {
            $order['billing_address'] = json_decode($order['billing_address_json'], true);
        }
        if ($order['shipping_address_json']) {
            $order['shipping_address'] = json_decode($order['shipping_address_json'], true);
        }

        return $order;
    }

    /**
     * Find order by order number
     */
    public function findByOrderNumber(string $orderNumber): ?array
    {
        $order = $this->db->fetch(
            "SELECT * FROM orders WHERE order_number = ?",
            [$orderNumber]
        );

        if (!$order) {
            return null;
        }

        return $this->findById((int) $order['id']);
    }

    /**
     * Get order items
     */
    public function getOrderItems(int $orderId): array
    {
        return $this->db->fetchAll(
            "SELECT
                oi.*,
                p.name as product_name,
                p.sku as product_sku
            FROM order_items oi
            LEFT JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = ?
            ORDER BY oi.id",
            [$orderId]
        );
    }

    /**
     * Get status history
     */
    public function getStatusHistory(int $orderId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM order_status_history
             WHERE order_id = ?
             ORDER BY created_at DESC",
            [$orderId]
        );
    }

    /**
     * Get orders by user
     */
    public function getByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM orders
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
    }

    /**
     * Get orders by status
     */
    public function getByStatus(string $status, int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM orders
             WHERE status = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?",
            [$status, $limit, $offset]
        );
    }

    /**
     * Search orders
     */
    public function search(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(order_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_status'])) {
            $where[] = "payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT * FROM orders {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Generate unique order number
     */
    protected function generateOrderNumber(): string
    {
        // Format: ORD-YYYYMMDD-XXXX
        $prefix = 'ORD';
        $date = date('Ymd');

        // Get daily sequence
        $lastOrder = $this->db->fetch(
            "SELECT order_number FROM orders
             WHERE order_number LIKE ?
             ORDER BY id DESC
             LIMIT 1",
            ["{$prefix}-{$date}-%"]
        );

        if ($lastOrder) {
            $parts = explode('-', $lastOrder['order_number']);
            $sequence = (int) end($parts) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    /**
     * Cancel order
     */
    public function cancel(int $orderId, ?string $reason = null): bool
    {
        return $this->updateStatus($orderId, 'cancelled', $reason);
    }

    /**
     * Process refund
     */
    public function refund(int $orderId, float $amount, ?string $reason = null): bool
    {
        $this->db->beginTransaction();

        try {
            $order = $this->findById($orderId);
            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Create refund record
            $refundData = [
                'order_id' => $orderId,
                'amount' => $amount,
                'currency_code' => $order['currency_code'],
                'reason' => $reason,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $this->db->insert('order_refunds', $refundData);

            // Update order status
            if ($amount >= $order['total']) {
                $this->updateStatus($orderId, 'refunded', 'Full refund processed');
                $this->updatePaymentStatus($orderId, 'refunded');
            } else {
                $this->updateStatus($orderId, 'partially_refunded', 'Partial refund processed');
            }

            $this->db->commit();

            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Add shipment tracking
     */
    public function addTracking(int $orderId, string $carrier, string $trackingNumber): bool
    {
        $this->db->beginTransaction();

        try {
            // Update order
            $this->db->update('orders',
                [
                    'shipping_carrier' => $carrier,
                    'shipping_tracking_number' => $trackingNumber,
                    'shipped_at' => date('Y-m-d H:i:s'),
                ],
                'id = ?',
                [$orderId]
            );

            // Update status
            $this->updateStatus($orderId, 'shipped', "Shipped via {$carrier}: {$trackingNumber}");

            $this->db->commit();

            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get order statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where[] = "created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stats = $this->db->fetch(
            "SELECT
                COUNT(*) as total_orders,
                SUM(total) as total_revenue,
                AVG(total) as average_order_value,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
            FROM orders
            {$whereClause}",
            $params
        );

        return $stats ?: [];
    }

    /**
     * Reduce stock after order
     */
    public function reduceStock(int $orderId): bool
    {
        $items = $this->getOrderItems($orderId);

        foreach ($items as $item) {
            if (!$item['product_id']) {
                continue;
            }

            // Reduce product stock
            $this->db->query(
                "UPDATE products
                 SET stock_quantity = stock_quantity - ?
                 WHERE id = ? AND track_inventory = 1",
                [$item['quantity'], $item['product_id']]
            );

            // Reduce variant stock if applicable
            if ($item['variant_id']) {
                $this->db->query(
                    "UPDATE product_variants
                     SET stock_quantity = stock_quantity - ?
                     WHERE id = ?",
                    [$item['quantity'], $item['variant_id']]
                );
            }
        }

        return true;
    }

    /**
     * Restore stock (on cancellation)
     */
    public function restoreStock(int $orderId): bool
    {
        $items = $this->getOrderItems($orderId);

        foreach ($items as $item) {
            if (!$item['product_id']) {
                continue;
            }

            // Restore product stock
            $this->db->query(
                "UPDATE products
                 SET stock_quantity = stock_quantity + ?
                 WHERE id = ? AND track_inventory = 1",
                [$item['quantity'], $item['product_id']]
            );

            // Restore variant stock if applicable
            if ($item['variant_id']) {
                $this->db->query(
                    "UPDATE product_variants
                     SET stock_quantity = stock_quantity + ?
                     WHERE id = ?",
                    [$item['quantity'], $item['variant_id']]
                );
            }
        }

        return true;
    }
}
