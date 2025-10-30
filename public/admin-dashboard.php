<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - E-Commerce</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f3f4f6;
        }
        .header {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #111827;
            font-size: 24px;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .user-name {
            color: #6b7280;
            font-size: 14px;
        }
        .btn-logout {
            padding: 8px 16px;
            background: #ef4444;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            transition: background 0.2s;
        }
        .btn-logout:hover {
            background: #dc2626;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 24px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .stat-card .value {
            color: #111827;
            font-size: 32px;
            font-weight: 700;
        }
        .welcome {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 32px;
            border-radius: 12px;
            margin-bottom: 32px;
        }
        .welcome h2 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .welcome p {
            font-size: 16px;
            opacity: 0.9;
        }
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        .quick-link {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-decoration: none;
            color: #111827;
            border: 2px solid #e5e7eb;
            transition: all 0.2s;
            display: block;
        }
        .quick-link:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .quick-link h3 {
            font-size: 16px;
            margin-bottom: 4px;
        }
        .quick-link p {
            font-size: 13px;
            color: #6b7280;
        }
        .alert {
            margin-top: 40px;
            padding: 20px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
        }
        .alert strong {
            display: block;
            margin-bottom: 8px;
        }
        .alert p {
            color: #856404;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚀 Admin Panel</h1>
        <div class="user-info">
            <span class="user-name">👤 {{USER_NAME}}</span>
            <a href="/admin/logout" class="btn-logout">Çıkış</a>
        </div>
    </div>

    <div class="container">
        <div class="welcome">
            <h2>Hoş geldin, {{USER_NAME}}! 👋</h2>
            <p>E-Commerce Admin Panel'ine başarıyla giriş yaptınız.</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Toplam Ürünler</h3>
                <div class="value">-</div>
            </div>
            <div class="stat-card">
                <h3>Toplam Siparişler</h3>
                <div class="value">-</div>
            </div>
            <div class="stat-card">
                <h3>Toplam Müşteriler</h3>
                <div class="value">-</div>
            </div>
            <div class="stat-card">
                <h3>Sistem Durumu</h3>
                <div class="value">✅</div>
            </div>
        </div>

        <h2 style="margin-bottom: 16px; color: #111827;">Hızlı Erişim</h2>
        <div class="quick-links">
            <a href="/api/v1/health" class="quick-link" target="_blank">
                <h3>🏥 API Health</h3>
                <p>Sistem durumunu kontrol et</p>
            </a>
            <a href="/healthz" class="quick-link" target="_blank">
                <h3>✅ Health Check</h3>
                <p>Container sağlık durumu</p>
            </a>
            <a href="/" class="quick-link" target="_blank">
                <h3>🏠 Ana Sayfa</h3>
                <p>Siteye git</p>
            </a>
            <a href="/admin/logout" class="quick-link">
                <h3>🚪 Çıkış Yap</h3>
                <p>Oturumu sonlandır</p>
            </a>
        </div>

        <div class="alert">
            <strong>⚠️ Önemli Hatırlatma:</strong>
            <p>
                Güvenlik nedeniyle admin şifrenizi değiştirmeniz önerilir.
                İlk kurulumda oluşturulan varsayılan şifreler üretim ortamında kullanılmamalıdır.
            </p>
        </div>
    </div>
</body>
</html>
