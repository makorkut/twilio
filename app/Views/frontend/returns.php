<?php
$pageTitle = 'İade & Değişim - Polyurethane';
$metaDescription = 'İade ve değişim koşulları, iade süreci hakkında bilgi.';

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
        <h1>İade & Değişim</h1>
        <p style="font-size: 18px; color: var(--color-text-light);">
            Müşteri memnuniyeti odaklı iade politikamız
        </p>
    </div>
</section>

<!-- Content Section -->
<section class="content-section">
    <div class="container-narrow">
        <div class="highlight-box">
            <h2 style="font-size: 32px; margin-bottom: 16px; color: var(--color-primary);">14 Gün İade Hakkı</h2>
            <p style="font-size: 18px; color: var(--color-text);">
                Ürünü teslim aldığınız tarihten itibaren 14 gün içinde iade edebilirsiniz
            </p>
        </div>

        <div class="info-box">
            <h2>İade Koşulları</h2>
            <ul style="padding-left: 20px;">
                <li>Ürün kullanılmamış ve orijinal ambalajında olmalıdır</li>
                <li>Fatura ve iade formu eksiksiz olmalıdır</li>
                <li>Özel üretim ürünler iade kapsamı dışındadır</li>
                <li>Hijyen gerektiren ürünler iade edilemez</li>
            </ul>
        </div>

        <div class="info-box">
            <h2>İade Süreci</h2>
            <ol style="padding-left: 20px; line-height: 2; color: var(--color-text-light);">
                <li>İletişim formundan iade talebinizi iletin</li>
                <li>İade onayı aldıktan sonra ürünü kargoya verin</li>
                <li>Ürün tarafımıza ulaştıktan sonra kontrol edilir</li>
                <li>İade tutarı 5-10 iş günü içinde hesabınıza iade edilir</li>
            </ol>
        </div>

        <div class="info-box">
            <h2>Değişim</h2>
            <p>
                Ürün değişimi yapmak isterseniz, lütfen <a href="/contact" style="color: var(--color-primary); text-decoration: none;">iletişime</a> geçiniz.
                Stok durumuna göre değişim işleminiz gerçekleştirilecektir.
            </p>
        </div>

        <div class="info-box">
            <h2>İletişim</h2>
            <p>
                İade ve değişim işlemleri hakkında detaylı bilgi almak için <a href="/contact" style="color: var(--color-primary); text-decoration: none;">iletişim</a> sayfamızı ziyaret edebilirsiniz.
            </p>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
