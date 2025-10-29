<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Services\PricingService;
use App\Http\Request;
use App\Http\Response;

class OrderController
{
    protected Database $db;
    protected PricingService $pricingService;

    public function __construct(Database $db, PricingService $pricingService)
    {
        $this->db = $db;
        $this->pricingService = $pricingService;
    }

    public function index(Request $request): Response
    {
        $page = (int) ($request->get('page') ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $status = $request->get('status');
        $search = $request->get('search');

        $where = ['o.deleted_at IS NULL'];
        $params = [];

        if ($status) {
            $where[] = 'o.status = ?';
            $params[] = $status;
        }

        if ($search) {
            $where[] = '(o.order_number LIKE ? OR u.email LIKE ? OR u.name LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $orders = $this->db->query(
            "SELECT o.*, u.name as customer_name, u.email as customer_email
             FROM orders o
             LEFT JOIN users u ON u.id = o.user_id
             {$whereClause}
             ORDER BY o.id DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = $this->db->query(
            "SELECT COUNT(*) as total FROM orders o LEFT JOIN users u ON u.id = o.user_id {$whereClause}",
            $params
        )[0]['total'];

        return Response::json([
            'success' => true,
            'data' => $orders,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'pages' => ceil($total / $perPage),
            ],
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $order = $this->db->query(
            "SELECT o.*, u.name as customer_name, u.email as customer_email
             FROM orders o
             LEFT JOIN users u ON u.id = o.user_id
             WHERE o.id = ?
             LIMIT 1",
            [$id]
        )[0] ?? null;

        if (!$order) {
            return Response::json(['error' => 'Order not found'], 404);
        }

        // Get order items
        $order['items'] = $this->db->query(
            "SELECT oi.*, p.sku, pl.name as product_name
             FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             LEFT JOIN product_lang pl ON pl.product_id = p.id AND pl.lang = ?
             WHERE oi.order_id = ?",
            [env('DEFAULT_LANG', 'tr'), $id]
        );

        // Get status history
        $order['status_history'] = $this->db->query(
            "SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at DESC",
            [$id]
        );

        return Response::json(['success' => true, 'data' => $order]);
    }

    public function updateStatus(Request $request, int $id): Response
    {
        $newStatus = $request->get('status');
        $notes = $request->get('notes');

        if (!$newStatus) {
            return Response::json(['error' => 'Status is required'], 400);
        }

        try {
            $this->db->update('orders', [
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['id' => $id]);

            // Log status change
            $this->db->insert('order_status_history', [
                'order_id' => $id,
                'status' => $newStatus,
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return Response::json(['success' => true, 'message' => 'Order status updated']);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function statistics(Request $request): Response
    {
        $stats = $this->db->query(
            "SELECT
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_order_value
             FROM orders
             WHERE deleted_at IS NULL"
        );

        return Response::json(['success' => true, 'data' => $stats[0]]);
    }
}
