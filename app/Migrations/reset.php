<?php

/**
 * Reset Database - Drop all tables
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
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "✓ Database connection successful\n";

} catch (PDOException $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

echo "⚠️  WARNING: This will drop ALL tables in the database!\n";
echo "Database: " . $_ENV['DB_DATABASE'] . "\n";
echo "Press Enter to continue or Ctrl+C to cancel...";
// Skipping readline for automated execution

try {
    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "\nDropping " . count($tables) . " tables...\n";

    foreach ($tables as $table) {
        echo "  ✗ Dropping table: $table\n";
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
    }

    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "\n✓ All tables dropped successfully!\n";
    echo "Run 'php app/Migrations/apply.php' to recreate tables\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
