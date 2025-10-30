<?php
$pageTitle = 'Ana Sayfa - Polyurethane';

$db = container()->get(App\Core\Database::class);

// Get featured products
$featuredProducts = $db->fetchAll(
    "SELECT * FROM products WHERE is_featured = 1 AND status = 'active' LIMIT 8"
);

// Get categories
$categories = $db->fetchAll(
    "SELECT * FROM categories WHERE is_featured = 1 AND is_active = 1 ORDER BY sort_order LIMIT 6"
);

ob_start();
?>

<style>
    .hero {
        background: linear-gradient(135deg, var(--color-bg-gray) 0%, white 100%);
        padding: 120px 0;
        text-align: center;
    }

    .hero h1 {
        font-size: 56px;
        font-weight: 300;
        letter-spacing: -1px;
        margin-bottom: 24px;
        color: var(--color-primary);
    }

    .hero p {
        font-size: 20px;
        color: var(--color-text-light);
        margin-bottom: 40px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .section {
        padding: 80px 0;
    }

    .section-title {
        font-size: 36px;
        font-weight: 300;
        text-align: center;
        margin-bottom: 60px;
        letter-spacing: -0.5px;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 24px;
        margin-bottom: 80px;
    }

    .category-card {
        background: white;
        border: 1px solid var(--color-border);
        padding: 40px 24px;
        text-align: center;
        transition: all 0.3s;
        text-decoration: none;
        color: var(--color-text);
    }

    .category-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        border-color: var(--color-primary);
    }

    .category-icon {
        font-size: 48px;
        margin-bottom: 16px;
    }

    .category-name {
        font-size: 18px;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .category-count {
        font-size: 14px;
        color: var(--color-text-light);
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 32px;
    }

    .product-card {
        text-decoration: none;
        color: var(--color-text);
        transition: transform 0.3s;
    }

    .product-card:hover {
        transform: translateY(-8px);
    }

    .product-image {
        width: 100%;
        aspect-ratio: 1;
        background: var(--color-bg-gray);
        margin-bottom: 16px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 64px;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-name {
        font-size: 16px;
        font-weight: 400;
        margin-bottom: 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .product-meta {
        font-size: 13px;
        color: var(--color-text-light);
        margin-bottom: 12px;
    }

    .product-price {
        font-size: 20px;
        font-weight: 600;
        color: var(--color-primary);
    }

    .cta-section {
        background: var(--color-primary);
        color: white;
        padding: 80px 0;
        text-align: center;
    }

    .cta-section h2 {
        font-size: 42px;
        font-weight: 300;
        margin-bottom: 24px;
    }

    .cta-section p {
        font-size: 18px;
        margin-bottom: 40px;
        opacity: 0.9;
    }

    @media (max-width: 1024px) {
        .products-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .hero h1 {
            font-size: 36px;
        }

        .products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .categories-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Premium Poliüretan Ürünler</h1>
        <p>Mekanlarınıza estetik ve kalite katın. Duvar kaplamalarından profillere, geniş ürün yelpazesi.</p>
        <a href="/products" class="btn">Ürünleri Keşfedin</a>
    </div>
</section>

<!-- Categories -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Kategoriler</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <a href="/category/<?= htmlspecialchars($category['slug']) ?>" class="category-card">
                    <div class="category-icon"><?= htmlspecialchars($category['icon'] ?? '📦') ?></div>
                    <div class="category-name"><?= htmlspecialchars($category['name']) ?></div>
                    <div class="category-count"><?= $category['product_count'] ?> ürün</div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="section" style="background: var(--color-bg-gray); padding: 80px 0;">
    <div class="container">
        <h2 class="section-title">Öne Çıkan Ürünler</h2>

        <?php if (empty($featuredProducts)): ?>
            <p style="text-align: center; color: var(--color-text-light);">Henüz öne çıkan ürün yok</p>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($featuredProducts as $product): ?>
                    <a href="/product/<?= htmlspecialchars($product['slug']) ?>" class="product-card">
                        <div class="product-image">
                            📦
                        </div>
                        <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                        <div class="product-meta"><?= htmlspecialchars($product['selling_unit']) ?></div>
                        <div class="product-price">₺<?= number_format($product['base_price'], 2) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2>B2B Müşteri misiniz?</h2>
        <p>Toptan fiyatlar, özel indirimler ve kredi limiti için bayi başvurusu yapın</p>
        <a href="/b2b" class="btn" style="background: white; color: var(--color-primary);">Bayi Başvurusu</a>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
