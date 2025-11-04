<?php
/**
 * Admin - Categories List
 * Hierarchical category management
 */

$pageTitle = trans('admin.categories.title');
$currentPage = 'categories';

// Get categories from database
try {
    $db = container()->get(App\Core\Database::class);
    $categoryService = new App\Services\CategoryService($db);

    // Search
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';

    // Build query
    $where = [];
    $params = [];

    if ($search) {
        $where[] = "name LIKE ?";
        $params[] = "%$search%";
    }

    if ($status === 'active') {
        $where[] = "is_active = 1";
    } elseif ($status === 'inactive') {
        $where[] = "is_active = 0";
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Get categories
    $sql = "SELECT * FROM categories {$whereClause} ORDER BY parent_id, sort_order, name";
    $categories = $db->fetchAll($sql, $params);

    // Build tree structure
    function buildTreeList($categories, $parentId = null, $level = 0) {
        $result = [];
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $category['level'] = $level;
                $result[] = $category;
                $children = buildTreeList($categories, $category['id'], $level + 1);
                $result = array_merge($result, $children);
            }
        }
        return $result;
    }

    $categoriesTree = buildTreeList($categories);

} catch (\Exception $e) {
    $categoriesTree = [];
    $errorMessage = $e->getMessage();
}

ob_start();
?>

<!-- Success/Error Messages -->
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
        <h1 class="page-title">Kategoriler</h1>
        <p class="page-description"><?= count($categoriesTree) ?> kategori</p>
    </div>
    <div>
        <a href="/admin/categories/create" class="btn btn-primary">
            ➕ Yeni Kategori Ekle
        </a>
    </div>
</div>

<style>
    .filters {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--color-border);
        padding: 20px;
        margin-bottom: 24px;
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .filter-group {
        flex: 1;
        min-width: 200px;
    }

    .filter-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: var(--color-text);
        margin-bottom: 6px;
    }

    .category-indent {
        display: inline-block;
    }

    .category-name {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
</style>

<!-- Filters -->
<div class="filters">
    <div class="filter-group">
        <label class="filter-label">Ara</label>
        <input type="text" class="form-control" placeholder="Kategori adı..."
               value="<?= htmlspecialchars($search) ?>"
               onchange="window.location.href = '?search=' + encodeURIComponent(this.value) + '&status=<?= htmlspecialchars($status) ?>'">
    </div>

    <div class="filter-group">
        <label class="filter-label">Durum</label>
        <select class="form-control"
                onchange="window.location.href = '?status=' + this.value + '&search=<?= htmlspecialchars($search) ?>'">
            <option value="">Tümü</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktif</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Pasif</option>
        </select>
    </div>

    <?php if ($search || $status): ?>
        <div class="filter-group" style="display: flex; align-items: flex-end;">
            <a href="/admin/categories" class="btn btn-secondary">
                🔄 Filtreleri Temizle
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Categories Table -->
<div class="card">
    <?php if (isset($errorMessage)): ?>
        <div style="padding: 40px; text-align: center; color: var(--color-danger);">
            <strong>⚠️ Hata:</strong> <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php elseif (empty($categoriesTree)): ?>
        <div style="padding: 60px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">📁</div>
            <h3 style="margin-bottom: 8px;">Henüz kategori yok</h3>
            <p style="color: var(--color-text-light); margin-bottom: 24px;">
                İlk kategorinizi ekleyerek başlayın
            </p>
            <a href="/admin/categories/create" class="btn btn-primary">
                ➕ Yeni Kategori Ekle
            </a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th style="text-align: center;">Ürün Sayısı</th>
                        <th style="text-align: center;">Sıra</th>
                        <th style="text-align: center;">Durum</th>
                        <th style="width: 200px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categoriesTree as $category): ?>
                        <tr>
                            <td>
                                <div class="category-name">
                                    <?php
                                    // Indentation for hierarchy
                                    $indent = str_repeat('—&nbsp;', $category['level']);
                                    echo $indent;
                                    ?>
                                    <?php if ($category['icon']): ?>
                                        <span><?= htmlspecialchars($category['icon']) ?></span>
                                    <?php endif; ?>
                                    <strong><?= htmlspecialchars($category['name']) ?></strong>
                                    <?php if ($category['is_featured']): ?>
                                        <span class="badge badge-warning">⭐ Öne Çıkan</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <?= number_format($category['product_count']) ?>
                            </td>
                            <td style="text-align: center;">
                                <?= $category['sort_order'] ?>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-<?= $category['is_active'] ? 'active' : 'inactive' ?>">
                                    <?= $category['is_active'] ? 'Aktif' : 'Pasif' ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="/admin/categories/edit/<?= $category['id'] ?>" class="btn btn-secondary btn-sm">
                                        ✏️ Düzenle
                                    </a>
                                    <a href="#" class="btn btn-secondary btn-sm"
                                       onclick="if(confirm('Bu kategoriyi silmek istediğinizden emin misiniz?')) { window.location.href='/admin/categories/delete/<?= $category['id'] ?>'; } return false;">
                                        🗑️ Sil
                                    </a>
                                </div>
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
