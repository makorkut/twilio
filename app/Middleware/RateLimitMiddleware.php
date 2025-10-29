<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;
use App\Http\Request;
use App\Http\Response;

class RateLimitMiddleware
{
    protected Database $db;
    protected int $maxAttempts;
    protected int $decayMinutes;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->maxAttempts = (int) env('RATE_LIMIT_MAX_ATTEMPTS', '60');
        $this->decayMinutes = (int) env('RATE_LIMIT_DECAY_MINUTES', '1');
    }

    public function handle(Request $request, callable $next): Response
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->tooManyAttempts($key)) {
            return $this->buildRateLimitResponse();
        }

        $this->hit($key);

        $response = $next($request);

        return $this->addRateLimitHeaders($response, $key);
    }

    protected function resolveRequestSignature(Request $request): string
    {
        $ip = $request->getClientIp();
        $path = $request->getPath();

        return 'rate_limit:' . sha1($ip . '|' . $path);
    }

    protected function tooManyAttempts(string $key): bool
    {
        $attempts = $this->getAttempts($key);

        return $attempts >= $this->maxAttempts;
    }

    protected function getAttempts(string $key): int
    {
        $cacheFile = cache_path('rate_limits/' . $key . '.json');

        if (!file_exists($cacheFile)) {
            return 0;
        }

        $data = json_decode(file_get_contents($cacheFile), true);

        if (!$data || !isset($data['expires_at'])) {
            return 0;
        }

        // Check if expired
        if (time() > $data['expires_at']) {
            unlink($cacheFile);
            return 0;
        }

        return $data['attempts'] ?? 0;
    }

    protected function hit(string $key): void
    {
        $cacheDir = cache_path('rate_limits');

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cacheFile = $cacheDir . '/' . $key . '.json';

        $attempts = $this->getAttempts($key) + 1;
        $expiresAt = time() + ($this->decayMinutes * 60);

        $data = [
            'attempts' => $attempts,
            'expires_at' => $expiresAt,
        ];

        file_put_contents($cacheFile, json_encode($data));
    }

    protected function buildRateLimitResponse(): Response
    {
        $retryAfter = $this->decayMinutes * 60;

        return Response::json([
            'error' => 'Too many requests',
            'retry_after' => $retryAfter,
        ], 429);
    }

    protected function addRateLimitHeaders(Response $response, string $key): Response
    {
        $attempts = $this->getAttempts($key);
        $remaining = max(0, $this->maxAttempts - $attempts);

        // Add headers via direct output (since Response doesn't support adding headers after creation)
        header('X-RateLimit-Limit: ' . $this->maxAttempts);
        header('X-RateLimit-Remaining: ' . $remaining);

        return $response;
    }

    /**
     * Specific rate limiter for API endpoints
     */
    public function handleApiRateLimit(Request $request, callable $next): Response
    {
        // More strict limits for API
        $this->maxAttempts = (int) env('API_RATE_LIMIT_MAX_ATTEMPTS', '100');
        $this->decayMinutes = (int) env('API_RATE_LIMIT_DECAY_MINUTES', '1');

        return $this->handle($request, $next);
    }

    /**
     * Very strict rate limiter for auth endpoints (login, register)
     */
    public function handleAuthRateLimit(Request $request, callable $next): Response
    {
        // Very strict for auth
        $this->maxAttempts = 5;
        $this->decayMinutes = 15;

        return $this->handle($request, $next);
    }
}
