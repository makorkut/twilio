<?php
$pageTitle = trans('cart.title');

// Safely get cart data
$cart = [];
try {
    $db = container()->get(App\Core\Database::class);
    if ($db) {
        $cartService = new App\Services\CartService($db);
        $cart = $cartService->getCart();
    }
} catch (\Exception $e) {
    error_log("Cart error: " . $e->getMessage());
    $cart = [];
}

ob_start();
?>

<style>
    .cart-page {
        padding: 60px 0;
        min-height: 50vh;
    }

    .cart-layout {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 40px;
    }

    .cart-items {
        background: white;
    }

    .cart-item {
        display: grid;
        grid-template-columns: 100px 1fr auto;
        gap: 24px;
        padding: 24px;
        border-bottom: 1px solid var(--color-border);
    }

    .item-image {
        width: 100px;
        height: 100px;
        background: var(--color-bg-gray);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
    }

    .item-details h3 {
        font-size: 18px;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .item-meta {
        color: var(--color-text-light);
        font-size: 14px;
    }

    .item-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 16px;
    }

    .item-price {
        font-size: 20px;
        font-weight: 600;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .quantity-btn {
        width: 32px;
        height: 32px;
        border: 1px solid var(--color-border);
        background: white;
        cursor: pointer;
        font-size: 18px;
    }

    .cart-summary {
        background: var(--color-bg-gray);
        padding: 32px;
        height: fit-content;
        position: sticky;
        top: 100px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 16px;
        font-size: 16px;
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        padding-top: 16px;
        margin-top: 16px;
        border-top: 2px solid var(--color-border);
        font-size: 24px;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .cart-layout {
            grid-template-columns: 1fr;
        }

        .cart-item {
            grid-template-columns: 80px 1fr;
        }

        .item-actions {
            grid-column: 1 / -1;
            flex-direction: row;
            justify-content: space-between;
        }
    }
</style>

<div class="cart-page">
    <div class="container-narrow">
        <h1 style="font-size: 42px; font-weight: 300; margin-bottom: 40px;">Sepetim</h1>

        <?php if (empty($cart['items'])): ?>
            <div style="text-align: center; padding: 80px 0;">
                <div style="font-size: 64px; margin-bottom: 24px;">🛒</div>
                <h3 style="margin-bottom: 16px;">Sepetiniz boş</h3>
                <p style="color: var(--color-text-light); margin-bottom: 32px;">Alışverişe başlamak için ürünleri keşfedin</p>
                <a href="/products" class="btn">Ürünleri İncele</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <!-- Items -->
                <div class="cart-items">
                    <?php foreach ($cart['items'] as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">📦</div>

                            <div class="item-details">
                                <h3><?= htmlspecialchars($item['name']) ?></h3>
                                <div class="item-meta">
                                    SKU: <?= htmlspecialchars($item['sku']) ?> •
                                    <?= ucfirst($item['selling_unit']) ?>
                                </div>
                            </div>

                            <div class="item-actions">
                                <div class="item-price">₺<?= number_format($item['unit_price'] * $item['quantity'], 2) ?></div>

                                <form method="POST" action="/cart/update" class="quantity-controls">
                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="action" value="decrease" class="quantity-btn">-</button>
                                    <span style="width: 40px; text-align: center;"><?= $item['quantity'] ?></span>
                                    <button type="submit" name="action" value="increase" class="quantity-btn">+</button>
                                </form>

                                <form method="POST" action="/cart/remove">
                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: var(--color-text-light); cursor: pointer; font-size: 14px;">
                                        🗑️ Kaldır
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Summary -->
                <div class="cart-summary">
                    <h3 style="margin-bottom: 24px; font-weight: 600;">Sipariş Özeti</h3>

                    <div class="summary-row">
                        <span>Ara Toplam</span>
                        <strong>₺<?= number_format($cart['totals']['subtotal'], 2) ?></strong>
                    </div>

                    <?php if ($cart['totals']['discount_total'] > 0): ?>
                    <div class="summary-row" style="color: var(--color-accent);">
                        <span>İndirim</span>
                        <strong>-₺<?= number_format($cart['totals']['discount_total'], 2) ?></strong>
                    </div>
                    <?php endif; ?>

                    <div class="summary-row">
                        <span>KDV</span>
                        <strong>₺<?= number_format($cart['totals']['tax_total'], 2) ?></strong>
                    </div>

                    <div class="summary-row">
                        <span>Kargo</span>
                        <strong><?= $cart['totals']['subtotal'] >= 500 ? 'Ücretsiz' : '₺50.00' ?></strong>
                    </div>

                    <div class="summary-total">
                        <span>Toplam</span>
                        <strong>₺<?= number_format($cart['totals']['total'] + ($cart['totals']['subtotal'] >= 500 ? 0 : 50), 2) ?></strong>
                    </div>

                    <a href="/checkout" class="btn" style="width: 100%; margin-top: 24px; text-align: center;">
                        Ödemeye Geç
                    </a>

                    <a href="/products" style="display: block; text-align: center; margin-top: 16px; color: var(--color-text-light); text-decoration: none; font-size: 14px;">
                        ← Alışverişe Devam Et
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
