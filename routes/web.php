<?php

/**
 * Web Routes (Frontend)
 */

use App\Http\Response;
use App\Controllers\Frontend\ProductFrontendController;

// Home
$router->get('/', function() {
    // Simple HTML response that doesn't require database
    $html = '<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Platform</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .status { padding: 20px; background: #e8f5e9; border-left: 4px solid #4caf50; margin: 20px 0; }
        .info { padding: 20px; background: #fff3e0; border-left: 4px solid #ff9800; margin: 20px 0; }
        a { color: #2196f3; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 E-Commerce Platform</h1>

        <div class="status">
            <strong>✅ System Status:</strong> Application is running
        </div>

        <div class="info">
            <strong>ℹ️ Database Status:</strong> Checking connection...
            <p>If you see this page, the application core is working correctly.</p>
            <p>Database-dependent features will be available once the database connection is established.</p>
        </div>

        <h2>Quick Links</h2>
        <ul>
            <li><a href="/admin">🔐 Admin Panel</a></li>
            <li><a href="/healthz">Health Check</a></li>
            <li><a href="/api/v1/health">API Health Check</a></li>
        </ul>

        <p><small>Environment: ' . ($_ENV['APP_ENV'] ?? 'production') . '</small></p>
    </div>
</body>
</html>';

    return new Response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

// Health check
$router->get('/health', function() {
    return Response::json(['status' => 'ok']);
});

// Products
$router->get('/{lang}/kategori/{categorySlug}', [ProductFrontendController::class, 'category']);
$router->get('/{lang}/{categorySlug}/{productSlug}', [ProductFrontendController::class, 'show']);

// Static pages
$router->get('/{lang}/about', function() {
    return Response::html('frontend/about');
});

$router->get('/{lang}/contact', function() {
    return Response::html('frontend/contact');
});

$router->get('/{lang}/projects', function() {
    return Response::html('frontend/projects');
});

// ============================================
// Admin Panel Routes
// ============================================

// Admin Login Page
$router->get('/admin/login', function() {
    // If already logged in, redirect to dashboard
    if (App\Core\Auth::check()) {
        redirect('/admin');
    }

    // Show login form
    $html = file_get_contents(PUBLIC_PATH . '/admin-login.php');
    return new Response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

// Admin Login Handler
$router->post('/admin/login', function() {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (App\Core\Auth::attempt($email, $password)) {
        redirect('/admin');
    }

    // Login failed - show error
    ob_start();
    $error = 'E-posta veya şifre hatalı!';
    include PUBLIC_PATH . '/admin-login.php';
    $html = ob_get_clean();

    return new Response($html, 401, ['Content-Type' => 'text/html; charset=utf-8']);
});

// Admin Logout
$router->get('/admin/logout', function() {
    App\Core\Auth::logout();
    redirect('/admin/login');
});

// Admin Dashboard
$router->get('/admin', function() {
    // Check if logged in
    if (!App\Core\Auth::check()) {
        redirect('/admin/login');
    }

    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Include the dashboard view
    ob_start();
    include APP_PATH . '/Views/admin/dashboard.php';
    $html = ob_get_clean();

    return new Response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

// Admin Products List
$router->get('/admin/products', function() {
    if (!App\Core\Auth::check()) {
        redirect('/admin/login');
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    ob_start();
    include APP_PATH . '/Views/admin/products/list.php';
    $html = ob_get_clean();

    return new Response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
});
