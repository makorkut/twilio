<?php

/**
 * Migration Apply Script
 *
 * Usage: php app/Migrations/apply.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

// Database connection
try {
    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'] ?? 3306,
            $_ENV['DB_DATABASE'],
            $_ENV['DB_CHARSET'] ?? 'utf8mb4'
        ),
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'"
        ]
    );

    echo "✓ Database connection successful\n";

} catch (PDOException $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// Create migrations tracking table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        version VARCHAR(50) NOT NULL UNIQUE,
        filename VARCHAR(255) NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        checksum VARCHAR(64) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// Get migration files
$migrationFiles = glob(__DIR__ . '/sql/*.sql');
sort($migrationFiles);

echo "\nFound " . count($migrationFiles) . " migration files\n";
echo str_repeat('=', 60) . "\n\n";

foreach ($migrationFiles as $file) {
    $filename = basename($file);
    $version = pathinfo($filename, PATHINFO_FILENAME);

    // Check if already executed
    $stmt = $pdo->prepare("SELECT * FROM migrations WHERE version = ?");
    $stmt->execute([$version]);

    if ($stmt->fetch()) {
        echo "⊘ Skipping {$filename} (already executed)\n";
        continue;
    }

    echo "▶ Executing {$filename}...\n";

    try {
        // Read SQL file
        $sql = file_get_contents($file);
        $checksum = md5($sql);

        // Execute SQL (support multiple statements)
        $pdo->exec($sql);

        // Record migration
        $stmt = $pdo->prepare("
            INSERT INTO migrations (version, filename, checksum)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$version, $filename, $checksum]);

        echo "  ✓ Success\n\n";

    } catch (PDOException $e) {
        echo "  ✗ Failed: " . $e->getMessage() . "\n\n";
        exit(1);
    }
}

echo str_repeat('=', 60) . "\n";
echo "✓ All migrations completed successfully!\n";
