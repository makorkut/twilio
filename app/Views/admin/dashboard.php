<?php
/**
 * Admin Dashboard
 * Statistics, recent orders, low stock alerts
 */

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

// Get statistics (will be replaced with real data from controller)
$stats = [
    'total_orders' => 0,
    'total_revenue' => 0,
    'total_products' => 0,
    'low_stock_count' => 0,
    'pending_orders' => 0,
    'today_orders' => 0,
];

try {
    $db = container()->get(App\Core\Database::class);

    // Total orders
    $result = $db->fetch("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $result['count'] ?? 0;

    // Total revenue
    $result = $db->fetch("SELECT SUM(grand_total) as total FROM orders WHERE payment_status = 'paid'");
    $stats['total_revenue'] = $result['total'] ?? 0;

    // Total products
    $result = $db->fetch("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
    $stats['total_products'] = $result['count'] ?? 0;

    // Low stock
    $result = $db->fetch("SELECT COUNT(*) as count FROM products WHERE stock_quantity <= low_stock_threshold AND track_inventory = 1");
    $stats['low_stock_count'] = $result['count'] ?? 0;

    // Pending orders
    $result = $db->fetch("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
    $stats['pending_orders'] = $result['count'] ?? 0;

    // Today's orders
    $result = $db->fetch("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
    $stats['today_orders'] = $result['count'] ?? 0;

    // Recent orders
    $recentOrders = $db->fetchAll("
        SELECT id, order_number, customer_name, grand_total, currency_code, status, created_at
        FROM orders
        ORDER BY created_at DESC
        LIMIT 10
    ");

    // Low stock products
    $lowStockProducts = $db->fetchAll("
        SELECT id, sku, name, stock_quantity, low_stock_threshold
        FROM products
        WHERE stock_quantity <= low_stock_threshold
        AND track_inventory = 1
        AND status = 'active'
        ORDER BY stock_quantity ASC
        LIMIT 10
    ");

} catch (\Exception $e) {
    $recentOrders = [];
    $lowStockProducts = [];
}

ob_start();
?>

<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-description">E-ticaret sisteminizin genel görünümü</p>
</div>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid var(--color-border);
    }

    .stat-label {
        font-size: 13px;
        color: var(--color-text-light);
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: var(--color-text);
        margin-bottom: 8px;
    }

    .stat-change {
        font-size: 13px;
        color: var(--color-success);
    }

    .stat-change.negative {
        color: var(--color-danger);
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        text-align: left;
        padding: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--color-text-light);
        border-bottom: 1px solid var(--color-border);
    }

    td {
        padding: 12px;
        border-bottom: 1px solid var(--color-border);
    }

    tr:hover {
        background: var(--color-bg);
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-processing {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-shipped {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-delivered {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .stock-warning {
        color: var(--color-danger);
        font-weight: 600;
    }

    .stock-ok {
        color: var(--color-success);
    }
</style>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Toplam Sipariş</div>
        <div class="stat-value"><?= number_format($stats['total_orders']) ?></div>
        <div class="stat-change">
            <?php if ($stats['today_orders'] > 0): ?>
                +<?= $stats['today_orders'] ?> bugün
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Toplam Gelir</div>
        <div class="stat-value">₺<?= number_format($stats['total_revenue'], 2) ?></div>
        <div class="stat-change">Ödenen siparişler</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Aktif Ürünler</div>
        <div class="stat-value"><?= number_format($stats['total_products']) ?></div>
        <div class="stat-change">
            <a href="/admin/products" style="color: var(--color-primary); text-decoration: none;">Tümünü gör →</a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Düşük Stok</div>
        <div class="stat-value" style="color: var(--color-danger);"><?= number_format($stats['low_stock_count']) ?></div>
        <div class="stat-change">Dikkat gerekiyor</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Bekleyen Siparişler</div>
        <div class="stat-value" style="color: var(--color-warning);"><?= number_format($stats['pending_orders']) ?></div>
        <div class="stat-change">
            <a href="/admin/orders?status=pending" style="color: var(--color-primary); text-decoration: none;">İşleme al →</a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Bugünkü Siparişler</div>
        <div class="stat-value" style="color: var(--color-success);"><?= number_format($stats['today_orders']) ?></div>
        <div class="stat-change">Son 24 saat</div>
    </div>
</div>

<!-- Recent Orders -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Son Siparişler</h2>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Sipariş No</th>
                    <th>Müşteri</th>
                    <th>Tutar</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--color-text-light);">
                            Henüz sipariş yok
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td>
                                <?= htmlspecialchars($order['currency_code']) ?>
                                <?= number_format($order['grand_total'], 2) ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($order['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                </span>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                            <td>
                                <a href="/admin/orders/<?= $order['id'] ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                                    Detay
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Low Stock Products -->
<?php if (!empty($lowStockProducts)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">⚠️ Düşük Stoklu Ürünler</h2>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Ürün Adı</th>
                    <th>Mevcut Stok</th>
                    <th>Minimum Stok</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lowStockProducts as $product): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($product['sku']) ?></strong></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td class="stock-warning">
                            <?= number_format($product['stock_quantity']) ?> adet
                        </td>
                        <td><?= number_format($product['low_stock_threshold']) ?> adet</td>
                        <td>
                            <a href="/admin/products/edit/<?= $product['id'] ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                                Düzenle
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
