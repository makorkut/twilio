<?php
$pageTitle = 'Arama - Polyurethane';
$metaDescription = 'Ürünlerde arama yapın.';

// Get search query
$searchQuery = $_GET['q'] ?? '';

// Search in database if query exists
$results = [];
if (!empty($searchQuery)) {
    try {
        $db = container()->get(App\Core\Database::class);
        $results = $db->query(
            "SELECT p.*, pl.name, pl.slug
             FROM products p
             LEFT JOIN product_lang pl ON pl.product_id = p.id AND pl.lang = 'tr'
             WHERE pl.name LIKE ? OR p.sku LIKE ? OR pl.description LIKE ?
             AND p.status = 'active'
             LIMIT 50",
            ["%{$searchQuery}%", "%{$searchQuery}%", "%{$searchQuery}%"]
        );
    } catch (\Exception $e) {
        error_log("Search error: " . $e->getMessage());
    }
}

ob_start();
?>

<style>
    .search-hero {
        background: var(--color-bg-gray);
        padding: 60px 0 40px;
    }

    .search-box {
        max-width: 600px;
        margin: 0 auto;
    }

    .search-input-wrapper {
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 16px 50px 16px 20px;
        border: 2px solid var(--color-border);
        border-radius: 8px;
        font-size: 16px;
    }

    .search-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: var(--color-primary);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
    }

    .results-section {
        padding: 60px 0;
    }

    .results-count {
        font-size: 18px;
        margin-bottom: 30px;
        color: var(--color-text-light);
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    .product-card {
        background: white;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        padding: 20px;
        text-decoration: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .product-name {
        font-size: 16px;
        font-weight: 500;
        color: var(--color-primary);
        margin-bottom: 8px;
    }

    .product-price {
        font-size: 18px;
        font-weight: 600;
        color: var(--color-text);
    }

    .no-results {
        text-align: center;
        padding: 80px 20px;
        color: var(--color-text-light);
    }

    @media (max-width: 1024px) {
        .product-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .product-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
    }
</style>

<!-- Search Hero -->
<section class="search-hero">
    <div class="container">
        <h1 style="text-align: center; font-size: 32px; font-weight: 300; margin-bottom: 30px;">Ürün Ara</h1>

        <div class="search-box">
            <form method="GET" action="/search">
                <div class="search-input-wrapper">
                    <input
                        type="text"
                        name="q"
                        class="search-input"
                        placeholder="Ürün adı, SKU veya açıklama..."
                        value="<?= htmlspecialchars($searchQuery) ?>"
                        autofocus
                    >
                    <button type="submit" class="search-btn">Ara</button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Results Section -->
<section class="results-section">
    <div class="container">
        <?php if (!empty($searchQuery)): ?>
            <div class="results-count">
                <strong><?= count($results) ?></strong> sonuç bulundu: "<?= htmlspecialchars($searchQuery) ?>"
            </div>

            <?php if (count($results) > 0): ?>
                <div class="product-grid">
                    <?php foreach ($results as $product): ?>
                        <a href="/product/<?= htmlspecialchars($product['slug'] ?? $product['id']) ?>" class="product-card">
                            <div class="product-name"><?= htmlspecialchars($product['name'] ?? 'İsimsiz Ürün') ?></div>
                            <div class="product-price">₺<?= number_format($product['base_price'] ?? 0, 2) ?></div>
                            <?php if (!empty($product['sku'])): ?>
                                <div style="font-size: 13px; color: #999; margin-top: 4px;">
                                    SKU: <?= htmlspecialchars($product['sku']) ?>
                                </div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <h2 style="font-size: 24px; margin-bottom: 16px;">Sonuç bulunamadı</h2>
                    <p>Arama kriterlerinizi değiştirerek tekrar deneyin.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <h2 style="font-size: 24px; margin-bottom: 16px;">Ürün Arama</h2>
                <p>Yukarıdaki arama kutusunu kullanarak ürün arayabilirsiniz.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
