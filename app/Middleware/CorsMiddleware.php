<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;

class CorsMiddleware
{
    protected array $allowedOrigins;
    protected array $allowedMethods;
    protected array $allowedHeaders;
    protected int $maxAge;

    public function __construct()
    {
        $this->allowedOrigins = explode(',', env('CORS_ALLOWED_ORIGINS', '*'));
        $this->allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        $this->allowedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With'];
        $this->maxAge = 86400; // 24 hours
    }

    public function handle(Request $request, callable $next): Response
    {
        $origin = $request->header('Origin');

        // Handle preflight OPTIONS request
        if ($request->getMethod() === 'OPTIONS') {
            return $this->handlePreflight($origin);
        }

        // Get response from next middleware/controller
        $response = $next($request);

        // Add CORS headers
        $this->addCorsHeaders($response, $origin);

        return $response;
    }

    protected function handlePreflight(?string $origin): Response
    {
        $headers = [
            'Access-Control-Allow-Methods' => implode(', ', $this->allowedMethods),
            'Access-Control-Allow-Headers' => implode(', ', $this->allowedHeaders),
            'Access-Control-Max-Age' => (string) $this->maxAge,
        ];

        if ($this->isOriginAllowed($origin)) {
            $headers['Access-Control-Allow-Origin'] = $origin;
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }

        return new Response('', 200, $headers);
    }

    protected function addCorsHeaders(Response $response, ?string $origin): void
    {
        if ($this->isOriginAllowed($origin)) {
            // Add CORS headers via reflection (since Response headers are protected)
            // In production, Response class should have addHeader() method
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
        }
    }

    protected function isOriginAllowed(?string $origin): bool
    {
        if (!$origin) {
            return false;
        }

        // Allow all origins if * is configured
        if (in_array('*', $this->allowedOrigins)) {
            return true;
        }

        return in_array($origin, $this->allowedOrigins);
    }
}
