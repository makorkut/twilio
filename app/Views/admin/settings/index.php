<?php
$pageTitle = trans('admin.settings.title') . ' - ' . trans('common.dashboard');

ob_start();
?>

<!DOCTYPE html>
<html lang="<?= get_language_short_form(get_current_language_id()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .header {
            background: white;
            padding: 20px 40px;
            border-bottom: 1px solid #e0e0e0;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 500;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 40px;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 32px;
            margin-bottom: 24px;
        }

        .card h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f0f0f0;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #555;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #1a1a1a;
        }

        .form-group small {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            color: #999;
        }

        .btn {
            padding: 12px 24px;
            background: #1a1a1a;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            border: none;
            cursor: pointer;
            font-weight: 500;
        }

        .btn:hover {
            background: #000;
        }

        .nav {
            padding: 20px 40px;
            background: white;
            border-bottom: 1px solid #e0e0e0;
        }

        .nav a {
            color: #666;
            text-decoration: none;
            margin-right: 20px;
            font-size: 14px;
        }

        .nav a:hover {
            color: #1a1a1a;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #1a1a1a;
            padding: 16px;
            margin-bottom: 24px;
            border-radius: 4px;
        }

        .info-box p {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px;
            border-radius: 8px;
        }

        .stat-card:nth-child(2) {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-card:nth-child(3) {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="nav">
        <a href="/admin"><?= trans('admin.nav.dashboard') ?></a>
        <a href="/admin/products"><?= trans('admin.nav.products') ?></a>
        <a href="/admin/categories"><?= trans('admin.nav.categories') ?></a>
        <a href="/admin/orders"><?= trans('admin.nav.orders') ?></a>
        <a href="/admin/languages"><?= trans('common.language') ?></a>
        <a href="/admin/settings" style="color: #1a1a1a; font-weight: 600;"><?= trans('admin.nav.settings') ?></a>
        <a href="/admin/logout" style="float: right;"><?= trans('common.logout') ?></a>
    </nav>

    <!-- Header -->
    <div class="header">
        <h1><?= trans('admin.settings.site_settings') ?></h1>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Statistics Dashboard -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" id="product-count">-</div>
                <div class="stat-label"><?= trans('common.products') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="order-count">-</div>
                <div class="stat-label"><?= trans('common.orders') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="customer-count">-</div>
                <div class="stat-label"><?= trans('common.customers') ?></div>
            </div>
        </div>

        <!-- General Settings -->
        <div class="card">
            <h2><?= trans('admin.settings.general') ?></h2>

            <div class="info-box">
                <p><strong><?= trans('message.info') ?>:</strong> <?= trans('admin.settings.env_notice', [], null) ?? 'These settings are read from the .env file. To make changes, edit the .env file and restart the server.' ?></p>
            </div>

            <form method="POST" action="/admin/settings/update">
                <div class="form-group">
                    <label for="site_name"><?= trans('admin.settings.site_name') ?></label>
                    <input type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($_ENV['APP_NAME'] ?? 'E-Commerce') ?>" readonly>
                    <small><?= trans('admin.settings.current_value', [], null) ?? 'Current value' ?>: <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'E-Commerce') ?></small>
                </div>

                <div class="form-group">
                    <label for="site_url"><?= trans('admin.settings.site_url', [], null) ?? 'Site URL' ?></label>
                    <input type="url" id="site_url" name="site_url" value="<?= htmlspecialchars($_ENV['APP_URL'] ?? '') ?>" readonly>
                    <small><?= trans('admin.settings.current_value', [], null) ?? 'Current value' ?>: <?= htmlspecialchars($_ENV['APP_URL'] ?? trans('admin.settings.not_defined', [], null) ?? 'Not defined') ?></small>
                </div>

                <div class="form-group">
                    <label for="default_lang"><?= trans('admin.settings.language') ?></label>
                    <input type="text" id="default_lang" name="default_lang" value="<?= htmlspecialchars($_ENV['DEFAULT_LANG'] ?? 'tr') ?>" readonly>
                    <small><?= trans('admin.settings.current_value', [], null) ?? 'Current value' ?>: <?= htmlspecialchars($_ENV['DEFAULT_LANG'] ?? 'tr') ?></small>
                </div>

                <div class="form-group">
                    <label for="timezone"><?= trans('admin.settings.timezone') ?></label>
                    <input type="text" id="timezone" name="timezone" value="<?= htmlspecialchars($_ENV['APP_TIMEZONE'] ?? 'UTC') ?>" readonly>
                    <small><?= trans('admin.settings.current_value', [], null) ?? 'Current value' ?>: <?= htmlspecialchars($_ENV['APP_TIMEZONE'] ?? 'UTC') ?></small>
                </div>

                <div class="form-group">
                    <label for="currency"><?= trans('admin.settings.currency') ?></label>
                    <input type="text" id="currency" name="currency" value="<?= htmlspecialchars($_ENV['DEFAULT_CURRENCY'] ?? 'TRY') ?>" readonly>
                    <small><?= trans('admin.settings.current_value', [], null) ?? 'Current value' ?>: <?= htmlspecialchars($_ENV['DEFAULT_CURRENCY'] ?? 'TRY') ?></small>
                </div>

                <div class="form-group">
                    <label for="debug_mode"><?= trans('admin.settings.debug_mode', [], null) ?? 'Debug Mode' ?></label>
                    <input type="text" id="debug_mode" name="debug_mode" value="<?= htmlspecialchars($_ENV['APP_DEBUG'] ?? 'false') ?>" readonly>
                    <small><?= trans('admin.settings.current_value', [], null) ?? 'Current value' ?>: <?= htmlspecialchars($_ENV['APP_DEBUG'] ?? 'false') ?></small>
                </div>
            </form>
        </div>

        <!-- Database Settings -->
        <div class="card">
            <h2><?= trans('admin.settings.database_info', [], null) ?? 'Database Information' ?></h2>

            <div class="form-group">
                <label><?= trans('admin.settings.db_host', [], null) ?? 'Database Server' ?></label>
                <input type="text" value="<?= htmlspecialchars($_ENV['DB_HOST'] ?? 'localhost') ?>" readonly>
            </div>

            <div class="form-group">
                <label><?= trans('admin.settings.db_name', [], null) ?? 'Database Name' ?></label>
                <input type="text" value="<?= htmlspecialchars($_ENV['DB_DATABASE'] ?? '') ?>" readonly>
            </div>

            <div class="form-group">
                <label><?= trans('admin.settings.db_port', [], null) ?? 'Port' ?></label>
                <input type="text" value="<?= htmlspecialchars($_ENV['DB_PORT'] ?? '3306') ?>" readonly>
            </div>
        </div>

        <!-- System Info -->
        <div class="card">
            <h2><?= trans('admin.settings.system_info', [], null) ?? 'System Information' ?></h2>

            <div class="form-group">
                <label><?= trans('admin.settings.php_version', [], null) ?? 'PHP Version' ?></label>
                <input type="text" value="<?= PHP_VERSION ?>" readonly>
            </div>

            <div class="form-group">
                <label><?= trans('admin.settings.server', [], null) ?? 'Server' ?></label>
                <input type="text" value="<?= $_SERVER['SERVER_SOFTWARE'] ?? trans('admin.settings.unknown', [], null) ?? 'Unknown' ?>" readonly>
            </div>

            <div class="form-group">
                <label><?= trans('admin.settings.operating_system', [], null) ?? 'Operating System' ?></label>
                <input type="text" value="<?= PHP_OS ?>" readonly>
            </div>
        </div>
    </div>

    <script>
        // Fetch statistics
        fetch('/admin/api/settings/statistics')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('product-count').textContent = data.data.products?.total || 0;
                    document.getElementById('order-count').textContent = data.data.orders?.total || 0;
                    document.getElementById('customer-count').textContent = data.data.customers || 0;
                }
            })
            .catch(error => console.error('Error fetching statistics:', error));
    </script>
</body>
</html>

<?php
$html = ob_get_clean();
echo $html;
?>
