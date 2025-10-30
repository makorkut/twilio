<?php
$pageTitle = 'Kataloglar';
$currentPage = 'catalogs';

$db = container()->get(App\Core\Database::class);
$catalogService = new App\Services\PDFCatalogService($db);
$catalogs = $catalogService->getAllCatalogs();

$lastCatalog = $_SESSION['last_catalog'] ?? null;
unset($_SESSION['last_catalog']);

ob_start();
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div style="padding: 16px 24px; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #10b981;">
        <strong>✓</strong> <?= htmlspecialchars($_SESSION['success_message']) ?>
        <?php if ($lastCatalog): ?>
            <a href="/<?= htmlspecialchars($lastCatalog) ?>" target="_blank" style="margin-left: 12px; color: inherit; text-decoration: underline;">
                Kataloğu Görüntüle
            </a>
        <?php endif; ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 class="page-title">📚 Kataloglar</h1>
        <p class="page-description"><?= count($catalogs) ?> katalog</p>
    </div>
    <a href="/admin/catalogs/generate" class="btn btn-primary">
        ➕ Yeni Katalog
    </a>
</div>

<div class="card">
    <?php if (empty($catalogs)): ?>
        <div style="padding: 60px; text-align: center;">
            <div style="font-size: 64px; margin-bottom: 16px;">📚</div>
            <h3 style="margin-bottom: 8px;">Henüz katalog yok</h3>
            <p style="color: var(--color-text-light); margin-bottom: 24px;">
                İlk kataloğunuzu oluşturun ve ürünlerinizi profesyonel bir şekilde sunun
            </p>
            <a href="/admin/catalogs/generate" class="btn btn-primary">
                Katalog Oluştur
            </a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Katalog</th>
                        <th style="text-align: center;">Düzen</th>
                        <th style="text-align: center;">Ürün Sayısı</th>
                        <th style="text-align: center;">Görseller</th>
                        <th style="text-align: center;">Fiyatlar</th>
                        <th>Oluşturma Tarihi</th>
                        <th style="width: 200px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($catalogs as $catalog): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($catalog['title']) ?></strong><br>
                                <small style="color: var(--color-text-light);">
                                    <?= htmlspecialchars($catalog['filename']) ?>
                                </small>
                            </td>
                            <td style="text-align: center;">
                                <?php
                                $layoutIcons = [
                                    'grid' => '🔲 Izgara',
                                    'list' => '📋 Liste',
                                    'detailed' => '📄 Detaylı'
                                ];
                                echo $layoutIcons[$catalog['layout']] ?? $catalog['layout'];
                                ?>
                            </td>
                            <td style="text-align: center;">
                                <strong><?= $catalog['product_count'] ?></strong>
                            </td>
                            <td style="text-align: center;">
                                <?= $catalog['include_images'] ? '✅' : '❌' ?>
                            </td>
                            <td style="text-align: center;">
                                <?= $catalog['include_prices'] ? '✅' : '❌' ?>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($catalog['created_at'])) ?></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="/<?= htmlspecialchars($catalog['filepath']) ?>"
                                       target="_blank"
                                       class="btn btn-secondary btn-sm">
                                        👁️ Görüntüle
                                    </a>
                                    <a href="/<?= htmlspecialchars($catalog['filepath']) ?>"
                                       download
                                       class="btn btn-secondary btn-sm">
                                        💾 İndir
                                    </a>
                                    <form method="POST" action="/admin/catalogs/<?= $catalog['id'] ?>/delete"
                                          style="display: inline;"
                                          onsubmit="return confirm('Bu kataloğu silmek istediğinize emin misiniz?');">
                                        <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Usage Info -->
<div class="card" style="margin-top: 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div style="padding: 24px;">
        <h3 style="margin-bottom: 16px; font-size: 20px;">💡 Katalog Kullanım İpuçları</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div>
                <strong style="display: block; margin-bottom: 8px;">📱 Dijital Dağıtım</strong>
                <p style="opacity: 0.9; font-size: 14px; line-height: 1.6;">
                    Katalogları e-posta ile gönderin veya web sitenizde yayınlayın
                </p>
            </div>
            <div>
                <strong style="display: block; margin-bottom: 8px;">🖨️ Baskı</strong>
                <p style="opacity: 0.9; font-size: 14px; line-height: 1.6;">
                    Katalogları yazdırarak müşterilerinize fiziksel kopya sunun
                </p>
            </div>
            <div>
                <strong style="display: block; margin-bottom: 8px;">📊 Güncelleme</strong>
                <p style="opacity: 0.9; font-size: 14px; line-height: 1.6;">
                    Fiyat veya ürün değişikliklerinde yeni katalog oluşturun
                </p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
