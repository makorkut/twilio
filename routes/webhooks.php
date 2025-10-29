<?php

/**
 * Webhook Routes
 *
 * These endpoints receive webhooks from external systems
 */

use App\Http\Response;
use App\Controllers\Webhooks\WebhookController;

// Local CRM webhook receiver
$router->post('/webhooks/local-crm', [WebhookController::class, 'localCrm']);

// Payment gateway webhooks
$router->post('/webhooks/stripe', [WebhookController::class, 'stripe']);
$router->post('/webhooks/iyzico', [WebhookController::class, 'iyzico']);
