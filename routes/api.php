<?php

/**
 * API Routes (v1)
 */

use App\Http\Response;

$router->get('/api/v1/health', function() {
    return Response::json([
        'status' => 'ok',
        'timestamp' => time()
    ]);
});
