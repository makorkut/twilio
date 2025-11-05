<?php
$pageTitle = trans('frontend.products.page_title');

$db = container()->get(App\Core\Database::class);

$page = (int) ($_GET['page'] ?? 1);
$perPage = 12;
$offset = ($page - 1) * $perPage;

$search = $_GET['search'] ?? '';
$categoryId = $_GET['category'] ?? '';
$sortBy = $_GET['sort'] ?? 'newest';

$where = ["status = 'active'"];
$params = [];

if ($search) {
    $where[] = "name LIKE ?";
    $params[] = "%$search%";
}

if ($categoryId) {
    $where[] = "id IN (SELECT product_id FROM product_categories WHERE category_id = ?)";
    $params[] = $categoryId;
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

$orderBy = match($sortBy) {
    'price_low' => 'base_price ASC',
    'price_high' => 'base_price DESC',
    'name' => 'name ASC',
    default => 'created_at DESC'
};

$sql = "SELECT * FROM products {$whereClause} ORDER BY {$orderBy} LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

$products = $db->fetchAll($sql, $params);

$categories = $db->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");

ob_start();
?>

<style>
    .products-page {
        padding: 60px 0;
    }

    .products-layout {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 40px;
    }

    .sidebar {
        position: sticky;
        top: 100px;
        height: fit-content;
    }

    .sidebar h3 {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 16px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-section {
        margin-bottom: 32px;
    }

    .filter-list {
        list-style: none;
    }

    .filter-list li {
        margin-bottom: 8px;
    }

    .filter-list a {
        color: var(--color-text-light);
        text-decoration: none;
        font-size: 14px;
        transition: color 0.2s;
    }

    .filter-list a:hover,
    .filter-list a.active {
        color: var(--color-primary);
        font-weight: 500;
    }

    .products-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--color-border);
    }

    .products-count {
        font-size: 14px;
        color: var(--color-text-light);
    }

    .sort-select {
        padding: 8px 16px;
        border: 1px solid var(--color-border);
        background: white;
        font-size: 14px;
        cursor: pointer;
    }

    @media (max-width: 768px) {
        .products-layout {
            grid-template-columns: 1fr;
        }

        .sidebar {
            position: relative;
            top: 0;
        }
    }
</style>

<div class="products-page">
    <div class="container">
        <h1 style="font-size: 42px; font-weight: 300; margin-bottom: 40px;"><?= trans('frontend.products.heading') ?></h1>

        <div class="products-layout">
            <!-- Sidebar Filters -->
            <aside class="sidebar">
                <div class="filter-section">
                    <h3><?= trans('frontend.products.categories') ?></h3>
                    <ul class="filter-list">
                        <li><a href="/products" class="<?= !$categoryId ? 'active' : '' ?>"><?= trans('frontend.products.all') ?></a></li>
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="?category=<?= $category['id'] ?>"
                                   class="<?= $categoryId == $category['id'] ? 'active' : '' ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                    <span style="color: var(--color-text-light); font-size: 12px;">
                                        (<?= $category['product_count'] ?>)
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>

            <!-- Products Grid -->
            <div>
                <div class="products-header">
                    <div class="products-count"><?= count($products) ?> <?= trans('frontend.products.showing_count') ?></div>
                    <select class="sort-select" onchange="window.location.href='?sort=' + this.value + '&category=<?= $categoryId ?>'">
                        <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>><?= trans('frontend.products.sort_newest') ?></option>
                        <option value="price_low" <?= $sortBy === 'price_low' ? 'selected' : '' ?>><?= trans('frontend.products.sort_price_low_high') ?></option>
                        <option value="price_high" <?= $sortBy === 'price_high' ? 'selected' : '' ?>><?= trans('frontend.products.sort_price_high_low') ?></option>
                        <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>><?= trans('frontend.products.sort_name_az') ?></option>
                    </select>
                </div>

                <?php if (empty($products)): ?>
                    <div style="text-align: center; padding: 80px 0;">
                        <div style="font-size: 64px; margin-bottom: 24px;">📦</div>
                        <h3 style="margin-bottom: 16px;"><?= trans('frontend.products.no_products_found') ?></h3>
                        <p style="color: var(--color-text-light);"><?= trans('frontend.products.no_matching_products') ?></p>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <a href="/product/<?= htmlspecialchars($product['slug']) ?>" class="product-card">
                                <div class="product-image">📦</div>
                                <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                                <div class="product-meta"><?= htmlspecialchars($product['selling_unit']) ?></div>
                                <div class="product-price">₺<?= number_format($product['base_price'], 2) ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
