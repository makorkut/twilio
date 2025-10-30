<?php

/**
 * Front Controller
 *
 * This is the single entry point for all requests
 */

declare(strict_types=1);

// Start output buffering
ob_start();

// Start session
session_start();

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('CONFIG_PATH', BASE_PATH . '/config');
define('THEME_PATH', BASE_PATH . '/themes');

// Load Composer autoload
require BASE_PATH . '/vendor/autoload.php';

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
} catch (\Throwable $e) {
    die("Failed to load .env file: " . $e->getMessage());
}

// Error handling - ALWAYS show errors for now
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/php-errors.log');

// Use Whoops for better error display (if available)
if (class_exists('\Whoops\Run')) {
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}

// Timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// Maintenance mode check
if (file_exists(BASE_PATH . '/.maintenance') &&
    !isset($_GET['bypass_maintenance']) &&
    $_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {

    http_response_code(503);
    include BASE_PATH . '/maintenance.html';
    exit;
}

try {
    // Bootstrap application
    $app = new App\Core\Application();

    // Make app globally accessible for helpers
    $GLOBALS['app'] = $app;

    // Run application
    $app->run();

} catch (\Throwable $e) {
    // Log error
    error_log(sprintf(
        "[%s] %s in %s:%d\nStack trace:\n%s",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));

    // Show error page
    http_response_code(500);

    if ($_ENV['APP_DEBUG'] === 'true') {
        echo '<h1>Application Error</h1>';
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        echo '<h1>500 - Internal Server Error</h1>';
        echo '<p>Something went wrong. Please try again later.</p>';
    }

    exit(1);
}
