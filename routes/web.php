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

// Admin Product Create - Show Form
$router->get('/admin/products/create', function() {
    if (!App\Core\Auth::check()) {
        redirect('/admin/login');
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    ob_start();
    include APP_PATH . '/Views/admin/products/form.php';
    $html = ob_get_clean();

    return new Response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

// Admin Product Create - Handle Submission
$router->post('/admin/products/create', function() {
    if (!App\Core\Auth::check()) {
        redirect('/admin/login');
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    try {
        $db = container()->get(App\Core\Database::class);
        $productService = new App\Services\ProductService($db);

        // Prepare product data
        $data = [
            'sku' => $_POST['sku'] ?? '',
            'name' => $_POST['name'] ?? '',
            'slug' => generateSlug($_POST['name'] ?? ''),
            'short_description' => $_POST['short_description'] ?? null,
            'description' => $_POST['description'] ?? null,
            'base_price' => (float) ($_POST['base_price'] ?? 0),
            'compare_price' => !empty($_POST['compare_price']) ? (float) $_POST['compare_price'] : null,
            'cost_price' => !empty($_POST['cost_price']) ? (float) $_POST['cost_price'] : null,
            'track_inventory' => isset($_POST['track_inventory']) ? 1 : 0,
            'stock_quantity' => (int) ($_POST['stock_quantity'] ?? 0),
            'low_stock_threshold' => (int) ($_POST['low_stock_threshold'] ?? 10),
            'allow_backorder' => isset($_POST['allow_backorder']) ? 1 : 0,
            'weight_kg' => !empty($_POST['weight_kg']) ? (float) $_POST['weight_kg'] : null,
            'length_mm' => !empty($_POST['length_mm']) ? (float) $_POST['length_mm'] : null,
            'width_mm' => !empty($_POST['width_mm']) ? (float) $_POST['width_mm'] : null,
            'height_mm' => !empty($_POST['height_mm']) ? (float) $_POST['height_mm'] : null,
            'selling_unit' => $_POST['selling_unit'] ?? 'piece',
            'package_quantity' => (int) ($_POST['package_quantity'] ?? 1),
            'status' => $_POST['status'] ?? 'draft',
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_new' => isset($_POST['is_new']) ? 1 : 0,
            'is_b2b_only' => isset($_POST['is_b2b_only']) ? 1 : 0,
            'min_order_quantity' => (int) ($_POST['min_order_quantity'] ?? 1),
            'max_order_quantity' => !empty($_POST['max_order_quantity']) ? (int) $_POST['max_order_quantity'] : null,
            'meta_title' => $_POST['meta_title'] ?? null,
            'meta_description' => $_POST['meta_description'] ?? null,
            'meta_keywords' => $_POST['meta_keywords'] ?? null,
        ];

        // Insert product
        $productId = $db->insert('products', $data);

        // Handle category assignment
        if (!empty($_POST['category_id'])) {
            $db->insert('product_categories', [
                'product_id' => $productId,
                'category_id' => (int) $_POST['category_id'],
                'is_primary' => 1,
            ]);
        }

        // Set success message
        $_SESSION['success_message'] = 'Ürün başarıyla oluşturuldu!';

        // Redirect to products list
        header('Location: /admin/products');
        exit;

    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
        header('Location: /admin/products/create');
        exit;
    }
});

// Admin Product Edit - Show Form
$router->get('/admin/products/edit/{id}', function($id) {
    if (!App\Core\Auth::check()) {
        redirect('/admin/login');
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    try {
        $db = container()->get(App\Core\Database::class);

        // Get product
        $product = $db->fetch("SELECT * FROM products WHERE id = ?", [(int) $id]);

        if (!$product) {
            $_SESSION['error_message'] = 'Ürün bulunamadı!';
            header('Location: /admin/products');
            exit;
        }

        // Get primary category
        $categoryResult = $db->fetch(
            "SELECT category_id FROM product_categories WHERE product_id = ? AND is_primary = 1",
            [(int) $id]
        );
        if ($categoryResult) {
            $product['category_id'] = $categoryResult['category_id'];
        }

        ob_start();
        include APP_PATH . '/Views/admin/products/form.php';
        $html = ob_get_clean();

        return new Response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);

    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
        header('Location: /admin/products');
        exit;
    }
});

// Admin Product Edit - Handle Submission
$router->post('/admin/products/edit/{id}', function($id) {
    if (!App\Core\Auth::check()) {
        redirect('/admin/login');
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    try {
        $db = container()->get(App\Core\Database::class);

        // Prepare product data
        $data = [
            'name' => $_POST['name'] ?? '',
            'slug' => generateSlug($_POST['name'] ?? ''),
            'short_description' => $_POST['short_description'] ?? null,
            'description' => $_POST['description'] ?? null,
            'base_price' => (float) ($_POST['base_price'] ?? 0),
            'compare_price' => !empty($_POST['compare_price']) ? (float) $_POST['compare_price'] : null,
            'cost_price' => !empty($_POST['cost_price']) ? (float) $_POST['cost_price'] : null,
            'track_inventory' => isset($_POST['track_inventory']) ? 1 : 0,
            'stock_quantity' => (int) ($_POST['stock_quantity'] ?? 0),
            'low_stock_threshold' => (int) ($_POST['low_stock_threshold'] ?? 10),
            'allow_backorder' => isset($_POST['allow_backorder']) ? 1 : 0,
            'weight_kg' => !empty($_POST['weight_kg']) ? (float) $_POST['weight_kg'] : null,
            'length_mm' => !empty($_POST['length_mm']) ? (float) $_POST['length_mm'] : null,
            'width_mm' => !empty($_POST['width_mm']) ? (float) $_POST['width_mm'] : null,
            'height_mm' => !empty($_POST['height_mm']) ? (float) $_POST['height_mm'] : null,
            'selling_unit' => $_POST['selling_unit'] ?? 'piece',
            'package_quantity' => (int) ($_POST['package_quantity'] ?? 1),
            'status' => $_POST['status'] ?? 'draft',
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_new' => isset($_POST['is_new']) ? 1 : 0,
            'is_b2b_only' => isset($_POST['is_b2b_only']) ? 1 : 0,
            'min_order_quantity' => (int) ($_POST['min_order_quantity'] ?? 1),
            'max_order_quantity' => !empty($_POST['max_order_quantity']) ? (int) $_POST['max_order_quantity'] : null,
            'meta_title' => $_POST['meta_title'] ?? null,
            'meta_description' => $_POST['meta_description'] ?? null,
            'meta_keywords' => $_POST['meta_keywords'] ?? null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Update product
        $db->update('products', $data, ['id' => (int) $id]);

        // Update category assignment
        if (!empty($_POST['category_id'])) {
            // Delete existing primary category
            $db->query("DELETE FROM product_categories WHERE product_id = ? AND is_primary = 1", [(int) $id]);

            // Insert new primary category
            $db->insert('product_categories', [
                'product_id' => (int) $id,
                'category_id' => (int) $_POST['category_id'],
                'is_primary' => 1,
            ]);
        }

        // Set success message
        $_SESSION['success_message'] = 'Ürün başarıyla güncellendi!';

        // Redirect to products list
        header('Location: /admin/products');
        exit;

    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
        header('Location: /admin/products/edit/' . $id);
        exit;
    }
});

// ============================================
// Admin Categories Routes
// ============================================

// Categories List
$router->get('/admin/categories', function() {
    if (!App\Core\Auth::check()) {
        redirect('/admin/login');
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    ob_start();
    include APP_PATH . '/Views/admin/categories/list.php';
    $html = ob_get_clean();

    return new Response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

// Category Create - Show Form
$router->get('/admin/categories/create', function() {
    if (!App\Core\Auth::check()) {
        redirect('/admin/login');
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    ob_start();
    include APP_PATH . '/Views/admin/categories/form.php';
    $html = ob_get_clean();

    return new Response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

// Category Create - Handle Submission
$router->post('/admin/categories/create', function() {
    if (!App\Core\Auth::check()) {
        redirect('/admin/login');
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    try {
        $db = container()->get(App\Core\Database::class);
        $categoryService = new App\Services\CategoryService($db);

        $data = [
            'parent_id' => !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null,
            'name' => $_POST['name'] ?? '',
            'slug' => generateSlug($_POST['name'] ?? ''),
            'description' => $_POST['description'] ?? null,
            'meta_title' => $_POST['meta_title'] ?? null,
            'meta_description' => $_POST['meta_description'] ?? null,
            'meta_keywords' => $_POST['meta_keywords'] ?? null,
            'icon' => $_POST['icon'] ?? null,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        ];

        $categoryService->create($data);

        $_SESSION['success_message'] = 'Kategori başarıyla oluşturuldu!';
        header('Location: /admin/categories');
        exit;

    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
        header('Location: /admin/categories/create');
        exit;
    }
});

// Category Edit - Show Form
$router->get('/admin/categories/edit/{id}', function($id) {
    if (!App\Core\Auth::check()) {
        redirect('/admin/login');
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    try {
        $db = container()->get(App\Core\Database::class);
        $categoryService = new App\Services\CategoryService($db);

        $category = $categoryService->findById((int) $id);

        if (!$category) {
            $_SESSION['error_message'] = 'Kategori bulunamadı!';
            header('Location: /admin/categories');
            exit;
        }

        ob_start();
        include APP_PATH . '/Views/admin/categories/form.php';
        $html = ob_get_clean();

        return new Response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);

    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
        header('Location: /admin/categories');
        exit;
    }
});

// Category Edit - Handle Submission
$router->post('/admin/categories/edit/{id}', function($id) {
    if (!App\Core\Auth::check()) {
        redirect('/admin/login');
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    try {
        $db = container()->get(App\Core\Database::class);
        $categoryService = new App\Services\CategoryService($db);

        $data = [
            'parent_id' => !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null,
            'name' => $_POST['name'] ?? '',
            'slug' => generateSlug($_POST['name'] ?? ''),
            'description' => $_POST['description'] ?? null,
            'meta_title' => $_POST['meta_title'] ?? null,
            'meta_description' => $_POST['meta_description'] ?? null,
            'meta_keywords' => $_POST['meta_keywords'] ?? null,
            'icon' => $_POST['icon'] ?? null,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        ];

        $categoryService->update((int) $id, $data);

        $_SESSION['success_message'] = 'Kategori başarıyla güncellendi!';
        header('Location: /admin/categories');
        exit;

    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
        header('Location: /admin/categories/edit/' . $id);
        exit;
    }
});

// Category Delete
$router->get('/admin/categories/delete/{id}', function($id) {
    if (!App\Core\Auth::check()) {
        redirect('/admin/login');
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    try {
        $db = container()->get(App\Core\Database::class);
        $categoryService = new App\Services\CategoryService($db);

        $categoryService->delete((int) $id);

        $_SESSION['success_message'] = 'Kategori başarıyla silindi!';
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }

    header('Location: /admin/categories');
    exit;
});

// ============================================
// Admin Orders Routes
// ============================================

$router->get('/admin/orders', function() {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();
    ob_start();
    include APP_PATH . '/Views/admin/orders/list.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->get('/admin/orders/{id}', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    $db = container()->get(App\Core\Database::class);
    $orderService = new App\Services\OrderService($db);
    $order = $orderService->findById((int) $id);

    if (!$order) {
        $_SESSION['error_message'] = 'Sipariş bulunamadı!';
        redirect('/admin/orders');
    }

    ob_start();
    include APP_PATH . '/Views/admin/orders/detail.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->post('/admin/orders/{id}/status', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    $db = container()->get(App\Core\Database::class);
    $orderService = new App\Services\OrderService($db);
    $orderService->updateStatus((int) $id, $_POST['status'] ?? '', $_POST['note'] ?? null);

    $_SESSION['success_message'] = 'Sipariş durumu güncellendi!';
    redirect('/admin/orders/' . $id);
});

// ============================================
// Admin Customers Routes
// ============================================

$router->get('/admin/customers', function() {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();
    ob_start();
    include APP_PATH . '/Views/admin/customers/list.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->get('/admin/customers/{id}', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    $db = container()->get(App\Core\Database::class);
    $customerService = new App\Services\CustomerService($db);
    $customer = $customerService->getWithB2BDetails((int) $id);

    if (!$customer) {
        $_SESSION['error_message'] = 'Müşteri bulunamadı!';
        redirect('/admin/customers');
    }

    ob_start();
    include APP_PATH . '/Views/admin/customers/detail.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->post('/admin/customers/{id}/group', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    $db = container()->get(App\Core\Database::class);
    $customerService = new App\Services\CustomerService($db);

    $groupId = !empty($_POST['customer_group_id']) ? (int) $_POST['customer_group_id'] : null;

    if ($groupId) {
        $customerService->updateGroup((int) $id, $groupId);
    } else {
        $db->update('users', ['customer_group_id' => null], ['id' => (int) $id]);
    }

    $_SESSION['success_message'] = 'Müşteri grubu güncellendi!';
    redirect('/admin/customers/' . $id);
});

// ============================================
// Admin Media Library Routes
// ============================================

$router->get('/admin/media', function() {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();
    ob_start();
    include APP_PATH . '/Views/admin/media/library.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->post('/admin/media/upload', function() {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        if (empty($_FILES['file'])) {
            throw new \Exception('No file uploaded');
        }

        $db = container()->get(App\Core\Database::class);
        $mediaService = new App\Services\MediaService($db);

        $userId = App\Core\Auth::user()['id'] ?? null;

        // Handle single or multiple files
        $files = $_FILES['file'];
        $uploadedCount = 0;

        if (is_array($files['name'])) {
            // Multiple files
            for ($i = 0; $i < count($files['name']); $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];

                if ($file['error'] === UPLOAD_ERR_OK) {
                    $mediaService->upload($file, ['uploaded_by' => $userId]);
                    $uploadedCount++;
                }
            }
        } else {
            // Single file
            $mediaService->upload($files, ['uploaded_by' => $userId]);
            $uploadedCount = 1;
        }

        $_SESSION['success_message'] = "{$uploadedCount} dosya başarıyla yüklendi!";
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }

    redirect('/admin/media');
});
