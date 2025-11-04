<?php
$pageTitle = trans('common.orders');
$currentPage = 'orders';

$db = container()->get(App\Core\Database::class);
$orderService = new App\Services\OrderService($db);

$page = (int) ($_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

$filters = [
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? '',
    'payment_status' => $_GET['payment_status'] ?? '',
];

$orders = $orderService->search($filters, $perPage, $offset);
$totalOrders = count($orders); // Simplified

ob_start();
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div style="padding: 16px 24px; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #10b981;">
        <strong>✓</strong> <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 class="page-title">Siparişler</h1>
        <p class="page-description"><?= $totalOrders ?> sipariş</p>
    </div>
</div>

<div class="filters">
    <div class="filter-group">
        <label class="filter-label">Ara</label>
        <input type="text" class="form-control" placeholder="Sipariş no, müşteri..."
               value="<?= htmlspecialchars($filters['search']) ?>"
               onchange="window.location.href = '?search=' + encodeURIComponent(this.value) + '&status=<?= $filters['status'] ?>'">
    </div>

    <div class="filter-group">
        <label class="filter-label">Durum</label>
        <select class="form-control"
                onchange="window.location.href = '?status=' + this.value + '&search=<?= $filters['search'] ?>'">
            <option value="">Tümü</option>
            <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Beklemede</option>
            <option value="processing" <?= $filters['status'] === 'processing' ? 'selected' : '' ?>>İşleniyor</option>
            <option value="shipped" <?= $filters['status'] === 'shipped' ? 'selected' : '' ?>>Kargoda</option>
            <option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>Tamamlandı</option>
            <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>İptal</option>
        </select>
    </div>
</div>

<div class="card">
    <?php if (empty($orders)): ?>
        <div style="padding: 60px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">🛒</div>
            <h3>Henüz sipariş yok</h3>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Sipariş No</th>
                        <th>Müşteri</th>
                        <th style="text-align: right;">Tutar</th>
                        <th style="text-align: center;">Durum</th>
                        <th style="text-align: center;">Ödeme</th>
                        <th>Tarih</th>
                        <th style="width: 150px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                            <td><?= htmlspecialchars($order['customer_name'] ?? $order['customer_email']) ?></td>
                            <td style="text-align: right;"><strong>₺<?= number_format($order['total'], 2) ?></strong></td>
                            <td style="text-align: center;">
                                <span class="badge badge-<?= $order['status'] ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-<?= $order['payment_status'] ?>">
                                    <?= ucfirst($order['payment_status']) ?>
                                </span>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                            <td>
                                <a href="/admin/orders/<?= $order['id'] ?>" class="btn btn-secondary btn-sm">
                                    👁️ Detay
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
