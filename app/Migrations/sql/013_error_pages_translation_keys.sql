-- ============================================================================
-- Migration: Error Pages Translation Keys
-- Date: 2024-11-04
-- Description: Add translation keys for 404, 501, and 503 error pages
-- ============================================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- PART 1: Create translation keys in i18n_keys table
-- ------------------------------------------------------------
INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('error.404.title', 'error', '404 page title'),
('error.404.heading', 'error', '404 page heading'),
('error.404.message', 'error', '404 page message'),
('error.501.title', 'error', '501 page title'),
('error.501.heading', 'error', '501 page heading'),
('error.501.message', 'error', '501 page message'),
('error.503.title', 'error', '503 page title'),
('error.503.heading', 'error', '503 page heading'),
('error.503.message', 'error', '503 page message'),
('error.503.maintenance_info', 'error', '503 maintenance info'),
('error.503.retry_later', 'error', '503 retry message'),
('error.back_home', 'error', 'Back to home link'),
('error.browse_products', 'error', 'Browse products link'),
('error.helpful_links', 'error', 'Helpful links heading'),
('error.contact_us', 'error', 'Contact us link'),
('error.retry_page', 'error', 'Retry page button')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- ------------------------------------------------------------
-- PART 2: Add English translations (lang_id = 1)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT
    k.id,
    1 as lang_id,
    CASE k.key_name
        WHEN 'error.404.title' THEN 'Page Not Found'
        WHEN 'error.404.heading' THEN 'Page Not Found'
        WHEN 'error.404.message' THEN 'The page you are looking for doesn''t exist or has been moved. Please check the URL or use the navigation above.'
        WHEN 'error.501.title' THEN 'Not Implemented'
        WHEN 'error.501.heading' THEN 'Feature Not Implemented'
        WHEN 'error.501.message' THEN 'This feature is currently under development. We''re working hard to bring it to you soon!'
        WHEN 'error.503.title' THEN 'Service Unavailable'
        WHEN 'error.503.heading' THEN 'Service Temporarily Unavailable'
        WHEN 'error.503.message' THEN 'We''re performing scheduled maintenance to improve your experience. We''ll be back shortly!'
        WHEN 'error.503.maintenance_info' THEN 'Our team is working on updates to serve you better. This usually takes just a few minutes.'
        WHEN 'error.503.retry_later' THEN 'Please try refreshing the page in a few moments.'
        WHEN 'error.back_home' THEN 'Back to Home'
        WHEN 'error.browse_products' THEN 'Browse Products'
        WHEN 'error.helpful_links' THEN 'Helpful Links'
        WHEN 'error.contact_us' THEN 'Contact Us'
        WHEN 'error.retry_page' THEN 'Retry Page'
        ELSE k.key_name
    END as value
FROM i18n_keys k
WHERE k.`group` = 'error'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ------------------------------------------------------------
-- PART 3: Add Turkish translations (lang_id = 2)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT
    k.id,
    2 as lang_id,
    CASE k.key_name
        WHEN 'error.404.title' THEN 'Sayfa Bulunamadı'
        WHEN 'error.404.heading' THEN 'Sayfa Bulunamadı'
        WHEN 'error.404.message' THEN 'Aradığınız sayfa mevcut değil veya taşınmış. Lütfen URL''yi kontrol edin veya yukarıdaki menüyü kullanın.'
        WHEN 'error.501.title' THEN 'Henüz Uygulanmadı'
        WHEN 'error.501.heading' THEN 'Özellik Henüz Uygulanmadı'
        WHEN 'error.501.message' THEN 'Bu özellik şu anda geliştirme aşamasında. Yakında sizlere sunmak için çalışıyoruz!'
        WHEN 'error.503.title' THEN 'Hizmet Kullanılamıyor'
        WHEN 'error.503.heading' THEN 'Hizmet Geçici Olarak Kullanılamıyor'
        WHEN 'error.503.message' THEN 'Deneyiminizi iyileştirmek için planlanmış bakım yapıyoruz. Kısa süre içinde geri döneceğiz!'
        WHEN 'error.503.maintenance_info' THEN 'Ekibimiz size daha iyi hizmet vermek için güncellemeler yapıyor. Bu genellikle sadece birkaç dakika sürer.'
        WHEN 'error.503.retry_later' THEN 'Lütfen birkaç dakika içinde sayfayı yenilemeyi deneyin.'
        WHEN 'error.back_home' THEN 'Ana Sayfaya Dön'
        WHEN 'error.browse_products' THEN 'Ürünlere Göz At'
        WHEN 'error.helpful_links' THEN 'Yararlı Bağlantılar'
        WHEN 'error.contact_us' THEN 'Bize Ulaşın'
        WHEN 'error.retry_page' THEN 'Sayfayı Yenile'
        ELSE k.key_name
    END as value
FROM i18n_keys k
WHERE k.`group` = 'error'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ============================================================================
-- Verification Query
-- ============================================================================
-- Run this to verify the insertions:
-- SELECT k.key_name, k.group, v.lang_id, v.value
-- FROM i18n_keys k
-- LEFT JOIN i18n_values v ON v.key_id = k.id
-- WHERE k.group = 'error'
-- ORDER BY k.key_name, v.lang_id;
