#!/usr/bin/env php
<?php

/**
 * Seed Admin User
 *
 * Creates an admin user account automatically on first deployment
 */

declare(strict_types=1);

// Define paths
define('ROOT_PATH', dirname(__DIR__, 2));
define('APP_PATH', ROOT_PATH . '/app');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Load Composer autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Get admin credentials from environment
$adminEmail = env('ADMIN_EMAIL', 'admin@polyes.tr');
$adminPassword = env('ADMIN_PASSWORD', 'Admin123!S3cur3');
$adminName = env('ADMIN_NAME', 'Admin User');

try {
    // Connect to database
    $db = new App\Core\Database([
        'host' => env('DB_HOST', 'localhost'),
        'port' => (int) env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'timeout' => 10
    ]);

    echo "📊 Checking for existing admin user...\n";

    // Check if users table exists
    $tableExists = $db->fetch("SHOW TABLES LIKE 'users'");

    if (!$tableExists) {
        echo "⚠️  Users table does not exist yet. Run migrations first.\n";
        exit(1);
    }

    // Check if admin user already exists
    $existingAdmin = $db->fetch(
        "SELECT * FROM users WHERE email = ? LIMIT 1",
        [$adminEmail]
    );

    if ($existingAdmin) {
        echo "✅ Admin user already exists: {$adminEmail}\n";
        exit(0);
    }

    echo "👤 Creating admin user...\n";

    // Hash password
    $passwordHash = password_hash($adminPassword, PASSWORD_ARGON2ID);

    // Insert admin user
    $userId = $db->insert('users', [
        'name' => $adminName,
        'email' => $adminEmail,
        'password' => $passwordHash,
        'role' => 'admin',
        'is_active' => 1,
        'email_verified_at' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    echo "✅ Admin user created successfully!\n";
    echo "\n";
    echo "================================================\n";
    echo "📋 Admin Credentials\n";
    echo "================================================\n";
    echo "Email:    {$adminEmail}\n";
    echo "Password: {$adminPassword}\n";
    echo "================================================\n";
    echo "\n";
    echo "⚠️  IMPORTANT: Change the admin password after first login!\n";
    echo "\n";

    exit(0);

} catch (\Exception $e) {
    echo "❌ Error creating admin user: " . $e->getMessage() . "\n";
    exit(1);
}
