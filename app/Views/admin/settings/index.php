<?php
$pageTitle = 'Ayarlar - Admin Panel';

ob_start();
?>

<!DOCTYPE html>
<html lang="tr">
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
        <a href="/admin">Dashboard</a>
        <a href="/admin/products">Ürünler</a>
        <a href="/admin/categories">Kategoriler</a>
        <a href="/admin/orders">Siparişler</a>
        <a href="/admin/languages">Diller</a>
        <a href="/admin/settings" style="color: #1a1a1a; font-weight: 600;">Ayarlar</a>
        <a href="/admin/logout" style="float: right;">Çıkış</a>
    </nav>

    <!-- Header -->
    <div class="header">
        <h1>Sistem Ayarları</h1>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Statistics Dashboard -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" id="product-count">-</div>
                <div class="stat-label">Toplam Ürün</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="order-count">-</div>
                <div class="stat-label">Toplam Sipariş</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="customer-count">-</div>
                <div class="stat-label">Toplam Müşteri</div>
            </div>
        </div>

        <!-- General Settings -->
        <div class="card">
            <h2>Genel Ayarlar</h2>

            <div class="info-box">
                <p><strong>Not:</strong> Bu ayarlar .env dosyasından okunmaktadır. Değişiklikler için .env dosyasını düzenleyin ve sunucuyu yeniden başlatın.</p>
            </div>

            <form method="POST" action="/admin/settings/update">
                <div class="form-group">
                    <label for="site_name">Site Adı</label>
                    <input type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($_ENV['APP_NAME'] ?? 'E-Commerce') ?>" readonly>
                    <small>Mevcut değer: <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'E-Commerce') ?></small>
                </div>

                <div class="form-group">
                    <label for="site_url">Site URL</label>
                    <input type="url" id="site_url" name="site_url" value="<?= htmlspecialchars($_ENV['APP_URL'] ?? '') ?>" readonly>
                    <small>Mevcut değer: <?= htmlspecialchars($_ENV['APP_URL'] ?? 'Tanımlı değil') ?></small>
                </div>

                <div class="form-group">
                    <label for="default_lang">Varsayılan Dil</label>
                    <input type="text" id="default_lang" name="default_lang" value="<?= htmlspecialchars($_ENV['DEFAULT_LANG'] ?? 'tr') ?>" readonly>
                    <small>Mevcut değer: <?= htmlspecialchars($_ENV['DEFAULT_LANG'] ?? 'tr') ?></small>
                </div>

                <div class="form-group">
                    <label for="timezone">Saat Dilimi</label>
                    <input type="text" id="timezone" name="timezone" value="<?= htmlspecialchars($_ENV['APP_TIMEZONE'] ?? 'UTC') ?>" readonly>
                    <small>Mevcut değer: <?= htmlspecialchars($_ENV['APP_TIMEZONE'] ?? 'UTC') ?></small>
                </div>

                <div class="form-group">
                    <label for="currency">Para Birimi</label>
                    <input type="text" id="currency" name="currency" value="<?= htmlspecialchars($_ENV['DEFAULT_CURRENCY'] ?? 'TRY') ?>" readonly>
                    <small>Mevcut değer: <?= htmlspecialchars($_ENV['DEFAULT_CURRENCY'] ?? 'TRY') ?></small>
                </div>

                <div class="form-group">
                    <label for="debug_mode">Debug Modu</label>
                    <input type="text" id="debug_mode" name="debug_mode" value="<?= htmlspecialchars($_ENV['APP_DEBUG'] ?? 'false') ?>" readonly>
                    <small>Mevcut değer: <?= htmlspecialchars($_ENV['APP_DEBUG'] ?? 'false') ?></small>
                </div>
            </form>
        </div>

        <!-- Database Settings -->
        <div class="card">
            <h2>Veritabanı Bilgileri</h2>

            <div class="form-group">
                <label>Veritabanı Sunucusu</label>
                <input type="text" value="<?= htmlspecialchars($_ENV['DB_HOST'] ?? 'localhost') ?>" readonly>
            </div>

            <div class="form-group">
                <label>Veritabanı Adı</label>
                <input type="text" value="<?= htmlspecialchars($_ENV['DB_DATABASE'] ?? '') ?>" readonly>
            </div>

            <div class="form-group">
                <label>Port</label>
                <input type="text" value="<?= htmlspecialchars($_ENV['DB_PORT'] ?? '3306') ?>" readonly>
            </div>
        </div>

        <!-- System Info -->
        <div class="card">
            <h2>Sistem Bilgileri</h2>

            <div class="form-group">
                <label>PHP Versiyonu</label>
                <input type="text" value="<?= PHP_VERSION ?>" readonly>
            </div>

            <div class="form-group">
                <label>Sunucu</label>
                <input type="text" value="<?= $_SERVER['SERVER_SOFTWARE'] ?? 'Bilinmiyor' ?>" readonly>
            </div>

            <div class="form-group">
                <label>İşletim Sistemi</label>
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
