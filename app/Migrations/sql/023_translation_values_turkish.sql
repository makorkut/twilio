-- ============================================================================
-- Migration 023: Turkish Translation Values
-- Date: 2025-11-05
-- Description: Add all Turkish (TR) translation values
-- Language ID: 2 (Turkish)
-- ============================================================================

SET NAMES utf8mb4;

-- ==========================
-- COMMON TRANSLATIONS (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    -- Common actions
    WHEN 'common.save' THEN 'Kaydet'
    WHEN 'common.cancel' THEN 'İptal'
    WHEN 'common.delete' THEN 'Sil'
    WHEN 'common.edit' THEN 'Düzenle'
    WHEN 'common.view' THEN 'Görüntüle'
    WHEN 'common.search' THEN 'Ara'
    WHEN 'common.send' THEN 'Gönder'
    WHEN 'common.filter' THEN 'Filtrele'
    WHEN 'common.close' THEN 'Kapat'
    WHEN 'common.yes' THEN 'Evet'
    WHEN 'common.no' THEN 'Hayır'
    WHEN 'common.loading' THEN 'Yükleniyor...'
    WHEN 'common.error' THEN 'Hata'
    WHEN 'common.success' THEN 'Başarılı'
    WHEN 'common.warning' THEN 'Uyarı'
    WHEN 'common.info' THEN 'Bilgi'
    WHEN 'common.update' THEN 'Güncelle'
    WHEN 'common.publish' THEN 'Yayınla'
    -- Common navigation
    WHEN 'common.products' THEN 'Ürünler'
    WHEN 'common.categories' THEN 'Kategoriler'
    WHEN 'common.projects' THEN 'Projeler'
    WHEN 'common.about' THEN 'Hakkımızda'
    WHEN 'common.contact' THEN 'İletişim'
    WHEN 'common.careers' THEN 'Kariyer'
    WHEN 'common.faq' THEN 'SSS'
    -- Language names
    WHEN 'common.lang_tr' THEN 'Türkçe'
    WHEN 'common.lang_en' THEN 'English'
    WHEN 'common.lang_de' THEN 'Deutsch'
    WHEN 'common.lang_fr' THEN 'Français'
END
FROM i18n_keys
WHERE key_name IN (
    'common.save', 'common.cancel', 'common.delete', 'common.edit', 'common.view',
    'common.search', 'common.send', 'common.filter', 'common.close', 'common.yes',
    'common.no', 'common.loading', 'common.error', 'common.success', 'common.warning',
    'common.info', 'common.update', 'common.publish', 'common.products', 'common.categories',
    'common.projects', 'common.about', 'common.contact', 'common.careers', 'common.faq',
    'common.lang_tr', 'common.lang_en', 'common.lang_de', 'common.lang_fr'
);

-- ==========================
-- FRONTEND - HOME PAGE (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'frontend.home.page_title' THEN 'Ana Sayfa - Polyurethane'
    WHEN 'frontend.home.hero_title' THEN 'Premium Poliüretan Ürünler'
    WHEN 'frontend.home.hero_subtitle' THEN 'Mekanlarınıza estetik ve kalite katın. Geniş ürün yelpazemizle ihtiyacınıza en uygun çözümü bulun.'
    WHEN 'frontend.home.explore_products' THEN 'Ürünleri Keşfet'
    WHEN 'frontend.home.categories_title' THEN 'Kategoriler'
    WHEN 'frontend.home.product_count_suffix' THEN 'Ürün'
    WHEN 'frontend.home.featured_products' THEN 'Öne Çıkan Ürünler'
    WHEN 'frontend.home.no_featured_products' THEN 'Henüz öne çıkan ürün bulunmamaktadır.'
    WHEN 'frontend.home.b2b_cta_title' THEN 'Toptan Alım mı Yapıyorsunuz?'
    WHEN 'frontend.home.b2b_cta_subtitle' THEN 'Bayilerimize özel fiyatlandırma ve avantajlar için hemen başvurun.'
    WHEN 'frontend.home.dealer_application' THEN 'Bayi Başvurusu'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.home.%';

-- ==========================
-- FRONTEND - PRODUCTS PAGE (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'frontend.products.page_title' THEN 'Tüm Ürünler'
    WHEN 'frontend.products.heading' THEN 'Ürünlerimiz'
    WHEN 'frontend.products.categories' THEN 'Kategoriler'
    WHEN 'frontend.products.all' THEN 'Tümü'
    WHEN 'frontend.products.showing_count' THEN 'ürün gösteriliyor'
    WHEN 'frontend.products.sort_newest' THEN 'En Yeni'
    WHEN 'frontend.products.sort_price_low_high' THEN 'Fiyat: Düşükten Yükseğe'
    WHEN 'frontend.products.sort_price_high_low' THEN 'Fiyat: Yüksekten Düşüğe'
    WHEN 'frontend.products.sort_name_az' THEN 'İsim: A-Z'
    WHEN 'frontend.products.no_products_found' THEN 'Ürün bulunamadı'
    WHEN 'frontend.products.no_matching_products' THEN 'Seçtiğiniz filtrelere uygun ürün bulunamadı.'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.products.%';

-- ==========================
-- FRONTEND - PRODUCT DETAIL (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'frontend.product.new_badge' THEN 'Yeni'
    WHEN 'frontend.product.add_to_cart' THEN 'Sepete Ekle'
    WHEN 'frontend.product.out_of_stock' THEN 'Stokta Yok'
    WHEN 'frontend.product.stock' THEN 'Stok'
    WHEN 'frontend.product.pieces' THEN 'Adet'
    WHEN 'frontend.product.qr_title' THEN 'QR Kod ile Paylaş'
    WHEN 'frontend.product.qr_description' THEN 'Bu ürünü mobil cihazınızla paylaşmak için QR kodu tarayın'
    WHEN 'frontend.product.technical_specs' THEN 'Teknik Özellikler'
    WHEN 'frontend.product.length' THEN 'Uzunluk'
    WHEN 'frontend.product.width' THEN 'Genişlik'
    WHEN 'frontend.product.height' THEN 'Yükseklik'
    WHEN 'frontend.product.weight' THEN 'Ağırlık'
    WHEN 'frontend.product.package_quantity' THEN 'Paket İçeriği'
    WHEN 'frontend.product.description' THEN 'Ürün Açıklaması'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.product.%';

-- ==========================
-- FRONTEND - CART (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'frontend.cart.title' THEN 'Sepetim'
    WHEN 'frontend.cart.empty' THEN 'Sepetiniz boş'
    WHEN 'frontend.cart.start_shopping' THEN 'Alışverişe başlamak için ürünlerimize göz atın'
    WHEN 'frontend.cart.browse_products' THEN 'Ürünlere Göz At'
    WHEN 'frontend.cart.remove' THEN 'Kaldır'
    WHEN 'frontend.cart.order_summary' THEN 'Sipariş Özeti'
    WHEN 'frontend.cart.subtotal' THEN 'Ara Toplam'
    WHEN 'frontend.cart.discount' THEN 'İndirim'
    WHEN 'frontend.cart.tax' THEN 'KDV'
    WHEN 'frontend.cart.shipping' THEN 'Kargo'
    WHEN 'frontend.cart.free' THEN 'Ücretsiz'
    WHEN 'frontend.cart.total' THEN 'Toplam'
    WHEN 'frontend.cart.proceed_to_checkout' THEN 'Ödemeye Geç'
    WHEN 'frontend.cart.continue_shopping' THEN 'Alışverişe Devam Et'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.cart.%';

-- ==========================
-- FRONTEND - ABOUT PAGE (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'frontend.about.page_title' THEN 'Hakkımızda'
    WHEN 'frontend.about.meta_description' THEN 'Poliüretan ürünleri konusunda uzman firmamız hakkında bilgi edinin'
    WHEN 'frontend.about.heading' THEN 'Hakkımızda'
    WHEN 'frontend.about.hero_text' THEN 'Poliüretan sektöründe lider konumumuzla, kaliteli ürünler ve müşteri memnuniyeti odaklı hizmet anlayışımızla yanınızdayız.'
    WHEN 'frontend.about.our_mission' THEN 'Misyonumuz'
    WHEN 'frontend.about.mission_text' THEN 'En kaliteli poliüretan ürünleri uygun fiyatlarla müşterilerimize sunmak ve sektörde öncü olmak.'
    WHEN 'frontend.about.our_vision' THEN 'Vizyonumuz'
    WHEN 'frontend.about.vision_text' THEN 'Poliüretan sektöründe yenilikçi çözümler sunarak, ulusal ve uluslararası pazarda tercih edilen marka olmak.'
    WHEN 'frontend.about.quality_assurance' THEN 'Kalite Güvencesi'
    WHEN 'frontend.about.quality_assurance_text' THEN 'Tüm ürünlerimiz uluslararası kalite standartlarına uygun olarak üretilmektedir.'
    WHEN 'frontend.about.fast_delivery' THEN 'Hızlı Teslimat'
    WHEN 'frontend.about.fast_delivery_text' THEN 'Geniş stok ağımız sayesinde siparişlerinizi hızlı bir şekilde teslim ediyoruz.'
    WHEN 'frontend.about.professional_support' THEN 'Profesyonel Destek'
    WHEN 'frontend.about.professional_support_text' THEN 'Uzman ekibimiz size en iyi hizmeti sunmak için her zaman hazır.'
    WHEN 'frontend.about.wide_product_range' THEN 'Geniş Ürün Yelpazesi'
    WHEN 'frontend.about.wide_product_range_text' THEN 'Her türlü ihtiyaca uygun binlerce çeşit ürün seçeneğimiz bulunmaktadır.'
    WHEN 'frontend.about.b2b_solutions' THEN 'B2B Çözümleri'
    WHEN 'frontend.about.b2b_solutions_text' THEN 'Toptan alımlarınız için özel fiyatlandırma ve ödeme seçenekleri sunuyoruz.'
    WHEN 'frontend.about.technical_support' THEN 'Teknik Destek'
    WHEN 'frontend.about.technical_support_text' THEN 'Ürün seçiminde ve uygulamalarında teknik destek sağlıyoruz.'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.about.%';

-- ==========================
-- FRONTEND - CONTACT PAGE (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'frontend.contact.page_title' THEN 'İletişim'
    WHEN 'frontend.contact.meta_description' THEN 'Bizimle iletişime geçin, sorularınızı yanıtlayalım'
    WHEN 'frontend.contact.heading' THEN 'İletişim'
    WHEN 'frontend.contact.hero_text' THEN 'Sorularınız için bize ulaşın, size yardımcı olmaktan mutluluk duyarız.'
    WHEN 'frontend.contact.address' THEN 'Adres'
    WHEN 'frontend.contact.address_text' THEN 'Örnek Mahallesi, Polyurethane Caddesi No:123, İstanbul, Türkiye'
    WHEN 'frontend.contact.phone' THEN 'Telefon'
    WHEN 'frontend.contact.email' THEN 'E-posta'
    WHEN 'frontend.contact.working_hours' THEN 'Çalışma Saatleri'
    WHEN 'frontend.contact.working_hours_text' THEN 'Pazartesi - Cuma: 09:00 - 18:00'
    WHEN 'frontend.contact.form_title' THEN 'Bize Mesaj Gönderin'
    WHEN 'frontend.contact.full_name' THEN 'Ad Soyad'
    WHEN 'frontend.contact.email_field' THEN 'E-posta Adresi'
    WHEN 'frontend.contact.phone_field' THEN 'Telefon'
    WHEN 'frontend.contact.subject' THEN 'Konu'
    WHEN 'frontend.contact.message' THEN 'Mesajınız'
    WHEN 'frontend.contact.live_support' THEN 'Canlı Destek'
    WHEN 'frontend.contact.live_support_text' THEN '7/24 canlı destek hattımızdan bize ulaşabilirsiniz.'
    WHEN 'frontend.contact.email_support' THEN 'E-posta Desteği'
    WHEN 'frontend.contact.email_support_text' THEN 'Sorularınızı e-posta ile gönderin, en kısa sürede yanıtlayalım.'
    WHEN 'frontend.contact.technical_consulting' THEN 'Teknik Danışmanlık'
    WHEN 'frontend.contact.technical_consulting_text' THEN 'Ürün seçimi ve uygulama konusunda uzman desteği alın.'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.contact.%';

-- ==========================
-- FRONTEND - SEARCH (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'frontend.search.heading' THEN 'Arama Sonuçları'
    WHEN 'frontend.search.placeholder' THEN 'Ürün ara...'
    WHEN 'frontend.search.search_button' THEN 'Ara'
    WHEN 'frontend.search.results_found' THEN 'sonuç bulundu'
    WHEN 'frontend.search.unnamed_product' THEN 'İsimsiz Ürün'
    WHEN 'frontend.search.no_results' THEN 'Sonuç bulunamadı'
    WHEN 'frontend.search.no_results_text' THEN 'Aradığınız terim için sonuç bulunamadı. Lütfen farklı anahtar kelimeler deneyin.'
    WHEN 'frontend.search.product_search' THEN 'Ürün Ara'
    WHEN 'frontend.search.search_prompt' THEN 'Ürün aramak için yukarıdaki arama kutusunu kullanın'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.search.%';

-- ==========================
-- FRONTEND - LAYOUT (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'frontend.layout.free_shipping_banner' THEN '1000 TL ve Üzeri Alışverişlerde ÜCRETSİZ KARGO!'
    WHEN 'frontend.layout.footer_description' THEN 'Poliüretan sektöründe öncü firmamız, kaliteli ürünler ve müşteri memnuniyeti odaklı hizmet anlayışıyla yanınızdadır.'
    WHEN 'frontend.layout.corporate' THEN 'Kurumsal'
    WHEN 'frontend.layout.customer_service' THEN 'Müşteri Hizmetleri'
    WHEN 'frontend.layout.shipping_delivery' THEN 'Kargo & Teslimat'
    WHEN 'frontend.layout.returns_exchanges' THEN 'İade & Değişim'
    WHEN 'frontend.layout.sample_order' THEN 'Numune Siparişi'
    WHEN 'frontend.layout.dealer_application' THEN 'Bayi Başvurusu'
    WHEN 'frontend.layout.wholesale_prices' THEN 'Toptan Fiyat Listesi'
    WHEN 'frontend.layout.pdf_catalog' THEN 'PDF Katalog'
    WHEN 'frontend.layout.api_integration' THEN 'API Entegrasyonu'
    WHEN 'frontend.layout.copyright' THEN '© 2025 Polyurethane. Tüm hakları saklıdır.'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.layout.%';

-- ==========================
-- FRONTEND - COMPONENTS (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'frontend.components.color_selector_title' THEN 'Renk Seçimi'
    WHEN 'frontend.components.out_of_stock' THEN 'Stokta Yok'
    WHEN 'frontend.components.selected_color' THEN 'Seçili Renk'
    WHEN 'frontend.components.calculator_title' THEN 'Alan Hesaplama'
    WHEN 'frontend.components.calculator_subtitle' THEN 'İhtiyacınız olan ürün miktarını hesaplayın'
    WHEN 'frontend.components.width_meters' THEN 'Genişlik (metre)'
    WHEN 'frontend.components.length_meters' THEN 'Uzunluk (metre)'
    WHEN 'frontend.components.waste_rate' THEN 'Fire Oranı (%)'
    WHEN 'frontend.components.waste_hint' THEN 'Genellikle %10 fire hesaplanır'
    WHEN 'frontend.components.calculate_button' THEN 'Hesapla'
    WHEN 'frontend.components.area' THEN 'Alan'
    WHEN 'frontend.components.area_with_waste' THEN 'Fire Dahil Alan'
    WHEN 'frontend.components.required_quantity' THEN 'Gerekli Miktar'
    WHEN 'frontend.components.add_to_cart' THEN 'Sepete Ekle'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.components.%';

-- ==========================
-- ADMIN - DASHBOARD (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'admin.dashboard.page_title' THEN 'Yönetim Paneli'
    WHEN 'admin.dashboard.welcome' THEN 'Hoş Geldiniz'
    WHEN 'admin.dashboard.total_orders' THEN 'Toplam Sipariş'
    WHEN 'admin.dashboard.total_revenue' THEN 'Toplam Gelir'
    WHEN 'admin.dashboard.total_products' THEN 'Toplam Ürün'
    WHEN 'admin.dashboard.total_customers' THEN 'Toplam Müşteri'
    WHEN 'admin.dashboard.pending_orders' THEN 'Bekleyen Siparişler'
    WHEN 'admin.dashboard.recent_orders' THEN 'Son Siparişler'
    WHEN 'admin.dashboard.view_all' THEN 'Tümünü Gör'
    WHEN 'admin.dashboard.order_number' THEN 'Sipariş No'
    WHEN 'admin.dashboard.customer' THEN 'Müşteri'
    WHEN 'admin.dashboard.total' THEN 'Toplam'
    WHEN 'admin.dashboard.status' THEN 'Durum'
    WHEN 'admin.dashboard.date' THEN 'Tarih'
    WHEN 'admin.dashboard.low_stock_products' THEN 'Düşük Stoklu Ürünler'
    WHEN 'admin.dashboard.stock_level' THEN 'Stok Seviyesi'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.dashboard.%';

-- ==========================
-- ADMIN - PRODUCTS (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'admin.products.page_title' THEN 'Ürün Yönetimi'
    WHEN 'admin.products.list_title' THEN 'Ürünler'
    WHEN 'admin.products.add_new' THEN 'Yeni Ürün Ekle'
    WHEN 'admin.products.edit' THEN 'Ürünü Düzenle'
    WHEN 'admin.products.delete' THEN 'Ürünü Sil'
    WHEN 'admin.products.name' THEN 'Ürün Adı'
    WHEN 'admin.products.sku' THEN 'SKU'
    WHEN 'admin.products.category' THEN 'Kategori'
    WHEN 'admin.products.price' THEN 'Fiyat'
    WHEN 'admin.products.stock' THEN 'Stok'
    WHEN 'admin.products.status' THEN 'Durum'
    WHEN 'admin.products.actions' THEN 'İşlemler'
    WHEN 'admin.products.active' THEN 'Aktif'
    WHEN 'admin.products.inactive' THEN 'Pasif'
    WHEN 'admin.products.basic_info' THEN 'Temel Bilgiler'
    WHEN 'admin.products.pricing' THEN 'Fiyatlandırma'
    WHEN 'admin.products.inventory' THEN 'Envanter'
    WHEN 'admin.products.images' THEN 'Görseller'
    WHEN 'admin.products.seo' THEN 'SEO'
    WHEN 'admin.products.specifications' THEN 'Teknik Özellikler'
    WHEN 'admin.products.delete_confirm' THEN 'Bu ürünü silmek istediğinizden emin misiniz?'
    WHEN 'admin.products.save_success' THEN 'Ürün başarıyla kaydedildi'
    WHEN 'admin.products.delete_success' THEN 'Ürün başarıyla silindi'
    WHEN 'admin.products.featured' THEN 'Öne Çıkan'
    WHEN 'admin.products.b2b_only' THEN 'Sadece B2B'
    WHEN 'admin.products.allow_samples' THEN 'Numune İzni'
    WHEN 'admin.products.calculator_type' THEN 'Hesap Makinesi Tipi'
    WHEN 'admin.products.coverage_per_unit' THEN 'Birim Kaplama'
    WHEN 'admin.products.main_image' THEN 'Ana Görsel'
    WHEN 'admin.products.gallery_images' THEN 'Galeri Görselleri'
    WHEN 'admin.products.meta_title' THEN 'Meta Başlık'
    WHEN 'admin.products.meta_description' THEN 'Meta Açıklama'
    WHEN 'admin.products.meta_keywords' THEN 'Meta Anahtar Kelimeler'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.products.%';

-- ==========================
-- ADMIN - CATEGORIES (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'admin.categories.page_title' THEN 'Kategori Yönetimi'
    WHEN 'admin.categories.list_title' THEN 'Kategoriler'
    WHEN 'admin.categories.add_new' THEN 'Yeni Kategori Ekle'
    WHEN 'admin.categories.edit' THEN 'Kategoriyi Düzenle'
    WHEN 'admin.categories.delete' THEN 'Kategoriyi Sil'
    WHEN 'admin.categories.name' THEN 'Kategori Adı'
    WHEN 'admin.categories.slug' THEN 'Slug'
    WHEN 'admin.categories.parent' THEN 'Üst Kategori'
    WHEN 'admin.categories.products_count' THEN 'Ürün Sayısı'
    WHEN 'admin.categories.actions' THEN 'İşlemler'
    WHEN 'admin.categories.no_parent' THEN 'Üst Kategori Yok'
    WHEN 'admin.categories.delete_confirm' THEN 'Bu kategoriyi silmek istediğinizden emin misiniz?'
    WHEN 'admin.categories.save_success' THEN 'Kategori başarıyla kaydedildi'
    WHEN 'admin.categories.delete_success' THEN 'Kategori başarıyla silindi'
    WHEN 'admin.categories.description' THEN 'Açıklama'
    WHEN 'admin.categories.image' THEN 'Kategori Görseli'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.categories.%';

-- ==========================
-- ADMIN - ORDERS (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'admin.orders.page_title' THEN 'Sipariş Yönetimi'
    WHEN 'admin.orders.list_title' THEN 'Siparişler'
    WHEN 'admin.orders.view' THEN 'Siparişi Görüntüle'
    WHEN 'admin.orders.order_number' THEN 'Sipariş No'
    WHEN 'admin.orders.customer' THEN 'Müşteri'
    WHEN 'admin.orders.date' THEN 'Tarih'
    WHEN 'admin.orders.total' THEN 'Toplam'
    WHEN 'admin.orders.status' THEN 'Durum'
    WHEN 'admin.orders.payment_status' THEN 'Ödeme Durumu'
    WHEN 'admin.orders.actions' THEN 'İşlemler'
    WHEN 'admin.orders.details' THEN 'Sipariş Detayları'
    WHEN 'admin.orders.items' THEN 'Sipariş Kalemleri'
    WHEN 'admin.orders.billing_address' THEN 'Fatura Adresi'
    WHEN 'admin.orders.shipping_address' THEN 'Teslimat Adresi'
    WHEN 'admin.orders.product' THEN 'Ürün'
    WHEN 'admin.orders.quantity' THEN 'Miktar'
    WHEN 'admin.orders.price' THEN 'Fiyat'
    WHEN 'admin.orders.subtotal' THEN 'Ara Toplam'
    WHEN 'admin.orders.discount' THEN 'İndirim'
    WHEN 'admin.orders.tax' THEN 'KDV'
    WHEN 'admin.orders.shipping' THEN 'Kargo'
    WHEN 'admin.orders.grand_total' THEN 'Genel Toplam'
    WHEN 'admin.orders.update_status' THEN 'Durumu Güncelle'
    WHEN 'admin.orders.status_pending' THEN 'Beklemede'
    WHEN 'admin.orders.status_processing' THEN 'İşleniyor'
    WHEN 'admin.orders.status_shipped' THEN 'Kargoya Verildi'
    WHEN 'admin.orders.status_delivered' THEN 'Teslim Edildi'
    WHEN 'admin.orders.status_cancelled' THEN 'İptal Edildi'
    WHEN 'admin.orders.payment_pending' THEN 'Ödeme Bekleniyor'
    WHEN 'admin.orders.payment_paid' THEN 'Ödendi'
    WHEN 'admin.orders.payment_failed' THEN 'Ödeme Başarısız'
    WHEN 'admin.orders.payment_refunded' THEN 'İade Edildi'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.orders.%';

-- ==========================
-- ADMIN - CUSTOMERS (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'admin.customers.page_title' THEN 'Müşteri Yönetimi'
    WHEN 'admin.customers.list_title' THEN 'Müşteriler'
    WHEN 'admin.customers.add_new' THEN 'Yeni Müşteri Ekle'
    WHEN 'admin.customers.edit' THEN 'Müşteriyi Düzenle'
    WHEN 'admin.customers.view' THEN 'Müşteriyi Görüntüle'
    WHEN 'admin.customers.name' THEN 'Müşteri Adı'
    WHEN 'admin.customers.email' THEN 'E-posta'
    WHEN 'admin.customers.phone' THEN 'Telefon'
    WHEN 'admin.customers.group' THEN 'Müşteri Grubu'
    WHEN 'admin.customers.orders_count' THEN 'Sipariş Sayısı'
    WHEN 'admin.customers.total_spent' THEN 'Toplam Harcama'
    WHEN 'admin.customers.status' THEN 'Durum'
    WHEN 'admin.customers.actions' THEN 'İşlemler'
    WHEN 'admin.customers.details' THEN 'Müşteri Detayları'
    WHEN 'admin.customers.order_history' THEN 'Sipariş Geçmişi'
    WHEN 'admin.customers.addresses' THEN 'Adresler'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.customers.%';

-- ==========================
-- ADMIN - SETTINGS (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'admin.settings.page_title' THEN 'Ayarlar'
    WHEN 'admin.settings.general' THEN 'Genel Ayarlar'
    WHEN 'admin.settings.site_name' THEN 'Site Adı'
    WHEN 'admin.settings.site_description' THEN 'Site Açıklaması'
    WHEN 'admin.settings.currency' THEN 'Para Birimi'
    WHEN 'admin.settings.tax_rate' THEN 'KDV Oranı'
    WHEN 'admin.settings.shipping' THEN 'Kargo Ayarları'
    WHEN 'admin.settings.payment' THEN 'Ödeme Ayarları'
    WHEN 'admin.settings.email' THEN 'E-posta Ayarları'
    WHEN 'admin.settings.save' THEN 'Ayarları Kaydet'
    WHEN 'admin.settings.save_success' THEN 'Ayarlar başarıyla kaydedildi'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.settings.%';

-- ==========================
-- ADMIN - TRANSLATIONS (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'admin.translations.page_title' THEN 'Çeviri Yönetimi'
    WHEN 'admin.translations.list_title' THEN 'Çeviriler'
    WHEN 'admin.translations.key' THEN 'Çeviri Anahtarı'
    WHEN 'admin.translations.group' THEN 'Grup'
    WHEN 'admin.translations.description' THEN 'Açıklama'
    WHEN 'admin.translations.turkish' THEN 'Türkçe'
    WHEN 'admin.translations.english' THEN 'İngilizce'
    WHEN 'admin.translations.actions' THEN 'İşlemler'
    WHEN 'admin.translations.edit' THEN 'Çeviriyi Düzenle'
    WHEN 'admin.translations.add_new' THEN 'Yeni Çeviri Ekle'
    WHEN 'admin.translations.save_success' THEN 'Çeviri başarıyla kaydedildi'
    WHEN 'admin.translations.delete_success' THEN 'Çeviri başarıyla silindi'
    WHEN 'admin.translations.delete_confirm' THEN 'Bu çeviriyi silmek istediğinizden emin misiniz?'
    WHEN 'admin.translations.filter_by_group' THEN 'Gruba Göre Filtrele'
    WHEN 'admin.translations.all_groups' THEN 'Tüm Gruplar'
    WHEN 'admin.translations.search_placeholder' THEN 'Çevirilerde ara...'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.translations.%';

-- ==========================
-- ADMIN - USERS (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'admin.users.page_title' THEN 'Kullanıcı Yönetimi'
    WHEN 'admin.users.list_title' THEN 'Kullanıcılar'
    WHEN 'admin.users.add_new' THEN 'Yeni Kullanıcı Ekle'
    WHEN 'admin.users.edit' THEN 'Kullanıcıyı Düzenle'
    WHEN 'admin.users.name' THEN 'Ad Soyad'
    WHEN 'admin.users.email' THEN 'E-posta'
    WHEN 'admin.users.role' THEN 'Rol'
    WHEN 'admin.users.status' THEN 'Durum'
    WHEN 'admin.users.actions' THEN 'İşlemler'
    WHEN 'admin.users.delete_confirm' THEN 'Bu kullanıcıyı silmek istediğinizden emin misiniz?'
    WHEN 'admin.users.save_success' THEN 'Kullanıcı başarıyla kaydedildi'
    WHEN 'admin.users.delete_success' THEN 'Kullanıcı başarıyla silindi'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.users.%';

-- ==========================
-- ADMIN - COMMON (TR)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 2, CASE key_name
    WHEN 'admin.common.dashboard' THEN 'Panel'
    WHEN 'admin.common.products' THEN 'Ürünler'
    WHEN 'admin.common.categories' THEN 'Kategoriler'
    WHEN 'admin.common.orders' THEN 'Siparişler'
    WHEN 'admin.common.customers' THEN 'Müşteriler'
    WHEN 'admin.common.users' THEN 'Kullanıcılar'
    WHEN 'admin.common.settings' THEN 'Ayarlar'
    WHEN 'admin.common.translations' THEN 'Çeviriler'
    WHEN 'admin.common.logout' THEN 'Çıkış'
    WHEN 'admin.common.welcome' THEN 'Hoş Geldiniz'
    WHEN 'admin.common.search' THEN 'Ara'
    WHEN 'admin.common.filter' THEN 'Filtrele'
    WHEN 'admin.common.export' THEN 'Dışa Aktar'
    WHEN 'admin.common.import' THEN 'İçe Aktar'
    WHEN 'admin.common.bulk_actions' THEN 'Toplu İşlemler'
    WHEN 'admin.common.select_all' THEN 'Tümünü Seç'
    WHEN 'admin.common.no_results' THEN 'Sonuç bulunamadı'
    WHEN 'admin.common.loading' THEN 'Yükleniyor...'
    WHEN 'admin.common.confirm_action' THEN 'İşlemi onaylıyor musunuz?'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.common.%';
