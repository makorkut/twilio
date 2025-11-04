<?php
$pageTitle = trans('error.404.title') . ' - Polyurethane';
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

    .error-suggestions {
        margin-top: 60px;
        padding-top: 40px;
        border-top: 1px solid var(--color-border);
    }

    .suggestions-title {
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 20px;
        color: var(--color-text-light);
    }

    .suggestion-links {
        display: flex;
        gap: 24px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .suggestion-links a {
        color: var(--color-text);
        text-decoration: none;
        font-size: 14px;
        transition: color 0.2s;
    }

    .suggestion-links a:hover {
        color: var(--color-primary);
        text-decoration: underline;
    }
</style>

<div class="container">
    <div class="error-page">
        <div class="error-content">
            <div class="error-code">404</div>
            <h1 class="error-title"><?= trans('error.404.heading') ?></h1>
            <p class="error-message">
                <?= trans('error.404.message') ?>
            </p>

            <div class="error-actions">
                <a href="/" class="btn"><?= trans('error.back_home') ?></a>
                <a href="/products" class="btn btn-secondary"><?= trans('error.browse_products') ?></a>
            </div>

            <div class="error-suggestions">
                <div class="suggestions-title"><?= trans('error.helpful_links') ?></div>
                <div class="suggestion-links">
                    <a href="/products"><?= trans('common.products') ?></a>
                    <a href="/categories"><?= trans('common.categories') ?></a>
                    <a href="/about"><?= trans('common.about') ?></a>
                    <a href="/contact"><?= trans('common.contact') ?></a>
                    <a href="/faq"><?= trans('common.faq') ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../frontend/layout.php';
