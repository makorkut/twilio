<?php
$isEdit = isset($translation);
$pageTitle = $isEdit ? trans('admin.translations.edit') : trans('admin.translations.add_new');

ob_start();
?>

<style>
    .form-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 40px 0;
    }

    .form-card {
        background: white;
        border: 1px solid var(--color-border);
        border-radius: 4px;
        padding: 32px;
    }

    .form-header {
        margin-bottom: 32px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--color-border);
    }

    .form-header h1 {
        font-size: 24px;
        font-weight: 500;
        margin: 0;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 8px;
        color: var(--color-text);
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 10px 16px;
        border: 1px solid var(--color-border);
        border-radius: 4px;
        font-size: 14px;
        font-family: inherit;
    }

    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }

    .form-group input[readonly] {
        background: var(--color-bg-gray);
        color: var(--color-text-light);
    }

    .form-group small {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        color: var(--color-text-light);
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid var(--color-border);
    }

    .btn-secondary {
        background: white;
        color: var(--color-text);
        border: 1px solid var(--color-border);
    }

    .btn-secondary:hover {
        background: var(--color-bg-gray);
    }

    .translation-section {
        background: var(--color-bg-gray);
        padding: 24px;
        border-radius: 4px;
        margin-bottom: 24px;
    }

    .translation-section h3 {
        font-size: 16px;
        font-weight: 500;
        margin-bottom: 16px;
        color: var(--color-primary);
    }
</style>

<div class="form-container">
    <div class="form-card">
        <div class="form-header">
            <h1><?= $pageTitle ?></h1>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="padding: 12px 16px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 4px; margin-bottom: 24px;">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="key_name"><?= trans('admin.translations.key') ?> *</label>
                <input
                    type="text"
                    id="key_name"
                    name="key_name"
                    value="<?= htmlspecialchars($translation['key_name'] ?? '') ?>"
                    <?= $isEdit ? 'readonly' : 'required' ?>
                >
                <small>Example: frontend.home.title, admin.products.add_new</small>
            </div>

            <div class="form-group">
                <label for="group"><?= trans('admin.translations.group') ?> *</label>
                <?php if (!$isEdit): ?>
                    <input
                        type="text"
                        id="group"
                        name="group"
                        list="group-list"
                        value="<?= htmlspecialchars($translation['group'] ?? '') ?>"
                        required
                    >
                    <datalist id="group-list">
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= htmlspecialchars($g['group']) ?>">
                        <?php endforeach; ?>
                    </datalist>
                    <small>Type to create new group or select existing: common, frontend, admin</small>
                <?php else: ?>
                    <select id="group" name="group" required>
                        <?php foreach ($groups as $g): ?>
                            <option
                                value="<?= htmlspecialchars($g['group']) ?>"
                                <?= ($translation['group'] ?? '') === $g['group'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($g['group']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description"><?= trans('admin.translations.description') ?></label>
                <input
                    type="text"
                    id="description"
                    name="description"
                    value="<?= htmlspecialchars($translation['description'] ?? '') ?>"
                >
                <small>Optional description for this translation key</small>
            </div>

            <div class="translation-section">
                <h3><?= trans('admin.translations.turkish') ?></h3>
                <div class="form-group">
                    <textarea
                        id="tr_value"
                        name="tr_value"
                        placeholder="Türkçe çeviri..."
                    ><?= htmlspecialchars($translation['tr_value'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="translation-section">
                <h3><?= trans('admin.translations.english') ?></h3>
                <div class="form-group">
                    <textarea
                        id="en_value"
                        name="en_value"
                        placeholder="English translation..."
                    ><?= htmlspecialchars($translation['en_value'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn"><?= trans('common.save') ?></button>
                <a href="/admin/translations" class="btn btn-secondary"><?= trans('common.cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
