<?php

/**
 * API Routes (v1)
 */

use App\Http\Response;
use App\Controllers\Api\ProductApiController;
use App\Controllers\Admin\ProductController;
use App\Controllers\Admin\MediaController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\Admin\OrderController;
use App\Controllers\Admin\UserController;

// Health check
$router->get('/api/v1/health', function() {
    return Response::json([
        'status' => 'ok',
        'timestamp' => time()
    ]);
});

// Public API - Products
$router->get('/api/v1/products', [ProductApiController::class, 'index']);
$router->get('/api/v1/products/{slug}', [ProductApiController::class, 'show']);
$router->get('/api/v1/products/{id}/price', [ProductApiController::class, 'calculatePrice']);

// Admin API - Products (requires auth)
$router->get('/api/v1/admin/products', [ProductController::class, 'index']);
$router->get('/api/v1/admin/products/{id}', [ProductController::class, 'show']);
$router->post('/api/v1/admin/products', [ProductController::class, 'store']);
$router->put('/api/v1/admin/products/{id}', [ProductController::class, 'update']);
$router->delete('/api/v1/admin/products/{id}', [ProductController::class, 'delete']);
$router->post('/api/v1/admin/products/bulk-import', [ProductController::class, 'bulkImport']);
$router->post('/api/v1/admin/products/{id}/media', [ProductController::class, 'attachMedia']);
$router->delete('/api/v1/admin/products/{id}/media', [ProductController::class, 'detachMedia']);
$router->put('/api/v1/admin/products/{id}/prices', [ProductController::class, 'updatePrices']);
$router->post('/api/v1/admin/products/{id}/duplicate', [ProductController::class, 'duplicate']);
$router->get('/api/v1/admin/products/statistics', [ProductController::class, 'statistics']);
$router->get('/api/v1/admin/products/pending-sync', [ProductController::class, 'pendingSync']);

// Admin API - Media
$router->get('/api/v1/admin/media', [MediaController::class, 'index']);
$router->get('/api/v1/admin/media/{id}', [MediaController::class, 'show']);
$router->post('/api/v1/admin/media/upload', [MediaController::class, 'upload']);
$router->post('/api/v1/admin/media/download-from-url', [MediaController::class, 'downloadFromUrl']);
$router->put('/api/v1/admin/media/{id}', [MediaController::class, 'update']);
$router->delete('/api/v1/admin/media/{id}', [MediaController::class, 'delete']);
$router->post('/api/v1/admin/media/{id}/tags', [MediaController::class, 'attachTag']);
$router->delete('/api/v1/admin/media/{id}/tags', [MediaController::class, 'detachTag']);
$router->post('/api/v1/admin/media/{id}/categories', [MediaController::class, 'attachCategory']);
$router->get('/api/v1/admin/media/by-sku', [MediaController::class, 'getBySku']);
$router->post('/api/v1/admin/media/bulk-attach-by-sku', [MediaController::class, 'bulkAttachBySku']);
$router->get('/api/v1/admin/media/tags', [MediaController::class, 'getTags']);
$router->get('/api/v1/admin/media/categories', [MediaController::class, 'getCategories']);
$router->get('/api/v1/admin/media/statistics', [MediaController::class, 'statistics']);

// Admin API - Categories
$router->get('/api/v1/admin/categories', [CategoryController::class, 'index']);
$router->get('/api/v1/admin/categories/{id}', [CategoryController::class, 'show']);
$router->post('/api/v1/admin/categories', [CategoryController::class, 'store']);
$router->put('/api/v1/admin/categories/{id}', [CategoryController::class, 'update']);
$router->delete('/api/v1/admin/categories/{id}', [CategoryController::class, 'delete']);
$router->get('/api/v1/admin/categories/tree', [CategoryController::class, 'tree']);

// Admin API - Orders
$router->get('/api/v1/admin/orders', [OrderController::class, 'index']);
$router->get('/api/v1/admin/orders/{id}', [OrderController::class, 'show']);
$router->put('/api/v1/admin/orders/{id}/status', [OrderController::class, 'updateStatus']);
$router->get('/api/v1/admin/orders/statistics', [OrderController::class, 'statistics']);

// Admin API - Users
$router->get('/api/v1/admin/users', [UserController::class, 'index']);
$router->get('/api/v1/admin/users/{id}', [UserController::class, 'show']);
$router->post('/api/v1/admin/users', [UserController::class, 'store']);
$router->put('/api/v1/admin/users/{id}', [UserController::class, 'update']);
$router->delete('/api/v1/admin/users/{id}', [UserController::class, 'delete']);
