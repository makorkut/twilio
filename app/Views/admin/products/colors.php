<?php
$pageTitle = trans('admin.products.color_management');
$currentPage = 'products';

if (!isset($product)) {
    header('Location: /admin/products');
    exit;
}

$db = container()->get(App\Core\Database::class);
$colorService = new App\Services\ColorCatalogService($db);
$colors = $colorService->getProductColors($product['id']);

ob_start();
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div style="padding: 16px 24px; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #10b981;">
        <strong>✓</strong> <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div class="page-header">
    <h1 class="page-title">
        🎨 Renk & Doku Yönetimi: <?= htmlspecialchars($product['name']) ?>
    </h1>
    <p class="page-description">SKU: <?= htmlspecialchars($product['sku']) ?></p>
</div>

<div class="form-grid">
    <div class="form-main">
        <!-- Add Color Form -->
        <div class="form-section">
            <h3 class="form-section-title"><?= trans('admin.products.add_color') ?></h3>

            <form method="POST" action="/admin/products/<?= $product['id'] ?>/colors/add">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><?= trans('admin.products.color_name') ?> *</label>
                        <input type="text" name="name" class="form-control" required
                               placeholder="<?= trans('admin.products.color_example', [], null) ?? 'e.g.: White, Anthracite, Wood Texture' ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?= trans('admin.products.color_code') ?></label>
                        <input type="text" name="color_code" class="form-control"
                               placeholder="<?= trans('admin.products.example_short', [], null) ?? 'e.g.' ?>: W100, ANT-200">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><?= trans('admin.products.ral_code', [], null) ?? 'RAL Code' ?></label>
                        <input type="text" name="ral_code" class="form-control" list="ral-colors"
                               placeholder="<?= trans('admin.products.example_short', [], null) ?? 'e.g.' ?>: RAL 9010">
                        <datalist id="ral-colors">
                            <option value="RAL 9010">Pure white</option>
                            <option value="RAL 9003">Signal white</option>
                            <option value="RAL 9016">Traffic white</option>
                            <option value="RAL 7016">Anthracite grey</option>
                            <option value="RAL 7021">Black grey</option>
                            <option value="RAL 9005">Jet black</option>
                        </datalist>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Hex Renk Kodu</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="color" name="hex_color" id="hex_color_picker"
                                   style="width: 60px; height: 42px; border: 1px solid var(--color-border); border-radius: 4px; cursor: pointer;">
                            <input type="text" name="hex_color_text" id="hex_color_text" class="form-control"
                                   placeholder="#FFFFFF" maxlength="7" style="flex: 1;"
                                   oninput="document.getElementById('hex_color_picker').value = this.value">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><?= trans('admin.products.texture_type', [], null) ?? 'Texture Type' ?></label>
                        <select name="texture_type" class="form-control">
                            <option value=""><?= trans('common.select') ?>...</option>
                            <?php foreach (App\Services\ColorCatalogService::getTextureTypes() as $value => $label): ?>
                                <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?= trans('admin.products.finish_type', [], null) ?? 'Finish Type' ?></label>
                        <select name="finish_type" class="form-control">
                            <option value=""><?= trans('common.select') ?>...</option>
                            <?php foreach (App\Services\ColorCatalogService::getFinishTypes() as $value => $label): ?>
                                <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><?= trans('admin.products.price_modifier', [], null) ?? 'Price Adjustment' ?></label>
                        <input type="number" name="price_modifier" class="form-control" step="0.01" value="0"
                               placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?= trans('admin.products.modifier_type', [], null) ?? 'Adjustment Type' ?></label>
                        <select name="price_modifier_type" class="form-control">
                            <option value="fixed"><?= trans('admin.products.fixed_price', [], null) ?? 'Fixed' ?> (₺)</option>
                            <option value="percent"><?= trans('admin.products.percentage', [], null) ?? 'Percentage' ?> (%)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?= trans('admin.products.stock_quantity') ?></label>
                        <input type="number" name="stock_quantity" class="form-control" value="0" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="is_available" value="1" checked>
                        <span>Satışta</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    ➕ Renk Ekle
                </button>
            </form>
        </div>

        <!-- Existing Colors -->
        <div class="form-section">
            <h3 class="form-section-title">Mevcut Renkler (<?= count($colors) ?>)</h3>

            <?php if (empty($colors)): ?>
                <div style="padding: 40px; text-align: center; color: var(--color-text-light);">
                    <div style="font-size: 48px; margin-bottom: 12px;">🎨</div>
                    <p>Henüz renk eklenmemiş</p>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 16px;">
                    <?php foreach ($colors as $color): ?>
                        <div style="padding: 16px; border: 1px solid var(--color-border); border-radius: 8px; display: flex; gap: 16px; align-items: center;">
                            <!-- Color Swatch -->
                            <div style="width: 80px; height: 80px; border-radius: 8px; overflow: hidden; flex-shrink: 0; border: 1px solid var(--color-border);">
                                <?php if ($color['swatch_image']): ?>
                                    <img src="<?= htmlspecialchars($color['swatch_image']) ?>"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php elseif ($color['hex_color']): ?>
                                    <div style="width: 100%; height: 100%; background: <?= htmlspecialchars($color['hex_color']) ?>;"></div>
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                                        🎨
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Color Info -->
                            <div style="flex: 1;">
                                <h4 style="margin-bottom: 8px; font-size: 16px; font-weight: 600;">
                                    <?= htmlspecialchars($color['name']) ?>
                                    <?php if (!$color['is_available']): ?>
                                        <span class="badge badge-inactive">Satışta Değil</span>
                                    <?php endif; ?>
                                </h4>

                                <div style="display: flex; gap: 20px; font-size: 13px; color: var(--color-text-light);">
                                    <?php if ($color['color_code']): ?>
                                        <div><strong>Kod:</strong> <?= htmlspecialchars($color['color_code']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($color['ral_code']): ?>
                                        <div><strong>RAL:</strong> <?= htmlspecialchars($color['ral_code']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($color['hex_color']): ?>
                                        <div><strong>HEX:</strong> <?= htmlspecialchars($color['hex_color']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($color['texture_type']): ?>
                                        <div><strong>Doku:</strong> <?= App\Services\ColorCatalogService::getTextureTypes()[$color['texture_type']] ?? $color['texture_type'] ?></div>
                                    <?php endif; ?>
                                    <?php if ($color['price_modifier'] != 0): ?>
                                        <div style="color: var(--color-success); font-weight: 600;">
                                            <?php if ($color['price_modifier_type'] === 'percent'): ?>
                                                +<?= number_format($color['price_modifier'], 0) ?>%
                                            <?php else: ?>
                                                +₺<?= number_format($color['price_modifier'], 2) ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div style="display: flex; gap: 8px; flex-shrink: 0;">
                                <form method="POST" action="/admin/products/<?= $product['id'] ?>/colors/<?= $color['id'] ?>/toggle"
                                      style="display: inline;">
                                    <button type="submit" class="btn btn-secondary btn-sm">
                                        <?= $color['is_available'] ? '👁️ Gizle' : '👁️ Göster' ?>
                                    </button>
                                </form>
                                <form method="POST" action="/admin/products/<?= $product['id'] ?>/colors/<?= $color['id'] ?>/delete"
                                      onsubmit="return confirm('Bu rengi silmek istediğinize emin misiniz?')"
                                      style="display: inline;">
                                    <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-sidebar">
        <!-- Product Info -->
        <div class="form-section">
            <h3 class="form-section-title">Ürün Bilgisi</h3>
            <div style="margin-bottom: 12px;">
                <strong>Ürün:</strong><br>
                <?= htmlspecialchars($product['name']) ?>
            </div>
            <div style="margin-bottom: 12px;">
                <strong>SKU:</strong><br>
                <?= htmlspecialchars($product['sku']) ?>
            </div>
            <div>
                <strong>Baz Fiyat:</strong><br>
                ₺<?= number_format($product['base_price'], 2) ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="form-section">
            <h3 class="form-section-title">İşlemler</h3>
            <a href="/admin/products/<?= $product['id'] ?>/edit" class="btn btn-secondary" style="width: 100%; margin-bottom: 8px;">
                ⬅️ Ürüne Dön
            </a>
            <a href="/admin/products" class="btn btn-secondary" style="width: 100%;">
                📦 Tüm Ürünler
            </a>
        </div>

        <!-- Tips -->
        <div class="form-section">
            <h3 class="form-section-title">💡 İpuçları</h3>
            <ul style="padding-left: 20px; font-size: 13px; line-height: 1.6; color: var(--color-text-light);">
                <li>RAL kodları standart endüstri renk kodlarıdır</li>
                <li>Hex kodları web gösterimi için kullanılır</li>
                <li>Fiyat değişikliği pozitif veya negatif olabilir</li>
                <li>Swatch görselleri müşteri deneyimini geliştirir</li>
            </ul>
        </div>
    </div>
</div>

<script>
// Sync color picker with text input
document.getElementById('hex_color_picker').addEventListener('input', function() {
    document.getElementById('hex_color_text').value = this.value;
});

document.getElementById('hex_color_text').addEventListener('input', function() {
    if (this.value.match(/^#[0-9A-F]{6}$/i)) {
        document.getElementById('hex_color_picker').value = this.value;
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
