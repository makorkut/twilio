<?php
$pageTitle = 'Müşteriler';
$currentPage = 'customers';

$db = container()->get(App\Core\Database::class);
$customerService = new App\Services\CustomerService($db);

$page = (int) ($_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

$filters = [
    'search' => $_GET['search'] ?? '',
    'customer_group_id' => $_GET['group'] ?? '',
    'status' => $_GET['status'] ?? '',
];

$customers = $customerService->search($filters, $perPage, $offset);

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
        <h1 class="page-title">Müşteriler</h1>
        <p class="page-description"><?= count($customers) ?> müşteri</p>
    </div>
</div>

<div class="filters">
    <div class="filter-group">
        <label class="filter-label">Ara</label>
        <input type="text" class="form-control" placeholder="Ad, e-posta, telefon..."
               value="<?= htmlspecialchars($filters['search']) ?>"
               onchange="window.location.href = '?search=' + encodeURIComponent(this.value)">
    </div>

    <div class="filter-group">
        <label class="filter-label">Müşteri Grubu</label>
        <select class="form-control" onchange="window.location.href = '?group=' + this.value">
            <option value="">Tümü</option>
            <?php
            $groups = $db->fetchAll("SELECT * FROM customer_groups ORDER BY name");
            foreach ($groups as $group):
            ?>
                <option value="<?= $group['id'] ?>" <?= $filters['customer_group_id'] == $group['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($group['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-group">
        <label class="filter-label">Durum</label>
        <select class="form-control" onchange="window.location.href = '?status=' + this.value">
            <option value="">Tümü</option>
            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Aktif</option>
            <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Pasif</option>
        </select>
    </div>
</div>

<div class="card">
    <?php if (empty($customers)): ?>
        <div style="padding: 60px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">👥</div>
            <h3>Henüz müşteri yok</h3>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Müşteri</th>
                        <th>E-posta</th>
                        <th>Telefon</th>
                        <th style="text-align: center;">Grup</th>
                        <th style="text-align: center;">Durum</th>
                        <th>Kayıt Tarihi</th>
                        <th style="width: 150px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($customer['name']) ?></strong></td>
                            <td><?= htmlspecialchars($customer['email']) ?></td>
                            <td><?= htmlspecialchars($customer['phone'] ?? '-') ?></td>
                            <td style="text-align: center;">
                                <?php if ($customer['group_name']): ?>
                                    <span class="badge badge-info"><?= htmlspecialchars($customer['group_name']) ?></span>
                                <?php else: ?>
                                    <span style="color: var(--color-text-light);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-<?= $customer['status'] ?>">
                                    <?= ucfirst($customer['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d.m.Y', strtotime($customer['created_at'])) ?></td>
                            <td>
                                <a href="/admin/customers/<?= $customer['id'] ?>" class="btn btn-secondary btn-sm">
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
