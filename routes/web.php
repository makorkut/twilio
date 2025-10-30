<?php

/**
 * Web Routes (Frontend)
 */

use App\Http\Response;
use App\Controllers\Frontend\ProductFrontendController;

// Frontend Routes
$router->get('/', function() {
    ob_start();
    include APP_PATH . '/Views/frontend/home.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->get('/products', function() {
    ob_start();
    include APP_PATH . '/Views/frontend/products.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->get('/product/{slug}', function($slug) {
    $db = container()->get(App\Core\Database::class);
    $product = $db->fetch("SELECT * FROM products WHERE slug = ? AND status = 'active'", [$slug]);
    if (!$product) redirect('/products');
    ob_start();
    include APP_PATH . '/Views/frontend/product-detail.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->get('/cart', function() {
    ob_start();
    include APP_PATH . '/Views/frontend/cart.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->post('/cart/add', function() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $db = container()->get(App\Core\Database::class);
    $cartService = new App\Services\CartService($db);
    $cartService->addItem((int)$_POST['product_id'], (int)($_POST['quantity'] ?? 1));
    redirect('/cart');
});

$router->post('/cart/update', function() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $db = container()->get(App\Core\Database::class);
    $cartService = new App\Services\CartService($db);
    $itemId = (int)$_POST['item_id'];
    $item = $db->fetch("SELECT * FROM cart_items WHERE id = ?", [$itemId]);
    $newQty = $_POST['action'] === 'increase' ? $item['quantity'] + 1 : max(1, $item['quantity'] - 1);
    $cartService->updateQuantity($itemId, $newQty);
    redirect('/cart');
});

$router->post('/cart/remove', function() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $db = container()->get(App\Core\Database::class);
    $cartService = new App\Services\CartService($db);
    $cartService->removeItem((int)$_POST['item_id']);
    redirect('/cart');
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
            'coverage_per_unit' => isset($_POST['enable_calculator']) && !empty($_POST['coverage_per_unit']) ? (float) $_POST['coverage_per_unit'] : null,
            'calculator_type' => isset($_POST['enable_calculator']) ? ($_POST['calculator_type'] ?? 'area') : null,
            'coverage_unit' => isset($_POST['enable_calculator']) ? ($_POST['coverage_unit'] ?? 'm²') : null,
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

// Admin Technical Documents
$router->get('/admin/documents', function() {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    ob_start();
    include APP_PATH . '/Views/admin/documents/list.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->get('/admin/documents/upload', function() {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    ob_start();
    include APP_PATH . '/Views/admin/documents/upload.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->post('/admin/documents/upload', function() {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        if (empty($_FILES['file'])) {
            throw new \Exception('Dosya seçilmedi');
        }

        $db = container()->get(App\Core\Database::class);
        $documentService = new App\Services\TechnicalDocumentService($db);

        $productId = (int)$_POST['product_id'];
        $documentType = $_POST['document_type'] ?? '';

        $metadata = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? null,
            'version' => $_POST['version'] ?? '1.0',
            'language' => $_POST['language'] ?? 'tr',
            'is_public' => isset($_POST['is_public']) ? 1 : 0,
        ];

        $documentId = $documentService->upload($productId, $_FILES['file'], $documentType, $metadata);

        $_SESSION['success_message'] = 'Doküman başarıyla yüklendi!';
        redirect('/admin/documents');
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
        redirect('/admin/documents/upload');
    }
});

$router->post('/admin/documents/{id}/delete', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        $db = container()->get(App\Core\Database::class);
        $documentService = new App\Services\TechnicalDocumentService($db);

        if ($documentService->delete((int)$id)) {
            $_SESSION['success_message'] = 'Doküman başarıyla silindi!';
        } else {
            $_SESSION['error_message'] = 'Doküman bulunamadı!';
        }
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }

    redirect('/admin/documents');
});

