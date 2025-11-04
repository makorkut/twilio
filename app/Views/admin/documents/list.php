<?php
$pageTitle = trans('admin.documents.title');
$currentPage = 'documents';

$db = container()->get(App\Core\Database::class);
$documentService = new App\Services\TechnicalDocumentService($db);

$page = (int) ($_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

$filters = [
    'search' => $_GET['search'] ?? '',
    'document_type' => $_GET['type'] ?? '',
    'product_id' => $_GET['product_id'] ?? '',
];

$documents = $documentService->search($filters, $perPage, $offset);
$stats = $documentService->getStatistics();

ob_start();
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div style="padding: 16px 24px; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #10b981;">
        <strong>✓</strong> <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div style="padding: 16px 24px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #ef4444;">
        <strong>✗</strong> <?= htmlspecialchars($_SESSION['error_message']) ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 class="page-title">Teknik Dokümanlar</h1>
        <p class="page-description">
            <?= $stats['total'] ?> doküman
            • <?= number_format($stats['total_size'] / 1024 / 1024, 2) ?> MB
        </p>
    </div>
    <a href="/admin/documents/upload" class="btn btn-primary">
        ➕ Yeni Doküman Yükle
    </a>
</div>

<!-- Statistics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <?php
    $types = ['cad', 'certificate', 'msds', 'tds', 'manual', 'specification', 'drawing'];
    foreach ($types as $type):
        $count = $stats['by_type'][$type] ?? 0;
        if ($count > 0):
    ?>
        <div class="card" style="padding: 20px; text-align: center;">
            <div style="font-size: 32px; margin-bottom: 8px;">
                <?= App\Services\TechnicalDocumentService::getTypeIcon($type) ?>
            </div>
            <div style="font-size: 24px; font-weight: bold; margin-bottom: 4px;">
                <?= $count ?>
            </div>
            <div style="color: var(--color-text-light); font-size: 13px;">
                <?= App\Services\TechnicalDocumentService::getTypeLabel($type) ?>
            </div>
        </div>
    <?php
        endif;
    endforeach;
    ?>
</div>

<!-- Filters -->
<div class="filters">
    <div class="filter-group">
        <label class="filter-label">Ara</label>
        <input type="text" class="form-control" placeholder="Doküman adı, açıklama..."
               value="<?= htmlspecialchars($filters['search']) ?>"
               onchange="window.location.href = '?search=' + encodeURIComponent(this.value) + '&type=<?= $filters['document_type'] ?>'">
    </div>

    <div class="filter-group">
        <label class="filter-label">Doküman Tipi</label>
        <select class="form-control"
                onchange="window.location.href = '?type=' + this.value + '&search=<?= urlencode($filters['search']) ?>'">
            <option value="">Tümü</option>
            <option value="cad" <?= $filters['document_type'] === 'cad' ? 'selected' : '' ?>>📐 CAD Dosyası</option>
            <option value="certificate" <?= $filters['document_type'] === 'certificate' ? 'selected' : '' ?>>🏆 Sertifika</option>
            <option value="msds" <?= $filters['document_type'] === 'msds' ? 'selected' : '' ?>>⚠️ MSDS</option>
            <option value="tds" <?= $filters['document_type'] === 'tds' ? 'selected' : '' ?>>📊 TDS</option>
            <option value="manual" <?= $filters['document_type'] === 'manual' ? 'selected' : '' ?>>📖 Kullanım Kılavuzu</option>
            <option value="specification" <?= $filters['document_type'] === 'specification' ? 'selected' : '' ?>>📋 Teknik Şartname</option>
            <option value="drawing" <?= $filters['document_type'] === 'drawing' ? 'selected' : '' ?>>📏 Teknik Çizim</option>
        </select>
    </div>
</div>

<!-- Documents List -->
<div class="card">
    <?php if (empty($documents)): ?>
        <div style="padding: 60px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">📄</div>
            <h3>Henüz doküman yok</h3>
            <p style="color: var(--color-text-light); margin-bottom: 24px;">İlk teknik dokümanı yükleyin</p>
            <a href="/admin/documents/upload" class="btn btn-primary">Doküman Yükle</a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Doküman</th>
                        <th>Ürün</th>
                        <th style="text-align: center;">Tip</th>
                        <th style="text-align: center;">Versiyon</th>
                        <th style="text-align: right;">Boyut</th>
                        <th>Yükleme Tarihi</th>
                        <th style="width: 200px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <span style="font-size: 24px;">
                                        <?= App\Services\TechnicalDocumentService::getTypeIcon($doc['document_type']) ?>
                                    </span>
                                    <div>
                                        <strong><?= htmlspecialchars($doc['title']) ?></strong><br>
                                        <small style="color: var(--color-text-light);">
                                            <?= htmlspecialchars($doc['original_name']) ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($doc['product_name'] ?? '-') ?></td>
                            <td style="text-align: center;">
                                <span class="badge badge-info" style="font-size: 11px;">
                                    <?= App\Services\TechnicalDocumentService::getTypeLabel($doc['document_type']) ?>
                                </span>
                            </td>
                            <td style="text-align: center;"><?= htmlspecialchars($doc['version']) ?></td>
                            <td style="text-align: right;"><?= number_format($doc['file_size'] / 1024, 0) ?> KB</td>
                            <td><?= date('d.m.Y H:i', strtotime($doc['created_at'])) ?></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="/storage/documents/<?= htmlspecialchars($doc['file_name']) ?>"
                                       target="_blank"
                                       class="btn btn-secondary btn-sm">
                                        📥 İndir
                                    </a>
                                    <a href="/admin/documents/<?= $doc['id'] ?>/edit" class="btn btn-secondary btn-sm">
                                        ✏️ Düzenle
                                    </a>
                                    <form method="POST" action="/admin/documents/<?= $doc['id'] ?>/delete"
                                          style="display: inline;"
                                          onsubmit="return confirm('Bu dokümanı silmek istediğinize emin misiniz?');">
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
