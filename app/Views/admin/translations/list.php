<?php
$pageTitle = trans('admin.translations.page_title');

ob_start();
?>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
    }

    .search-bar {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
    }

    .search-bar input {
        flex: 1;
        padding: 10px 16px;
        border: 1px solid var(--color-border);
        border-radius: 4px;
    }

    .search-bar select {
        padding: 10px 16px;
        border: 1px solid var(--color-border);
        border-radius: 4px;
        background: white;
    }

    .table-container {
        background: white;
        border: 1px solid var(--color-border);
        border-radius: 4px;
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: var(--color-bg-gray);
    }

    th {
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        color: var(--color-text);
        border-bottom: 1px solid var(--color-border);
    }

    td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--color-border);
        font-size: 14px;
    }

    tbody tr:hover {
        background: var(--color-bg-gray);
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        background: var(--color-bg-gray);
        color: var(--color-text-light);
    }

    .translation-text {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: var(--color-text-light);
        font-size: 13px;
    }

    .actions {
        display: flex;
        gap: 8px;
    }

    .btn-small {
        padding: 6px 12px;
        font-size: 13px;
        text-decoration: none;
        display: inline-block;
        border-radius: 4px;
        border: 1px solid var(--color-border);
        background: white;
        color: var(--color-text);
        cursor: pointer;
    }

    .btn-small:hover {
        background: var(--color-bg-gray);
    }

    .btn-danger {
        color: #dc3545;
        border-color: #dc3545;
    }

    .btn-danger:hover {
        background: #dc3545;
        color: white;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 24px;
    }

    .pagination a {
        padding: 8px 12px;
        border: 1px solid var(--color-border);
        border-radius: 4px;
        text-decoration: none;
        color: var(--color-text);
    }

    .pagination a.active {
        background: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
    }

    .key-name {
        font-family: 'Courier New', monospace;
        font-size: 13px;
        color: var(--color-primary);
    }
</style>

<div class="container" style="padding: 40px 0;">
    <div class="page-header">
        <h1><?= trans('admin.translations.list_title') ?></h1>
        <a href="/admin/translations/create" class="btn"><?= trans('admin.translations.add_new') ?></a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div style="padding: 12px 16px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 4px; margin-bottom: 24px;">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="padding: 12px 16px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 4px; margin-bottom: 24px;">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="GET" action="/admin/translations" class="search-bar">
        <input
            type="text"
            name="search"
            placeholder="<?= trans('admin.translations.search_placeholder') ?>"
            value="<?= htmlspecialchars($search) ?>"
        >
        <select name="group">
            <option value=""><?= trans('admin.translations.all_groups') ?></option>
            <?php foreach ($groups as $g): ?>
                <option value="<?= htmlspecialchars($g['group']) ?>" <?= $group === $g['group'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($g['group']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn"><?= trans('common.search') ?></button>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><?= trans('admin.translations.key') ?></th>
                    <th><?= trans('admin.translations.group') ?></th>
                    <th><?= trans('admin.translations.turkish') ?></th>
                    <th><?= trans('admin.translations.english') ?></th>
                    <th><?= trans('admin.translations.actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($translations)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: var(--color-text-light);">
                            <?= trans('admin.common.no_results') ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($translations as $t): ?>
                        <tr>
                            <td>
                                <div class="key-name"><?= htmlspecialchars($t['key_name']) ?></div>
                                <?php if ($t['description']): ?>
                                    <div style="font-size: 12px; color: var(--color-text-light); margin-top: 4px;">
                                        <?= htmlspecialchars($t['description']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge"><?= htmlspecialchars($t['group']) ?></span>
                            </td>
                            <td>
                                <div class="translation-text"><?= htmlspecialchars($t['tr_value'] ?: '-') ?></div>
                            </td>
                            <td>
                                <div class="translation-text"><?= htmlspecialchars($t['en_value'] ?: '-') ?></div>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="/admin/translations/edit/<?= $t['id'] ?>" class="btn-small">
                                        <?= trans('common.edit') ?>
                                    </a>
                                    <form method="POST" action="/admin/translations/delete/<?= $t['id'] ?>" style="display: inline;"
                                          onsubmit="return confirm('<?= trans('admin.translations.delete_confirm') ?>')">
                                        <button type="submit" class="btn-small btn-danger">
                                            <?= trans('common.delete') ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&group=<?= urlencode($group) ?>"
                   class="<?= $i === $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
