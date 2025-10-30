<?php
$pageTitle = 'Numune Siparişleri';
$currentPage = 'samples';

$db = container()->get(App\Core\Database::class);
$sampleService = new App\Services\SampleOrderService($db);

$page = (int) ($_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

$filters = [
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? '',
];

$samples = $sampleService->search($filters, $perPage, $offset);
$stats = $sampleService->getStatistics();

ob_start();
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div style="padding: 16px 24px; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #10b981;">
        <strong>✓</strong> <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div style="padding: 16px 24px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #ef4444;">
        <strong>✗</strong> <?= htmlspecialchars($_SESSION['error_message']) ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 class="page-title">Numune Siparişleri</h1>
        <p class="page-description">
            <?= $stats['total'] ?> toplam talep
            <?php if ($stats['pending_approval'] > 0): ?>
                • <strong style="color: var(--color-warning);"><?= $stats['pending_approval'] ?> onay bekliyor</strong>
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- Statistics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <?php
    $statusList = [
        'pending' => ['label' => 'Beklemede', 'color' => '#f59e0b'],
        'approved' => ['label' => 'Onaylandı', 'color' => '#10b981'],
        'preparing' => ['label' => 'Hazırlanıyor', 'color' => '#3b82f6'],
        'shipped' => ['label' => 'Kargoda', 'color' => '#8b5cf6'],
        'delivered' => ['label' => 'Teslim Edildi', 'color' => '#22c55e'],
        'rejected' => ['label' => 'Reddedildi', 'color' => '#ef4444'],
    ];

    foreach ($statusList as $status => $info):
        $count = $stats['by_status'][$status] ?? 0;
    ?>
        <div class="card" style="padding: 20px; text-align: center;">
            <div style="font-size: 28px; margin-bottom: 4px; color: <?= $info['color'] ?>;">
                <?= $count ?>
            </div>
            <div style="color: var(--color-text-light); font-size: 13px;">
                <?= $info['label'] ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="filters">
    <div class="filter-group">
        <label class="filter-label">Ara</label>
        <input type="text" class="form-control" placeholder="Sipariş no, müşteri, ürün..."
               value="<?= htmlspecialchars($filters['search']) ?>"
               onchange="window.location.href = '?search=' + encodeURIComponent(this.value) + '&status=<?= $filters['status'] ?>'">
    </div>

    <div class="filter-group">
        <label class="filter-label">Durum</label>
        <select class="form-control"
                onchange="window.location.href = '?status=' + this.value + '&search=<?= urlencode($filters['search']) ?>'">
            <option value="">Tümü</option>
            <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>⏳ Beklemede</option>
            <option value="approved" <?= $filters['status'] === 'approved' ? 'selected' : '' ?>>✅ Onaylandı</option>
            <option value="preparing" <?= $filters['status'] === 'preparing' ? 'selected' : '' ?>>📦 Hazırlanıyor</option>
            <option value="shipped" <?= $filters['status'] === 'shipped' ? 'selected' : '' ?>>🚚 Kargoda</option>
            <option value="delivered" <?= $filters['status'] === 'delivered' ? 'selected' : '' ?>>✔️ Teslim Edildi</option>
            <option value="rejected" <?= $filters['status'] === 'rejected' ? 'selected' : '' ?>>❌ Reddedildi</option>
        </select>
    </div>
</div>

<!-- Sample Orders List -->
<div class="card">
    <?php if (empty($samples)): ?>
        <div style="padding: 60px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">📦</div>
            <h3>Henüz numune talebi yok</h3>
            <p style="color: var(--color-text-light);">Müşteriler ürün sayfasından numune talep edebilir</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Sipariş No</th>
                        <th>Müşteri</th>
                        <th>Ürün</th>
                        <th style="text-align: center;">Miktar</th>
                        <th style="text-align: center;">Durum</th>
                        <th style="text-align: right;">Kargo</th>
                        <th>Talep Tarihi</th>
                        <th style="width: 150px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($samples as $sample): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($sample['order_number']) ?></strong></td>
                            <td>
                                <?= htmlspecialchars($sample['customer_name']) ?><br>
                                <small style="color: var(--color-text-light);">
                                    <?= htmlspecialchars($sample['customer_email']) ?>
                                </small>
                            </td>
                            <td>
                                <?= htmlspecialchars($sample['product_name']) ?><br>
                                <small style="color: var(--color-text-light);">
                                    SKU: <?= htmlspecialchars($sample['product_sku']) ?>
                                </small>
                            </td>
                            <td style="text-align: center;"><strong><?= $sample['quantity'] ?></strong></td>
                            <td style="text-align: center;">
                                <span class="badge badge-<?= $sample['status'] ?>">
                                    <?= App\Services\SampleOrderService::getStatusIcon($sample['status']) ?>
                                    <?= App\Services\SampleOrderService::getStatusLabel($sample['status']) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <?php if ($sample['shipping_cost'] > 0): ?>
                                    ₺<?= number_format($sample['shipping_cost'], 2) ?>
                                <?php else: ?>
                                    <span style="color: var(--color-success);">Ücretsiz</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($sample['created_at'])) ?></td>
                            <td>
                                <a href="/admin/samples/<?= $sample['id'] ?>" class="btn btn-secondary btn-sm">
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
