<?php
$pageTitle = 'Kargo & Teslimat - Polyurethane';
$metaDescription = 'Kargo ve teslimat bilgileri, ücretsiz kargo koşulları.';

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

    .info-box p {
        line-height: 1.8;
        color: var(--color-text-light);
        margin-bottom: 16px;
    }

    .highlight-box {
        background: var(--color-bg-gray);
        padding: 30px;
        border-radius: 8px;
        margin: 30px 0;
        text-align: center;
    }

    @media (max-width: 768px) {
        .page-hero h1 {
            font-size: 32px;
        }

        .info-box {
            padding: 30px 20px;
        }
    }
</style>

<!-- Hero Section -->
<section class="page-hero">
    <div class="container-narrow">
        <h1>Kargo & Teslimat</h1>
        <p style="font-size: 18px; color: var(--color-text-light);">
            Hızlı ve güvenli teslimat
        </p>
    </div>
</section>

<!-- Content Section -->
<section class="content-section">
    <div class="container-narrow">
        <div class="highlight-box">
            <h2 style="font-size: 32px; margin-bottom: 16px; color: var(--color-primary);">🚚 Ücretsiz Kargo</h2>
            <p style="font-size: 18px; color: var(--color-text);">
                500₺ ve üzeri siparişlerde ücretsiz kargo
            </p>
        </div>

        <div class="info-box">
            <h2>Teslimat Süreleri</h2>
            <ul style="line-height: 2; color: var(--color-text-light); padding-left: 20px;">
                <li>Aynı gün kargo: Saat 14:00'a kadar verilen siparişler</li>
                <li>Türkiye geneli: 1-3 iş günü</li>
                <li>Yurtdışı teslimat: 5-10 iş günü</li>
            </ul>
        </div>

        <div class="info-box">
            <h2>Kargo Firmaları</h2>
            <p>
                Siparişleriniz güvenilir kargo firmaları ile gönderilmektedir.
                Kargo takip numaranız sipariş tamamlandıktan sonra tarafınıza iletilecektir.
            </p>
        </div>

        <div class="info-box">
            <h2>İletişim</h2>
            <p>
                Teslimat ve kargo hakkında detaylı bilgi almak için <a href="/contact" style="color: var(--color-primary); text-decoration: none;">iletişime</a> geçebilirsiniz.
            </p>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
