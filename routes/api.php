<?php
/**
 * API Routes v1
 */

use App\Http\Response;
use App\Controllers\Api\ProductApiController;

// API Base endpoint
$router->get('/api', function() {
    return Response::json([
        'name' => 'E-Commerce API',
        'version' => '1.0',
        'endpoints' => [
            'health' => '/api/v1/health',
            'products' => '/api/v1/products',
            'orders' => '/api/v1/orders',
            'cart' => '/api/v1/cart'
        ]
    ]);
});

// API Health
$router->get('/api/v1/health', function() {
    return Response::json(['status' => 'ok', 'version' => '1.0']);
});

// Products API
$router->get('/api/v1/products', [ProductApiController::class, 'index']);
$router->get('/api/v1/products/{id}', [ProductApiController::class, 'show']);
$router->post('/api/v1/products', [ProductApiController::class, 'store']);

// Orders API
$router->get('/api/v1/orders', function() {
    $db = container()->get(App\Core\Database::class);
    $orderService = new App\Services\OrderService($db);
    $orders = $orderService->search([], (int)($_GET['limit'] ?? 50), 0);
    return Response::json(['success' => true, 'data' => $orders]);
});

$router->post('/api/v1/orders', function() {
    $db = container()->get(App\Core\Database::class);
    $orderService = new App\Services\OrderService($db);
    $data = json_decode(file_get_contents('php://input'), true);
    $orderId = $orderService->create($data);
    return Response::json(['success' => true, 'data' => ['id' => $orderId]], 201);
});

// Cart API
$router->post('/api/v1/cart/items', function() {
    $db = container()->get(App\Core\Database::class);
    $cartService = new App\Services\CartService($db);
    $data = json_decode(file_get_contents('php://input'), true);
    $itemId = $cartService->addItem((int)$data['product_id'], (int)($data['quantity'] ?? 1));
    return Response::json(['success' => true, 'data' => ['item_id' => $itemId]], 201);
});

// Webhook
$router->post('/api/v1/webhooks/product-sync', function() {
    $data = json_decode(file_get_contents('php://input'), true);
    $db = container()->get(App\Core\Database::class);

    $db->insert('webhook_logs', [
        'event_type' => 'product_sync',
        'payload' => json_encode($data),
        'received_at' => date('Y-m-d H:i:s')
    ]);

    return Response::json(['success' => true, 'message' => 'Webhook received']);
});
