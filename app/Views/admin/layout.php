<?php
/**
 * Admin Panel Layout
 * Modern sidebar design
 */

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: /admin/login');
    exit;
}

$user = App\Core\Auth::user();
$pageTitle = $pageTitle ?? 'Admin Panel';
$currentPage = $currentPage ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - E-Commerce Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --sidebar-width: 260px;
            --header-height: 60px;
            --color-primary: #2563eb;
            --color-primary-dark: #1e40af;
            --color-bg: #f8fafc;
            --color-sidebar: #1e293b;
            --color-sidebar-hover: #334155;
            --color-text: #1e293b;
            --color-text-light: #64748b;
            --color-border: #e2e8f0;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--color-bg);
            color: var(--color-text);
            font-size: 14px;
            line-height: 1.5;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--color-sidebar);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logo {
            font-size: 20px;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: block;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-section {
            margin-bottom: 24px;
        }

        .nav-section-title {
            padding: 0 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .nav-item {
            display: block;
            padding: 10px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-item:hover {
            background: var(--color-sidebar-hover);
            color: white;
        }

        .nav-item.active {
            background: var(--color-primary);
            color: white;
        }

        .nav-item-icon {
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        /* Main Content */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Header */
        .header {
            position: sticky;
            top: 0;
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            z-index: 999;
        }

        .header-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--color-text);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: var(--color-bg);
            border-radius: 8px;
            cursor: pointer;
            position: relative;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--color-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 500;
            font-size: 13px;
        }

        .user-role {
            font-size: 11px;
            color: var(--color-text-light);
        }

        /* Content Area */
        .content {
            padding: 32px;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-text);
            margin-bottom: 8px;
        }

        .page-description {
            color: var(--color-text-light);
            font-size: 14px;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--color-border);
            padding: 24px;
            margin-bottom: 24px;
        }

        .card-header {
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--color-border);
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--color-text);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--color-primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--color-primary-dark);
        }

        .btn-secondary {
            background: var(--color-bg);
            color: var(--color-text);
            border: 1px solid var(--color-border);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        /* Tables */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            text-align: left;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text);
            background: var(--color-bg);
            border-bottom: 1px solid var(--color-border);
        }

        table td {
            padding: 12px 16px;
            font-size: 14px;
            border-bottom: 1px solid var(--color-border);
        }

        table tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-draft {
            background: #f3f4f6;
            color: #374151;
        }

        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-archived {
            background: #e0e7ff;
            color: #3730a3;
        }

        .badge-processing {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-warning {
            background: #fed7aa;
            color: #92400e;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-wrapper {
                margin-left: 0;
            }

            .header {
                padding: 0 16px;
            }

            .content {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="/admin" class="sidebar-logo">
                🏢 E-Commerce Admin
            </a>
        </div>

        <nav class="sidebar-nav">
            <!-- Dashboard -->
            <div class="nav-section">
                <a href="/admin" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <span class="nav-item-icon">📊</span>
                    <span>Dashboard</span>
                </a>
            </div>

            <!-- Catalog -->
            <div class="nav-section">
                <div class="nav-section-title">Katalog</div>
                <a href="/admin/products" class="nav-item <?= $currentPage === 'products' ? 'active' : '' ?>">
                    <span class="nav-item-icon">📦</span>
                    <span>Ürünler</span>
                </a>
                <a href="/admin/categories" class="nav-item <?= $currentPage === 'categories' ? 'active' : '' ?>">
                    <span class="nav-item-icon">📁</span>
                    <span>Kategoriler</span>
                </a>
                <a href="/admin/media" class="nav-item <?= $currentPage === 'media' ? 'active' : '' ?>">
                    <span class="nav-item-icon">🖼️</span>
                    <span>Medya Kütüphanesi</span>
                </a>
            </div>

            <!-- Sales -->
            <div class="nav-section">
                <div class="nav-section-title">Satışlar</div>
                <a href="/admin/orders" class="nav-item <?= $currentPage === 'orders' ? 'active' : '' ?>">
                    <span class="nav-item-icon">🛒</span>
                    <span>Siparişler</span>
                </a>
                <a href="/admin/customers" class="nav-item <?= $currentPage === 'customers' ? 'active' : '' ?>">
                    <span class="nav-item-icon">👥</span>
                    <span>Müşteriler</span>
                </a>
                <a href="/admin/coupons" class="nav-item <?= $currentPage === 'coupons' ? 'active' : '' ?>">
                    <span class="nav-item-icon">🎟️</span>
                    <span>Kuponlar</span>
                </a>
            </div>

            <!-- Content -->
            <div class="nav-section">
                <div class="nav-section-title">İçerik</div>
                <a href="/admin/documents" class="nav-item <?= $currentPage === 'documents' ? 'active' : '' ?>">
                    <span class="nav-item-icon">📄</span>
                    <span>Teknik Dokümanlar</span>
                </a>
                <a href="/admin/media" class="nav-item <?= $currentPage === 'media' ? 'active' : '' ?>">
                    <span class="nav-item-icon">🖼️</span>
                    <span>Medya Kütüphanesi</span>
                </a>
            </div>

            <!-- B2B -->
            <div class="nav-section">
                <div class="nav-section-title">B2B</div>
                <a href="/admin/samples" class="nav-item <?= $currentPage === 'samples' ? 'active' : '' ?>">
                    <span class="nav-item-icon">📦</span>
                    <span>Numune Siparişleri</span>
                </a>
                <a href="/admin/customer-groups" class="nav-item <?= $currentPage === 'customer-groups' ? 'active' : '' ?>">
                    <span class="nav-item-icon">👔</span>
                    <span>Müşteri Grupları</span>
                </a>
                <a href="/admin/tier-pricing" class="nav-item <?= $currentPage === 'tier-pricing' ? 'active' : '' ?>">
                    <span class="nav-item-icon">💰</span>
                    <span>Kademeli Fiyatlar</span>
                </a>
            </div>

            <!-- Automation -->
            <div class="nav-section">
                <div class="nav-section-title">Otomasyon</div>
                <a href="/admin/webhooks" class="nav-item <?= $currentPage === 'webhooks' ? 'active' : '' ?>">
                    <span class="nav-item-icon">🔗</span>
                    <span>Webhooks</span>
                </a>
                <a href="/admin/api-tokens" class="nav-item <?= $currentPage === 'api-tokens' ? 'active' : '' ?>">
                    <span class="nav-item-icon">🔑</span>
                    <span>API Tokens</span>
                </a>
            </div>

            <!-- Settings -->
            <div class="nav-section">
                <div class="nav-section-title">Ayarlar</div>
                <a href="/admin/settings" class="nav-item <?= $currentPage === 'settings' ? 'active' : '' ?>">
                    <span class="nav-item-icon">⚙️</span>
                    <span>Genel Ayarlar</span>
                </a>
                <a href="/admin/languages" class="nav-item <?= $currentPage === 'languages' ? 'active' : '' ?>">
                    <span class="nav-item-icon">🌍</span>
                    <span>Diller</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
        <!-- Header -->
        <header class="header">
            <div class="header-title"><?= htmlspecialchars($pageTitle) ?></div>

            <div class="header-actions">
                <div class="user-menu">
                    <div class="user-avatar">
                        <?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'Admin') ?></div>
                        <div class="user-role"><?= htmlspecialchars($user['role'] ?? 'Administrator') ?></div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="content">
            <?php
            // Content will be injected here
            if (isset($content)) {
                echo $content;
            }
            ?>
        </main>
    </div>
</body>
</html>