// Admin Sample Orders
$router->get('/admin/samples', function() {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    ob_start();
    include APP_PATH . '/Views/admin/samples/list.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->get('/admin/samples/{id}', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    $db = container()->get(App\Core\Database::class);
    $sampleService = new App\Services\SampleOrderService($db);
    $sample = $sampleService->getById((int)$id);

    if (!$sample) {
        $_SESSION['error_message'] = 'Numune talebi bulunamadı!';
        redirect('/admin/samples');
    }

    ob_start();
    include APP_PATH . '/Views/admin/samples/detail.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->post('/admin/samples/{id}/status', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        $db = container()->get(App\Core\Database::class);
        $sampleService = new App\Services\SampleOrderService($db);

        $status = $_POST['status'] ?? '';
        $note = $_POST['note'] ?? null;
        $trackingNumber = $_POST['tracking_number'] ?? null;

        // Update tracking number if provided
        if ($trackingNumber) {
            $db->update('sample_orders', ['tracking_number' => $trackingNumber], ['id' => (int)$id]);
            if (!$note) {
                $note = "Kargo takip no: {$trackingNumber}";
            }
        }

        if ($sampleService->updateStatus((int)$id, $status, $note)) {
            $_SESSION['success_message'] = 'Durum başarıyla güncellendi!';
        } else {
            $_SESSION['error_message'] = 'Durum güncellenemedi!';
        }
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }

    redirect('/admin/samples/' . $id);
});

$router->post('/admin/samples/{id}/approve', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        $db = container()->get(App\Core\Database::class);
        $sampleService = new App\Services\SampleOrderService($db);

        if ($sampleService->approve((int)$id)) {
            $_SESSION['success_message'] = 'Numune talebi onaylandı!';
        } else {
            $_SESSION['error_message'] = 'Numune talebi onaylanamadı!';
        }
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }

    redirect('/admin/samples/' . $id);
});

$router->post('/admin/samples/{id}/reject', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        $reason = $_POST['reason'] ?? 'Onaylanmadı';

        $db = container()->get(App\Core\Database::class);
        $sampleService = new App\Services\SampleOrderService($db);

        if ($sampleService->reject((int)$id, $reason)) {
            $_SESSION['success_message'] = 'Numune talebi reddedildi!';
        } else {
            $_SESSION['error_message'] = 'Numune talebi reddedilemedi!';
        }
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }

    redirect('/admin/samples/' . $id);
});

// Admin Product Colors
$router->get('/admin/products/{id}/colors', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    $db = container()->get(App\Core\Database::class);
    $product = $db->fetchOne("SELECT * FROM products WHERE id = ?", [(int)$id]);

    if (!$product) {
        $_SESSION['error_message'] = 'Ürün bulunamadı!';
        redirect('/admin/products');
    }

    ob_start();
    include APP_PATH . '/Views/admin/products/colors.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->post('/admin/products/{id}/colors/add', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        $db = container()->get(App\Core\Database::class);
        $colorService = new App\Services\ColorCatalogService($db);

        $data = [
            'name' => $_POST['name'] ?? '',
            'color_code' => $_POST['color_code'] ?? null,
            'ral_code' => $_POST['ral_code'] ?? null,
            'hex_color' => $_POST['hex_color_text'] ?? $_POST['hex_color'] ?? null,
            'texture_type' => $_POST['texture_type'] ?? null,
            'finish_type' => $_POST['finish_type'] ?? null,
            'price_modifier' => !empty($_POST['price_modifier']) ? (float)$_POST['price_modifier'] : 0,
            'price_modifier_type' => $_POST['price_modifier_type'] ?? 'fixed',
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'is_available' => isset($_POST['is_available']) ? 1 : 0,
        ];

        $colorService->addColor((int)$id, $data);
        $_SESSION['success_message'] = 'Renk başarıyla eklendi!';
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }

    redirect('/admin/products/' . $id . '/colors');
});

$router->post('/admin/products/{id}/colors/{colorId}/toggle', function($id, $colorId) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        $db = container()->get(App\Core\Database::class);
        $colorService = new App\Services\ColorCatalogService($db);

        $color = $colorService->getColorById((int)$colorId);
        if ($color) {
            $colorService->updateColor((int)$colorId, [
                'is_available' => $color['is_available'] ? 0 : 1
            ]);
            $_SESSION['success_message'] = 'Renk durumu güncellendi!';
        }
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }

    redirect('/admin/products/' . $id . '/colors');
});

$router->post('/admin/products/{id}/colors/{colorId}/delete', function($id, $colorId) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        $db = container()->get(App\Core\Database::class);
        $colorService = new App\Services\ColorCatalogService($db);

        if ($colorService->deleteColor((int)$colorId)) {
            $_SESSION['success_message'] = 'Renk başarıyla silindi!';
        } else {
            $_SESSION['error_message'] = 'Renk silinemedi!';
        }
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }

    redirect('/admin/products/' . $id . '/colors');
});

