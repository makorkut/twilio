<?php
$pageTitle = trans('admin.samples.detail');
$currentPage = 'samples';

if (!isset($sample)) {
    header('Location: /admin/samples');
    exit;
}

ob_start();
?>

<div class="page-header">
    <h1 class="page-title">
        <?= App\Services\SampleOrderService::getStatusIcon($sample['status']) ?>
        Numune Talebi: <?= htmlspecialchars($sample['order_number']) ?>
    </h1>
    <p class="page-description"><?= date('d.m.Y H:i', strtotime($sample['created_at'])) ?></p>
</div>

<div class="form-grid">
    <div class="form-main">
        <!-- Product Info -->
        <div class="form-section">
            <h3 class="form-section-title">Talep Edilen Ürün</h3>
            <div style="display: flex; gap: 20px; align-items: start;">
                <?php if ($sample['product_image']): ?>
                    <img src="<?= htmlspecialchars($sample['product_image']) ?>"
                         alt="<?= htmlspecialchars($sample['product_name']) ?>"
                         style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid var(--color-border);">
                <?php endif; ?>
                <div style="flex: 1;">
                    <h4 style="margin-bottom: 8px;"><?= htmlspecialchars($sample['product_name']) ?></h4>
                    <div style="color: var(--color-text-light); margin-bottom: 12px;">
                        <strong>SKU:</strong> <?= htmlspecialchars($sample['product_sku']) ?>
                    </div>
                    <div style="font-size: 24px; font-weight: bold; color: var(--color-primary);">
                        Miktar: <?= $sample['quantity'] ?> adet
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Details -->
        <div class="form-section">
            <h3 class="form-section-title">Talep Detayları</h3>

            <?php if ($sample['purpose']): ?>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Kullanım Amacı:</label>
                    <div style="padding: 12px; background: var(--color-bg); border-radius: 8px;">
                        <?= nl2br(htmlspecialchars($sample['purpose'])) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($sample['project_details']): ?>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Proje Detayları:</label>
                    <div style="padding: 12px; background: var(--color-bg); border-radius: 8px;">
                        <?= nl2br(htmlspecialchars($sample['project_details'])) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($sample['notes']): ?>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Ek Notlar:</label>
                    <div style="padding: 12px; background: var(--color-bg); border-radius: 8px;">
                        <?= nl2br(htmlspecialchars($sample['notes'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Shipping Info -->
        <div class="form-section">
            <h3 class="form-section-title">Teslimat Bilgileri</h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Teslimat Adresi:</label>
                    <?php if (!empty($sample['shipping_address'])): ?>
                        <div style="padding: 12px; background: var(--color-bg); border-radius: 8px; line-height: 1.6;">
                            <?php
                            $address = $sample['shipping_address'];
                            echo htmlspecialchars($address['address'] ?? '') . '<br>';
                            echo htmlspecialchars($address['city'] ?? '') . ' / ' . htmlspecialchars($address['district'] ?? '') . '<br>';
                            echo htmlspecialchars($address['postal_code'] ?? '');
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Kargo Bilgileri:</label>
                    <div style="padding: 12px; background: var(--color-bg); border-radius: 8px;">
                        <div style="margin-bottom: 8px;">
                            <strong>Kargo Ücreti:</strong>
                            <?php if ($sample['shipping_cost'] > 0): ?>
                                ₺<?= number_format($sample['shipping_cost'], 2) ?>
                            <?php else: ?>
                                <span style="color: var(--color-success);">Ücretsiz</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($sample['tracking_number']): ?>
                            <div>
                                <strong>Takip No:</strong> <?= htmlspecialchars($sample['tracking_number']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status History -->
        <div class="form-section">
            <h3 class="form-section-title">Durum Geçmişi</h3>
            <?php if (!empty($sample['status_history'])): ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($sample['status_history'] as $history): ?>
                        <div style="padding: 16px; background: var(--color-bg); border-radius: 8px; border-left: 4px solid var(--color-primary);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 20px;">
                                        <?= App\Services\SampleOrderService::getStatusIcon($history['status']) ?>
                                    </span>
                                    <strong><?= App\Services\SampleOrderService::getStatusLabel($history['status']) ?></strong>
                                </div>
                                <span style="color: var(--color-text-light); font-size: 13px;">
                                    <?= date('d.m.Y H:i', strtotime($history['created_at'])) ?>
                                </span>
                            </div>
                            <?php if ($history['note']): ?>
                                <div style="color: var(--color-text-light); font-size: 14px; margin-top: 8px;">
                                    <?= htmlspecialchars($history['note']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($history['created_by_name']): ?>
                                <div style="color: var(--color-text-light); font-size: 12px; margin-top: 8px;">
                                    <em>Güncelleyen: <?= htmlspecialchars($history['created_by_name']) ?></em>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-sidebar">
        <!-- Customer Info -->
        <div class="form-section">
            <h3 class="form-section-title">Müşteri Bilgileri</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <strong>Ad Soyad:</strong><br>
                    <?= htmlspecialchars($sample['customer_name']) ?>
                </div>
                <div>
                    <strong>E-posta:</strong><br>
                    <a href="mailto:<?= htmlspecialchars($sample['customer_email']) ?>">
                        <?= htmlspecialchars($sample['customer_email']) ?>
                    </a>
                </div>
                <?php if ($sample['customer_phone']): ?>
                    <div>
                        <strong>Telefon:</strong><br>
                        <?= htmlspecialchars($sample['customer_phone']) ?>
                    </div>
                <?php endif; ?>
                <?php if ($sample['customer_group_name']): ?>
                    <div>
                        <strong>Müşteri Grubu:</strong><br>
                        <span class="badge badge-info"><?= htmlspecialchars($sample['customer_group_name']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status Update -->
        <?php if ($sample['status'] !== 'delivered' && $sample['status'] !== 'rejected'): ?>
            <div class="form-section">
                <h3 class="form-section-title">Durum Güncelle</h3>
                <form method="POST" action="/admin/samples/<?= $sample['id'] ?>/status">
                    <div class="form-group">
                        <label class="form-label">Yeni Durum</label>
                        <select name="status" class="form-control" required>
                            <?php if ($sample['status'] === 'pending'): ?>
                                <option value="approved">✅ Onayla</option>
                                <option value="rejected">❌ Reddet</option>
                            <?php endif; ?>
                            <?php if ($sample['status'] === 'approved'): ?>
                                <option value="preparing">📦 Hazırlanıyor</option>
                                <option value="shipped">🚚 Kargoya Verildi</option>
                            <?php endif; ?>
                            <?php if ($sample['status'] === 'preparing'): ?>
                                <option value="shipped">🚚 Kargoya Verildi</option>
                            <?php endif; ?>
                            <?php if ($sample['status'] === 'shipped'): ?>
                                <option value="delivered">✔️ Teslim Edildi</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <?php if ($sample['status'] === 'approved' || $sample['status'] === 'preparing'): ?>
                        <div class="form-group">
                            <label class="form-label">Kargo Takip No (opsiyonel)</label>
                            <input type="text" name="tracking_number" class="form-control"
                                   value="<?= htmlspecialchars($sample['tracking_number'] ?? '') ?>"
                                   placeholder="Örn: 123456789">
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Not (opsiyonel)</label>
                        <textarea name="note" class="form-control" rows="3"
                                  placeholder="Durum değişikliği hakkında not..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        💾 Durumu Güncelle
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <?php if ($sample['status'] === 'pending'): ?>
            <div class="form-section">
                <h3 class="form-section-title">Hızlı İşlemler</h3>
                <form method="POST" action="/admin/samples/<?= $sample['id'] ?>/approve" style="margin-bottom: 8px;">
                    <button type="submit" class="btn btn-success" style="width: 100%;">
                        ✅ Onayla ve Hazırlamaya Başla
                    </button>
                </form>
                <form method="POST" action="/admin/samples/<?= $sample['id'] ?>/reject"
                      onsubmit="return prompt('Ret nedeni:') !== null">
                    <button type="submit" class="btn btn-danger" style="width: 100%;">
                        ❌ Reddet
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Contact Customer -->
        <div class="form-section">
            <h3 class="form-section-title">İletişim</h3>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <a href="mailto:<?= htmlspecialchars($sample['customer_email']) ?>" class="btn btn-secondary">
                    📧 E-posta Gönder
                </a>
                <?php if ($sample['customer_phone']): ?>
                    <a href="tel:<?= htmlspecialchars($sample['customer_phone']) ?>" class="btn btn-secondary">
                        📞 Telefon Et
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
