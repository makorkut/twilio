<?php

/**
 * Web Routes (Frontend)
 */

use App\Http\Response;
use App\Controllers\Frontend\ProductFrontendController;

// Home
$router->get('/', function() {
    return Response::html('frontend/home', [
        'title' => 'Home',
        'featured_products' => [],
    ]);
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
