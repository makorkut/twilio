<?php
/**
 * Admin - Product Add/Edit Form
 * Comprehensive form for creating and editing products
 */

$pageTitle = isset($product) ? 'Ürünü Düzenle' : 'Yeni Ürün Ekle';
$currentPage = 'products';

// Get categories for dropdown
$db = container()->get(App\Core\Database::class);
$categories = $db->fetchAll("SELECT id, name, parent_id FROM categories WHERE is_active = 1 ORDER BY sort_order, name");

// Check if this is edit mode
$isEdit = isset($product);
$product = $product ?? [];

ob_start();
?>

<style>
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 24px;
    }

    .form-main {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .form-sidebar {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .form-section {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--color-border);
        padding: 24px;
    }

    .form-section-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--color-text);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--color-border);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: var(--color-text);
        margin-bottom: 6px;
    }

    .form-label.required::after {
        content: '*';
        color: var(--color-danger);
        margin-left: 4px;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s;
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--color-primary);
    }

    .form-control:disabled {
        background: var(--color-bg);
        cursor: not-allowed;
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .form-hint {
        font-size: 12px;
        color: var(--color-text-light);
        margin-top: 4px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .form-row-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }

    .checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .checkbox-group label {
        font-size: 14px;
        color: var(--color-text);
        cursor: pointer;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding: 20px;
        background: white;
        border-top: 1px solid var(--color-border);
        position: sticky;
        bottom: 0;
        margin: 0 -32px -32px;
        border-radius: 0 0 12px 12px;
    }

    .slug-preview {
        font-size: 12px;
        color: var(--color-text-light);
        margin-top: 4px;
        font-family: 'Courier New', monospace;
    }

    .input-group {
        display: flex;
        align-items: stretch;
    }

    .input-group-addon {
        padding: 10px 14px;
        background: var(--color-bg);
        border: 1px solid var(--color-border);
        border-right: none;
        border-radius: 8px 0 0 8px;
        font-size: 14px;
        color: var(--color-text-light);
    }

    .input-group .form-control {
        border-radius: 0 8px 8px 0;
    }

    .badge-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fed7aa;
        color: #92400e;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    @media (max-width: 1024px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-row-3 {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 class="page-title"><?= $pageTitle ?></h1>
        <p class="page-description">
            <?= $isEdit ? 'Ürün bilgilerini güncelleyin' : 'Yeni bir ürün oluşturun' ?>
        </p>
    </div>
</div>

<form method="POST" action="<?= $isEdit ? "/admin/products/edit/{$product['id']}" : '/admin/products/create' ?>" id="productForm">
    <div class="form-grid">
        <!-- Main Content -->
        <div class="form-main">
            <!-- Basic Information -->
            <div class="form-section">
                <h3 class="form-section-title">Temel Bilgiler</h3>

                <div class="form-group">
                    <label class="form-label required">Ürün Adı</label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($product['name'] ?? '') ?>"
                           required maxlength="255"
                           oninput="generateSlug(this.value)">
                    <div class="slug-preview" id="slugPreview">
                        <?= isset($product['slug']) ? '/' . htmlspecialchars($product['slug']) : '' ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">SKU (Stok Kodu)</label>
                    <input type="text" name="sku" class="form-control"
                           value="<?= htmlspecialchars($product['sku'] ?? '') ?>"
                           required maxlength="100">
                    <div class="form-hint">Benzersiz ürün kodu (örn: PU-PANEL-001)</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Kısa Açıklama</label>
                    <textarea name="short_description" class="form-control" rows="3"
                              maxlength="500"><?= htmlspecialchars($product['short_description'] ?? '') ?></textarea>
                    <div class="form-hint">Ürün listelerinde gösterilecek kısa açıklama</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Detaylı Açıklama</label>
                    <textarea name="description" class="form-control" rows="8"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Pricing -->
            <div class="form-section">
                <h3 class="form-section-title">Fiyatlandırma</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Satış Fiyatı</label>
                        <div class="input-group">
                            <span class="input-group-addon">₺</span>
                            <input type="number" name="base_price" class="form-control"
                                   value="<?= htmlspecialchars($product['base_price'] ?? '0') ?>"
                                   step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Karşılaştırma Fiyatı</label>
                        <div class="input-group">
                            <span class="input-group-addon">₺</span>
                            <input type="number" name="compare_price" class="form-control"
                                   value="<?= htmlspecialchars($product['compare_price'] ?? '') ?>"
                                   step="0.01" min="0">
                        </div>
                        <div class="form-hint">İndirim öncesi fiyat (opsiyonel)</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Maliyet Fiyatı</label>
                    <div class="input-group">
                        <span class="input-group-addon">₺</span>
                        <input type="number" name="cost_price" class="form-control"
                               value="<?= htmlspecialchars($product['cost_price'] ?? '') ?>"
                               step="0.01" min="0">
                    </div>
                    <div class="form-hint">Ürünün size maliyeti (kar marjı hesabı için)</div>
                </div>
            </div>

            <!-- Inventory -->
            <div class="form-section">
                <h3 class="form-section-title">Stok Yönetimi</h3>

                <div class="checkbox-group">
                    <input type="checkbox" name="track_inventory" id="track_inventory" value="1"
                           <?= ($product['track_inventory'] ?? 1) ? 'checked' : '' ?>>
                    <label for="track_inventory">Stok miktarını takip et</label>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Stok Miktarı</label>
                        <input type="number" name="stock_quantity" class="form-control"
                               value="<?= htmlspecialchars($product['stock_quantity'] ?? '0') ?>"
                               min="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Düşük Stok Eşiği</label>
                        <input type="number" name="low_stock_threshold" class="form-control"
                               value="<?= htmlspecialchars($product['low_stock_threshold'] ?? '10') ?>"
                               min="0">
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="allow_backorder" id="allow_backorder" value="1"
                           <?= ($product['allow_backorder'] ?? 0) ? 'checked' : '' ?>>
                    <label for="allow_backorder">Stok yokken sipariş alınabilsin</label>
                </div>
            </div>

            <!-- Physical Attributes -->
            <div class="form-section">
                <h3 class="form-section-title">Fiziksel Özellikler</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Satış Birimi</label>
                        <select name="selling_unit" class="form-control">
                            <option value="piece" <?= ($product['selling_unit'] ?? 'piece') === 'piece' ? 'selected' : '' ?>>Adet</option>
                            <option value="meter" <?= ($product['selling_unit'] ?? '') === 'meter' ? 'selected' : '' ?>>Metre</option>
                            <option value="m2" <?= ($product['selling_unit'] ?? '') === 'm2' ? 'selected' : '' ?>>m²</option>
                            <option value="package" <?= ($product['selling_unit'] ?? '') === 'package' ? 'selected' : '' ?>>Paket</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Paket İçi Miktar</label>
                        <input type="number" name="package_quantity" class="form-control"
                               value="<?= htmlspecialchars($product['package_quantity'] ?? '1') ?>"
                               min="1">
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Uzunluk (mm)</label>
                        <input type="number" name="length_mm" class="form-control"
                               value="<?= htmlspecialchars($product['length_mm'] ?? '') ?>"
                               step="0.01" min="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Genişlik (mm)</label>
                        <input type="number" name="width_mm" class="form-control"
                               value="<?= htmlspecialchars($product['width_mm'] ?? '') ?>"
                               step="0.01" min="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Yükseklik (mm)</label>
                        <input type="number" name="height_mm" class="form-control"
                               value="<?= htmlspecialchars($product['height_mm'] ?? '') ?>"
                               step="0.01" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Ağırlık (kg)</label>
                    <input type="number" name="weight_kg" class="form-control"
                           value="<?= htmlspecialchars($product['weight_kg'] ?? '') ?>"
                           step="0.001" min="0">
                </div>
            </div>

            <!-- B2B Settings -->
            <div class="form-section">
                <h3 class="form-section-title">B2B Ayarları</h3>

                <div class="checkbox-group">
                    <input type="checkbox" name="is_b2b_only" id="is_b2b_only" value="1"
                           <?= ($product['is_b2b_only'] ?? 0) ? 'checked' : '' ?>>
                    <label for="is_b2b_only">Sadece B2B müşterilerine sat</label>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Minimum Sipariş Miktarı</label>
                        <input type="number" name="min_order_quantity" class="form-control"
                               value="<?= htmlspecialchars($product['min_order_quantity'] ?? '1') ?>"
                               min="1">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Maksimum Sipariş Miktarı</label>
                        <input type="number" name="max_order_quantity" class="form-control"
                               value="<?= htmlspecialchars($product['max_order_quantity'] ?? '') ?>"
                               min="1">
                        <div class="form-hint">Boş bırakılırsa sınırsız</div>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="form-section">
                <h3 class="form-section-title">SEO</h3>

                <div class="form-group">
                    <label class="form-label">Meta Başlık</label>
                    <input type="text" name="meta_title" class="form-control"
                           value="<?= htmlspecialchars($product['meta_title'] ?? '') ?>"
                           maxlength="255">
                    <div class="form-hint">Boş bırakılırsa ürün adı kullanılır</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Meta Açıklama</label>
                    <textarea name="meta_description" class="form-control" rows="3"
                              maxlength="500"><?= htmlspecialchars($product['meta_description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Meta Anahtar Kelimeler</label>
                    <input type="text" name="meta_keywords" class="form-control"
                           value="<?= htmlspecialchars($product['meta_keywords'] ?? '') ?>"
                           maxlength="500">
                    <div class="form-hint">Virgülle ayırın</div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="form-sidebar">
            <!-- Status -->
            <div class="form-section">
                <h3 class="form-section-title">Durum</h3>

                <div class="form-group">
                    <label class="form-label">Yayın Durumu</label>
                    <select name="status" class="form-control">
                        <option value="draft" <?= ($product['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Taslak</option>
                        <option value="active" <?= ($product['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                        <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Pasif</option>
                        <option value="archived" <?= ($product['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Arşiv</option>
                    </select>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="is_featured" id="is_featured" value="1"
                           <?= ($product['is_featured'] ?? 0) ? 'checked' : '' ?>>
                    <label for="is_featured">Öne çıkan ürün</label>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="is_new" id="is_new" value="1"
                           <?= ($product['is_new'] ?? 0) ? 'checked' : '' ?>>
                    <label for="is_new">Yeni ürün rozeti göster</label>
                </div>
            </div>

            <!-- Categories -->
            <div class="form-section">
                <h3 class="form-section-title">Kategoriler</h3>

                <div class="form-group">
                    <label class="form-label">Ana Kategori</label>
                    <select name="category_id" class="form-control">
                        <option value="">Kategori Seçin</option>
                        <?php foreach ($categories as $category): ?>
                            <?php
                            $selected = isset($product['category_id']) && $product['category_id'] == $category['id'] ? 'selected' : '';
                            $prefix = $category['parent_id'] ? '— ' : '';
                            ?>
                            <option value="<?= $category['id'] ?>" <?= $selected ?>>
                                <?= $prefix . htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Statistics (Edit Mode Only) -->
            <?php if ($isEdit): ?>
            <div class="form-section">
                <h3 class="form-section-title">İstatistikler</h3>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--color-border);">
                        <span style="color: var(--color-text-light); font-size: 13px;">Görüntülenme</span>
                        <strong><?= number_format($product['view_count'] ?? 0) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--color-border);">
                        <span style="color: var(--color-text-light); font-size: 13px;">Sipariş Sayısı</span>
                        <strong><?= number_format($product['order_count'] ?? 0) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--color-border);">
                        <span style="color: var(--color-text-light); font-size: 13px;">Ortalama Puan</span>
                        <strong><?= number_format($product['rating_avg'] ?? 0, 2) ?> ⭐</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                        <span style="color: var(--color-text-light); font-size: 13px;">Yorum Sayısı</span>
                        <strong><?= number_format($product['review_count'] ?? 0) ?></strong>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
        <a href="/admin/products" class="btn btn-secondary">İptal</a>
        <button type="submit" name="action" value="draft" class="btn btn-secondary">Taslak Olarak Kaydet</button>
        <button type="submit" name="action" value="publish" class="btn btn-primary">
            <?= $isEdit ? 'Güncelle' : 'Yayınla' ?>
        </button>
    </div>
</form>

<script>
// Auto-generate slug from product name
function generateSlug(name) {
    const charMap = {
        'ç': 'c', 'Ç': 'C',
        'ğ': 'g', 'Ğ': 'G',
        'ı': 'i', 'İ': 'I',
        'ö': 'o', 'Ö': 'O',
        'ş': 's', 'Ş': 'S',
        'ü': 'u', 'Ü': 'U'
    };

    let slug = name;
    for (let char in charMap) {
        slug = slug.replace(new RegExp(char, 'g'), charMap[char]);
    }

    slug = slug.toLowerCase()
               .replace(/[^a-z0-9\s-]/g, '')
               .replace(/\s+/g, '-')
               .replace(/-+/g, '-')
               .replace(/^-|-$/g, '');

    document.getElementById('slugPreview').textContent = slug ? '/' + slug : '';
}

// Form validation
document.getElementById('productForm').addEventListener('submit', function(e) {
    const name = document.querySelector('input[name="name"]').value;
    const sku = document.querySelector('input[name="sku"]').value;
    const basePrice = document.querySelector('input[name="base_price"]').value;

    if (!name || !sku || !basePrice) {
        e.preventDefault();
        alert('Lütfen zorunlu alanları doldurun:\n- Ürün Adı\n- SKU\n- Satış Fiyatı');
        return false;
    }

    // Set status based on action button
    const action = e.submitter?.value;
    if (action === 'draft') {
        document.querySelector('select[name="status"]').value = 'draft';
    } else if (action === 'publish') {
        const currentStatus = document.querySelector('select[name="status"]').value;
        if (currentStatus === 'draft') {
            document.querySelector('select[name="status"]').value = 'active';
        }
    }
});

// Track inventory checkbox toggle
document.getElementById('track_inventory').addEventListener('change', function() {
    const stockInputs = document.querySelectorAll('input[name="stock_quantity"], input[name="low_stock_threshold"]');
    stockInputs.forEach(input => {
        input.disabled = !this.checked;
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
