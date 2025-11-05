<?php
$pageTitle = trans('frontend.about.page_title');
$metaDescription = trans('frontend.about.meta_description');

ob_start();
?>

<style>
    .page-hero {
        background: linear-gradient(135deg, var(--color-bg-gray) 0%, white 100%);
        padding: 80px 0;
        text-align: center;
    }

    .page-hero h1 {
        font-size: 48px;
        font-weight: 300;
        letter-spacing: -1px;
        margin-bottom: 24px;
        color: var(--color-primary);
    }

    .page-hero p {
        font-size: 18px;
        color: var(--color-text-light);
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.8;
    }

    .content-section {
        padding: 80px 0;
    }

    .content-section h2 {
        font-size: 36px;
        font-weight: 300;
        margin-bottom: 24px;
        color: var(--color-primary);
    }

    .content-section p {
        font-size: 16px;
        color: var(--color-text);
        line-height: 1.8;
        margin-bottom: 20px;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 40px;
        margin-top: 60px;
    }

    .feature-card {
        text-align: center;
        padding: 40px 20px;
        background: var(--color-bg-gray);
        border-radius: 4px;
    }

    .feature-icon {
        font-size: 48px;
        margin-bottom: 20px;
    }

    .feature-card h3 {
        font-size: 20px;
        font-weight: 500;
        margin-bottom: 12px;
        color: var(--color-primary);
    }

    .feature-card p {
        font-size: 14px;
        color: var(--color-text-light);
        margin: 0;
    }

    @media (max-width: 768px) {
        .page-hero h1 {
            font-size: 32px;
        }

        .features-grid {
            grid-template-columns: 1fr;
            gap: 24px;
        }
    }
</style>

<!-- Hero Section -->
<section class="page-hero">
    <div class="container-narrow">
        <h1><?= trans('frontend.about.heading') ?></h1>
        <p><?= trans('frontend.about.hero_text') ?></p>
    </div>
</section>

<!-- Main Content -->
<section class="content-section">
    <div class="container-narrow">
        <h2><?= trans('frontend.about.our_mission') ?></h2>
        <p><?= trans('frontend.about.mission_text') ?></p>

        <h2 style="margin-top: 60px;"><?= trans('frontend.about.our_vision') ?></h2>
        <p><?= trans('frontend.about.vision_text') ?></p>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🏆</div>
                <h3><?= trans('frontend.about.quality_assurance') ?></h3>
                <p><?= trans('frontend.about.quality_assurance_text') ?></p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🚚</div>
                <h3><?= trans('frontend.about.fast_delivery') ?></h3>
                <p><?= trans('frontend.about.fast_delivery_text') ?></p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">💡</div>
                <h3><?= trans('frontend.about.professional_support') ?></h3>
                <p><?= trans('frontend.about.professional_support_text') ?></p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📦</div>
                <h3><?= trans('frontend.about.wide_product_range') ?></h3>
                <p><?= trans('frontend.about.wide_product_range_text') ?></p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🏢</div>
                <h3><?= trans('frontend.about.b2b_solutions') ?></h3>
                <p><?= trans('frontend.about.b2b_solutions_text') ?></p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🔧</div>
                <h3><?= trans('frontend.about.technical_support') ?></h3>
                <p><?= trans('frontend.about.technical_support_text') ?></p>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
