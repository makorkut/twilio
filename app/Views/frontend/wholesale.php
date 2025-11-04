<?php
$pageTitle = 'Toptan Satış - Polyurethane';
$metaDescription = 'Toptan polyurethane ürün fiyatları. Kurumsal ve toptan alım için özel fiyatlar.';

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

    .price-tiers {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin: 30px 0;
    }

    .tier-card {
        background: var(--color-bg-gray);
        padding: 30px;
        border-radius: 8px;
        text-align: center;
        border: 2px solid var(--color-border);
    }

    .tier-card.featured {
        border-color: var(--color-primary);
        background: linear-gradient(135deg, #f8f9ff 0%, white 100%);
    }

    .tier-card h3 {
        font-size: 20px;
        margin-bottom: 10px;
        color: var(--color-primary);
    }

    .tier-card .discount {
        font-size: 32px;
        font-weight: bold;
        color: var(--color-primary);
        margin: 20px 0;
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

        .price-tiers {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Hero Section -->
<section class="page-hero">
    <div class="container-narrow">
        <h1>Toptan Satış</h1>
        <p style="font-size: 18px; color: var(--color-text-light);">
            Kurumsal ve toptan alımlarda özel fiyatlar
        </p>
    </div>
</section>

<!-- Content Section -->
<section class="content-section">
    <div class="container-narrow">
        <div class="info-box">
            <h2>Toptan Alımda Avantajlar</h2>
            <p>
                Büyük miktarlı alımlarınızda özel indirimler ve avantajlardan yararlanın.
                Kurumsal müşterilerimize özel fiyatlandırma ve ödeme koşulları sunuyoruz.
            </p>
        </div>

        <div class="price-tiers">
            <div class="tier-card">
                <h3>Standart</h3>
                <div class="discount">%10</div>
                <p style="font-size: 14px; margin: 0;">
                    5.000₺ - 20.000₺<br>
                    sipariş tutarı
                </p>
            </div>
            <div class="tier-card featured">
                <h3>Tercih Edilen</h3>
                <div class="discount">%15</div>
                <p style="font-size: 14px; margin: 0;">
                    20.000₺ - 50.000₺<br>
                    sipariş tutarı
                </p>
            </div>
            <div class="tier-card">
                <h3>Premium</h3>
                <div class="discount">%20+</div>
                <p style="font-size: 14px; margin: 0;">
                    50.000₺ ve üzeri<br>
                    sipariş tutarı
                </p>
            </div>
        </div>

        <div class="info-box">
            <h2>Kimler Yararlanabilir?</h2>
            <ul style="padding-left: 20px; line-height: 2;">
                <li>İnşaat firmaları ve müteahhitler</li>
                <li>Üretim tesisleri ve fabrikalar</li>
                <li>Toptan dağıtıcılar ve bayiler</li>
                <li>Mobilya ve dekorasyon firmaları</li>
                <li>Otomotiv ve endüstriyel kuruluşlar</li>
                <li>Kamu kurumları ve belediyeler</li>
            </ul>
        </div>

        <div class="info-box">
            <h2>Ek Avantajlar</h2>
            <ul style="padding-left: 20px; line-height: 2;">
                <li>✓ Ücretsiz kargo (minimum tutar şartı olmaksızın)</li>
                <li>✓ Esnek ödeme seçenekleri ve vadeli ödeme</li>
                <li>✓ Açık hesap imkanı (onaylı firmalar için)</li>
                <li>✓ Özel üretim ve kişiselleştirme seçenekleri</li>
                <li>✓ Öncelikli üretim ve teslimat</li>
                <li>✓ Teknik destek ve danışmanlık hizmeti</li>
            </ul>
        </div>

        <div class="info-box">
            <h2>Toptan Fiyat Talebi</h2>
            <p>
                Toptan fiyat bilgisi almak için lütfen aşağıdaki bilgileri belirterek iletişime geçin:
            </p>
            <ul style="padding-left: 20px; line-height: 2;">
                <li>Firma bilgileri ve vergi numarası</li>
                <li>Talep edilen ürünler ve miktarları</li>
                <li>Teslimat adresi</li>
                <li>Tercih edilen ödeme şekli</li>
            </ul>
        </div>

        <div class="cta-box">
            <h2 style="font-size: 28px; margin-bottom: 16px; color: white;">Özel Fiyat Teklifi Alın</h2>
            <p style="margin-bottom: 30px; opacity: 0.9;">
                Toptan alımlarınız için özel fiyat teklifi almak ve detaylı bilgi için iletişime geçin.
            </p>
            <a href="/contact" class="btn" style="background: white; color: var(--color-primary);">İletişime Geç</a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
