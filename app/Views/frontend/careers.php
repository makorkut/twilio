<?php
$pageTitle = 'Kariyer Fırsatları - Polyurethane';
$metaDescription = 'Polyurethane ailesine katılın. Açık pozisyonlarımız ve kariyer fırsatları.';

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

    .content-box {
        background: white;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        padding: 40px;
        margin-bottom: 30px;
    }

    .content-box h2 {
        font-size: 24px;
        font-weight: 500;
        margin-bottom: 20px;
        color: var(--color-primary);
    }

    .content-box p {
        line-height: 1.8;
        color: var(--color-text-light);
        margin-bottom: 16px;
    }

    .contact-cta {
        text-align: center;
        padding: 40px;
        background: var(--color-bg-gray);
        border-radius: 8px;
        margin-top: 40px;
    }

    @media (max-width: 768px) {
        .page-hero h1 {
            font-size: 32px;
        }

        .content-box {
            padding: 30px 20px;
        }
    }
</style>

<!-- Hero Section -->
<section class="page-hero">
    <div class="container-narrow">
        <h1>Kariyer Fırsatları</h1>
        <p style="font-size: 18px; color: var(--color-text-light);">
            Polyurethane ailesine katılın
        </p>
    </div>
</section>

<!-- Content Section -->
<section class="content-section">
    <div class="container-narrow">
        <div class="content-box">
            <h2>Ekibimize Katılın</h2>
            <p>
                Polyurethane olarak, sektörde lider konumumuzu korumak ve büyümeye devam etmek için
                yetenekli ve tutkulu çalışanlar arıyoruz.
            </p>
            <p>
                Dinamik ve yenilikçi çalışma ortamımızda kariyerinizi geliştirmek için birçok fırsat sunuyoruz.
            </p>
        </div>

        <div class="content-box">
            <h2>Neden Polyurethane?</h2>
            <ul style="line-height: 2; color: var(--color-text-light); padding-left: 20px;">
                <li>Rekabetçi maaş ve yan haklar</li>
                <li>Kariyer gelişim fırsatları</li>
                <li>Modern çalışma ortamı</li>
                <li>Eğitim ve gelişim programları</li>
                <li>Esnek çalışma saatleri</li>
            </ul>
        </div>

        <div class="contact-cta">
            <h2 style="font-size: 24px; margin-bottom: 16px;">Başvuru Yapın</h2>
            <p style="color: var(--color-text-light); margin-bottom: 20px;">
                Açık pozisyonlarımız hakkında bilgi almak ve başvuru yapmak için iletişime geçin.
            </p>
            <a href="/contact" class="btn">İletişime Geç</a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
