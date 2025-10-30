<?php
$pageTitle = 'Diller - Admin Panel';
$db = container()->get(App\Core\Database::class);

// Fetch languages
$languages = $db->query(
    "SELECT * FROM languages ORDER BY is_default DESC, name ASC"
);

ob_start();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .header {
            background: white;
            padding: 20px 40px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 500;
        }

        .btn {
            padding: 10px 20px;
            background: #1a1a1a;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #000;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 40px;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f8f8;
        }

        th {
            text-align: left;
            padding: 16px 20px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
        }

        td {
            padding: 16px 20px;
            border-top: 1px solid #f0f0f0;
        }

        tr:hover {
            background: #fafafa;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-default {
            background: #fff3e0;
            color: #e65100;
        }

        .badge-inactive {
            background: #f5f5f5;
            color: #666;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        .btn-secondary {
            background: white;
            color: #1a1a1a;
            border: 1px solid #e0e0e0;
        }

        .btn-secondary:hover {
            background: #f5f5f5;
        }

        .btn-danger {
            background: #d32f2f;
            color: white;
        }

        .btn-danger:hover {
            background: #b71c1c;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .nav {
            padding: 20px 40px;
            background: white;
            border-bottom: 1px solid #e0e0e0;
        }

        .nav a {
            color: #666;
            text-decoration: none;
            margin-right: 20px;
            font-size: 14px;
        }

        .nav a:hover {
            color: #1a1a1a;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="nav">
        <a href="/admin">Dashboard</a>
        <a href="/admin/products">Ürünler</a>
        <a href="/admin/categories">Kategoriler</a>
        <a href="/admin/orders">Siparişler</a>
        <a href="/admin/languages" style="color: #1a1a1a; font-weight: 600;">Diller</a>
        <a href="/admin/settings">Ayarlar</a>
        <a href="/admin/logout" style="float: right;">Çıkış</a>
    </nav>

    <!-- Header -->
    <div class="header">
        <h1>Diller</h1>
        <a href="/admin/languages/create" class="btn">+ Yeni Dil Ekle</a>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="card">
            <?php if (empty($languages)): ?>
                <div class="empty-state">
                    <p>Henüz dil eklenmemiş</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Dil Adı</th>
                            <th>Kod</th>
                            <th>Locale</th>
                            <th>Durum</th>
                            <th>Varsayılan</th>
                            <th>Tarih Formatı</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($languages as $language): ?>
                            <tr>
                                <td><?= $language['id'] ?></td>
                                <td style="font-weight: 500;">
                                    <?= htmlspecialchars($language['name']) ?>
                                </td>
                                <td>
                                    <code style="background: #f5f5f5; padding: 2px 8px; border-radius: 3px; font-size: 12px;">
                                        <?= htmlspecialchars($language['language_code']) ?>
                                    </code>
                                </td>
                                <td><?= htmlspecialchars($language['locale_code'] ?? '-') ?></td>
                                <td>
                                    <?php if ($language['status']): ?>
                                        <span class="badge badge-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">Pasif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($language['is_default']): ?>
                                        <span class="badge badge-default">Varsayılan</span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($language['date_format']) ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="/admin/languages/edit/<?= $language['id'] ?>" class="btn btn-sm btn-secondary">Düzenle</a>
                                        <?php if (!$language['is_default']): ?>
                                            <form method="POST" action="/admin/languages/delete/<?= $language['id'] ?>" style="display: inline;">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu dili silmek istediğinizden emin misiniz?')">
                                                    Sil
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$html = ob_get_clean();
echo $html;
?>
