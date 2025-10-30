#!/usr/bin/env php
<?php

/**
 * Reset Admin Password
 *
 * Quick script to reset admin password manually
 */

declare(strict_types=1);

// Define paths
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');

// Load Composer autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Get email and password from command line
$email = $argv[1] ?? 'admin@polyes.tr';
$newPassword = $argv[2] ?? 'Admin123!S3cur3';

try {
    // Connect to database
    $db = new App\Core\Database([
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => (int) ($_ENV['DB_PORT'] ?? '3306'),
        'database' => $_ENV['DB_DATABASE'] ?? 'ecommerce_db',
        'username' => $_ENV['DB_USERNAME'] ?? 'ecommerce_user',
        'password' => $_ENV['DB_PASSWORD'] ?? 'Ec0mm3rc3!S3cur3P@ss',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'timeout' => 10
    ]);

    echo "🔍 Looking for user: {$email}\n";

    // Check if user exists
    $user = $db->fetch(
        "SELECT * FROM users WHERE email = ? LIMIT 1",
        [$email]
    );

    if (!$user) {
        echo "❌ User not found: {$email}\n";
        echo "Creating new admin user...\n";

        // Create new admin
        $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);

        $userId = $db->insert('users', [
            'name' => 'Admin User',
            'email' => $email,
            'password' => $passwordHash,
            'role' => 'admin',
            'is_active' => 1,
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);

        echo "✅ Admin user created!\n";
    } else {
        echo "✅ User found! Updating password...\n";

        // Update password
        $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);

        $db->query(
            "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?",
            [$passwordHash, $user['id']]
        );

        echo "✅ Password updated!\n";
    }

    echo "\n";
    echo "================================================\n";
    echo "📋 Admin Credentials\n";
    echo "================================================\n";
    echo "Email:    {$email}\n";
    echo "Password: {$newPassword}\n";
    echo "================================================\n";
    echo "\n";

    exit(0);

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
