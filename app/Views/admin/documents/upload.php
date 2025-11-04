<?php
$pageTitle = trans('admin.documents.upload');
$currentPage = 'documents';

$db = container()->get(App\Core\Database::class);

// Get all products for dropdown
$products = $db->fetchAll("SELECT id, name, sku FROM products WHERE status = 'active' ORDER BY name");

ob_start();
?>

<div class="page-header">
    <h1 class="page-title">Teknik Doküman Yükle</h1>
    <p class="page-description">CAD dosyaları, sertifikalar, MSDS, TDS ve diğer teknik dokümanlar</p>
</div>

<form method="POST" action="/admin/documents/upload" enctype="multipart/form-data">
    <div class="form-grid">
        <div class="form-main">
            <!-- File Upload -->
            <div class="form-section">
                <h3 class="form-section-title">Dosya Seçimi</h3>

                <div class="form-group">
                    <label class="form-label">Doküman Tipi *</label>
                    <select name="document_type" class="form-control" required id="documentType">
                        <option value="">Seçiniz...</option>
                        <option value="cad">📐 CAD Dosyası (.dwg, .dxf, .step, .stl)</option>
                        <option value="certificate">🏆 Sertifika (.pdf, .jpg)</option>
                        <option value="msds">⚠️ MSDS - Malzeme Güvenlik Bilgi Formu (.pdf)</option>
                        <option value="tds">📊 TDS - Teknik Veri Sayfası (.pdf)</option>
                        <option value="manual">📖 Kullanım Kılavuzu (.pdf)</option>
                        <option value="specification">📋 Teknik Şartname (.pdf, .docx)</option>
                        <option value="drawing">📏 Teknik Çizim (.pdf, .dwg, .jpg)</option>
                        <option value="other">📄 Diğer</option>
                    </select>
                    <small class="form-help" id="allowedExtensions"></small>
                </div>

                <div class="form-group">
                    <label class="form-label">Dosya Seç *</label>
                    <input type="file" name="file" class="form-control" required accept="*/*">
                    <small class="form-help">Maksimum dosya boyutu: 50 MB</small>
                </div>
            </div>

            <!-- Document Info -->
            <div class="form-section">
                <h3 class="form-section-title">Doküman Bilgileri</h3>

                <div class="form-group">
                    <label class="form-label">İlgili Ürün *</label>
                    <select name="product_id" class="form-control" required>
                        <option value="">Seçiniz...</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>">
                                <?= htmlspecialchars($product['name']) ?> (<?= htmlspecialchars($product['sku']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Doküman Başlığı *</label>
                    <input type="text" name="title" class="form-control" required
                           placeholder="Örn: Teknik Katalog 2024">
                    <small class="form-help">Dokümanın açıklayıcı başlığı</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Açıklama</label>
                    <textarea name="description" class="form-control" rows="4"
                              placeholder="Doküman hakkında detaylı açıklama..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Versiyon</label>
                        <input type="text" name="version" class="form-control" value="1.0"
                               placeholder="Örn: 1.0, 2.1, Rev.3">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Dil</label>
                        <select name="language" class="form-control">
                            <option value="tr">Türkçe</option>
                            <option value="en">English</option>
                            <option value="de">Deutsch</option>
                            <option value="fr">Français</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="form-section">
                <h3 class="form-section-title">Görünürlük</h3>

                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="is_public" value="1" checked>
                        <span>Herkese açık (Ön yüzde göster)</span>
                    </label>
                    <small class="form-help">
                        İşaretlenmezse sadece admin panelinden erişilebilir olur
                    </small>
                </div>
            </div>
        </div>

        <div class="form-sidebar">
            <!-- Tips -->
            <div class="form-section">
                <h3 class="form-section-title">💡 İpuçları</h3>
                <ul style="padding-left: 20px; color: var(--color-text-light); font-size: 14px; line-height: 1.6;">
                    <li>CAD dosyaları için DWG, DXF, STEP formatlarını tercih edin</li>
                    <li>Sertifikalar için PDF formatı kullanın</li>
                    <li>MSDS ve TDS dokümanları için PDF zorunludur</li>
                    <li>Dosya adında Türkçe karakter kullanmayın</li>
                    <li>Versiyonları takip edin (1.0, 2.0, 2.1)</li>
                </ul>
            </div>

            <!-- File Types -->
            <div class="form-section">
                <h3 class="form-section-title">Desteklenen Formatlar</h3>
                <div style="font-size: 13px; color: var(--color-text-light); line-height: 1.8;">
                    <strong>CAD:</strong> DWG, DXF, STEP, STL<br>
                    <strong>Doküman:</strong> PDF, DOC, DOCX<br>
                    <strong>Görsel:</strong> JPG, PNG
                </div>
            </div>

            <!-- Actions -->
            <div class="form-section">
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 8px;">
                    💾 Dokümanı Yükle
                </button>
                <a href="/admin/documents" class="btn btn-secondary" style="width: 100%; display: block; text-align: center;">
                    İptal
                </a>
            </div>
        </div>
    </div>
</form>

<script>
// Show allowed extensions based on document type
document.getElementById('documentType').addEventListener('change', function() {
    const extensions = {
        'cad': 'İzin verilen: .dwg, .dxf, .step, .stp, .iges, .igs, .stl, .obj',
        'certificate': 'İzin verilen: .pdf, .jpg, .jpeg, .png',
        'msds': 'İzin verilen: .pdf, .doc, .docx',
        'tds': 'İzin verilen: .pdf, .doc, .docx',
        'manual': 'İzin verilen: .pdf, .doc, .docx',
        'specification': 'İzin verilen: .pdf, .doc, .docx',
        'drawing': 'İzin verilen: .pdf, .dwg, .dxf, .jpg, .jpeg, .png',
        'other': 'İzin verilen: .pdf, .doc, .docx, .jpg, .jpeg, .png'
    };

    const helpText = document.getElementById('allowedExtensions');
    helpText.textContent = extensions[this.value] || '';
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
