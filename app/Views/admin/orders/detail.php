<?php
$pageTitle = 'Sipariş Detayı';
$currentPage = 'orders';

if (!isset($order)) {
    header('Location: /admin/orders');
    exit;
}

ob_start();
?>

<div class="page-header">
    <h1 class="page-title">Sipariş #<?= htmlspecialchars($order['order_number']) ?></h1>
    <p class="page-description"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
</div>

<div class="form-grid">
    <div class="form-main">
        <!-- Order Items -->
        <div class="form-section">
            <h3 class="form-section-title">Sipariş Ürünleri</h3>
            <table>
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th style="text-align: center;">Miktar</th>
                        <th style="text-align: right;">Birim Fiyat</th>
                        <th style="text-align: right;">Toplam</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($item['product_name']) ?></strong><br>
                                <small style="color: var(--color-text-light);">SKU: <?= htmlspecialchars($item['product_sku']) ?></small>
                            </td>
                            <td style="text-align: center;"><?= $item['quantity'] ?></td>
                            <td style="text-align: right;">₺<?= number_format($item['unit_price'], 2) ?></td>
                            <td style="text-align: right;"><strong>₺<?= number_format($item['total_price'], 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--color-border);">
                <div style="display: flex; justify-content: flex-end;">
                    <div style="min-width: 300px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>Ara Toplam:</span>
                            <strong>₺<?= number_format($order['subtotal'], 2) ?></strong>
                        </div>
                        <?php if ($order['discount_total'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: var(--color-success);">
                            <span>İndirim:</span>
                            <strong>-₺<?= number_format($order['discount_total'], 2) ?></strong>
                        </div>
                        <?php endif; ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>KDV:</span>
                            <strong>₺<?= number_format($order['tax_total'], 2) ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>Kargo:</span>
                            <strong>₺<?= number_format($order['shipping_total'], 2) ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding-top: 12px; border-top: 1px solid var(--color-border); font-size: 18px;">
                            <span><strong>Genel Toplam:</strong></span>
                            <strong>₺<?= number_format($order['total'], 2) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Addresses -->
        <div class="form-section">
            <h3 class="form-section-title">Adres Bilgileri</h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                <div>
                    <h4 style="margin-bottom: 12px;">Fatura Adresi</h4>
                    <?php if (!empty($order['billing_address'])): ?>
                        <div style="color: var(--color-text-light);">
                            <?= nl2br(htmlspecialchars(json_encode($order['billing_address'], JSON_PRETTY_PRINT))) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <h4 style="margin-bottom: 12px;">Teslimat Adresi</h4>
                    <?php if (!empty($order['shipping_address'])): ?>
                        <div style="color: var(--color-text-light);">
                            <?= nl2br(htmlspecialchars(json_encode($order['shipping_address'], JSON_PRETTY_PRINT))) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Status History -->
        <div class="form-section">
            <h3 class="form-section-title">Sipariş Geçmişi</h3>
            <?php if (!empty($order['status_history'])): ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($order['status_history'] as $history): ?>
                        <div style="padding: 12px; background: var(--color-bg); border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <strong><?= ucfirst($history['status']) ?></strong>
                                <span style="color: var(--color-text-light); font-size: 13px;">
                                    <?= date('d.m.Y H:i', strtotime($history['created_at'])) ?>
                                </span>
                            </div>
                            <?php if ($history['note']): ?>
                                <div style="color: var(--color-text-light); font-size: 14px;">
                                    <?= htmlspecialchars($history['note']) ?>
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
                    <?= htmlspecialchars($order['customer_name'] ?? 'Misafir') ?>
                </div>
                <div>
                    <strong>E-posta:</strong><br>
                    <?= htmlspecialchars($order['customer_email']) ?>
                </div>
                <?php if ($order['customer_phone']): ?>
                <div>
                    <strong>Telefon:</strong><br>
                    <?= htmlspecialchars($order['customer_phone']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status -->
        <div class="form-section">
            <h3 class="form-section-title">Durum Güncelle</h3>
            <form method="POST" action="/admin/orders/<?= $order['id'] ?>/status">
                <div class="form-group">
                    <select name="status" class="form-control">
                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Beklemede</option>
                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>İşleniyor</option>
                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Kargoda</option>
                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Tamamlandı</option>
                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>İptal</option>
                    </select>
                </div>
                <div class="form-group">
                    <textarea name="note" class="form-control" placeholder="Not (opsiyonel)" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Durum Güncelle</button>
            </form>
        </div>

        <!-- Actions -->
        <div class="form-section">
            <h3 class="form-section-title">İşlemler</h3>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <a href="#" class="btn btn-secondary">📧 Müşteriye E-posta Gönder</a>
                <a href="#" class="btn btn-secondary">🖨️ Sipariş Faturası</a>
                <a href="#" class="btn btn-secondary">📦 Sevkiyat Etiketi</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
