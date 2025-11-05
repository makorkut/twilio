<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class CustomerService
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Get customer by user ID
     */
    public function findByUserId(int $userId): ?array
    {
        return $this->db->fetch(
            "SELECT u.*, cg.name as group_name, cg.discount_percentage
             FROM users u
             LEFT JOIN customer_groups cg ON cg.id = u.customer_group_id
             WHERE u.id = ?",
            [$userId]
        );
    }

    /**
     * Get customer with B2B details
     */
    public function getWithB2BDetails(int $userId): ?array
    {
        $customer = $this->findByUserId($userId);
        if (!$customer) {
            return null;
        }

        // Get credit info
        $creditInfo = $this->db->fetch(
            "SELECT * FROM customer_credit WHERE user_id = ?",
            [$userId]
        );

        if ($creditInfo) {
            $customer['credit_limit'] = $creditInfo['credit_limit'];
            $customer['credit_used'] = $creditInfo['credit_used'];
            $customer['credit_available'] = $creditInfo['credit_limit'] - $creditInfo['credit_used'];
            $customer['payment_terms'] = $creditInfo['payment_terms'];
        }

        // Get addresses
        $customer['addresses'] = $this->getAddresses($userId);

        // Get order statistics
        $stats = $this->db->fetch(
            "SELECT
                COUNT(*) as total_orders,
                SUM(total) as total_spent,
                AVG(total) as average_order_value
             FROM orders
             WHERE user_id = ? AND status != 'cancelled'",
            [$userId]
        );

        $customer['order_stats'] = $stats;

        return $customer;
    }

    /**
     * Get customer addresses
     */
    public function getAddresses(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM user_addresses
             WHERE user_id = ?
             ORDER BY is_default DESC, id DESC",
            [$userId]
        );
    }

    /**
     * Create address
     */
    public function createAddress(int $userId, array $data): int
    {
        $this->db->beginTransaction();

        try {
            // If this is default, unset others
            if ($data['is_default'] ?? false) {
                $this->db->query(
                    "UPDATE user_addresses SET is_default = 0 WHERE user_id = ?",
                    [$userId]
                );
            }

            $addressData = [
                'user_id' => $userId,
                'address_type' => $data['address_type'] ?? 'shipping',
                'company_name' => $data['company_name'] ?? null,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'address_line_1' => $data['address_line_1'],
                'address_line_2' => $data['address_line_2'] ?? null,
                'city' => $data['city'],
                'state_province' => $data['state_province'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country' => $data['country'] ?? 'TR',
                'is_default' => $data['is_default'] ?? 0,
            ];

            $addressId = $this->db->insert('user_addresses', $addressData);

            $this->db->commit();

            return $addressId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update address
     */
    public function updateAddress(int $addressId, array $data): bool
    {
        $this->db->beginTransaction();

        try {
            // Get address to check ownership
            $address = $this->db->fetch(
                "SELECT * FROM user_addresses WHERE id = ?",
                [$addressId]
            );

            if (!$address) {
                throw new \Exception('Address not found');
            }

            // If setting as default, unset others
            if ($data['is_default'] ?? false) {
                $this->db->query(
                    "UPDATE user_addresses SET is_default = 0 WHERE user_id = ?",
                    [$address['user_id']]
                );
            }

            $updateData = [
                'company_name' => $data['company_name'] ?? null,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'address_line_1' => $data['address_line_1'],
                'address_line_2' => $data['address_line_2'] ?? null,
                'city' => $data['city'],
                'state_province' => $data['state_province'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country' => $data['country'] ?? 'TR',
                'is_default' => $data['is_default'] ?? 0,
            ];

            $this->db->update('user_addresses', $updateData, 'id = ?', [$addressId]);

            $this->db->commit();

            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete address
     */
    public function deleteAddress(int $addressId): bool
    {
        return $this->db->delete('user_addresses', 'id = ?', [$addressId]);
    }

    /**
     * Update customer group
     */
    public function updateGroup(int $userId, int $customerGroupId): bool
    {
        return $this->db->update('users',
            ['customer_group_id' => $customerGroupId],
            'id = ?',
            [$userId]
        );
    }

    /**
     * Set credit limit
     */
    public function setCreditLimit(int $userId, float $creditLimit, ?string $paymentTerms = null): bool
    {
        // Check if credit record exists
        $existing = $this->db->fetch(
            "SELECT * FROM customer_credit WHERE user_id = ?",
            [$userId]
        );

        $data = [
            'credit_limit' => $creditLimit,
            'payment_terms' => $paymentTerms ?? 'net30',
        ];

        if ($existing) {
            return $this->db->update('customer_credit', $data, 'user_id = ?', [$userId]);
        } else {
            $data['user_id'] = $userId;
            $data['credit_used'] = 0;
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('customer_credit', $data);
            return true;
        }
    }

    /**
     * Use credit (on order)
     */
    public function useCredit(int $userId, float $amount): bool
    {
        return $this->db->query(
            "UPDATE customer_credit
             SET credit_used = credit_used + ?
             WHERE user_id = ?",
            [$amount, $userId]
        );
    }

    /**
     * Release credit (on order cancellation)
     */
    public function releaseCredit(int $userId, float $amount): bool
    {
        return $this->db->query(
            "UPDATE customer_credit
             SET credit_used = GREATEST(0, credit_used - ?)
             WHERE user_id = ?",
            [$amount, $userId]
        );
    }

    /**
     * Check if customer can place order (credit limit check)
     */
    public function canPlaceOrder(int $userId, float $orderTotal): bool
    {
        $creditInfo = $this->db->fetch(
            "SELECT * FROM customer_credit WHERE user_id = ?",
            [$userId]
        );

        if (!$creditInfo) {
            return true; // No credit limit set
        }

        $availableCredit = $creditInfo['credit_limit'] - $creditInfo['credit_used'];

        return $availableCredit >= $orderTotal;
    }

    /**
     * Get tier pricing for customer
     */
    public function getTierPrice(int $userId, int $productId, int $quantity): ?float
    {
        $customer = $this->findByUserId($userId);

        if (!$customer || !$customer['customer_group_id']) {
            return null; // No special pricing
        }

        // Get tier pricing
        $tierPrice = $this->db->fetch(
            "SELECT * FROM tier_pricing
             WHERE product_id = ?
             AND customer_group_id = ?
             AND min_quantity <= ?
             ORDER BY min_quantity DESC
             LIMIT 1",
            [$productId, $customer['customer_group_id'], $quantity]
        );

        return $tierPrice ? (float) $tierPrice['price'] : null;
    }

    /**
     * Search customers
     */
    public function search(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ['role != ?'];
        $params = ['admin'];

        if (!empty($filters['search'])) {
            $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['customer_group_id'])) {
            $where[] = "customer_group_id = ?";
            $params[] = $filters['customer_group_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT u.*, cg.name as group_name
                FROM users u
                LEFT JOIN customer_groups cg ON cg.id = u.customer_group_id
                {$whereClause}
                ORDER BY u.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get customer statistics
     */
    public function getStatistics(?int $customerGroupId = null): array
    {
        $where = ['role != ?'];
        $params = ['admin'];

        if ($customerGroupId) {
            $where[] = "customer_group_id = ?";
            $params[] = $customerGroupId;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $stats = $this->db->fetch(
            "SELECT
                COUNT(*) as total_customers,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_customers,
                SUM(CASE WHEN customer_group_id IS NOT NULL THEN 1 ELSE 0 END) as b2b_customers
            FROM users
            {$whereClause}",
            $params
        );

        return $stats ?: [];
    }

    /**
     * Get top customers by revenue
     */
    public function getTopCustomers(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT
                u.id,
                u.name,
                u.email,
                COUNT(o.id) as order_count,
                SUM(o.total) as total_spent
            FROM users u
            INNER JOIN orders o ON o.user_id = u.id
            WHERE o.status != 'cancelled'
            GROUP BY u.id
            ORDER BY total_spent DESC
            LIMIT ?",
            [$limit]
        );
    }

    /**
     * Create customer note
     */
    public function addNote(int $userId, string $note, ?int $createdBy = null): int
    {
        return $this->db->insert('customer_notes', [
            'user_id' => $userId,
            'note' => $note,
            'created_by' => $createdBy,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get customer notes
     */
    public function getNotes(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT cn.*, u.name as created_by_name
             FROM customer_notes cn
             LEFT JOIN users u ON u.id = cn.created_by
             WHERE cn.user_id = ?
             ORDER BY cn.created_at DESC",
            [$userId]
        );
    }
}
