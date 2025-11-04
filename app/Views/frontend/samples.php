<?php
$pageTitle = 'Numune Sipariş - Polyurethane';
$metaDescription = 'Polyurethane ürün numuneleri sipariş edin. Karar vermeden önce ürünlerimizi test edin.';

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
        margin-bottom: 16px;
    }

    .content-section {
        padding: 60px 0;
    }

    .info-box {
        background: white;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        padding: 40px;
        margin-bottom: 20px;
    }

    .info-box h2 {
        font-size: 24px;
        font-weight: 500;
        margin-bottom: 20px;
        color: var(--color-primary);
    }

    .info-box p, .info-box li {
        line-height: 1.8;
        color: var(--color-text-light);
        margin-bottom: 16px;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin: 30px 0;
    }

    .feature-card {
        background: var(--color-bg-gray);
        padding: 30px;
        border-radius: 8px;
        text-align: center;
    }

    .feature-card h3 {
        font-size: 18px;
        margin-top: 10px;
        color: var(--color-primary);
    }

    .cta-box {
        background: var(--color-primary);
        color: white;
        padding: 40px;
        border-radius: 8px;
        text-align: center;
        margin-top: 40px;
    }

    @media (max-width: 768px) {
        .page-hero h1 {
            font-size: 32px;
        }

        .info-box {
            padding: 30px 20px;
        }

        .features-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Hero Section -->
<section class="page-hero">
    <div class="container-narrow">
        <h1>Numune Sipariş</h1>
        <p style="font-size: 18px; color: var(--color-text-light);">
            Satın almadan önce ürünlerimizi test edin
        </p>
    </div>
</section>

<!-- Content Section -->
<section class="content-section">
    <div class="container-narrow">
        <div class="info-box">
            <h2>Ücretsiz Numune Hizmeti</h2>
            <p>
                Ürünlerimizi satın almadan önce kalitesini ve uygunluğunu test etmek için
                ücretsiz numune hizmeti sunuyoruz. Böylece doğru ürünü seçtiğinizden emin olabilirsiniz.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div style="font-size: 48px;">📦</div>
                <h3>Ücretsiz Gönderim</h3>
                <p style="font-size: 14px; color: var(--color-text-light); margin: 0;">
                    Numune gönderimi ücretsizdir
                </p>
            </div>
            <div class="feature-card">
                <div style="font-size: 48px;">⚡</div>
                <h3>Hızlı Teslimat</h3>
                <p style="font-size: 14px; color: var(--color-text-light); margin: 0;">
                    1-3 iş günü içinde elinizde
                </p>
            </div>
            <div class="feature-card">
                <div style="font-size: 48px;">✓</div>
                <h3>Kalite Garantisi</h3>
                <p style="font-size: 14px; color: var(--color-text-light); margin: 0;">
                    Orijinal ürün numuneleri
                </p>
            </div>
        </div>

        <div class="info-box">
            <h2>Numune Sipariş Süreci</h2>
            <ol style="padding-left: 20px; line-height: 2;">
                <li>İletişim formundan veya telefon ile başvuru yapın</li>
                <li>İhtiyacınız olan ürün numunelerini belirtin</li>
                <li>Teslimat adresinizi iletin</li>
                <li>1-3 iş günü içinde numunelerinizi alın</li>
                <li>Test edin ve kararınızı verin</li>
            </ol>
        </div>

        <div class="info-box">
            <h2>Kimler Başvurabilir?</h2>
            <ul style="padding-left: 20px; line-height: 2;">
                <li>Kurumsal firmalar ve şirketler</li>
                <li>İnşaat ve yapı firmaları</li>
                <li>Üretim ve imalat tesisleri</li>
                <li>Toptan alım yapacak bayiler</li>
                <li>Proje bazlı alım yapacak müşteriler</li>
            </ul>
        </div>

        <div class="cta-box">
            <h2 style="font-size: 28px; margin-bottom: 16px; color: white;">Numune Talebinde Bulunun</h2>
            <p style="margin-bottom: 30px; opacity: 0.9;">
                Ürünlerimizi test etmek ve numune sipariş vermek için hemen iletişime geçin.
            </p>
            <a href="/contact" class="btn" style="background: white; color: var(--color-primary);">İletişime Geç</a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
