<?php
/**
 * Product Calculator Widget
 * Helps customers calculate required quantity based on area
 */

$productId = $productId ?? 0;
$calculatorType = $calculatorType ?? 'area'; // area, volume, length
$coveragePerUnit = $coveragePerUnit ?? 1; // m² per unit
$unit = $unit ?? 'm²';
?>

<div class="product-calculator" style="margin: 32px 0; padding: 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; color: white;">
    <h3 style="margin-bottom: 16px; font-size: 20px; font-weight: 600;">
        🧮 Miktar Hesaplayıcı
    </h3>
    <p style="margin-bottom: 24px; opacity: 0.9; font-size: 14px;">
        İhtiyacınız olan miktarı hesaplayın
    </p>

    <div class="calculator-form">
        <?php if ($calculatorType === 'area'): ?>
            <!-- Area Calculator (for flooring, paint, wallpaper) -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; opacity: 0.9;">
                        Genişlik (metre)
                    </label>
                    <input type="number" id="calc_width" step="0.1" min="0" value="5"
                           style="width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; opacity: 0.9;">
                        Uzunluk (metre)
                    </label>
                    <input type="number" id="calc_length" step="0.1" min="0" value="4"
                           style="width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600;">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-size: 14px; opacity: 0.9;">
                    Fire Oranı (%)
                </label>
                <select id="calc_waste" style="width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600;">
                    <option value="0">%0 - Fire yok</option>
                    <option value="5">%5 - Normal</option>
                    <option value="10" selected>%10 - Önerilen</option>
                    <option value="15">%15 - Karmaşık döşeme</option>
                    <option value="20">%20 - Çok karmaşık</option>
                </select>
                <small style="display: block; margin-top: 4px; opacity: 0.7; font-size: 12px;">
                    Kesim ve döşeme kayıpları için ekstra miktar
                </small>
            </div>

        <?php elseif ($calculatorType === 'volume'): ?>
            <!-- Volume Calculator (for paint, concrete) -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; opacity: 0.9;">
                        Genişlik (m)
                    </label>
                    <input type="number" id="calc_width" step="0.1" min="0" value="5"
                           style="width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; opacity: 0.9;">
                        Uzunluk (m)
                    </label>
                    <input type="number" id="calc_length" step="0.1" min="0" value="4"
                           style="width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 14px; opacity: 0.9;">
                        Yükseklik (m)
                    </label>
                    <input type="number" id="calc_height" step="0.1" min="0" value="0.1"
                           style="width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600;">
                </div>
            </div>

        <?php elseif ($calculatorType === 'length'): ?>
            <!-- Length Calculator (for cables, pipes) -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-size: 14px; opacity: 0.9;">
                    Toplam Uzunluk (metre)
                </label>
                <input type="number" id="calc_total_length" step="0.1" min="0" value="50"
                       style="width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600;">
            </div>
        <?php endif; ?>

        <button type="button" onclick="calculateQuantity()"
                style="width: 100%; padding: 14px; background: rgba(255,255,255,0.2); color: white; border: 2px solid white; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s;"
                onmouseover="this.style.background='white'; this.style.color='#667eea'"
                onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.color='white'">
            ✨ Hesapla
        </button>

        <!-- Results -->
        <div id="calc_results" style="margin-top: 24px; padding: 20px; background: rgba(255,255,255,0.15); border-radius: 8px; backdrop-filter: blur(10px); display: none;">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div>
                    <div style="opacity: 0.9; font-size: 13px; margin-bottom: 4px;">Alan</div>
                    <div id="calc_area" style="font-size: 24px; font-weight: 700;">0</div>
                </div>
                <div>
                    <div style="opacity: 0.9; font-size: 13px; margin-bottom: 4px;">Fire ile Alan</div>
                    <div id="calc_area_with_waste" style="font-size: 24px; font-weight: 700;">0</div>
                </div>
            </div>

            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.3);">
                <div style="opacity: 0.9; font-size: 14px; margin-bottom: 8px;">Gereken Miktar</div>
                <div id="calc_quantity" style="font-size: 36px; font-weight: 700; margin-bottom: 16px;">0</div>

                <button type="button" onclick="addCalculatedToCart()"
                        style="width: 100%; padding: 14px; background: white; color: #667eea; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    🛒 Sepete Ekle (<span id="calc_quantity_short">0</span> adet)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Product calculator configuration