// Admin Product QR Codes
$router->get('/admin/products/{id}/qrcode', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    $db = container()->get(App\Core\Database::class);
    $product = $db->fetchOne("SELECT * FROM products WHERE id = ?", [(int)$id]);

    if (!$product) {
        $_SESSION['error_message'] = 'Ürün bulunamadı!';
        redirect('/admin/products');
    }

    ob_start();
    include APP_PATH . '/Views/admin/products/qrcode.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->post('/admin/products/{id}/qrcode/generate', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        $db = container()->get(App\Core\Database::class);
        $qrService = new App\Services\QRCodeService($db);

        $options = [
            'size' => (int)($_POST['size'] ?? 300),
            'format' => 'png'
        ];

        $qrService->generateForProduct((int)$id, $options);
        $_SESSION['success_message'] = 'QR kod başarıyla oluşturuldu!';
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }

    redirect('/admin/products/' . $id . '/qrcode');
});

$router->post('/admin/products/{id}/qrcode/regenerate', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        $db = container()->get(App\Core\Database::class);
        $qrService = new App\Services\QRCodeService($db);

        // Get existing QR code to preserve settings
        $existingQR = $qrService->getProductQRCode((int)$id);
        $options = [
            'size' => $existingQR['size'] ?? 300,
            'format' => 'png'
        ];

        // Delete old QR code
        if ($existingQR) {
            $qrService->delete($existingQR['id']);
        }

        // Generate new one
        $qrService->generateForProduct((int)$id, $options);
        $_SESSION['success_message'] = 'QR kod yeniden oluşturuldu!';
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }

    redirect('/admin/products/' . $id . '/qrcode');
});

// Batch QR Code Generation
$router->get('/admin/qrcodes/batch', function() {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    ob_start();
    include APP_PATH . '/Views/admin/qrcodes/batch.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->post('/admin/qrcodes/batch/generate', function() {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        $db = container()->get(App\Core\Database::class);
        $qrService = new App\Services\QRCodeService($db);

        $productIds = $_POST['product_ids'] ?? [];
        if (empty($productIds)) {
            throw new \Exception('Lütfen en az bir ürün seçin');
        }

        $options = [
            'size' => (int)($_POST['size'] ?? 300)
        ];

        $results = $qrService->generateBatch($productIds, $options);

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $_SESSION['success_message'] = "{$successCount} ürün için QR kod oluşturuldu!";
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }

    redirect('/admin/qrcodes/batch');
});

// Admin Catalogs
$router->get('/admin/catalogs', function() {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    ob_start();
    include APP_PATH . '/Views/admin/catalogs/list.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->get('/admin/catalogs/generate', function() {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    ob_start();
    include APP_PATH . '/Views/admin/catalogs/generate.php';
    return new Response(ob_get_clean(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
});

$router->post('/admin/catalogs/generate', function() {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        $db = container()->get(App\Core\Database::class);
        $catalogService = new App\Services\PDFCatalogService($db);

        $options = [
            'title' => $_POST['title'] ?? 'Ürün Kataloğu',
            'layout' => $_POST['layout'] ?? 'detailed',
            'include_images' => isset($_POST['include_images']),
            'include_prices' => isset($_POST['include_prices']),
        ];

        // Determine product selection
        $selectionType = $_POST['selection_type'] ?? 'all';
        if ($selectionType === 'category' && !empty($_POST['category_id'])) {
            $options['category_id'] = (int)$_POST['category_id'];
        } elseif ($selectionType === 'custom' && !empty($_POST['product_ids'])) {
            $options['product_ids'] = array_map('intval', $_POST['product_ids']);
        }

        $filepath = $catalogService->generateCatalog($options);
        $_SESSION['success_message'] = 'Katalog başarıyla oluşturuldu!';
        $_SESSION['last_catalog'] = $filepath;
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }

    redirect('/admin/catalogs');
});

$router->post('/admin/catalogs/{id}/delete', function($id) {
    if (!App\Core\Auth::check()) redirect('/admin/login');
    if (session_status() === PHP_SESSION_NONE) session_start();

    try {
        $db = container()->get(App\Core\Database::class);
        $catalogService = new App\Services\PDFCatalogService($db);

        if ($catalogService->delete((int)$id)) {
            $_SESSION['success_message'] = 'Katalog silindi!';
        } else {
            $_SESSION['error_message'] = 'Katalog silinemedi!';
        }
    } catch (\Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }

    redirect('/admin/catalogs');
});
