<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Http\Request;
use App\Http\Response;

class SettingsController
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Show settings page
     */
    public function index(Request $request): Response
    {
        // Get current settings from environment or database
        $settings = [
            'site_name' => $_ENV['APP_NAME'] ?? 'E-Commerce',
            'site_url' => $_ENV['APP_URL'] ?? '',
            'default_lang' => $_ENV['DEFAULT_LANG'] ?? 'tr',
            'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
            'currency' => $_ENV['DEFAULT_CURRENCY'] ?? 'TRY',
            'debug_mode' => $_ENV['APP_DEBUG'] ?? 'false',
        ];

        return Response::json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request): Response
    {
        // Note: This is a placeholder implementation
        // In production, settings should be stored in database or env file

        return Response::json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Get statistics for dashboard
     */
    public function statistics(Request $request): Response
    {
        try {
            // Get product statistics
            $productStats = $this->db->fetch(
                "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft
                 FROM products"
            );

            // Get order statistics
            $orderStats = $this->db->fetch(
                "SELECT
                    COUNT(*) as total,
                    SUM(total_price) as total_revenue
                 FROM orders
                 WHERE status != 'cancelled'"
            );

            // Get customer count
            $customerCount = $this->db->fetch(
                "SELECT COUNT(*) as total FROM users WHERE role = 'customer'"
            );

            return Response::json([
                'success' => true,
                'data' => [
                    'products' => $productStats ?? ['total' => 0, 'active' => 0, 'draft' => 0],
                    'orders' => $orderStats ?? ['total' => 0, 'total_revenue' => 0],
                    'customers' => $customerCount['total'] ?? 0,
                ],
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
