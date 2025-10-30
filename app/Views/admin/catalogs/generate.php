<?php
$pageTitle = 'Katalog Oluştur';
$currentPage = 'catalogs';

$db = container()->get(App\Core\Database::class);

// Get all active products
$products = $db->fetchAll("SELECT id, name, sku, base_price FROM products WHERE status = 'active' ORDER BY name");

// Get categories
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

ob_start();
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div style="padding: 16px 24px; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #10b981;">
        <strong>✓</strong> <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div class="page-header">
    <h1 class="page-title">📚 Yeni Katalog Oluştur</h1>
    <p class="page-description">Ürünlerinizden profesyonel katalog oluşturun</p>
</div>

<form method="POST" action="/admin/catalogs/generate">
    <div class="form-grid">
        <div class="form-main">
            <!-- Catalog Settings -->
            <div class="form-section">
                <h3 class="form-section-title">Katalog Ayarları</h3>

                <div class="form-group">
                    <label class="form-label">Katalog Başlığı *</label>
                    <input type="text" name="title" class="form-control" required
                           value="Ürün Kataloğu <?= date('Y') ?>"
                           placeholder="Örn: 2024 Ürün Kataloğu">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Düzen</label>
                        <select name="layout" class="form-control">
                            <option value="grid">Izgara (Kompakt Kartlar)</option>
                            <option value="list">Liste (Yatay Satırlar)</option>
                            <option value="detailed" selected>Detaylı (Tam Bilgi)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ürün Seçimi</label>
                        <select name="selection_type" id="selection_type" class="form-control"
                                onchange="toggleSelectionOptions()">
                            <option value="all">Tüm Aktif Ürünler</option>
                            <option value="category">Kategoriye Göre</option>
                            <option value="custom">Manuel Seçim</option>
                        </select>
                    </div>
                </div>

                <!-- Category Selection -->
                <div id="category_selection" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">Kategori Seçin</label>
                        <select name="category_id" class="form-control">
                            <option value="">Kategori seçin...</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Custom Product Selection -->
                <div id="custom_selection" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">Ürünleri Seçin</label>
                        <div style="max-height: 300px; overflow-y: auto; border: 1px solid var(--color-border); border-radius: 8px; padding: 12px;">
                            <?php foreach ($products as $product): ?>
                                <label style="display: flex; align-items: center; padding: 8px; cursor: pointer; border-radius: 4px;"
                                       onmouseover="this.style.background='#f8fafc'"
                                       onmouseout="this.style.background='transparent'">
                                    <input type="checkbox" name="product_ids[]" value="<?= $product['id'] ?>" style="margin-right: 12px;">
                                    <span style="flex: 1;">
                                        <strong><?= htmlspecialchars($product['name']) ?></strong><br>
                                        <small style="color: var(--color-text-light);">
                                            SKU: <?= htmlspecialchars($product['sku']) ?> •
                                            ₺<?= number_format($product['base_price'], 2) ?>
                                        </small>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <small class="form-help">
                            <span id="selected_count">0</span> ürün seçildi
                        </small>
                    </div>
                </div>
            </div>

            <!-- Content Options -->
            <div class="form-section">
                <h3 class="form-section-title">İçerik Seçenekleri</h3>

                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="include_images" value="1" checked>
                        <span>Ürün Görselleri</span>
                    </label>
                    <small class="form-help">Ürün fotoğraflarını kataloğa ekle</small>
                </div>

                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="include_prices" value="1" checked>
                        <span>Fiyat Bilgileri</span>
                    </label>
                    <small class="form-help">Ürün fiyatlarını göster</small>
                </div>

                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="include_specs" value="1" checked>
                        <span>Teknik Özellikler</span>
                    </label>
                    <small class="form-help">Boyut, ağırlık gibi teknik detayları ekle</small>
                </div>
            </div>
        </div>

        <div class="form-sidebar">
            <!-- Preview -->
            <div class="form-section">
                <h3 class="form-section-title">📋 Önizleme</h3>
                <div style="padding: 20px; background: #f8fafc; border-radius: 8px; text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 12px;">📚</div>
                    <p style="color: var(--color-text-light); font-size: 14px; line-height: 1.6;">
                        Katalog oluşturulduktan sonra HTML formatında kaydedilir ve yazdırabilirsiniz.
                    </p>
                </div>
            </div>

            <!-- Info -->
            <div class="form-section">
                <h3 class="form-section-title">💡 Bilgi</h3>
                <ul style="padding-left: 20px; font-size: 13px; line-height: 1.8; color: var(--color-text-light);">
                    <li><strong>Izgara:</strong> Kompakt kart görünümü, sayfa başına çok ürün</li>
                    <li><strong>Liste:</strong> Yatay satır görünümü, hızlı tarama</li>
                    <li><strong>Detaylı:</strong> Tam ürün bilgisi, açıklamalar ve özellikler</li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="form-section">
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 8px;">
                    ✨ Katalog Oluştur
                </button>
                <a href="/admin/catalogs" class="btn btn-secondary" style="width: 100%; display: block; text-align: center;">
                    İptal
                </a>
            </div>
        </div>
    </div>
</form>

<script>
function toggleSelectionOptions() {
    const selectionType = document.getElementById('selection_type').value;
    document.getElementById('category_selection').style.display = selectionType === 'category' ? 'block' : 'none';
    document.getElementById('custom_selection').style.display = selectionType === 'custom' ? 'block' : 'none';
}

// Count selected products
document.querySelectorAll('input[name="product_ids[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
});

function updateSelectedCount() {
    const count = document.querySelectorAll('input[name="product_ids[]"]:checked').length;
    document.getElementById('selected_count').textContent = count;
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
