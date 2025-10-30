<?php
$pageTitle = 'Projeler - Polyurethane';
$metaDescription = 'Tamamladığımız poliüretan projeleri görün. Otel, restoran, konut ve ofis projelerimiz.';

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

    .projects-section {
        padding: 80px 0;
    }

    .projects-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 40px;
        margin-top: 40px;
    }

    .project-card {
        background: white;
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .project-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .project-image {
        width: 100%;
        height: 240px;
        background: var(--color-bg-gray);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 64px;
    }

    .project-content {
        padding: 24px;
    }

    .project-category {
        font-size: 12px;
        color: var(--color-accent);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .project-title {
        font-size: 20px;
        font-weight: 500;
        margin-bottom: 12px;
        color: var(--color-primary);
    }

    .project-description {
        font-size: 14px;
        color: var(--color-text-light);
        line-height: 1.6;
        margin-bottom: 16px;
    }

    .project-meta {
        display: flex;
        gap: 16px;
        font-size: 13px;
        color: var(--color-text-light);
        padding-top: 16px;
        border-top: 1px solid var(--color-border);
    }

    .project-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .filter-section {
        display: flex;
        justify-content: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 60px;
    }

    .filter-btn {
        padding: 10px 24px;
        background: white;
        border: 1px solid var(--color-border);
        border-radius: 4px;
        font-size: 14px;
        color: var(--color-text);
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
    }

    @media (max-width: 1024px) {
        .projects-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .page-hero h1 {
            font-size: 32px;
        }

        .projects-grid {
            grid-template-columns: 1fr;
            gap: 24px;
        }
    }
</style>

<!-- Hero Section -->
<section class="page-hero">
    <div class="container-narrow">
        <h1>Projelerimiz</h1>
        <p>Türkiye'nin dört bir yanında gerçekleştirdiğimiz, kalite ve estetiği bir araya getiren poliüretan uygulama projelerimizi keşfedin.</p>
    </div>
</section>

<!-- Projects Section -->
<section class="projects-section">
    <div class="container">
        <!-- Filters -->
        <div class="filter-section">
            <button class="filter-btn active">Tümü</button>
            <button class="filter-btn">Otel</button>
            <button class="filter-btn">Restoran</button>
            <button class="filter-btn">Konut</button>
            <button class="filter-btn">Ofis</button>
            <button class="filter-btn">Mağaza</button>
        </div>

        <!-- Projects Grid -->
        <div class="projects-grid">
            <div class="project-card">
                <div class="project-image">🏨</div>
                <div class="project-content">
                    <div class="project-category">Otel</div>
                    <h3 class="project-title">Hilton İstanbul Projesi</h3>
                    <p class="project-description">5 yıldızlı otel için özel tasarım duvar panelleri ve dekoratif profil uygulaması. 15.000 m² alan kaplama.</p>
                    <div class="project-meta">
                        <div class="project-meta-item">📍 İstanbul</div>
                        <div class="project-meta-item">📅 2024</div>
                    </div>
                </div>
            </div>

            <div class="project-card">
                <div class="project-image">🍽️</div>
                <div class="project-content">
                    <div class="project-category">Restoran</div>
                    <h3 class="project-title">Nusret Etiler Şubesi</h3>
                    <p class="project-description">Modern tasarım konseptiyle uyumlu duvar süslemeleri ve tavan bordürleri. Premium kalite ürünler kullanıldı.</p>
                    <div class="project-meta">
                        <div class="project-meta-item">📍 İstanbul</div>
                        <div class="project-meta-item">📅 2024</div>
                    </div>
                </div>
            </div>

            <div class="project-card">
                <div class="project-image">🏢</div>
                <div class="project-content">
                    <div class="project-category">Ofis</div>
                    <h3 class="project-title">Microsoft Türkiye Ofisi</h3>
                    <p class="project-description">Şık ve modern ofis tasarımı için dekoratif profiller ve duvar kaplamaları. 8.000 m² uygulama.</p>
                    <div class="project-meta">
                        <div class="project-meta-item">📍 İstanbul</div>
                        <div class="project-meta-item">📅 2024</div>
                    </div>
                </div>
            </div>

            <div class="project-card">
                <div class="project-image">🏠</div>
                <div class="project-content">
                    <div class="project-category">Konut</div>
                    <h3 class="project-title">Nef 22 Residence</h3>
                    <p class="project-description">Lüks konut projesinde örnek daire süslemeleri. Klasik ve modern tarzda uygulamalar.</p>
                    <div class="project-meta">
                        <div class="project-meta-item">📍 İstanbul</div>
                        <div class="project-meta-item">📅 2023</div>
                    </div>
                </div>
            </div>

            <div class="project-card">
                <div class="project-image">🛍️</div>
                <div class="project-content">
                    <div class="project-category">Mağaza</div>
                    <h3 class="project-title">Louis Vuitton Nişantaşı</h3>
                    <p class="project-description">Lüks mağaza konsepti için özel üretim dekoratif ürünler. Altın varak kaplama detaylar.</p>
                    <div class="project-meta">
                        <div class="project-meta-item">📍 İstanbul</div>
                        <div class="project-meta-item">📅 2023</div>
                    </div>
                </div>
            </div>

            <div class="project-card">
                <div class="project-image">🏛️</div>
                <div class="project-content">
                    <div class="project-category">Konut</div>
                    <h3 class="project-title">Vadi İstanbul Evleri</h3>
                    <p class="project-description">500 konutluk projede tüm dairelere poliüretan süsleme uygulaması. Toplu proje deneyimi.</p>
                    <div class="project-meta">
                        <div class="project-meta-item">📍 İstanbul</div>
                        <div class="project-meta-item">📅 2023</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section style="background: var(--color-primary); color: white; padding: 80px 0; text-align: center;">
    <div class="container">
        <h2 style="font-size: 42px; font-weight: 300; margin-bottom: 24px;">Projeniz için Teklif Alın</h2>
        <p style="font-size: 18px; margin-bottom: 40px; opacity: 0.9;">Uzman ekibimiz projeniz için en uygun çözümleri sunmaya hazır</p>
        <a href="/contact" class="btn" style="background: white; color: var(--color-primary);">İletişime Geçin</a>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
