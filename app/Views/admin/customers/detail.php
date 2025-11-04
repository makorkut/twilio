<?php
$pageTitle = trans('admin.customers.detail');
$currentPage = 'customers';

if (!isset($customer)) {
    header('Location: /admin/customers');
    exit;
}

ob_start();
?>

<div class="page-header">
    <h1 class="page-title"><?= htmlspecialchars($customer['name']) ?></h1>
    <p class="page-description"><?= htmlspecialchars($customer['email']) ?></p>
</div>

<div class="form-grid">
    <div class="form-main">
        <!-- Customer Info -->
        <div class="form-section">
            <h3 class="form-section-title">Müşteri Bilgileri</h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                <div>
                    <strong>Ad Soyad:</strong><br>
                    <?= htmlspecialchars($customer['name']) ?>
                </div>
                <div>
                    <strong>E-posta:</strong><br>
                    <?= htmlspecialchars($customer['email']) ?>
                </div>
                <div>
                    <strong>Telefon:</strong><br>
                    <?= htmlspecialchars($customer['phone'] ?? '-') ?>
                </div>
                <div>
                    <strong>Müşteri Grubu:</strong><br>
                    <?= htmlspecialchars($customer['group_name'] ?? 'Bireysel') ?>
                </div>
                <div>
                    <strong>Kayıt Tarihi:</strong><br>
                    <?= date('d.m.Y H:i', strtotime($customer['created_at'])) ?>
                </div>
                <div>
                    <strong>Durum:</strong><br>
                    <span class="badge badge-<?= $customer['status'] ?>">
                        <?= ucfirst($customer['status']) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- B2B Info -->
        <?php if (!empty($customer['credit_limit'])): ?>
        <div class="form-section">
            <h3 class="form-section-title">B2B Bilgileri</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
                <div>
                    <strong>Kredi Limiti:</strong><br>
                    ₺<?= number_format($customer['credit_limit'], 2) ?>
                </div>
                <div>
                    <strong>Kullanılan Kredi:</strong><br>
                    ₺<?= number_format($customer['credit_used'], 2) ?>
                </div>
                <div>
                    <strong>Kullanılabilir Kredi:</strong><br>
                    <span style="color: var(--color-success);">
                        ₺<?= number_format($customer['credit_available'], 2) ?>
                    </span>
                </div>
                <div>
                    <strong>Ödeme Şartı:</strong><br>
                    <?= htmlspecialchars($customer['payment_terms'] ?? 'net30') ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Addresses -->
        <?php if (!empty($customer['addresses'])): ?>
        <div class="form-section">
            <h3 class="form-section-title">Adresler</h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <?php foreach ($customer['addresses'] as $address): ?>
                    <div style="padding: 16px; background: var(--color-bg); border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <strong><?= ucfirst($address['address_type']) ?></strong>
                            <?php if ($address['is_default']): ?>
                                <span class="badge badge-success">Varsayılan</span>
                            <?php endif; ?>
                        </div>
                        <div style="color: var(--color-text-light); font-size: 14px;">
                            <?php if ($address['company_name']): ?>
                                <strong><?= htmlspecialchars($address['company_name']) ?></strong><br>
                            <?php endif; ?>
                            <?= htmlspecialchars($address['first_name'] . ' ' . $address['last_name']) ?><br>
                            <?= htmlspecialchars($address['address_line_1']) ?><br>
                            <?php if ($address['address_line_2']): ?>
                                <?= htmlspecialchars($address['address_line_2']) ?><br>
                            <?php endif; ?>
                            <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['postal_code']) ?><br>
                            <?= htmlspecialchars($address['country']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Order History -->
        <div class="form-section">
            <h3 class="form-section-title">Sipariş Geçmişi</h3>
            <?php
            $orderService = new App\Services\OrderService($db);
            $orders = $orderService->getByUser((int) $customer['id'], 10);
            ?>
            <?php if (empty($orders)): ?>
                <p style="color: var(--color-text-light);">Henüz sipariş yok</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Sipariş No</th>
                            <th style="text-align: right;">Tutar</th>
                            <th style="text-align: center;">Durum</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <a href="/admin/orders/<?= $order['id'] ?>">
                                        <?= htmlspecialchars($order['order_number']) ?>
                                    </a>
                                </td>
                                <td style="text-align: right;">₺<?= number_format($order['total'], 2) ?></td>
                                <td style="text-align: center;">
                                    <span class="badge badge-<?= $order['status'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d.m.Y', strtotime($order['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-sidebar">
        <!-- Statistics -->
        <?php if (!empty($customer['order_stats'])): ?>
        <div class="form-section">
            <h3 class="form-section-title">İstatistikler</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--color-border);">
                    <span style="color: var(--color-text-light);">Toplam Sipariş</span>
                    <strong><?= number_format($customer['order_stats']['total_orders']) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--color-border);">
                    <span style="color: var(--color-text-light);">Toplam Harcama</span>
                    <strong>₺<?= number_format($customer['order_stats']['total_spent'], 2) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span style="color: var(--color-text-light);">Ortalama Sipariş</span>
                    <strong>₺<?= number_format($customer['order_stats']['average_order_value'], 2) ?></strong>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Update Group -->
        <div class="form-section">
            <h3 class="form-section-title">Grup Güncelle</h3>
            <form method="POST" action="/admin/customers/<?= $customer['id'] ?>/group">
                <div class="form-group">
                    <select name="customer_group_id" class="form-control">
                        <option value="">Bireysel</option>
                        <?php
                        $groups = $db->fetchAll("SELECT * FROM customer_groups ORDER BY name");
                        foreach ($groups as $group):
                        ?>
                            <option value="<?= $group['id'] ?>" <?= $customer['customer_group_id'] == $group['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($group['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Grup Güncelle</button>
            </form>
        </div>

        <!-- Actions -->
        <div class="form-section">
            <h3 class="form-section-title">İşlemler</h3>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <a href="#" class="btn btn-secondary">📧 E-posta Gönder</a>
                <a href="#" class="btn btn-secondary">📝 Not Ekle</a>
                <a href="/admin/customers/<?= $customer['id'] ?>/edit" class="btn btn-secondary">✏️ Düzenle</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
