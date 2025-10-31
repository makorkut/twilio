<?php
$pageTitle = 'Sık Sorulan Sorular - Polyurethane';
$metaDescription = 'Sık sorulan sorular ve cevapları. Polyurethane ürünleri hakkında bilgi.';

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

    .faq-item {
        background: white;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 20px;
    }

    .faq-item h3 {
        font-size: 20px;
        font-weight: 500;
        margin-bottom: 16px;
        color: var(--color-primary);
    }

    .faq-item p {
        line-height: 1.8;
        color: var(--color-text-light);
    }

    .contact-box {
        background: var(--color-bg-gray);
        padding: 40px;
        border-radius: 8px;
        margin-top: 40px;
        text-align: center;
    }

    @media (max-width: 768px) {
        .page-hero h1 {
            font-size: 32px;
        }

        .faq-item {
            padding: 20px;
        }
    }
</style>

<!-- Hero Section -->
<section class="page-hero">
    <div class="container-narrow">
        <h1>Sık Sorulan Sorular</h1>
        <p style="font-size: 18px; color: var(--color-text-light);">
            Merak ettiğiniz sorular ve cevapları
        </p>
    </div>
</section>

<!-- Content Section -->
<section class="content-section">
    <div class="container-narrow">
        <div class="faq-item">
            <h3>❓ Sipariş nasıl veririm?</h3>
            <p>
                Ürün sayfalarından beğendiğiniz ürünleri sepete ekleyerek sipariş verebilirsiniz.
                Kurumsal müşterilerimiz için özel fiyat ve sipariş imkanları mevcuttur.
            </p>
        </div>

        <div class="faq-item">
            <h3>📦 Kargo ücreti ne kadardır?</h3>
            <p>
                500₺ ve üzeri siparişlerde kargo ücretsizdir.
                500₺'nin altındaki siparişlerde kargo ücreti sepet toplamına eklenir.
            </p>
        </div>

        <div class="faq-item">
            <h3>⏱️ Teslimat ne kadar sürer?</h3>
            <p>
                Türkiye genelinde 1-3 iş günü içinde teslimat gerçekleştirilmektedir.
                Saat 14:00'a kadar verilen siparişler aynı gün kargoya verilir.
            </p>
        </div>

        <div class="faq-item">
            <h3>🔄 İade ve değişim yapabilir miyim?</h3>
            <p>
                Evet, ürünü teslim aldıktan sonra 14 gün içinde iade hakkınız bulunmaktadır.
                Detaylı bilgi için <a href="/returns" style="color: var(--color-primary); text-decoration: none;">iade & değişim</a> sayfamızı ziyaret edebilirsiniz.
            </p>
        </div>

        <div class="faq-item">
            <h3>💳 Hangi ödeme yöntemlerini kabul ediyorsunuz?</h3>
            <p>
                Kredi kartı, banka havalesi ve kapıda ödeme seçeneklerini sunuyoruz.
                Kurumsal müşterilerimiz için açık hesap imkanı mevcuttur.
            </p>
        </div>

        <div class="faq-item">
            <h3>🏢 Toptan satış yapıyor musunuz?</h3>
            <p>
                Evet, toptan satış ve bayi başvuruları için <a href="/b2b" style="color: var(--color-primary); text-decoration: none;">B2B</a> sayfamızı ziyaret edebilirsiniz.
            </p>
        </div>

        <div class="faq-item">
            <h3>📋 Numune sipariş edebilir miyim?</h3>
            <p>
                Evet, ürünlerimizin numunelerini sipariş edebilirsiniz.
                Detaylı bilgi için <a href="/samples" style="color: var(--color-primary); text-decoration: none;">numune sipariş</a> sayfamızı ziyaret edebilirsiniz.
            </p>
        </div>

        <div class="contact-box">
            <h2 style="font-size: 24px; margin-bottom: 16px;">Sorunuz mu var?</h2>
            <p style="color: var(--color-text-light); margin-bottom: 20px;">
                Bulamadığınız bir soru varsa bizimle iletişime geçin.
            </p>
            <a href="/contact" class="btn">İletişime Geç</a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
