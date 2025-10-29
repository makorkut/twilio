<?php

declare(strict_types=1);

namespace App\Controllers\Webhooks;

use App\Core\Database;
use App\Services\WebhookService;
use App\Http\Request;
use App\Http\Response;

class WebhookController
{
    protected Database $db;
    protected WebhookService $webhookService;

    public function __construct(Database $db, WebhookService $webhookService)
    {
        $this->db = $db;
        $this->webhookService = $webhookService;
    }

    /**
     * Local CRM webhook receiver
     */
    public function localCrm(Request $request): Response
    {
        try {
            $payload = $request->all();
            $eventType = $payload['event_type'] ?? null;
            $signature = $request->header('X-Webhook-Signature');

            if (!$eventType) {
                return Response::json(['error' => 'Missing event_type'], 400);
            }

            $secret = env('LOCAL_CRM_WEBHOOK_SECRET');

            $result = $this->webhookService->receive('local-crm', $eventType, $payload, $signature, $secret);

            return Response::json($result);
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Stripe webhook receiver
     */
    public function stripe(Request $request): Response
    {
        try {
            $payload = $request->getRawBody();
            $signature = $request->header('Stripe-Signature');

            // TODO: Verify Stripe signature and process webhook
            // This would use Stripe SDK in production

            return Response::json(['received' => true]);
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Iyzico webhook receiver
     */
    public function iyzico(Request $request): Response
    {
        try {
            $payload = $request->all();

            // TODO: Verify Iyzico signature and process webhook
            // This would use Iyzico SDK in production

            return Response::json(['received' => true]);
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }
    }
}
