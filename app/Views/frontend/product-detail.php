<?php
$pageTitle = htmlspecialchars($product['name'] ?? 'Ürün');

ob_start();
?>

<style>
    .product-detail {
        padding: 60px 0;
    }

    .product-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 80px;
        margin-bottom: 80px;
    }

    .product-gallery {
        position: sticky;
        top: 100px;
        height: fit-content;
    }

    .main-image {
        width: 100%;
        aspect-ratio: 1;
        background: var(--color-bg-gray);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 120px;
    }

    .product-info h1 {
        font-size: 36px;
        font-weight: 300;
        margin-bottom: 16px;
    }

    .product-meta {
        color: var(--color-text-light);
        margin-bottom: 24px;
        font-size: 14px;
    }

    .product-price {
        font-size: 42px;
        font-weight: 600;
        margin-bottom: 32px;
    }

    .product-description {
        line-height: 1.8;
        margin-bottom: 32px;
        padding-bottom: 32px;
        border-bottom: 1px solid var(--color-border);
    }

    .add-to-cart-section {
        display: flex;
        gap: 16px;
        align-items: center;
        margin-bottom: 32px;
    }

    .quantity-input {
        width: 120px;
        padding: 14px;
        border: 1px solid var(--color-border);
        font-size: 16px;
        text-align: center;
    }

    .product-specs {
        background: var(--color-bg-gray);
        padding: 32px;
        margin-top: 40px;
    }

    .specs-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .spec-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid var(--color-border);
    }

    @media (max-width: 768px) {
        .product-layout {
            grid-template-columns: 1fr;
            gap: 40px;
        }

        .specs-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="product-detail">
    <div class="container-narrow">
        <div class="product-layout">
            <!-- Gallery -->
            <div class="product-gallery">
                <div class="main-image">📦</div>
            </div>

            <!-- Info -->
            <div class="product-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>

                <div class="product-meta">
                    SKU: <?= htmlspecialchars($product['sku']) ?> •
                    <?= ucfirst($product['selling_unit']) ?>
                    <?php if ($product['is_new']): ?>
                        • <span style="color: var(--color-accent);">🆕 Yeni</span>
                    <?php endif; ?>
                </div>

                <div class="product-price">
                    ₺<?= number_format($product['base_price'], 2) ?>
                    <?php if ($product['compare_price']): ?>
                        <span style="font-size: 24px; text-decoration: line-through; color: var(--color-text-light); margin-left: 16px;">
                            ₺<?= number_format($product['compare_price'], 2) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($product['short_description']): ?>
                    <div class="product-description">
                        <?= nl2br(htmlspecialchars($product['short_description'])) ?>
                    </div>
                <?php endif; ?>

                <!-- Add to Cart -->
                <form method="POST" action="/cart/add" class="add-to-cart-section">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="number" name="quantity" value="1" min="<?= $product['min_order_quantity'] ?? 1 ?>"
                           class="quantity-input" <?= !$product['track_inventory'] || $product['stock_quantity'] > 0 ? '' : 'disabled' ?>>
                    <button type="submit" class="btn" style="flex: 1;"
                            <?= !$product['track_inventory'] || $product['stock_quantity'] > 0 ? '' : 'disabled' ?>>
                        <?= !$product['track_inventory'] || $product['stock_quantity'] > 0 ? 'Sepete Ekle' : 'Stokta Yok' ?>
                    </button>
                </form>

                <?php if ($product['track_inventory'] && $product['stock_quantity'] > 0): ?>
                    <div style="color: var(--color-text-light); font-size: 14px; margin-bottom: 24px;">
                        Stok: <?= $product['stock_quantity'] ?> adet
                    </div>
                <?php endif; ?>

                <!-- Specs -->
                <?php if ($product['length_mm'] || $product['width_mm'] || $product['weight_kg']): ?>
                <div class="product-specs">
                    <h3 style="margin-bottom: 20px; font-weight: 600;">Teknik Özellikler</h3>
                    <div class="specs-grid">
                        <?php if ($product['length_mm']): ?>
                            <div class="spec-item">
                                <span>Uzunluk</span>
                                <strong><?= $product['length_mm'] ?> mm</strong>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['width_mm']): ?>
                            <div class="spec-item">
                                <span>Genişlik</span>
                                <strong><?= $product['width_mm'] ?> mm</strong>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['height_mm']): ?>
                            <div class="spec-item">
                                <span>Yükseklik</span>
                                <strong><?= $product['height_mm'] ?> mm</strong>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['weight_kg']): ?>
                            <div class="spec-item">
                                <span>Ağırlık</span>
                                <strong><?= $product['weight_kg'] ?> kg</strong>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['package_quantity']): ?>
                            <div class="spec-item">
                                <span>Paket İçi Miktar</span>
                                <strong><?= $product['package_quantity'] ?> adet</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Product Calculator Widget -->
                <?php if (!empty($product['coverage_per_unit'])): ?>
                    <?php
                    $productId = $product['id'];
                    $coveragePerUnit = $product['coverage_per_unit'];
                    $calculatorType = $product['calculator_type'] ?? 'area';
                    $unit = $product['coverage_unit'] ?? 'm²';
                    include __DIR__ . '/components/product-calculator.php';
                    ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Description -->
        <?php if ($product['description']): ?>
        <div style="padding: 60px 0; border-top: 1px solid var(--color-border);">
            <h2 style="font-size: 28px; font-weight: 300; margin-bottom: 24px;">Ürün Açıklaması</h2>
            <div style="line-height: 1.8; color: var(--color-text-light);">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
