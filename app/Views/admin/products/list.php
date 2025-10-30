<?php
/**
 * Admin - Products List
 * CRUD operations for products
 */

$pageTitle = 'Ürünler';
$currentPage = 'products';

// Get products from database
try {
    $db = container()->get(App\Core\Database::class);

    // Pagination
    $page = (int) ($_GET['page'] ?? 1);
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    // Search
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';

    // Build query
    $where = [];
    $params = [];

    if ($search) {
        $where[] = "(name LIKE ? OR sku LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($status) {
        $where[] = "status = ?";
        $params[] = $status;
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM products $whereClause";
    $result = $db->fetch($countSql, $params);
    $totalProducts = $result['total'] ?? 0;
    $totalPages = ceil($totalProducts / $perPage);

    // Get products
    $sql = "
        SELECT
            p.id,
            p.sku,
            p.name,
            p.base_price,
            p.stock_quantity,
            p.status,
            p.is_featured,
            p.created_at,
            (SELECT COUNT(*) FROM product_variants WHERE product_id = p.id) as variant_count
        FROM products p
        $whereClause
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $params[] = $perPage;
    $params[] = $offset;

    $products = $db->fetchAll($sql, $params);

} catch (\Exception $e) {
    $products = [];
    $totalProducts = 0;
    $totalPages = 0;
    $errorMessage = $e->getMessage();
}

ob_start();
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 class="page-title">Ürünler</h1>
        <p class="page-description"><?= number_format($totalProducts) ?> ürün</p>
    </div>
    <div>
        <a href="/admin/products/create" class="btn btn-primary">
            ➕ Yeni Ürün Ekle
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

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--color-primary);
    }

    .table-actions {
        display: flex;
        gap: 8px;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }

    .pagination {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin-top: 24px;
    }

    .pagination a,
    .pagination span {
        padding: 8px 12px;
        border: 1px solid var(--color-border);
        border-radius: 6px;
        text-decoration: none;
        color: var(--color-text);
        font-size: 14px;
    }

    .pagination a:hover {
        background: var(--color-bg);
    }

    .pagination .active {
        background: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
    }

    .product-sku {
        font-family: 'Courier New', monospace;
        font-size: 13px;
        color: var(--color-text-light);
    }

    .product-image-placeholder {
        width: 48px;
        height: 48px;
        background: var(--color-bg);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
</style>

<!-- Filters -->
<div class="filters">
    <div class="filter-group">
        <label class="filter-label">Ara</label>
        <input type="text" class="form-control" placeholder="Ürün adı veya SKU..."
               value="<?= htmlspecialchars($search) ?>"
               onchange="window.location.href = '?search=' + encodeURIComponent(this.value) + '&status=<?= htmlspecialchars($status) ?>'">
    </div>

    <div class="filter-group">
        <label class="filter-label">Durum</label>
        <select class="form-control"
                onchange="window.location.href = '?status=' + this.value + '&search=<?= htmlspecialchars($search) ?>'">
            <option value="">Tümü</option>
            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Taslak</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktif</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Pasif</option>
            <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Arşiv</option>
        </select>
    </div>

    <?php if ($search || $status): ?>
        <div class="filter-group" style="display: flex; align-items: flex-end;">
            <a href="/admin/products" class="btn btn-secondary">
                🔄 Filtreleri Temizle
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Products Table -->
<div class="card">
    <?php if (isset($errorMessage)): ?>
        <div style="padding: 40px; text-align: center; color: var(--color-danger);">
            <strong>⚠️ Hata:</strong> <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php elseif (empty($products)): ?>
        <div style="padding: 60px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">📦</div>
            <h3 style="margin-bottom: 8px;">Henüz ürün yok</h3>
            <p style="color: var(--color-text-light); margin-bottom: 24px;">
                İlk ürününüzü ekleyerek başlayın
            </p>
            <a href="/admin/products/create" class="btn btn-primary">
                ➕ Yeni Ürün Ekle
            </a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;"></th>
                        <th>Ürün</th>
                        <th>SKU</th>
                        <th style="text-align: right;">Fiyat</th>
                        <th style="text-align: center;">Stok</th>
                        <th style="text-align: center;">Varyant</th>
                        <th style="text-align: center;">Durum</th>
                        <th style="width: 200px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <div class="product-image-placeholder">
                                    📦
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($product['name']) ?></strong>
                                    <?php if ($product['is_featured']): ?>
                                        <span class="badge badge-warning" style="margin-left: 8px;">⭐ Öne Çıkan</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="product-sku"><?= htmlspecialchars($product['sku']) ?></span>
                            </td>
                            <td style="text-align: right;">
                                <strong>₺<?= number_format($product['base_price'], 2) ?></strong>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($product['stock_quantity'] <= 0): ?>
                                    <span style="color: var(--color-danger);">Stokta yok</span>
                                <?php else: ?>
                                    <span><?= number_format($product['stock_quantity']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($product['variant_count'] > 0): ?>
                                    <span class="badge badge-processing">
                                        <?= $product['variant_count'] ?> varyant
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--color-text-light);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-<?= htmlspecialchars($product['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($product['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="/admin/products/edit/<?= $product['id'] ?>" class="btn btn-secondary btn-sm">
                                        ✏️ Düzenle
                                    </a>
                                    <a href="/admin/products/<?= $product['id'] ?>" class="btn btn-secondary btn-sm">
                                        👁️ Görüntüle
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">
                        ← Önceki
                    </a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">
                        Sonraki →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
