<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'E-Commerce') ?></title>

    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? '') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($metaKeywords ?? '') ?>">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --color-bg: #FFFFFF;
            --color-bg-gray: #F5F5F5;
            --color-text: #2C2C2C;
            --color-text-light: #6B6B6B;
            --color-primary: #1A1A1A;
            --color-accent: #D4AF37;
            --color-border: #E0E0E0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--color-text);
            background: var(--color-bg);
            font-size: 16px;
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: white;
            border-bottom: 1px solid var(--color-border);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-top {
            background: var(--color-bg-gray);
            padding: 8px 0;
            font-size: 13px;
            color: var(--color-text-light);
            text-align: center;
        }

        .header-main {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 24px;
            font-weight: 300;
            letter-spacing: 2px;
            color: var(--color-primary);
            text-decoration: none;
            text-transform: uppercase;
        }

        .nav {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .nav a {
            color: var(--color-text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 400;
            transition: color 0.2s;
            letter-spacing: 0.5px;
        }

        .nav a:hover {
            color: var(--color-primary);
        }

        .header-actions {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .cart-icon {
            position: relative;
            color: var(--color-text);
            text-decoration: none;
            font-size: 20px;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--color-primary);
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 500;
        }

        /* Main Content */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .container-narrow {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px;
        }

        /* Footer */
        .footer {
            background: var(--color-bg-gray);
            margin-top: 80px;
            padding: 60px 0 20px;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 60px;
            margin-bottom: 40px;
        }

        .footer-section h4 {
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 12px;
        }

        .footer-section a {
            color: var(--color-text-light);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }

        .footer-section a:hover {
            color: var(--color-primary);
        }

        .footer-bottom {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px 40px 0;
            border-top: 1px solid var(--color-border);
            text-align: center;
            color: var(--color-text-light);
            font-size: 13px;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: var(--color-primary);
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #000;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }

        .btn-secondary {
            background: white;
            color: var(--color-primary);
            border: 1px solid var(--color-border);
        }

        .btn-secondary:hover {
            background: var(--color-bg-gray);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-main {
                padding: 16px 20px;
            }

            .container,
            .container-narrow {
                padding: 0 20px;
            }

            .nav {
                display: none; /* Mobile menu needed */
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 40px;
                padding: 0 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            📦 Ücretsiz Kargo - 500₺ Üzeri Siparişlerde
        </div>
        <div class="header-main">
            <a href="/" class="logo">POLYURETHANE</a>

            <nav class="nav">
                <a href="/products">Ürünler</a>
                <a href="/categories">Kategoriler</a>
                <a href="/projects">Projeler</a>
                <a href="/about">Hakkımızda</a>
                <a href="/contact">İletişim</a>
            </nav>

            <div class="header-actions">
                <a href="/search" style="text-decoration: none; color: var(--color-text);">🔍</a>
                <a href="/cart" class="cart-icon">
                    🛒
                    <span class="cart-count">0</span>
                </a>
                <a href="/account" style="text-decoration: none; color: var(--color-text);">👤</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Polyurethane</h4>
                <p style="color: var(--color-text-light); font-size: 14px; line-height: 1.8;">
                    Yüksek kaliteli poliüretan ürünleri ile mekanlarınıza değer katıyoruz.
                    Profesyonel çözümler, hızlı teslimat.
                </p>
            </div>

            <div class="footer-section">
                <h4>Kurumsal</h4>
                <ul>
                    <li><a href="/about">Hakkımızda</a></li>
                    <li><a href="/projects">Projeler</a></li>
                    <li><a href="/careers">Kariyer</a></li>
                    <li><a href="/contact">İletişim</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Müşteri Hizmetleri</h4>
                <ul>
                    <li><a href="/shipping">Kargo & Teslimat</a></li>
                    <li><a href="/returns">İade & Değişim</a></li>
                    <li><a href="/faq">Sık Sorulan Sorular</a></li>
                    <li><a href="/samples">Numune Sipariş</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>B2B</h4>
                <ul>
                    <li><a href="/b2b">Bayi Başvurusu</a></li>
                    <li><a href="/wholesale">Toptan Fiyatlar</a></li>
                    <li><a href="/catalog">PDF Katalog</a></li>
                    <li><a href="/api">API Entegrasyon</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2024 Polyurethane. Tüm hakları saklıdır.</p>
        </div>
    </footer>
</body>
</html>
