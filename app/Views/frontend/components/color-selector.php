<?php
/**
 * Color/Texture Selector Component
 * For product detail pages
 */

$productId = $productId ?? 0;
$colors = $colors ?? [];
$selectedColorId = $selectedColorId ?? null;

if (empty($colors)) {
    return;
}
?>

<div class="color-selector" style="margin: 32px 0;">
    <h3 style="margin-bottom: 16px; font-weight: 600; font-size: 18px;">
        🎨 Renk & Doku Seçimi
    </h3>

    <div class="colors-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; margin-bottom: 16px;">
        <?php foreach ($colors as $color): ?>
            <?php
            $isSelected = $selectedColorId == $color['id'];
            $isAvailable = $color['is_available'] && ($color['stock_quantity'] > 0 || !$color['track_inventory']);
            ?>
            <div class="color-option <?= $isSelected ? 'selected' : '' ?> <?= !$isAvailable ? 'unavailable' : '' ?>"
                 data-color-id="<?= $color['id'] ?>"
                 data-color-name="<?= htmlspecialchars($color['name']) ?>"
                 data-price-modifier="<?= $color['price_modifier'] ?>"
                 data-price-modifier-type="<?= $color['price_modifier_type'] ?>"
                 style="cursor: <?= $isAvailable ? 'pointer' : 'not-allowed' ?>; border: 2px solid <?= $isSelected ? 'var(--color-primary)' : 'var(--color-border)' ?>; border-radius: 8px; padding: 12px; transition: all 0.2s; opacity: <?= $isAvailable ? '1' : '0.5' ?>;"
                 onclick="<?= $isAvailable ? 'selectColor(this)' : '' ?>">

                <!-- Color Swatch -->
                <div class="color-swatch" style="width: 100%; aspect-ratio: 1; border-radius: 6px; margin-bottom: 8px; overflow: hidden; position: relative; border: 1px solid var(--color-border);">
                    <?php if ($color['swatch_image']): ?>
                        <img src="<?= htmlspecialchars($color['swatch_image']) ?>"
                             alt="<?= htmlspecialchars($color['name']) ?>"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    <?php elseif ($color['hex_color']): ?>
                        <div style="width: 100%; height: 100%; background: <?= htmlspecialchars($color['hex_color']) ?>;"></div>
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; background: linear-gradient(45deg, #ccc 25%, transparent 25%, transparent 75%, #ccc 75%, #ccc), linear-gradient(45deg, #ccc 25%, transparent 25%, transparent 75%, #ccc 75%, #ccc); background-size: 10px 10px; background-position: 0 0, 5px 5px;"></div>
                    <?php endif; ?>

                    <?php if ($isSelected): ?>
                        <div style="position: absolute; top: 4px; right: 4px; background: var(--color-primary); color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                            ✓
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Color Info -->
                <div style="text-align: center;">
                    <div style="font-weight: 600; font-size: 13px; margin-bottom: 4px;">
                        <?= htmlspecialchars($color['name']) ?>
                    </div>

                    <?php if ($color['ral_code']): ?>
                        <div style="font-size: 11px; color: var(--color-text-light); margin-bottom: 2px;">
                            <?= htmlspecialchars($color['ral_code']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($color['price_modifier'] != 0): ?>
                        <div style="font-size: 12px; color: var(--color-success); font-weight: 600;">
                            <?php if ($color['price_modifier_type'] === 'percent'): ?>
                                +<?= number_format($color['price_modifier'], 0) ?>%
                            <?php else: ?>
                                +₺<?= number_format($color['price_modifier'], 2) ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!$isAvailable): ?>
                        <div style="font-size: 11px; color: var(--color-danger); margin-top: 4px;">
                            Stokta Yok
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Selected Color Info -->
    <div id="selected-color-info" style="padding: 16px; background: var(--color-bg-gray); border-radius: 8px; display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong>Seçilen Renk:</strong>
                <span id="selected-color-name"></span>
            </div>
            <div id="selected-color-price" style="font-weight: 600; color: var(--color-success);"></div>
        </div>
    </div>

    <input type="hidden" name="selected_color_id" id="selected_color_id" value="<?= $selectedColorId ?>">
</div>

<style>
.color-option:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.color-option.selected {
    border-color: var(--color-primary) !important;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
}

.color-option.unavailable {
    cursor: not-allowed !important;
    filter: grayscale(50%);
}
</style>

<script>
let basePrice = <?= $basePrice ?? 0 ?>;

function selectColor(element) {
    // Remove previous selection
    document.querySelectorAll('.color-option').forEach(el => {
        el.classList.remove('selected');
        el.style.borderColor = 'var(--color-border)';
    });

    // Mark as selected
    element.classList.add('selected');
    element.style.borderColor = 'var(--color-primary)';

    // Get color data
    const colorId = element.dataset.colorId;
    const colorName = element.dataset.colorName;
    const priceModifier = parseFloat(element.dataset.priceModifier) || 0;
    const priceModifierType = element.dataset.priceModifierType;

    // Update hidden input
    document.getElementById('selected_color_id').value = colorId;

    // Calculate price
    let finalPrice = basePrice;
    let priceText = '';

    if (priceModifier !== 0) {
        if (priceModifierType === 'percent') {
            finalPrice = basePrice * (1 + priceModifier / 100);
            priceText = `+${priceModifier.toFixed(0)}%: ₺${finalPrice.toFixed(2)}`;
        } else {
            finalPrice = basePrice + priceModifier;
            priceText = `+₺${priceModifier.toFixed(2)}: ₺${finalPrice.toFixed(2)}`;
        }
    }

    // Update info display
    document.getElementById('selected-color-name').textContent = colorName;
    document.getElementById('selected-color-price').textContent = priceText;
    document.getElementById('selected-color-info').style.display = 'block';

    // Update main product price if exists
    const priceElement = document.querySelector('.product-price');
    if (priceElement && priceModifier !== 0) {
        // Store original price if not already stored
        if (!priceElement.dataset.originalPrice) {
            priceElement.dataset.originalPrice = priceElement.textContent;
        }
        priceElement.innerHTML = `₺${finalPrice.toFixed(2)}`;
    }
}

// Auto-select first available color if none selected
document.addEventListener('DOMContentLoaded', function() {
    const selectedInput = document.getElementById('selected_color_id');
    if (!selectedInput.value) {
        const firstAvailable = document.querySelector('.color-option:not(.unavailable)');
        if (firstAvailable) {
            selectColor(firstAvailable);
        }
    }
});
</script>
