<?php
$pageTitle = 'Medya Kütüphanesi';
$currentPage = 'media';

$db = container()->get(App\Core\Database::class);

$page = (int) ($_GET['page'] ?? 1);
$perPage = 24;
$offset = ($page - 1) * $perPage;

$search = $_GET['search'] ?? '';
$mediaType = $_GET['type'] ?? '';

$where = [];
$params = [];

if ($search) {
    $where[] = "filename LIKE ?";
    $params[] = "%$search%";
}

if ($mediaType) {
    $where[] = "media_type = ?";
    $params[] = $mediaType;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT * FROM media {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

$mediaItems = $db->fetchAll($sql, $params);

ob_start();
?>

<style>
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 16px;
        margin-top: 24px;
    }

    .media-item {
        border: 1px solid var(--color-border);
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s;
    }

    .media-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .media-thumbnail {
        width: 100%;
        height: 150px;
        background: var(--color-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .media-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .media-info {
        padding: 12px;
    }

    .media-filename {
        font-size: 13px;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 4px;
    }

    .media-meta {
        font-size: 11px;
        color: var(--color-text-light);
    }

    .upload-area {
        border: 2px dashed var(--color-border);
        border-radius: 12px;
        padding: 40px;
        text-align: center;
        background: var(--color-bg);
        cursor: pointer;
        transition: all 0.2s;
    }

    .upload-area:hover {
        border-color: var(--color-primary);
        background: white;
    }

    .upload-area.dragover {
        border-color: var(--color-primary);
        background: #e0e7ff;
    }
</style>

<?php if (isset($_SESSION['success_message'])): ?>
    <div style="padding: 16px 24px; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #10b981;">
        <strong>✓</strong> <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 class="page-title">Medya Kütüphanesi</h1>
        <p class="page-description"><?= count($mediaItems) ?> dosya</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
            ⬆️ Dosya Yükle
        </button>
    </div>
</div>

<!-- Upload Area (Hidden) -->
<form id="uploadForm" method="POST" action="/admin/media/upload" enctype="multipart/form-data" style="display: none;">
    <input type="file" id="fileInput" name="file" multiple accept="image/*,video/*,application/pdf"
           onchange="document.getElementById('uploadForm').submit()">
</form>

<!-- Filters -->
<div class="filters">
    <div class="filter-group">
        <label class="filter-label">Ara</label>
        <input type="text" class="form-control" placeholder="Dosya adı..."
               value="<?= htmlspecialchars($search) ?>"
               onchange="window.location.href = '?search=' + encodeURIComponent(this.value)">
    </div>

    <div class="filter-group">
        <label class="filter-label">Tür</label>
        <select class="form-control" onchange="window.location.href = '?type=' + this.value">
            <option value="">Tümü</option>
            <option value="image" <?= $mediaType === 'image' ? 'selected' : '' ?>>Görsel</option>
            <option value="video" <?= $mediaType === 'video' ? 'selected' : '' ?>>Video</option>
            <option value="document" <?= $mediaType === 'document' ? 'selected' : '' ?>>Döküman</option>
        </select>
    </div>
</div>

<!-- Media Grid -->
<div class="card">
    <?php if (empty($mediaItems)): ?>
        <div class="upload-area" onclick="document.getElementById('fileInput').click()">
            <div style="font-size: 48px; margin-bottom: 16px;">📁</div>
            <h3 style="margin-bottom: 8px;">Henüz dosya yok</h3>
            <p style="color: var(--color-text-light); margin-bottom: 24px;">
                Dosya yüklemek için tıklayın veya sürükleyin
            </p>
            <button type="button" class="btn btn-primary">Dosya Seç</button>
        </div>
    <?php else: ?>
        <div class="media-grid">
            <?php foreach ($mediaItems as $media): ?>
                <div class="media-item" onclick="viewMedia(<?= $media['id'] ?>)">
                    <div class="media-thumbnail">
                        <?php if ($media['media_type'] === 'image'): ?>
                            <img src="/uploads/media/<?= htmlspecialchars($media['file_path']) ?>"
                                 alt="<?= htmlspecialchars($media['filename']) ?>">
                        <?php else: ?>
                            <div style="font-size: 48px;">
                                <?php
                                echo match($media['media_type']) {
                                    'video' => '🎥',
                                    'document' => '📄',
                                    default => '📎'
                                };
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="media-info">
                        <div class="media-filename" title="<?= htmlspecialchars($media['filename']) ?>">
                            <?= htmlspecialchars($media['filename']) ?>
                        </div>
                        <div class="media-meta">
                            <?= ucfirst($media['media_type']) ?> •
                            <?= number_format($media['file_size'] / 1024, 1) ?> KB
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function viewMedia(id) {
    window.location.href = '/admin/media/' + id;
}

// Drag & drop support
const uploadArea = document.querySelector('.upload-area');
if (uploadArea) {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => uploadArea.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => uploadArea.classList.remove('dragover'), false);
    });

    uploadArea.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        document.getElementById('fileInput').files = files;
        document.getElementById('uploadForm').submit();
    }, false);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
