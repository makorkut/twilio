<?php

/**
 * Web Routes
 */

use App\Http\Response;

$router->get('/', function() {
    return Response::json([
        'message' => 'Poliüretan E-Commerce API',
        'version' => '1.0.0',
        'status' => 'active'
    ]);
});

$router->get('/health', function() {
    return Response::json(['status' => 'ok']);
});
