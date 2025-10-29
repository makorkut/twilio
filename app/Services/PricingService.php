<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class PricingService
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Calculate final price for a product
     * Takes into account: currency, quantity (tier pricing), customer group, VAT
     */
    public function calculatePrice(int $productId, string $currencyCode, int $quantity = 1, ?int $userId = null): ?array
    {
        // Get product
        $product = $this->db->query(
            "SELECT * FROM products WHERE id = ? LIMIT 1",
            [$productId]
        );

        if (empty($product)) {
            return null;
        }

        $product = $product[0];

        // Get base price from product_prices_currency
        $priceData = $this->db->query(
            "SELECT * FROM product_prices_currency WHERE product_id = ? AND currency_code = ? LIMIT 1",
            [$productId, $currencyCode]
        );

        if (empty($priceData)) {
            return null;
        }

        $priceData = $priceData[0];

        // Start with base price
        $basePrice = (float) $priceData['base_price'];
        $finalPrice = $basePrice;

        // Apply tier pricing based on quantity
        $tierApplied = null;
        if ($priceData['tier_3_min_qty'] && $quantity >= $priceData['tier_3_min_qty'] && $priceData['tier_3_price']) {
            $finalPrice = (float) $priceData['tier_3_price'];
            $tierApplied = 3;
        } elseif ($priceData['tier_2_min_qty'] && $quantity >= $priceData['tier_2_min_qty'] && $priceData['tier_2_price']) {
            $finalPrice = (float) $priceData['tier_2_price'];
            $tierApplied = 2;
        } elseif ($priceData['tier_1_min_qty'] && $quantity >= $priceData['tier_1_min_qty'] && $priceData['tier_1_price']) {
            $finalPrice = (float) $priceData['tier_1_price'];
            $tierApplied = 1;
        }

        // Apply customer group discount
        $customerGroupDiscount = 0;
        if ($userId) {
            $customerGroupDiscount = $this->getCustomerGroupDiscount($productId, $userId, $currencyCode);
            if ($customerGroupDiscount > 0) {
                $finalPrice = $finalPrice * (1 - $customerGroupDiscount / 100);
            }
        }

        // Get VAT rate
        $vatRate = $this->getVatRate($product['vat_class_id']);

        // Calculate price with/without VAT based on settings
        $priceIncludesVat = env('PRICE_INCLUDES_VAT', 'true') === 'true';

        if ($priceIncludesVat) {
            $priceWithVat = $finalPrice;
            $priceWithoutVat = $finalPrice / (1 + $vatRate / 100);
            $vatAmount = $priceWithVat - $priceWithoutVat;
        } else {
            $priceWithoutVat = $finalPrice;
            $vatAmount = $priceWithoutVat * ($vatRate / 100);
            $priceWithVat = $priceWithoutVat + $vatAmount;
        }

        // Calculate total for quantity
        $totalWithoutVat = $priceWithoutVat * $quantity;
        $totalVat = $vatAmount * $quantity;
        $totalWithVat = $priceWithVat * $quantity;

        // Calculate savings
        $savings = 0;
        $savingsPercent = 0;
        if ($priceData['compare_at_price'] && $priceData['compare_at_price'] > $finalPrice) {
            $savings = ((float) $priceData['compare_at_price'] - $finalPrice) * $quantity;
            $savingsPercent = (($priceData['compare_at_price'] - $finalPrice) / $priceData['compare_at_price']) * 100;
        }

        return [
            'product_id' => $productId,
            'currency' => $currencyCode,
            'quantity' => $quantity,
            'base_price' => $basePrice,
            'final_unit_price' => $finalPrice,
            'price_without_vat' => round($priceWithoutVat, 2),
            'vat_rate' => $vatRate,
            'vat_amount' => round($vatAmount, 2),
            'price_with_vat' => round($priceWithVat, 2),
            'total_without_vat' => round($totalWithoutVat, 2),
            'total_vat' => round($totalVat, 2),
            'total_with_vat' => round($totalWithVat, 2),
            'tier_applied' => $tierApplied,
            'customer_group_discount' => $customerGroupDiscount,
            'compare_at_price' => $priceData['compare_at_price'] ? (float) $priceData['compare_at_price'] : null,
            'savings' => round($savings, 2),
            'savings_percent' => round($savingsPercent, 2),
            'display_price' => round($priceIncludesVat ? $priceWithVat : $priceWithoutVat, 2),
        ];
    }

    /**
     * Get customer group discount for a product
     */
    protected function getCustomerGroupDiscount(int $productId, int $userId, string $currencyCode): float
    {
        // Get user's customer group
        $user = $this->db->query(
            "SELECT customer_group_id FROM users WHERE id = ? LIMIT 1",
            [$userId]
        );

        if (empty($user) || !$user[0]['customer_group_id']) {
            return 0;
        }

        $customerGroupId = (int) $user[0]['customer_group_id'];

        // Check for product-specific group price
        $groupPrice = $this->db->query(
            "SELECT * FROM customer_group_prices
             WHERE customer_group_id = ?
             AND product_id = ?
             AND currency_code = ?
             LIMIT 1",
            [$customerGroupId, $productId, $currencyCode]
        );

        if (!empty($groupPrice)) {
            // Use specific group price (return 0 since price is already set)
            return 0;
        }

        // Use group's general discount
        $group = $this->db->query(
            "SELECT discount_percent FROM customer_groups WHERE id = ? LIMIT 1",
            [$customerGroupId]
        );

        if (!empty($group)) {
            return (float) $group[0]['discount_percent'];
        }

        return 0;
    }

    /**
     * Get VAT rate for a product
     */
    protected function getVatRate(int $vatClassId): float
    {
        if (!$vatClassId) {
            return (float) env('DEFAULT_VAT_RATE', '20');
        }

        $result = $this->db->query(
            "SELECT rate FROM tax_classes WHERE id = ? LIMIT 1",
            [$vatClassId]
        );

        if (!empty($result)) {
            return (float) $result[0]['rate'];
        }

        return (float) env('DEFAULT_VAT_RATE', '20');
    }

    /**
     * Get product price in specific customer group
     */
    public function getCustomerGroupPrice(int $productId, int $customerGroupId, string $currencyCode): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM customer_group_prices
             WHERE customer_group_id = ?
             AND product_id = ?
             AND currency_code = ?
             LIMIT 1",
            [$customerGroupId, $productId, $currencyCode]
        );

        return $result[0] ?? null;
    }

    /**
     * Set customer group price
     */
    public function setCustomerGroupPrice(int $customerGroupId, int $productId, string $currencyCode, float $price): void
    {
        $data = [
            'customer_group_id' => $customerGroupId,
            'product_id' => $productId,
            'currency_code' => $currencyCode,
            'price' => $price,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Check if exists
        $existing = $this->getCustomerGroupPrice($productId, $customerGroupId, $currencyCode);

        if ($existing) {
            $this->db->update('customer_group_prices', ['price' => $price], [
                'customer_group_id' => $customerGroupId,
                'product_id' => $productId,
                'currency_code' => $currencyCode,
            ]);
        } else {
            $this->db->insert('customer_group_prices', $data);
        }
    }

    /**
     * Calculate cart total
     */
    public function calculateCartTotal(array $items, string $currencyCode, ?int $userId = null): array
    {
        $subtotal = 0;
        $totalVat = 0;
        $totalSavings = 0;
        $itemsCalculated = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $quantity = (int) $item['quantity'];

            $priceCalc = $this->calculatePrice($productId, $currencyCode, $quantity, $userId);

            if ($priceCalc) {
                $subtotal += $priceCalc['total_without_vat'];
                $totalVat += $priceCalc['total_vat'];
                $totalSavings += $priceCalc['savings'];

                $itemsCalculated[] = array_merge($item, $priceCalc);
            }
        }

        $total = $subtotal + $totalVat;

        return [
            'items' => $itemsCalculated,
            'currency' => $currencyCode,
            'subtotal' => round($subtotal, 2),
            'total_vat' => round($totalVat, 2),
            'total' => round($total, 2),
            'total_savings' => round($totalSavings, 2),
            'item_count' => count($itemsCalculated),
        ];
    }

    /**
     * Convert price between currencies
     */
    public function convertCurrency(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        // Get latest exchange rate
        $result = $this->db->query(
            "SELECT rate FROM exchange_rates
             WHERE from_currency = ?
             AND to_currency = ?
             AND date <= CURDATE()
             ORDER BY date DESC
             LIMIT 1",
            [$fromCurrency, $toCurrency]
        );

        if (!empty($result)) {
            return $amount * (float) $result[0]['rate'];
        }

        // Fallback: try reverse rate
        $result = $this->db->query(
            "SELECT rate FROM exchange_rates
             WHERE from_currency = ?
             AND to_currency = ?
             AND date <= CURDATE()
             ORDER BY date DESC
             LIMIT 1",
            [$toCurrency, $fromCurrency]
        );

        if (!empty($result)) {
            return $amount / (float) $result[0]['rate'];
        }

        // No exchange rate found
        throw new \Exception("Exchange rate not found for {$fromCurrency} to {$toCurrency}");
    }

    /**
     * Update exchange rate
     */
    public function updateExchangeRate(string $fromCurrency, string $toCurrency, float $rate, ?string $date = null): int
    {
        $date = $date ?? date('Y-m-d');

        $data = [
            'from_currency' => $fromCurrency,
            'to_currency' => $toCurrency,
            'rate' => $rate,
            'date' => $date,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return $this->db->insert('exchange_rates', $data);
    }

    /**
     * Get currency formatting
     */
    public function formatPrice(float $price, string $currencyCode): string
    {
        $currency = $this->db->query(
            "SELECT * FROM currencies WHERE code = ? LIMIT 1",
            [$currencyCode]
        );

        if (empty($currency)) {
            return number_format($price, 2) . ' ' . $currencyCode;
        }

        $currency = $currency[0];

        $formatted = number_format(
            $price,
            (int) $currency['decimal_places'],
            $currency['decimal_separator'] ?? '.',
            $currency['thousands_separator'] ?? ','
        );

        // Apply format template
        $template = $currency['format_template'] ?? '{amount} {symbol}';
        $output = str_replace('{amount}', $formatted, $template);
        $output = str_replace('{symbol}', $currency['symbol'], $output);

        return $output;
    }

    /**
     * Get all available currencies
     */
    public function getAvailableCurrencies(): array
    {
        return $this->db->query(
            "SELECT * FROM currencies WHERE is_active = 1 ORDER BY sort_order ASC"
        );
    }

    /**
     * Get default currency
     */
    public function getDefaultCurrency(): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM currencies WHERE is_default = 1 LIMIT 1"
        );

        return $result[0] ?? null;
    }

    /**
     * Check if user has sufficient credit (B2B)
     */
    public function checkCreditLimit(int $userId, float $orderTotal, string $currencyCode): array
    {
        $user = $this->db->query(
            "SELECT credit_limit, credit_used FROM users WHERE id = ? LIMIT 1",
            [$userId]
        );

        if (empty($user)) {
            return ['approved' => false, 'reason' => 'User not found'];
        }

        $user = $user[0];

        if (!$user['credit_limit']) {
            return ['approved' => true, 'reason' => 'No credit limit set'];
        }

        // Convert order total to default currency for comparison
        $defaultCurrency = $this->getDefaultCurrency();
        if ($currencyCode !== $defaultCurrency['code']) {
            $orderTotal = $this->convertCurrency($orderTotal, $currencyCode, $defaultCurrency['code']);
        }

        $creditLimit = (float) $user['credit_limit'];
        $creditUsed = (float) $user['credit_used'];
        $availableCredit = $creditLimit - $creditUsed;

        if ($orderTotal <= $availableCredit) {
            return [
                'approved' => true,
                'credit_limit' => $creditLimit,
                'credit_used' => $creditUsed,
                'available_credit' => $availableCredit,
                'remaining_after_order' => $availableCredit - $orderTotal,
            ];
        }

        return [
            'approved' => false,
            'reason' => 'Insufficient credit',
            'credit_limit' => $creditLimit,
            'credit_used' => $creditUsed,
            'available_credit' => $availableCredit,
            'required_credit' => $orderTotal,
            'shortage' => $orderTotal - $availableCredit,
        ];
    }

    /**
     * Update user credit usage
     */
    public function updateCreditUsage(int $userId, float $amount, string $operation = 'add'): bool
    {
        $user = $this->db->query(
            "SELECT credit_used FROM users WHERE id = ? LIMIT 1",
            [$userId]
        );

        if (empty($user)) {
            return false;
        }

        $creditUsed = (float) $user[0]['credit_used'];

        if ($operation === 'add') {
            $newCreditUsed = $creditUsed + $amount;
        } elseif ($operation === 'subtract') {
            $newCreditUsed = max(0, $creditUsed - $amount);
        } else {
            throw new \Exception("Invalid operation: {$operation}");
        }

        return $this->db->update('users', [
            'credit_used' => $newCreditUsed,
        ], ['id' => $userId]);
    }

    /**
     * Get price visibility setting
     */
    public function isPriceVisible(?int $userId = null): bool
    {
        $visibility = env('PRICE_VISIBILITY', 'visible');

        if ($visibility === 'visible') {
            return true;
        }

        if ($visibility === 'hidden') {
            return false;
        }

        if ($visibility === 'login_required') {
            return $userId !== null;
        }

        return true;
    }

    /**
     * Calculate profit margin
     */
    public function calculateProfitMargin(int $productId, string $currencyCode): ?array
    {
        $priceData = $this->db->query(
            "SELECT base_price, cost_price FROM product_prices_currency
             WHERE product_id = ? AND currency_code = ?
             LIMIT 1",
            [$productId, $currencyCode]
        );

        if (empty($priceData) || !$priceData[0]['cost_price']) {
            return null;
        }

        $basePrice = (float) $priceData[0]['base_price'];
        $costPrice = (float) $priceData[0]['cost_price'];

        $profit = $basePrice - $costPrice;
        $profitMargin = ($profit / $basePrice) * 100;
        $markup = ($profit / $costPrice) * 100;

        return [
            'base_price' => $basePrice,
            'cost_price' => $costPrice,
            'profit' => round($profit, 2),
            'profit_margin_percent' => round($profitMargin, 2),
            'markup_percent' => round($markup, 2),
        ];
    }
}
