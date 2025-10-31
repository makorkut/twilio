<?php
$pageTitle = 'Kategoriler - Polyurethane';
$metaDescription = 'Tüm ürün kategorilerimize göz atın.';

// Fetch categories from database
$categories = [];
try {
    $db = container()->get(App\Core\Database::class);
    $categories = $db->query(
        "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name"
    );
} catch (\Exception $e) {
    error_log("Categories fetch error: " . $e->getMessage());
}

ob_start();
?>

<style>
    .page-hero {
        background: linear-gradient(135deg, var(--color-bg-gray) 0%, white 100%);
        padding: 80px 0;
        text-align: center;
    }

    .page-hero h1 {
        font-size: 48px;
        font-weight: 300;
        margin-bottom: 16px;
    }

    .categories-section {
        padding: 60px 0;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
    }

    .category-card {
        background: white;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        padding: 40px 30px;
        text-align: center;
        text-decoration: none;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .category-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }

    .category-icon {
        font-size: 48px;
        margin-bottom: 20px;
    }

    .category-name {
        font-size: 20px;
        font-weight: 500;
        color: var(--color-primary);
        margin-bottom: 8px;
    }

    .category-count {
        font-size: 14px;
        color: var(--color-text-light);
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: var(--color-text-light);
    }

    @media (max-width: 1024px) {
        .categories-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .page-hero h1 {
            font-size: 32px;
        }

        .categories-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .category-card {
            padding: 30px 20px;
        }
    }
</style>

<!-- Hero Section -->
<section class="page-hero">
    <div class="container-narrow">
        <h1>Kategoriler</h1>
        <p style="font-size: 18px; color: var(--color-text-light);">
            Poliüretan ürünlerimizi kategorilere göre keşfedin
        </p>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <?php if (count($categories) > 0): ?>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <a href="/category/<?= htmlspecialchars($category['slug']) ?>" class="category-card">
                        <div class="category-icon">
                            <?= htmlspecialchars($category['icon'] ?? '📦') ?>
                        </div>
                        <div class="category-name">
                            <?= htmlspecialchars($category['name']) ?>
                        </div>
                        <div class="category-count">
                            <?= (int)($category['product_count'] ?? 0) ?> ürün
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2 style="font-size: 24px; margin-bottom: 16px;">Henüz kategori eklenmemiş</h2>
                <p>Çok yakında ürün kategorilerimiz burada olacak.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
