<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Simple Authentication Helper
 */
class Auth
{
    /**
     * Attempt to log in with email and password
     */
    public static function attempt(string $email, string $password): bool
    {
        $db = container()->get(Database::class);

        $user = $db->fetch(
            "SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1",
            [$email]
        );

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        // Start session and store user data
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        // Update last login
        $db->query(
            "UPDATE users SET last_login_at = NOW() WHERE id = ?",
            [$user['id']]
        );

        return true;
    }

    /**
     * Check if user is logged in
     */
    public static function check(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Get current user
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'role' => $_SESSION['user_role'] ?? null,
        ];
    }

    /**
     * Logout user
     */
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();
    }

    /**
     * Check if user has specific role
     */
    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && $user['role'] === 'admin';
    }
}
