<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;

class AuthMiddleware
{
    /**
     * Check if user is authenticated
     */
    public function handle(Request $request, callable $next): Response
    {
        // Check session for user
        session_start();

        if (!isset($_SESSION['user_id'])) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        // Continue to next middleware/controller
        return $next($request);
    }

    /**
     * Check API token authentication
     */
    public function handleApiAuth(Request $request, callable $next): Response
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return Response::json(['error' => 'Missing authorization token'], 401);
        }

        // Remove "Bearer " prefix
        $token = str_replace('Bearer ', '', $token);

        // Validate JWT token (using Firebase JWT library in production)
        if (!$this->validateToken($token)) {
            return Response::json(['error' => 'Invalid token'], 401);
        }

        return $next($request);
    }

    /**
     * Validate JWT token
     */
    protected function validateToken(string $token): bool
    {
        // TODO: Implement JWT validation with Firebase JWT library
        // For now, simple validation
        return !empty($token);
    }

    /**
     * Check if user has specific role
     */
    public function requireRole(string $role): callable
    {
        return function (Request $request, callable $next) use ($role): Response {
            session_start();

            $userRole = $_SESSION['user_role'] ?? null;

            if ($userRole !== $role && $userRole !== 'admin') {
                return Response::json(['error' => 'Forbidden'], 403);
            }

            return $next($request);
        };
    }
}
