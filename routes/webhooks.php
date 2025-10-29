<?php

/**
 * Webhook Routes
 */

use App\Http\Response;

$router->post('/webhooks/local-crm/products', function() {
    return Response::json(['message' => 'Webhook received']);
});

$router->post('/webhooks/stripe', function() {
    return Response::json(['message' => 'Stripe webhook received']);
});

$router->post('/webhooks/iyzico', function() {
    return Response::json(['message' => 'İyzico webhook received']);
});
