<?php
$pageTitle = 'Hakkımızda - Polyurethane';
$metaDescription = 'Polyurethane olarak yüksek kaliteli poliüretan ürünleri ile mekanlarınıza değer katıyoruz.';

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
        <h1>Hakkımızda</h1>
        <p>Polyurethane olarak, yüksek kaliteli poliüretan ürünleri ile mekanlarınıza estetik ve değer katıyoruz. Yılların deneyimi ve uzman ekibimizle müşterilerimize en iyi hizmeti sunuyoruz.</p>
    </div>
</section>

<!-- Main Content -->
<section class="content-section">
    <div class="container-narrow">
        <h2>Misyonumuz</h2>
        <p>Poliüretan sektöründe lider bir marka olarak, müşterilerimize en kaliteli ürünleri en uygun fiyatlarla sunmayı hedefliyoruz. Sürekli gelişen teknolojimiz ve geniş ürün yelpazemiz ile her türlü ihtiyaca çözüm üretiyoruz.</p>

        <h2 style="margin-top: 60px;">Vizyonumuz</h2>
        <p>Türkiye'nin önde gelen poliüretan tedarikçisi olarak, uluslararası standartlarda üretim yapan, müşteri memnuniyetini ön planda tutan ve sürdürülebilir büyümeyi hedefleyen bir firma olmayı amaçlıyoruz.</p>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🏆</div>
                <h3>Kalite Güvencesi</h3>
                <p>Tüm ürünlerimiz kalite kontrolünden geçer ve garantilidir.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🚚</div>
                <h3>Hızlı Teslimat</h3>
                <p>Geniş stok kapasitemiz ile hızlı ve güvenilir teslimat.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">💡</div>
                <h3>Profesyonel Destek</h3>
                <p>Uzman ekibimiz her zaman yanınızda.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📦</div>
                <h3>Geniş Ürün Yelpazesi</h3>
                <p>Duvar kaplamalarından profillere kadar çok çeşitli ürün seçenekleri.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🏢</div>
                <h3>B2B Çözümler</h3>
                <p>Toptan ve bayi satış için özel fiyatlar ve koşullar.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🔧</div>
                <h3>Teknik Destek</h3>
                <p>Montaj ve uygulama konusunda profesyonel danışmanlık.</p>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
