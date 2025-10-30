<?php
/**
 * Deployment Diagnostic Script
 *
 * Access this file directly via browser: https://yourdomain.com/diagnostic.php
 * This script checks common deployment issues
 */

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Deployment Diagnostics</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            padding: 40px;
        }
        h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #333;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .test-section {
            margin-bottom: 30px;
        }
        .test-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .test-result {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid;
        }
        .test-success {
            background: #d1fae5;
            border-color: #10b981;
            color: #065f46;
        }
        .test-error {
            background: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
        }
        .test-warning {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }
        .test-info {
            background: #dbeafe;
            border-color: #3b82f6;
            color: #1e3a8a;
        }
        .test-details {
            font-size: 13px;
            margin-top: 8px;
            padding: 10px;
            background: rgba(0,0,0,0.02);
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        .test-details pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .icon { font-size: 24px; }
        .summary {
            margin-top: 40px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
        }
        .summary-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .counter {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            margin-right: 10px;
            font-size: 14px;
        }
        .counter-success { background: #10b981; color: white; }
        .counter-error { background: #ef4444; color: white; }
        .counter-warning { background: #f59e0b; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Deployment Diagnostics</h1>
        <p class="subtitle">Checking deployment configuration and environment...</p>

<?php

$results = [];
$successCount = 0;
$errorCount = 0;
$warningCount = 0;

function addResult($title, $status, $message, $details = null) {
    global $results, $successCount, $errorCount, $warningCount;

    if ($status === 'success') $successCount++;
    elseif ($status === 'error') $errorCount++;
    elseif ($status === 'warning') $warningCount++;

    $results[] = [
        'title' => $title,
        'status' => $status,
        'message' => $message,
        'details' => $details
    ];
}

// ============================================================
// TEST 1: PHP Version
// ============================================================
$phpVersion = phpversion();
if (version_compare($phpVersion, '8.0.0', '>=')) {
    addResult('PHP Version', 'success', "PHP $phpVersion installed", "Minimum requirement: PHP 8.0");
} else {
    addResult('PHP Version', 'error', "PHP $phpVersion is too old", "Please upgrade to PHP 8.0 or higher");
}

// ============================================================
// TEST 2: Required PHP Extensions
// ============================================================
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl', 'gd', 'fileinfo', 'openssl'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (empty($missingExtensions)) {
    addResult('PHP Extensions', 'success', 'All required extensions are loaded', implode(', ', $requiredExtensions));
} else {
    addResult('PHP Extensions', 'error', 'Missing PHP extensions', 'Missing: ' . implode(', ', $missingExtensions));
}

// ============================================================
// TEST 3: Base Path
// ============================================================
$basePath = dirname(__DIR__);
if (is_dir($basePath)) {
    addResult('Base Path', 'success', "Base path exists: $basePath");
} else {
    addResult('Base Path', 'error', "Base path not found: $basePath");
}

// ============================================================
// TEST 4: Composer Autoload
// ============================================================
$autoloadPath = $basePath . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require $autoloadPath;
    addResult('Composer Autoload', 'success', 'Composer autoload found and loaded', $autoloadPath);
} else {
    addResult('Composer Autoload', 'error', 'Composer autoload not found', "Expected: $autoloadPath\nRun: composer install");
}

// ============================================================
// TEST 5: .env File
// ============================================================
$envPath = $basePath . '/.env';
if (file_exists($envPath)) {
    try {
        if (class_exists('\Dotenv\Dotenv')) {
            $dotenv = \Dotenv\Dotenv::createImmutable($basePath);
            $dotenv->load();
            addResult('.env File', 'success', '.env file exists and loaded successfully', $envPath);
        } else {
            addResult('.env File', 'warning', '.env file exists but Dotenv class not found', 'Install vlucas/phpdotenv via composer');
        }
    } catch (\Exception $e) {
        addResult('.env File', 'error', '.env file exists but failed to load', $e->getMessage());
    }
} else {
    addResult('.env File', 'error', '.env file not found', "Expected: $envPath\nCopy .env.example to .env and configure");
}

// ============================================================
// TEST 6: Required Directories
// ============================================================
$requiredDirs = [
    'app' => $basePath . '/app',
    'public' => $basePath . '/public',
    'storage' => $basePath . '/storage',
    'storage/logs' => $basePath . '/storage/logs',
    'storage/uploads' => $basePath . '/storage/uploads',
    'storage/cache' => $basePath . '/storage/cache',
    'storage/catalogs' => $basePath . '/storage/catalogs',
    'storage/qrcodes' => $basePath . '/storage/qrcodes',
    'routes' => $basePath . '/routes',
];

$missingDirs = [];
$notWritableDirs = [];

foreach ($requiredDirs as $name => $path) {
    if (!is_dir($path)) {
        $missingDirs[] = $name;
    } elseif (strpos($name, 'storage') !== false && !is_writable($path)) {
        $notWritableDirs[] = $name;
    }
}

if (empty($missingDirs) && empty($notWritableDirs)) {
    addResult('Required Directories', 'success', 'All required directories exist and are writable');
} else {
    $msg = '';
    if (!empty($missingDirs)) $msg .= 'Missing: ' . implode(', ', $missingDirs) . '. ';
    if (!empty($notWritableDirs)) $msg .= 'Not writable: ' . implode(', ', $notWritableDirs);
    addResult('Required Directories', 'error', $msg, 'Run: chmod -R 755 storage && chmod -R 777 storage/*');
}

// ============================================================
// TEST 7: Route Files
// ============================================================
$routeFiles = [
    'web.php' => $basePath . '/routes/web.php',
    'api.php' => $basePath . '/routes/api.php',
    'webhooks.php' => $basePath . '/routes/webhooks.php',
];

$missingRoutes = [];
foreach ($routeFiles as $name => $path) {
    if (!file_exists($path)) {
        $missingRoutes[] = $name;
    }
}

if (empty($missingRoutes)) {
    addResult('Route Files', 'success', 'All route files exist', implode(', ', array_keys($routeFiles)));
} else {
    addResult('Route Files', 'error', 'Missing route files', implode(', ', $missingRoutes));
}

// ============================================================
// TEST 8: Database Connection
// ============================================================
if (isset($_ENV['DB_HOST'])) {
    $dbConfig = [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_DATABASE'] ?? '',
        'username' => $_ENV['DB_USERNAME'] ?? '',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'port' => $_ENV['DB_PORT'] ?? 3306,
    ];

    try {
        $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Test query
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '{$dbConfig['database']}'");
        $result = $stmt->fetch();
        $tableCount = $result['count'];

        addResult('Database Connection', 'success', "Connected to database: {$dbConfig['database']}", "Tables found: $tableCount\nHost: {$dbConfig['host']}:{$dbConfig['port']}");
    } catch (PDOException $e) {
        addResult('Database Connection', 'error', 'Cannot connect to database', "Error: " . $e->getMessage() . "\nHost: {$dbConfig['host']}:{$dbConfig['port']}\nDatabase: {$dbConfig['database']}");
    }
} else {
    addResult('Database Connection', 'error', 'Database credentials not found in .env', 'Check DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD');
}

// ============================================================
// TEST 9: Database Tables
// ============================================================
if (isset($pdo)) {
    try {
        $requiredTables = ['products', 'categories', 'users', 'orders', 'settings'];
        $stmt = $pdo->query("SHOW TABLES");
        $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $missingTables = array_diff($requiredTables, $existingTables);

        if (empty($missingTables)) {
            addResult('Database Tables', 'success', 'Core tables exist', count($existingTables) . ' tables found');
        } else {
            addResult('Database Tables', 'warning', 'Some core tables are missing', 'Missing: ' . implode(', ', $missingTables) . "\nRun migrations: php bin/migrate.php");
        }
    } catch (PDOException $e) {
        addResult('Database Tables', 'error', 'Cannot check tables', $e->getMessage());
    }
}

// ============================================================
// TEST 10: Environment Variables
// ============================================================
$criticalEnvVars = [
    'APP_URL' => $_ENV['APP_URL'] ?? null,
    'APP_ENV' => $_ENV['APP_ENV'] ?? null,
    'DB_HOST' => $_ENV['DB_HOST'] ?? null,
    'DB_DATABASE' => $_ENV['DB_DATABASE'] ?? null,
];

$missingEnvVars = array_filter($criticalEnvVars, function($v) { return empty($v); });

if (empty($missingEnvVars)) {
    addResult('Environment Variables', 'success', 'Critical environment variables are set',
        "APP_URL: {$_ENV['APP_URL']}\nAPP_ENV: {$_ENV['APP_ENV']}\nDB_HOST: {$_ENV['DB_HOST']}");
} else {
    addResult('Environment Variables', 'error', 'Missing critical environment variables',
        'Missing: ' . implode(', ', array_keys($missingEnvVars)));
}

// ============================================================
// TEST 11: Core Application Files
// ============================================================
$coreFiles = [
    'Application.php' => $basePath . '/app/Core/Application.php',
    'Router.php' => $basePath . '/app/Core/Router.php',
    'Database.php' => $basePath . '/app/Core/Database.php',
    'bootstrap.php' => $basePath . '/app/bootstrap.php',
];

$missingCoreFiles = [];
foreach ($coreFiles as $name => $path) {
    if (!file_exists($path)) {
        $missingCoreFiles[] = $name;
    }
}

if (empty($missingCoreFiles)) {
    addResult('Core Application Files', 'success', 'All core files exist');
} else {
    addResult('Core Application Files', 'error', 'Missing core files', implode(', ', $missingCoreFiles));
}

// ============================================================
// TEST 12: URL Rewriting (.htaccess)
// ============================================================
$htaccessPath = __DIR__ . '/.htaccess';
if (file_exists($htaccessPath)) {
    $htaccessContent = file_get_contents($htaccessPath);
    if (strpos($htaccessContent, 'RewriteEngine On') !== false) {
        addResult('URL Rewriting', 'success', '.htaccess file exists with rewrite rules', $htaccessPath);
    } else {
        addResult('URL Rewriting', 'warning', '.htaccess exists but missing RewriteEngine', 'Check Apache mod_rewrite configuration');
    }
} else {
    addResult('URL Rewriting', 'warning', '.htaccess file not found',
        "For Apache, create .htaccess in public directory\nFor Nginx, configure rewrite rules in nginx.conf");
}

// ============================================================
// Display Results
// ============================================================
foreach ($results as $result) {
    $statusClass = "test-{$result['status']}";
    $icon = match($result['status']) {
        'success' => '✅',
        'error' => '❌',
        'warning' => '⚠️',
        'info' => 'ℹ️',
        default => '•'
    };

    echo "<div class='test-section'>";
    echo "<div class='test-title'><span class='icon'>$icon</span> {$result['title']}</div>";
    echo "<div class='test-result $statusClass'>";
    echo "<strong>{$result['message']}</strong>";
    if ($result['details']) {
        echo "<div class='test-details'><pre>" . htmlspecialchars($result['details']) . "</pre></div>";
    }
    echo "</div>";
    echo "</div>";
}

// ============================================================
// Summary
// ============================================================
?>
        <div class="summary">
            <div class="summary-title">📊 Summary</div>
            <div>
                <span class="counter counter-success"><?= $successCount ?> Passed</span>
                <span class="counter counter-error"><?= $errorCount ?> Failed</span>
                <span class="counter counter-warning"><?= $warningCount ?> Warnings</span>
            </div>

            <?php if ($errorCount === 0 && $warningCount === 0): ?>
                <p style="margin-top: 20px; color: #065f46; font-weight: 600;">
                    ✅ All checks passed! Your deployment looks healthy.
                </p>
            <?php elseif ($errorCount > 0): ?>
                <p style="margin-top: 20px; color: #991b1b; font-weight: 600;">
                    ❌ Found <?= $errorCount ?> critical error<?= $errorCount > 1 ? 's' : '' ?>. Please fix before proceeding.
                </p>
            <?php else: ?>
                <p style="margin-top: 20px; color: #92400e; font-weight: 600;">
                    ⚠️ Found <?= $warningCount ?> warning<?= $warningCount > 1 ? 's' : '' ?>. Application may work but with issues.
                </p>
            <?php endif; ?>

            <div style="margin-top: 20px; padding: 15px; background: white; border-radius: 6px; font-size: 13px;">
                <strong>Next Steps:</strong>
                <ol style="margin-left: 20px; margin-top: 10px; line-height: 1.8;">
                    <?php if ($errorCount > 0): ?>
                        <li>Fix all critical errors (❌) shown above</li>
                        <li>Refresh this page to re-check</li>
                    <?php endif; ?>
                    <li>Delete this diagnostic.php file after resolving issues (security)</li>
                    <li>Try accessing homepage: <a href="/" target="_blank">Go to Homepage</a></li>
                    <li>Try accessing admin: <a href="/admin" target="_blank">Go to Admin</a></li>
                </ol>
            </div>
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center; font-size: 13px; color: #666;">
            <strong>🔒 Security Note:</strong> Delete this diagnostic.php file after fixing issues<br>
            Generated: <?= date('Y-m-d H:i:s') ?> | PHP <?= phpversion() ?>
        </div>
    </div>
</body>
</html>
