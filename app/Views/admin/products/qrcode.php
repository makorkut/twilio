<?php
$pageTitle = trans('admin.products.qrcode');
$currentPage = 'products';

if (!isset($product)) {
    header('Location: /admin/products');
    exit;
}

$db = container()->get(App\Core\Database::class);
$qrService = new App\Services\QRCodeService($db);
$qrCode = $qrService->getProductQRCode($product['id']);

ob_start();
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div style="padding: 16px 24px; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #10b981;">
        <strong>✓</strong> <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div class="page-header">
    <h1 class="page-title">
        📱 QR Kod: <?= htmlspecialchars($product['name']) ?>
    </h1>
    <p class="page-description">SKU: <?= htmlspecialchars($product['sku']) ?></p>
</div>

<div class="form-grid">
    <div class="form-main">
        <!-- Current QR Code -->
        <?php if ($qrCode): ?>
            <div class="form-section">
                <h3 class="form-section-title">Mevcut QR Kod</h3>

                <div style="text-align: center; padding: 40px; background: #f8fafc; border-radius: 8px; margin-bottom: 24px;">
                    <img src="/<?= htmlspecialchars($qrCode['file_path']) ?>"
                         alt="Product QR Code"
                         style="max-width: 300px; border: 4px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 8px;">
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 24px;">
                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 4px;">Oluşturulma:</label>
                        <?= date('d.m.Y H:i', strtotime($qrCode['created_at'])) ?>
                    </div>
                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 4px;">Boyut:</label>
                        <?= $qrCode['size'] ?>x<?= $qrCode['size'] ?> px
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label style="font-weight: 600; display: block; margin-bottom: 4px;">İçerik:</label>
                        <code style="padding: 8px 12px; background: white; border-radius: 4px; display: block; word-break: break-all;">
                            <?= htmlspecialchars($qrCode['content']) ?>
                        </code>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                    <a href="/<?= htmlspecialchars($qrCode['file_path']) ?>"
                       download="qr_<?= htmlspecialchars($product['sku']) ?>.png"
                       class="btn btn-primary">
                        💾 İndir
                    </a>
                    <button type="button" onclick="printQR()" class="btn btn-secondary">
                        🖨️ Yazdır
                    </button>
                    <form method="POST" action="/admin/products/<?= $product['id'] ?>/qrcode/regenerate" style="display: inline;">
                        <button type="submit" class="btn btn-secondary" style="width: 100%;">
                            🔄 Yeniden Oluştur
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="form-section">
                <h3 class="form-section-title">QR Kod Oluştur</h3>

                <div style="text-align: center; padding: 60px; background: #f8fafc; border-radius: 8px; margin-bottom: 24px;">
                    <div style="font-size: 64px; margin-bottom: 16px;">📱</div>
                    <h3 style="margin-bottom: 8px;">Henüz QR kod oluşturulmamış</h3>
                    <p style="color: var(--color-text-light); margin-bottom: 24px;">
                        Bu ürün için QR kod oluşturun ve müşterileriniz mobil cihazlarıyla kolayca ürüne ulaşsın
                    </p>
                </div>

                <form method="POST" action="/admin/products/<?= $product['id'] ?>/qrcode/generate">
                    <div class="form-group">
                        <label class="form-label">QR Kod Boyutu</label>
                        <select name="size" class="form-control">
                            <option value="200">200x200 px (Küçük)</option>
                            <option value="300" selected>300x300 px (Orta)</option>
                            <option value="500">500x500 px (Büyük)</option>
                            <option value="1000">1000x1000 px (Baskı Kalitesi)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        ✨ QR Kod Oluştur
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- QR Code Usage Info -->
        <div class="form-section">
            <h3 class="form-section-title">💡 QR Kod Kullanım Alanları</h3>
            <ul style="padding-left: 20px; line-height: 1.8; color: var(--color-text-light);">
                <li><strong>Ürün Etiketleri:</strong> Fiziksel ürünlere yapıştırarak müşterilerin detaylı bilgiye ulaşmasını sağlayın</li>
                <li><strong>Kataloglar:</strong> Basılı kataloglara ekleyerek online mağazaya yönlendirin</li>
                <li><strong>Depo Yönetimi:</strong> Envanter takibi ve stok kontrolü için kullanın</li>
                <li><strong>Müşteri Deneyimi:</strong> Mağaza içinde ürün bilgilerine kolay erişim</li>
                <li><strong>Pazarlama:</strong> Afişler, broşürler ve reklamlarda kullanın</li>
            </ul>
        </div>
    </div>

    <div class="form-sidebar">
        <!-- Product Info -->
        <div class="form-section">
            <h3 class="form-section-title">Ürün Bilgisi</h3>
            <div style="margin-bottom: 12px;">
                <strong>Ürün:</strong><br>
                <?= htmlspecialchars($product['name']) ?>
            </div>
            <div style="margin-bottom: 12px;">
                <strong>SKU:</strong><br>
                <?= htmlspecialchars($product['sku']) ?>
            </div>
            <div style="margin-bottom: 12px;">
                <strong>Ürün URL:</strong><br>
                <a href="/product/<?= htmlspecialchars($product['slug']) ?>" target="_blank" style="word-break: break-all; font-size: 12px;">
                    <?= htmlspecialchars($_ENV['APP_URL'] ?? 'http://localhost') ?>/product/<?= htmlspecialchars($product['slug']) ?>
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="form-section">
            <h3 class="form-section-title">İşlemler</h3>
            <a href="/admin/products/<?= $product['id'] ?>/edit" class="btn btn-secondary" style="width: 100%; margin-bottom: 8px;">
                ⬅️ Ürüne Dön
            </a>
            <a href="/product/<?= htmlspecialchars($product['slug']) ?>" target="_blank" class="btn btn-secondary" style="width: 100%; margin-bottom: 8px;">
                👁️ Önizleme
            </a>
            <a href="/admin/products" class="btn btn-secondary" style="width: 100%;">
                📦 Tüm Ürünler
            </a>
        </div>

        <!-- Technical Info -->
        <div class="form-section">
            <h3 class="form-section-title">📋 Teknik Bilgi</h3>
            <div style="font-size: 13px; line-height: 1.6; color: var(--color-text-light);">
                <strong>QR Kod Hakkında:</strong><br>
                QR (Quick Response) kodlar 2 boyutlu barkodlardır. Akıllı telefonlarla taranarak web sitelerine, ürün bilgilerine veya başka içeriklere hızlı erişim sağlar.
                <br><br>
                <strong>Önerilen Boyutlar:</strong><br>
                • Etiket: 200-300px<br>
                • Katalog: 300-500px<br>
                • Poster: 500-1000px
            </div>
        </div>
    </div>
</div>

<script>
function printQR() {
    const qrImage = document.querySelector('img[alt="Product QR Code"]');
    if (!qrImage) return;

    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write('<html><head><title>QR Kod - <?= htmlspecialchars($product['name']) ?></title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; margin: 0; font-family: Arial, sans-serif; }');
    printWindow.document.write('img { max-width: 400px; margin: 20px; }');
    printWindow.document.write('.info { text-align: center; margin-top: 20px; }');
    printWindow.document.write('@media print { body { padding: 20px; } }');
    printWindow.document.write('</style></head><body>');
    printWindow.document.write('<img src="' + qrImage.src + '" alt="QR Code">');
    printWindow.document.write('<div class="info">');
    printWindow.document.write('<h2><?= htmlspecialchars($product['name']) ?></h2>');
    printWindow.document.write('<p>SKU: <?= htmlspecialchars($product['sku']) ?></p>');
    printWindow.document.write('</div>');
    printWindow.document.write('</body></html>');
    printWindow.document.close();

    setTimeout(() => {
        printWindow.print();
    }, 250);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
