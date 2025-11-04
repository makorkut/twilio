<?php
$pageTitle = trans('admin.samples.title');
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
        <h1 class="page-title"><?= trans('admin.samples.title') ?></h1>
        <p class="page-description">
            <?= $stats['total'] ?> <?= trans('admin.samples.total_requests', [], null) ?? 'total requests' ?>
            <?php if ($stats['pending_approval'] > 0): ?>
                • <strong style="color: var(--color-warning);"><?= $stats['pending_approval'] ?> <?= trans('admin.samples.pending_approval', [], null) ?? 'awaiting approval' ?></strong>
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- Statistics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <?php
    $statusList = [
        'pending' => ['label' => trans('admin.samples.status_pending'), 'color' => '#f59e0b'],
        'approved' => ['label' => trans('admin.samples.status_approved'), 'color' => '#10b981'],
        'preparing' => ['label' => trans('admin.samples.status_preparing', [], null) ?? 'Preparing', 'color' => '#3b82f6'],
        'shipped' => ['label' => trans('admin.samples.status_sent'), 'color' => '#8b5cf6'],
        'delivered' => ['label' => trans('admin.samples.status_received'), 'color' => '#22c55e'],
        'rejected' => ['label' => trans('admin.samples.status_cancelled'), 'color' => '#ef4444'],
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
        <label class="filter-label"><?= trans('common.search') ?></label>
        <input type="text" class="form-control" placeholder="<?= trans('admin.samples.search_placeholder', [], null) ?? 'Order #, customer, product...' ?>"
               value="<?= htmlspecialchars($filters['search']) ?>"
               onchange="window.location.href = '?search=' + encodeURIComponent(this.value) + '&status=<?= $filters['status'] ?>'">
    </div>

    <div class="filter-group">
        <label class="filter-label"><?= trans('common.status') ?></label>
        <select class="form-control"
                onchange="window.location.href = '?status=' + this.value + '&search=<?= urlencode($filters['search']) ?>'">
            <option value=""><?= trans('common.all') ?></option>
            <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>⏳ <?= trans('admin.samples.status_pending') ?></option>
            <option value="approved" <?= $filters['status'] === 'approved' ? 'selected' : '' ?>>✅ <?= trans('admin.samples.status_approved') ?></option>
            <option value="preparing" <?= $filters['status'] === 'preparing' ? 'selected' : '' ?>>📦 <?= trans('admin.samples.status_preparing', [], null) ?? 'Preparing' ?></option>
            <option value="shipped" <?= $filters['status'] === 'shipped' ? 'selected' : '' ?>>🚚 <?= trans('admin.samples.status_sent') ?></option>
            <option value="delivered" <?= $filters['status'] === 'delivered' ? 'selected' : '' ?>>✔️ <?= trans('admin.samples.status_received') ?></option>
            <option value="rejected" <?= $filters['status'] === 'rejected' ? 'selected' : '' ?>>❌ <?= trans('admin.samples.status_cancelled') ?></option>
        </select>
    </div>
</div>

<!-- Sample Orders List -->
<div class="card">
    <?php if (empty($samples)): ?>
        <div style="padding: 60px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">📦</div>
            <h3><?= trans('admin.samples.no_samples') ?></h3>
            <p style="color: var(--color-text-light);"><?= trans('admin.samples.no_samples_description', [], null) ?? 'Customers can request samples from product pages' ?></p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><?= trans('admin.samples.sample_code') ?></th>
                        <th><?= trans('admin.samples.customer') ?></th>
                        <th><?= trans('admin.samples.product') ?></th>
                        <th style="text-align: center;"><?= trans('admin.samples.quantity') ?></th>
                        <th style="text-align: center;"><?= trans('common.status') ?></th>
                        <th style="text-align: right;"><?= trans('admin.samples.shipping_cost', [], null) ?? 'Shipping' ?></th>
                        <th><?= trans('admin.samples.request_date') ?></th>
                        <th style="width: 150px;"><?= trans('common.actions') ?></th>
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
                                    <span style="color: var(--color-success);"><?= trans('admin.samples.free_shipping', [], null) ?? 'Free' ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($sample['created_at'])) ?></td>
                            <td>
                                <a href="/admin/samples/<?= $sample['id'] ?>" class="btn btn-secondary btn-sm">
                                    👁️ <?= trans('common.details') ?>
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
