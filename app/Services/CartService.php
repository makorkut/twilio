<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class CartService
{
    protected Database $db;
    protected string $sessionKey = 'cart_session_id';

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Get or create cart session
     */
    protected function getOrCreateSession(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = bin2hex(random_bytes(16));
        }

        return $_SESSION[$this->sessionKey];
    }

    /**
     * Get cart for current session or user
     */
    public function getCart(?int $userId = null): array
    {
        $sessionId = $this->getOrCreateSession();

        $sql = "SELECT * FROM carts WHERE ";
        if ($userId) {
            $sql .= "user_id = ? LIMIT 1";
            $params = [$userId];
        } else {
            $sql .= "session_id = ? LIMIT 1";
            $params = [$sessionId];
        }

        $cart = $this->db->fetch($sql, $params);

        if (!$cart) {
            // Create new cart
            $cartData = [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'currency_code' => 'TRY', // Default currency
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $cartId = $this->db->insert('carts', $cartData);
            $cart = $this->db->fetch("SELECT * FROM carts WHERE id = ?", [$cartId]);
        }

        // Get cart items
        $cart['items'] = $this->getCartItems((int) $cart['id']);
        $cart['totals'] = $this->calculateTotals((int) $cart['id']);

        return $cart;
    }

    /**
     * Get cart items with product details
     */
    public function getCartItems(int $cartId): array
    {
        return $this->db->fetchAll(
            "SELECT
                ci.*,
                p.name,
                p.sku,
                p.slug,
                p.base_price,
                p.selling_unit,
                p.stock_quantity,
                p.track_inventory
            FROM cart_items ci
            JOIN products p ON p.id = ci.product_id
            WHERE ci.cart_id = ?
            ORDER BY ci.created_at DESC",
            [$cartId]
        );
    }

    /**
     * Add item to cart
     */
    public function addItem(int $productId, int $quantity = 1, ?array $options = null, ?int $userId = null): int
    {
        $cart = $this->getCart($userId);
        $cartId = (int) $cart['id'];

        // Check if product exists
        $product = $this->db->fetch(
            "SELECT * FROM products WHERE id = ?",
            [$productId]
        );

        if (!$product) {
            throw new \Exception('Product not found');
        }

        // Check stock
        if ($product['track_inventory'] && $product['stock_quantity'] < $quantity) {
            throw new \Exception('Insufficient stock');
        }

        // Check if item already exists in cart
        $optionsJson = $options ? json_encode($options) : null;
        $existingItem = $this->db->fetch(
            "SELECT * FROM cart_items
             WHERE cart_id = ? AND product_id = ? AND options_json = ?",
            [$cartId, $productId, $optionsJson]
        );

        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            $this->db->update('cart_items',
                ['quantity' => $newQuantity],
                ['id' => $existingItem['id']]
            );
            return (int) $existingItem['id'];
        } else {
            // Add new item
            $itemData = [
                'cart_id' => $cartId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $product['base_price'],
                'options_json' => $optionsJson,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            return $this->db->insert('cart_items', $itemData);
        }
    }

    /**
     * Update item quantity
     */
    public function updateQuantity(int $itemId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->removeItem($itemId);
        }

        // Get item to check stock
        $item = $this->db->fetch(
            "SELECT ci.*, p.track_inventory, p.stock_quantity
             FROM cart_items ci
             JOIN products p ON p.id = ci.product_id
             WHERE ci.id = ?",
            [$itemId]
        );

        if (!$item) {
            throw new \Exception('Cart item not found');
        }

        if ($item['track_inventory'] && $item['stock_quantity'] < $quantity) {
            throw new \Exception('Insufficient stock');
        }

        return $this->db->update('cart_items',
            ['quantity' => $quantity],
            ['id' => $itemId]
        );
    }

    /**
     * Remove item from cart
     */
    public function removeItem(int $itemId): bool
    {
        return $this->db->delete('cart_items', ['id' => $itemId]);
    }

    /**
     * Clear cart
     */
    public function clearCart(?int $userId = null): bool
    {
        $cart = $this->getCart($userId);
        return $this->db->delete('cart_items', ['cart_id' => $cart['id']]);
    }

    /**
     * Calculate cart totals
     */
    public function calculateTotals(int $cartId): array
    {
        $items = $this->getCartItems($cartId);

        $subtotal = 0;
        $taxTotal = 0;
        $discountTotal = 0;

        foreach ($items as $item) {
            $lineTotal = $item['unit_price'] * $item['quantity'];
            $subtotal += $lineTotal;
        }

        $total = $subtotal + $taxTotal - $discountTotal;

        return [
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'discount_total' => $discountTotal,
            'shipping_total' => 0, // Will be calculated at checkout
            'total' => $total,
            'item_count' => count($items),
        ];
    }

    /**
     * Apply coupon to cart
     */
    public function applyCoupon(string $couponCode, ?int $userId = null): bool
    {
        $cart = $this->getCart($userId);

        // Get coupon
        $coupon = $this->db->fetch(
            "SELECT * FROM coupons
             WHERE code = ?
             AND is_active = 1
             AND (valid_from IS NULL OR valid_from <= NOW())
             AND (valid_until IS NULL OR valid_until >= NOW())
             LIMIT 1",
            [$couponCode]
        );

        if (!$coupon) {
            throw new \Exception('Invalid or expired coupon');
        }

        // Check usage limit
        if ($coupon['max_uses'] > 0) {
            $usageCount = $this->db->fetch(
                "SELECT COUNT(*) as count FROM orders WHERE coupon_id = ?",
                [$coupon['id']]
            );

            if ($usageCount['count'] >= $coupon['max_uses']) {
                throw new \Exception('Coupon usage limit reached');
            }
        }

        // Apply coupon to cart
        return $this->db->update('carts',
            ['coupon_id' => $coupon['id']],
            ['id' => $cart['id']]
        );
    }

    /**
     * Merge guest cart with user cart (on login)
     */
    public function mergeGuestCart(int $userId): void
    {
        $sessionId = $this->getOrCreateSession();

        // Get guest cart
        $guestCart = $this->db->fetch(
            "SELECT * FROM carts WHERE session_id = ? AND user_id IS NULL",
            [$sessionId]
        );

        if (!$guestCart) {
            return;
        }

        // Get or create user cart
        $userCart = $this->db->fetch(
            "SELECT * FROM carts WHERE user_id = ?",
            [$userId]
        );

        if (!$userCart) {
            // Convert guest cart to user cart
            $this->db->update('carts',
                ['user_id' => $userId],
                ['id' => $guestCart['id']]
            );
        } else {
            // Merge items
            $guestItems = $this->db->fetchAll(
                "SELECT * FROM cart_items WHERE cart_id = ?",
                [$guestCart['id']]
            );

            foreach ($guestItems as $item) {
                $this->addItem(
                    (int) $item['product_id'],
                    (int) $item['quantity'],
                    json_decode($item['options_json'], true),
                    $userId
                );
            }

            // Delete guest cart
            $this->db->delete('cart_items', ['cart_id' => $guestCart['id']]);
            $this->db->delete('carts', ['id' => $guestCart['id']]);
        }
    }

    /**
     * Convert cart to order
     */
    public function convertToOrder(int $cartId, array $orderData): int
    {
        $this->db->beginTransaction();

        try {
            $cart = $this->db->fetch("SELECT * FROM carts WHERE id = ?", [$cartId]);
            if (!$cart) {
                throw new \Exception('Cart not found');
            }

            $totals = $this->calculateTotals($cartId);

            // Create order
            $orderData = array_merge([
                'user_id' => $cart['user_id'],
                'currency_code' => $cart['currency_code'],
                'subtotal' => $totals['subtotal'],
                'tax_total' => $totals['tax_total'],
                'discount_total' => $totals['discount_total'],
                'shipping_total' => $totals['shipping_total'],
                'total' => $totals['total'],
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ], $orderData);

            $orderId = $this->db->insert('orders', $orderData);

            // Copy cart items to order items
            $items = $this->getCartItems($cartId);
            foreach ($items as $item) {
                $this->db->insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['unit_price'] * $item['quantity'],
                    'options_json' => $item['options_json'],
                ]);
            }

            // Clear cart
            $this->db->delete('cart_items', ['cart_id' => $cartId]);
            $this->db->update('carts',
                ['status' => 'converted'],
                ['id' => $cartId]
            );

            $this->db->commit();

            return $orderId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
