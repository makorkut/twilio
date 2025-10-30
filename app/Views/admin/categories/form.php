<?php
/**
 * Admin - Category Add/Edit Form
 */

$pageTitle = isset($category) ? 'Kategori Düzenle' : 'Yeni Kategori Ekle';
$currentPage = 'categories';
$isEdit = isset($category);
$category = $category ?? [];

// Get all categories for parent dropdown
$allCategories = [];
try {
    $db = container()->get(App\Core\Database::class);
    if ($db) {
        $allCategories = $db->fetchAll("SELECT id, parent_id, name FROM categories ORDER BY name");
    }
} catch (\Exception $e) {
    error_log("Error fetching categories in category form: " . $e->getMessage());
    // allCategories will be empty array, form will still render
}

// Build tree for dropdown
function buildCategoryOptions($categories, $parentId = null, $level = 0, $exclude = null) {
    $options = '';
    foreach ($categories as $cat) {
        if ($cat['parent_id'] == $parentId && $cat['id'] != $exclude) {
            $indent = str_repeat('— ', $level);
            $options .= '<option value="' . $cat['id'] . '">' . $indent . htmlspecialchars($cat['name']) . '</option>';
            $options .= buildCategoryOptions($categories, $cat['id'], $level + 1, $exclude);
        }
    }
    return $options;
}

ob_start();
?>

<div class="page-header">
    <h1 class="page-title"><?= $pageTitle ?></h1>
    <p class="page-description"><?= $isEdit ? 'Kategori bilgilerini güncelleyin' : 'Yeni bir kategori oluşturun' ?></p>
</div>

<form method="POST" action="<?= $isEdit ? "/admin/categories/edit/{$category['id']}" : '/admin/categories/create' ?>">
    <div class="form-grid">
        <div class="form-main">
            <!-- Basic Info -->
            <div class="form-section">
                <h3 class="form-section-title">Temel Bilgiler</h3>

                <div class="form-group">
                    <label class="form-label required">Kategori Adı</label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($category['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Açıklama</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">İkon (Emoji)</label>
                    <input type="text" name="icon" class="form-control" placeholder="📦"
                           value="<?= htmlspecialchars($category['icon'] ?? '') ?>">
                    <div class="form-hint">Kategori için bir emoji seçin (ör: 📦, 🏠, 🎨)</div>
                </div>
            </div>

            <!-- SEO -->
            <div class="form-section">
                <h3 class="form-section-title">SEO</h3>

                <div class="form-group">
                    <label class="form-label">Meta Başlık</label>
                    <input type="text" name="meta_title" class="form-control"
                           value="<?= htmlspecialchars($category['meta_title'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Meta Açıklama</label>
                    <textarea name="meta_description" class="form-control" rows="3"><?= htmlspecialchars($category['meta_description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Meta Anahtar Kelimeler</label>
                    <input type="text" name="meta_keywords" class="form-control"
                           value="<?= htmlspecialchars($category['meta_keywords'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="form-sidebar">
            <!-- Hierarchy -->
            <div class="form-section">
                <h3 class="form-section-title">Hiyerarşi</h3>

                <div class="form-group">
                    <label class="form-label">Üst Kategori</label>
                    <select name="parent_id" class="form-control">
                        <option value="">Üst Kategori Yok (Ana Kategori)</option>
                        <?php
                        $excludeId = $isEdit ? $category['id'] : null;
                        echo buildCategoryOptions($allCategories, null, 0, $excludeId);
                        ?>
                    </select>
                    <?php if ($isEdit): ?>
                    <script>
                        document.querySelector('select[name="parent_id"]').value = '<?= $category['parent_id'] ?? '' ?>';
                    </script>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Sıra</label>
                    <input type="number" name="sort_order" class="form-control"
                           value="<?= $category['sort_order'] ?? 0 ?>" min="0">
                    <div class="form-hint">Kategorilerin görüntülenme sırası</div>
                </div>
            </div>

            <!-- Status -->
            <div class="form-section">
                <h3 class="form-section-title">Durum</h3>

                <div class="checkbox-group">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           <?= ($category['is_active'] ?? 1) ? 'checked' : '' ?>>
                    <label for="is_active">Aktif</label>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="is_featured" id="is_featured" value="1"
                           <?= ($category['is_featured'] ?? 0) ? 'checked' : '' ?>>
                    <label for="is_featured">Öne çıkan kategori</label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="/admin/categories" class="btn btn-secondary">İptal</a>
        <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Güncelle' : 'Kaydet' ?>
        </button>
    </div>
</form>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
