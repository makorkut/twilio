<?php
$pageTitle = 'Bayi Başvurusu - Polyurethane';
$metaDescription = 'Polyurethane bayisi olun. Bayi başvurusu ve B2B iş birliği fırsatları.';

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

    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin: 30px 0;
    }

    .benefit-card {
        background: var(--color-bg-gray);
        padding: 30px;
        border-radius: 8px;
    }

    .benefit-card h3 {
        font-size: 18px;
        margin-bottom: 10px;
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

        .benefits-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Hero Section -->
<section class="page-hero">
    <div class="container-narrow">
        <h1>Bayi Başvurusu</h1>
        <p style="font-size: 18px; color: var(--color-text-light);">
            Polyurethane bayisi olun, kazancınızı artırın
        </p>
    </div>
</section>

<!-- Content Section -->
<section class="content-section">
    <div class="container-narrow">
        <div class="info-box">
            <h2>Bayilik Avantajları</h2>
            <p>
                Polyurethane ailesine katılarak sektördeki yerinizi sağlamlaştırın.
                Kaliteli ürünler, rekabetçi fiyatlar ve güçlü marka desteği ile işinizi büyütün.
            </p>
        </div>

        <div class="benefits-grid">
            <div class="benefit-card">
                <h3>💰 Özel Bayi Fiyatları</h3>
                <p style="font-size: 14px; color: var(--color-text-light); margin: 0;">
                    Rekabetçi bayi fiyatlandırması ve özel kampanyalar
                </p>
            </div>
            <div class="benefit-card">
                <h3>📦 Esnek Ödeme Koşulları</h3>
                <p style="font-size: 14px; color: var(--color-text-light); margin: 0;">
                    Vade seçenekleri ve açık hesap imkanı
                </p>
            </div>
            <div class="benefit-card">
                <h3>🎯 Pazarlama Desteği</h3>
                <p style="font-size: 14px; color: var(--color-text-light); margin: 0;">
                    Reklam materyalleri ve pazarlama desteği
                </p>
            </div>
            <div class="benefit-card">
                <h3>🚚 Hızlı Teslimat</h3>
                <p style="font-size: 14px; color: var(--color-text-light); margin: 0;">
                    Öncelikli ve hızlı kargo ile teslimat
                </p>
            </div>
            <div class="benefit-card">
                <h3>📚 Teknik Destek</h3>
                <p style="font-size: 14px; color: var(--color-text-light); margin: 0;">
                    Ürün eğitimleri ve teknik danışmanlık
                </p>
            </div>
            <div class="benefit-card">
                <h3>🌍 Bölge Koruması</h3>
                <p style="font-size: 14px; color: var(--color-text-light); margin: 0;">
                    Seçilen bölgelerde münhasırlık hakkı
                </p>
            </div>
        </div>

        <div class="info-box">
            <h2>Başvuru Koşulları</h2>
            <ul style="padding-left: 20px; line-height: 2;">
                <li>Ticari faaliyet belgesi bulunması</li>
                <li>Uygun depo ve satış alanına sahip olunması</li>
                <li>Sektör deneyimi (tercih sebebidir)</li>
                <li>Minimum sipariş tutarını karşılayabilme</li>
            </ul>
        </div>

        <div class="info-box">
            <h2>Başvuru Süreci</h2>
            <ol style="padding-left: 20px; line-height: 2;">
                <li>Başvuru formunu doldurun veya iletişime geçin</li>
                <li>Ticari belgelerinizi paylaşın</li>
                <li>Bayilik görüşmesi gerçekleştirin</li>
                <li>Sözleşme imzalayın</li>
                <li>Eğitim alın ve satışa başlayın</li>
            </ol>
        </div>

        <div class="cta-box">
            <h2 style="font-size: 28px; margin-bottom: 16px; color: white;">Hemen Başvurun</h2>
            <p style="margin-bottom: 30px; opacity: 0.9;">
                Bayilik başvurunuz için detaylı bilgi almak ve başvuru yapmak için iletişime geçin.
            </p>
            <a href="/contact" class="btn" style="background: white; color: var(--color-primary);">İletişime Geç</a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
