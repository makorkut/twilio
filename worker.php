#!/usr/bin/env php
<?php

/**
 * Queue Worker
 *
 * Process background jobs from the queue
 *
 * Usage:
 *   php worker.php                  # Process jobs once
 *   php worker.php --daemon         # Run continuously
 *   php worker.php --daemon --sleep=5  # Run continuously with 5 second sleep
 */

declare(strict_types=1);

// Define paths
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Load Composer autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Parse command line arguments
$options = getopt('', ['daemon', 'sleep:', 'limit:']);
$daemon = isset($options['daemon']);
$sleep = (int) ($options['sleep'] ?? env('QUEUE_WORKER_SLEEP', '3'));
$limit = (int) ($options['limit'] ?? 50);

// Create services
try {
    $db = new App\Core\Database([
        'host' => env('DB_HOST', 'localhost'),
        'port' => (int) env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'timeout' => 5
    ]);
} catch (\Throwable $e) {
    output('❌ Database connection failed: ' . $e->getMessage());
    output('');
    output('Please check:');
    output('  1. Database server is running');
    output('  2. DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD in .env are correct');
    output('  3. Database server allows connections from this IP');
    output('  4. Firewall rules permit access to database port');
    output('');
    output('Worker cannot run without database connection.');
    exit(1);
}

$productService = new App\Services\ProductService($db);
$mediaService = new App\Services\MediaService($db);
$queueService = new App\Services\QueueService($db, $productService, $mediaService);

// Output helper
function output(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

output('Queue Worker Started');
output('Mode: ' . ($daemon ? 'Daemon' : 'Single Run'));

if ($daemon) {
    output("Sleep: {$sleep} seconds between cycles");
}

output("Batch Size: {$limit} jobs per cycle");
output('---');

$iteration = 0;

do {
    $iteration++;

    try {
        // Get pending count
        $pendingCount = $queueService->getPendingCount();

        if ($pendingCount > 0) {
            output("Processing {$pendingCount} pending jobs (iteration #{$iteration})...");

            // Process jobs
            $results = $queueService->processJobs($limit);

            // Count results
            $success = 0;
            $errors = 0;

            foreach ($results as $result) {
                if ($result['status'] === 'success') {
                    $success++;
                    output("✓ Job #{$result['job_id']} ({$result['job_type']}) completed successfully");
                } elseif ($result['status'] === 'error') {
                    $errors++;
                    output("✗ Job #{$result['job_id']} ({$result['job_type']}) failed: {$result['error']}");
                }
            }

            output("Processed: {$success} successful, {$errors} errors");
        } else {
            if (!$daemon || $iteration === 1) {
                output('No pending jobs in queue');
            }
        }

        // Get statistics
        if ($iteration % 10 === 0) {
            $stats = $queueService->getStatistics();
            output('Queue Statistics: ' . json_encode($stats));
        }

    } catch (\Exception $e) {
        output('Error: ' . $e->getMessage());
    }

    // Sleep if daemon mode
    if ($daemon) {
        sleep($sleep);
    }

} while ($daemon);

output('---');
output('Queue Worker Stopped');
