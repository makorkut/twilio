<?php
$pageTitle = trans('frontend.contact.page_title');
$metaDescription = trans('frontend.contact.meta_description');

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

    .contact-section {
        padding: 80px 0;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        margin-top: 40px;
    }

    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 40px;
    }

    .contact-item {
        display: flex;
        gap: 20px;
    }

    .contact-icon {
        font-size: 32px;
        width: 60px;
        height: 60px;
        background: var(--color-bg-gray);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        flex-shrink: 0;
    }

    .contact-details h3 {
        font-size: 18px;
        font-weight: 500;
        margin-bottom: 8px;
        color: var(--color-primary);
    }

    .contact-details p {
        font-size: 14px;
        color: var(--color-text-light);
        margin: 0;
        line-height: 1.6;
    }

    .contact-form {
        background: var(--color-bg-gray);
        padding: 40px;
        border-radius: 4px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 8px;
        color: var(--color-primary);
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--color-border);
        border-radius: 4px;
        font-size: 14px;
        font-family: inherit;
        background: white;
        transition: border-color 0.2s;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--color-primary);
    }

    .form-group textarea {
        min-height: 150px;
        resize: vertical;
    }

    .form-submit {
        width: 100%;
    }

    .info-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin-top: 60px;
        padding-top: 60px;
        border-top: 1px solid var(--color-border);
    }

    .info-card {
        text-align: center;
        padding: 30px 20px;
        background: var(--color-bg-gray);
        border-radius: 4px;
    }

    .info-card-icon {
        font-size: 36px;
        margin-bottom: 16px;
    }

    .info-card h3 {
        font-size: 16px;
        font-weight: 500;
        margin-bottom: 8px;
        color: var(--color-primary);
    }

    .info-card p {
        font-size: 14px;
        color: var(--color-text-light);
        margin: 0;
    }

    @media (max-width: 768px) {
        .page-hero h1 {
            font-size: 32px;
        }

        .contact-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }

        .info-cards {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .contact-form {
            padding: 24px;
        }
    }
</style>

<!-- Hero Section -->
<section class="page-hero">
    <div class="container-narrow">
        <h1><?= trans('frontend.contact.heading') ?></h1>
        <p><?= trans('frontend.contact.hero_text') ?></p>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container-narrow">
        <div class="contact-grid">
            <!-- Contact Information -->
            <div class="contact-info">
                <div class="contact-item">
                    <div class="contact-icon">📍</div>
                    <div class="contact-details">
                        <h3><?= trans('frontend.contact.address') ?></h3>
                        <p><?= trans('frontend.contact.address_text') ?></p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">📞</div>
                    <div class="contact-details">
                        <h3><?= trans('frontend.contact.phone') ?></h3>
                        <p>+90 (216) 555 0 555<br>+90 (532) 555 0 555 (WhatsApp)</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">✉️</div>
                    <div class="contact-details">
                        <h3><?= trans('frontend.contact.email') ?></h3>
                        <p>info@polyurethane.com<br>sales@polyurethane.com</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">🕐</div>
                    <div class="contact-details">
                        <h3><?= trans('frontend.contact.working_hours') ?></h3>
                        <p><?= trans('frontend.contact.working_hours_text') ?></p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <h2 style="font-size: 24px; font-weight: 500; margin-bottom: 24px; color: var(--color-primary);"><?= trans('frontend.contact.form_title') ?></h2>

                <form action="/contact/submit" method="POST">
                    <div class="form-group">
                        <label for="name"><?= trans('frontend.contact.full_name') ?></label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email"><?= trans('frontend.contact.email_field') ?></label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone"><?= trans('frontend.contact.phone_field') ?></label>
                        <input type="tel" id="phone" name="phone">
                    </div>

                    <div class="form-group">
                        <label for="subject"><?= trans('frontend.contact.subject') ?></label>
                        <input type="text" id="subject" name="subject" required>
                    </div>

                    <div class="form-group">
                        <label for="message"><?= trans('frontend.contact.message') ?></label>
                        <textarea id="message" name="message" required></textarea>
                    </div>

                    <button type="submit" class="btn form-submit"><?= trans('common.send') ?></button>
                </form>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="info-cards">
            <div class="info-card">
                <div class="info-card-icon">💬</div>
                <h3><?= trans('frontend.contact.live_support') ?></h3>
                <p><?= trans('frontend.contact.live_support_text') ?></p>
            </div>

            <div class="info-card">
                <div class="info-card-icon">📧</div>
                <h3><?= trans('frontend.contact.email_support') ?></h3>
                <p><?= trans('frontend.contact.email_support_text') ?></p>
            </div>

            <div class="info-card">
                <div class="info-card-icon">🎯</div>
                <h3><?= trans('frontend.contact.technical_consulting') ?></h3>
                <p><?= trans('frontend.contact.technical_consulting_text') ?></p>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