const calculatorConfig = {
    productId: <?= $productId ?>,
    type: '<?= $calculatorType ?>',
    coveragePerUnit: <?= $coveragePerUnit ?>,
    unit: '<?= $unit ?>'
};

function calculateQuantity() {
    const type = calculatorConfig.type;
    let area = 0;

    if (type === 'area') {
        const width = parseFloat(document.getElementById('calc_width').value) || 0;
        const length = parseFloat(document.getElementById('calc_length').value) || 0;
        const wastePercent = parseFloat(document.getElementById('calc_waste').value) || 0;

        area = width * length;
        const areaWithWaste = area * (1 + wastePercent / 100);

        document.getElementById('calc_area').textContent = area.toFixed(2) + ' ' + calculatorConfig.unit;
        document.getElementById('calc_area_with_waste').textContent = areaWithWaste.toFixed(2) + ' ' + calculatorConfig.unit;

        const quantity = Math.ceil(areaWithWaste / calculatorConfig.coveragePerUnit);
        document.getElementById('calc_quantity').textContent = quantity + ' adet';
        document.getElementById('calc_quantity_short').textContent = quantity;

        // Store for cart
        window.calculatedQuantity = quantity;

    } else if (type === 'volume') {
        const width = parseFloat(document.getElementById('calc_width').value) || 0;
        const length = parseFloat(document.getElementById('calc_length').value) || 0;
        const height = parseFloat(document.getElementById('calc_height').value) || 0;

        const volume = width * length * height;
        document.getElementById('calc_area').textContent = volume.toFixed(2) + ' m³';

        const quantity = Math.ceil(volume / calculatorConfig.coveragePerUnit);
        document.getElementById('calc_quantity').textContent = quantity + ' adet';
        document.getElementById('calc_quantity_short').textContent = quantity;

        window.calculatedQuantity = quantity;

    } else if (type === 'length') {
        const totalLength = parseFloat(document.getElementById('calc_total_length').value) || 0;
        document.getElementById('calc_area').textContent = totalLength.toFixed(2) + ' m';

        const quantity = Math.ceil(totalLength / calculatorConfig.coveragePerUnit);
        document.getElementById('calc_quantity').textContent = quantity + ' adet';
        document.getElementById('calc_quantity_short').textContent = quantity;

        window.calculatedQuantity = quantity;
    }

    // Show results
    document.getElementById('calc_results').style.display = 'block';

    // Smooth scroll to results
    document.getElementById('calc_results').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function addCalculatedToCart() {
    const quantity = window.calculatedQuantity || 1;

    // Update main quantity input if exists
    const qtyInput = document.getElementById('quantity');
    if (qtyInput) {
        qtyInput.value = quantity;
    }

    // Submit add to cart form or call add to cart function
    const form = document.getElementById('add-to-cart-form');
    if (form) {
        form.querySelector('input[name="quantity"]').value = quantity;
        form.submit();
    } else {
        // Alternative: redirect with quantity
        window.location.href = '/cart/add?product_id=' + calculatorConfig.productId + '&quantity=' + quantity;
    }
}

// Auto-calculate on input change
document.addEventListener('DOMContentLoaded', function() {
    const inputs = ['calc_width', 'calc_length', 'calc_height', 'calc_waste', 'calc_total_length'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', () => {
                if (document.getElementById('calc_results').style.display !== 'none') {
                    calculateQuantity();
                }
            });
        }
    });
});
</script>

<style>
.product-calculator input[type="number"],
.product-calculator select {
    transition: all 0.2s;
}

.product-calculator input[type="number"]:focus,
.product-calculator select:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255,255,255,0.3);
    transform: scale(1.02);
}
</style>
