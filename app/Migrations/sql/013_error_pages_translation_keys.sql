-- ============================================================================
-- Migration: Error Pages Translation Keys
-- Date: 2024-11-04
-- Description: Add translation keys for 404, 501, and 503 error pages
-- ============================================================================

-- Error page translations (404, 501, 503)
-- English translations (lang_id = 1)
INSERT INTO translation_keys (namespace, key_name, lang_id, translation) VALUES
('error', '404.title', 1, 'Page Not Found'),
('error', '404.heading', 1, 'Page Not Found'),
('error', '404.message', 1, 'The page you are looking for doesn''t exist or has been moved. Please check the URL or use the navigation above.'),
('error', '501.title', 1, 'Not Implemented'),
('error', '501.heading', 1, 'Feature Not Implemented'),
('error', '501.message', 1, 'This feature is currently under development. We''re working hard to bring it to you soon!'),
('error', '503.title', 1, 'Service Unavailable'),
('error', '503.heading', 1, 'Service Temporarily Unavailable'),
('error', '503.message', 1, 'We''re performing scheduled maintenance to improve your experience. We''ll be back shortly!'),
('error', '503.maintenance_info', 1, 'Our team is working on updates to serve you better. This usually takes just a few minutes.'),
('error', '503.retry_later', 1, 'Please try refreshing the page in a few moments.'),
('error', 'back_home', 1, 'Back to Home'),
('error', 'browse_products', 1, 'Browse Products'),
('error', 'helpful_links', 1, 'Helpful Links'),
('error', 'contact_us', 1, 'Contact Us'),
('error', 'retry_page', 1, 'Retry Page');

-- Turkish translations (lang_id = 2)
INSERT INTO translation_keys (namespace, key_name, lang_id, translation) VALUES
('error', '404.title', 2, 'Sayfa Bulunamadı'),
('error', '404.heading', 2, 'Sayfa Bulunamadı'),
('error', '404.message', 2, 'Aradığınız sayfa mevcut değil veya taşınmış. Lütfen URL''yi kontrol edin veya yukarıdaki menüyü kullanın.'),
('error', '501.title', 2, 'Henüz Uygulanmadı'),
('error', '501.heading', 2, 'Özellik Henüz Uygulanmadı'),
('error', '501.message', 2, 'Bu özellik şu anda geliştirme aşamasında. Yakında sizlere sunmak için çalışıyoruz!'),
('error', '503.title', 2, 'Hizmet Kullanılamıyor'),
('error', '503.heading', 2, 'Hizmet Geçici Olarak Kullanılamıyor'),
('error', '503.message', 2, 'Deneyiminizi iyileştirmek için planlanmış bakım yapıyoruz. Kısa süre içinde geri döneceğiz!'),
('error', '503.maintenance_info', 2, 'Ekibimiz size daha iyi hizmet vermek için güncellemeler yapıyor. Bu genellikle sadece birkaç dakika sürer.'),
('error', '503.retry_later', 2, 'Lütfen birkaç dakika içinde sayfayı yenilemeyi deneyin.'),
('error', 'back_home', 2, 'Ana Sayfaya Dön'),
('error', 'browse_products', 2, 'Ürünlere Göz At'),
('error', 'helpful_links', 2, 'Yararlı Bağlantılar'),
('error', 'contact_us', 2, 'Bize Ulaşın'),
('error', 'retry_page', 2, 'Sayfayı Yenile');

-- ============================================================================
-- Verification Query
-- ============================================================================
-- Run this to verify the insertions:
-- SELECT namespace, key_name, lang_id, translation
-- FROM translation_keys
-- WHERE namespace = 'error'
-- ORDER BY key_name, lang_id;
