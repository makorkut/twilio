<?php
$pageTitle = trans('error.501.title') . ' - Polyurethane';
ob_start();
?>

<style>
    .error-page {
        min-height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 60px 0;
        text-align: center;
    }

    .error-content {
        max-width: 600px;
        margin: 0 auto;
    }

    .error-code {
        font-size: 120px;
        font-weight: 200;
        color: var(--color-primary);
        line-height: 1;
        margin-bottom: 20px;
        letter-spacing: -2px;
    }

    .error-title {
        font-size: 32px;
        font-weight: 300;
        margin-bottom: 16px;
        color: var(--color-text);
    }

    .error-message {
        font-size: 16px;
        color: var(--color-text-light);
        margin-bottom: 40px;
        line-height: 1.8;
    }

    .error-actions {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .error-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }
</style>

<div class="container">
    <div class="error-page">
        <div class="error-content">
            <div class="error-icon">🚧</div>
            <div class="error-code">501</div>
            <h1 class="error-title"><?= trans('error.501.heading') ?></h1>
            <p class="error-message">
                <?= trans('error.501.message') ?>
            </p>

            <div class="error-actions">
                <a href="/" class="btn"><?= trans('error.back_home') ?></a>
                <a href="/contact" class="btn btn-secondary"><?= trans('error.contact_us') ?></a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../frontend/layout.php';
