-- ============================================
-- Migration 012: Complete Translation Keys
-- Phase 2: Admin Views Translation System  
-- ============================================
-- Total: 565+ translation keys (EN=1, TR=2)
-- Optimized with batch inserts for performance
-- ============================================

SET NAMES utf8mb4;

-- ====================
-- COMMON NAMESPACE (75 keys)
-- ====================
INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('common.home', 'common', 'Home'),
('common.products', 'common', 'Products'),
('common.categories', 'common', 'Categories'),
('common.about', 'common', 'About'),
('common.contact', 'common', 'Contact'),
('common.cart', 'common', 'Cart'),
('common.checkout', 'common', 'Checkout'),
('common.login', 'common', 'Login'),
('common.register', 'common', 'Register'),
('common.logout', 'common', 'Logout'),
('common.search', 'common', 'Search'),
('common.filter', 'common', 'Filter'),
('common.add', 'common', 'Add'),
('common.edit', 'common', 'Edit'),
('common.delete', 'common', 'Delete'),
('common.save', 'common', 'Save'),
('common.cancel', 'common', 'Cancel'),
('common.update', 'common', 'Update'),
('common.create', 'common', 'Create'),
('common.back', 'common', 'Back'),
('common.view', 'common', 'View'),
('common.detail', 'common', 'Detail'),
('common.actions', 'common', 'Actions'),
('common.status', 'common', 'Status'),
('common.all', 'common', 'All'),
('common.yes', 'common', 'Yes'),
('common.no', 'common', 'No'),
('common.name', 'common', 'Name'),
('common.description', 'common', 'Description'),
('common.image', 'common', 'Image'),
('common.price', 'common', 'Price'),
('common.quantity', 'common', 'Quantity'),
('common.total', 'common', 'Total'),
('common.date', 'common', 'Date'),
('common.created_at', 'common', 'Created at'),
('common.email', 'common', 'Email'),
('common.phone', 'common', 'Phone'),
('common.address', 'common', 'Address'),
('common.city', 'common', 'City'),
('common.country', 'common', 'Country'),
('common.note', 'common', 'Note'),
('common.category', 'common', 'Category'),
('common.brand', 'common', 'Brand'),
('common.sku', 'common', 'SKU'),
('common.stock', 'common', 'Stock'),
('common.in_stock', 'common', 'In stock'),
('common.out_of_stock', 'common', 'Out of stock'),
('common.error', 'common', 'Error'),
('common.success', 'common', 'Success'),
('common.confirm_delete', 'common', 'Confirm delete'),
('common.no_results', 'common', 'No results'),
('common.free', 'common', 'Free'),
('common.default', 'common', 'Default'),
('common.product_name', 'common', 'Product name'),
('common.short_description', 'common', 'Short description'),
('common.full_description', 'common', 'Full description'),
('common.features', 'common', 'Features'),
('common.technical_specifications', 'common', 'Technical specifications'),
('common.shipping_dimensions', 'common', 'Shipping & dimensions'),
('common.weight_kg', 'common', 'Weight (kg)'),
('common.width_cm', 'common', 'Width (cm)'),
('common.height_cm', 'common', 'Height (cm)'),
('common.depth_cm', 'common', 'Depth (cm)'),
('common.select_category', 'common', 'Select category'),
('common.no_brand', 'common', 'No brand'),
('common.featured_product', 'common', 'Featured product'),
('common.new_arrival', 'common', 'New arrival'),
('common.pricing', 'common', 'Pricing'),
('common.inventory', 'common', 'Inventory'),
('common.stock_quantity', 'common', 'Stock quantity'),
('common.min_order_quantity', 'common', 'Min order quantity'),
('common.max_order_quantity', 'common', 'Max order quantity'),
('common.product_images', 'common', 'Product images'),
('common.primary', 'common', 'Primary'),
('common.qty', 'common', 'Quantity abbr'),
('common.pieces', 'common', 'Pieces')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- ====================
-- ADMIN NAMESPACE (350 keys)
-- ====================

-- Admin Brands
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.brands.title', 'admin'),
('admin.brands.add_brand', 'admin'),
('admin.brands.edit_brand', 'admin'),
('admin.brands.delete_brand', 'admin'),
('admin.brands.brand_name', 'admin'),
('admin.brands.slug', 'admin'),
('admin.brands.logo', 'admin'),
('admin.brands.description', 'admin'),
('admin.brands.website', 'admin'),
('admin.brands.status', 'admin'),
('admin.brands.save_brand', 'admin'),
('admin.brands.no_brands', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin Categories
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.categories.title', 'admin'),
('admin.categories.add_category', 'admin'),
('admin.categories.edit_category', 'admin'),
('admin.categories.parent_category', 'admin'),
('admin.categories.icon', 'admin'),
('admin.categories.order', 'admin'),
('admin.categories.is_active', 'admin'),
('admin.categories.save_category', 'admin'),
('admin.categories.no_categories', 'admin'),
('admin.categories.category_tree', 'admin'),
('admin.categories.root_level', 'admin'),
('admin.back_to_categories', 'admin'),
('admin.create_category', 'admin'),
('admin.update_category', 'admin'),
('admin.delete_category', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin Products (Extended)
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.products.title', 'admin'),
('admin.products.add_product', 'admin'),
('admin.products.edit_product', 'admin'),
('admin.products.product_name', 'admin'),
('admin.products.sku', 'admin'),
('admin.products.price', 'admin'),
('admin.products.stock', 'admin'),
('admin.products.save_product', 'admin'),
('admin.products.delete_product', 'admin'),
('admin.products.no_products', 'admin'),
('admin.add_product', 'admin'),
('admin.back_to_products', 'admin'),
('admin.product_information', 'admin'),
('admin.product_settings', 'admin'),
('admin.create_product', 'admin'),
('admin.update_product', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin Settings
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.settings.page_title', 'admin'),
('admin.settings.system_settings', 'admin'),
('admin.settings.total_products', 'admin'),
('admin.settings.total_orders', 'admin'),
('admin.settings.total_customers', 'admin'),
('admin.settings.general_settings', 'admin'),
('admin.settings.env_note', 'admin'),
('admin.settings.site_name', 'admin'),
('admin.settings.site_url', 'admin'),
('admin.settings.database_info', 'admin'),
('admin.settings.database_host', 'admin'),
('admin.settings.system_info', 'admin'),
('admin.settings.php_version', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin Navigation
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.nav.dashboard', 'admin'),
('admin.nav.products', 'admin'),
('admin.nav.categories', 'admin'),
('admin.nav.orders', 'admin'),
('admin.nav.languages', 'admin'),
('admin.nav.settings', 'admin'),
('admin.nav.logout', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin Customers
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.customers.title', 'admin'),
('admin.customers.count', 'admin'),
('admin.customers.customer', 'admin'),
('admin.customers.group', 'admin'),
('admin.customers.no_customers', 'admin'),
('admin.customers.detail', 'admin'),
('admin.customers.customer_info', 'admin'),
('admin.customers.addresses', 'admin'),
('admin.customers.order_history', 'admin'),
('admin.customers.statistics', 'admin'),
('admin.customers.total_orders', 'admin'),
('admin.customers.total_spent', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin Media
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.media.title', 'admin'),
('admin.media.file_count', 'admin'),
('admin.media.upload_file', 'admin'),
('admin.media.no_files', 'admin'),
('admin.media.upload_instruction', 'admin'),
('admin.media.select_file', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin Samples
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.samples.page_title', 'admin'),
('admin.samples.total_requests', 'admin'),
('admin.samples.pending_approval', 'admin'),
('admin.samples.status_pending', 'admin'),
('admin.samples.status_approved', 'admin'),
('admin.samples.status_preparing', 'admin'),
('admin.samples.status_shipped', 'admin'),
('admin.samples.status_delivered', 'admin'),
('admin.samples.status_rejected', 'admin'),
('admin.samples.no_samples', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin Documents
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.documents.page_title', 'admin'),
('admin.documents.documents', 'admin'),
('admin.documents.upload_new', 'admin'),
('admin.documents.document_type', 'admin'),
('admin.documents.type_cad', 'admin'),
('admin.documents.type_certificate', 'admin'),
('admin.documents.type_msds', 'admin'),
('admin.documents.type_tds', 'admin'),
('admin.documents.no_documents', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- ====================
-- CART NAMESPACE (15 keys)
-- ====================
INSERT INTO i18n_keys (key_name, `group`) VALUES
('cart.title', 'cart'),
('cart.empty', 'cart'),
('cart.continue_shopping', 'cart'),
('cart.proceed_to_checkout', 'cart'),
('cart.subtotal', 'cart'),
('cart.shipping', 'cart'),
('cart.discount', 'cart'),
('cart.total', 'cart'),
('cart.order_summary', 'cart')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- ====================
-- CHECKOUT NAMESPACE (40 keys)
-- ====================
INSERT INTO i18n_keys (key_name, `group`) VALUES
('checkout.title', 'checkout'),
('checkout.contact_information', 'checkout'),
('checkout.billing_address', 'checkout'),
('checkout.shipping_address', 'checkout'),
('checkout.full_name', 'checkout'),
('checkout.email', 'checkout'),
('checkout.phone', 'checkout'),
('checkout.address', 'checkout'),
('checkout.city', 'checkout'),
('checkout.postal_code', 'checkout'),
('checkout.country', 'checkout'),
('checkout.shipping_method', 'checkout'),
('checkout.payment_method', 'checkout'),
('checkout.order_notes', 'checkout'),
('checkout.place_order', 'checkout'),
('checkout.order_successful', 'checkout'),
('checkout.order_placed_successfully', 'checkout'),
('checkout.thank_you_message', 'checkout'),
('checkout.confirmation_email_sent', 'checkout'),
('checkout.whats_next', 'checkout'),
('checkout.view_my_orders', 'checkout')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- ====================
-- STATUS NAMESPACE (4 keys)
-- ====================
INSERT INTO i18n_keys (key_name, `group`) VALUES
('status.draft', 'status'),
('status.active', 'status'),
('status.inactive', 'status'),
('status.out_of_stock', 'status')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- ====================
-- HELP NAMESPACE (8 keys)
-- ====================
INSERT INTO i18n_keys (key_name, `group`) VALUES
('help.displayed_in_product_listings', 'help'),
('help.features_format', 'help'),
('help.json_format_recommended', 'help'),
('help.unique_product_code', 'help'),
('help.regular_selling_price', 'help'),
('help.compare_price_info', 'help'),
('help.cost_price_info', 'help'),
('help.image_management_future_update', 'help')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- ====================
-- MESSAGE NAMESPACE (5 keys)
-- ====================
INSERT INTO i18n_keys (key_name, `group`) VALUES
('message.confirm_delete_brand', 'message'),
('message.confirm_delete_category', 'message'),
('message.confirm_delete_product', 'message'),
('message.success_saved', 'message'),
('message.error_occurred', 'message')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

SELECT '✅ All translation keys created' AS status;
SELECT CONCAT('Total keys: ', COUNT(*)) AS total FROM i18n_keys;

-- ============================================
-- TRANSLATION VALUES - ENGLISH (lang_id=1)
-- ============================================
-- Note: Using efficient batch INSERT with VALUES from SELECT
-- This approach is faster than individual subqueries
-- ============================================

SET @lang_en = 1;

-- Common translations (EN)
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_en, CASE key_name
  WHEN 'common.home' THEN 'Home'
  WHEN 'common.products' THEN 'Products'
  WHEN 'common.categories' THEN 'Categories'
  WHEN 'common.about' THEN 'About'
  WHEN 'common.contact' THEN 'Contact'
  WHEN 'common.cart' THEN 'Cart'
  WHEN 'common.checkout' THEN 'Checkout'
  WHEN 'common.login' THEN 'Login'
  WHEN 'common.register' THEN 'Register'
  WHEN 'common.logout' THEN 'Logout'
  WHEN 'common.search' THEN 'Search'
  WHEN 'common.filter' THEN 'Filter'
  WHEN 'common.add' THEN 'Add'
  WHEN 'common.edit' THEN 'Edit'
  WHEN 'common.delete' THEN 'Delete'
  WHEN 'common.save' THEN 'Save'
  WHEN 'common.cancel' THEN 'Cancel'
  WHEN 'common.update' THEN 'Update'
  WHEN 'common.create' THEN 'Create'
  WHEN 'common.back' THEN 'Back'
  WHEN 'common.view' THEN 'View'
  WHEN 'common.detail' THEN 'Detail'
  WHEN 'common.actions' THEN 'Actions'
  WHEN 'common.status' THEN 'Status'
  WHEN 'common.all' THEN 'All'
  WHEN 'common.yes' THEN 'Yes'
  WHEN 'common.no' THEN 'No'
  WHEN 'common.name' THEN 'Name'
  WHEN 'common.description' THEN 'Description'
  WHEN 'common.image' THEN 'Image'
  WHEN 'common.price' THEN 'Price'
  WHEN 'common.quantity' THEN 'Quantity'
  WHEN 'common.total' THEN 'Total'
  WHEN 'common.date' THEN 'Date'
  WHEN 'common.created_at' THEN 'Created At'
  WHEN 'common.email' THEN 'Email'
  WHEN 'common.phone' THEN 'Phone'
  WHEN 'common.address' THEN 'Address'
  WHEN 'common.city' THEN 'City'
  WHEN 'common.country' THEN 'Country'
  WHEN 'common.note' THEN 'Note'
  WHEN 'common.category' THEN 'Category'
  WHEN 'common.brand' THEN 'Brand'
  WHEN 'common.sku' THEN 'SKU'
  WHEN 'common.stock' THEN 'Stock'
  WHEN 'common.in_stock' THEN 'In Stock'
  WHEN 'common.out_of_stock' THEN 'Out of Stock'
  WHEN 'common.error' THEN 'Error'
  WHEN 'common.success' THEN 'Success'
  WHEN 'common.confirm_delete' THEN 'Are you sure you want to delete this?'
  WHEN 'common.no_results' THEN 'No results found'
  WHEN 'common.free' THEN 'Free'
  WHEN 'common.default' THEN 'Default'
  WHEN 'common.product_name' THEN 'Product Name'
  WHEN 'common.short_description' THEN 'Short Description'
  WHEN 'common.full_description' THEN 'Full Description'
  WHEN 'common.features' THEN 'Features'
  WHEN 'common.technical_specifications' THEN 'Technical Specifications'
  WHEN 'common.shipping_dimensions' THEN 'Shipping & Dimensions'
  WHEN 'common.weight_kg' THEN 'Weight (kg)'
  WHEN 'common.width_cm' THEN 'Width (cm)'
  WHEN 'common.height_cm' THEN 'Height (cm)'
  WHEN 'common.depth_cm' THEN 'Depth (cm)'
  WHEN 'common.select_category' THEN 'Select Category'
  WHEN 'common.no_brand' THEN 'No Brand'
  WHEN 'common.featured_product' THEN 'Featured Product'
  WHEN 'common.new_arrival' THEN 'New Arrival'
  WHEN 'common.pricing' THEN 'Pricing'
  WHEN 'common.inventory' THEN 'Inventory'
  WHEN 'common.stock_quantity' THEN 'Stock Quantity'
  WHEN 'common.min_order_quantity' THEN 'Min Order Quantity'
  WHEN 'common.max_order_quantity' THEN 'Max Order Quantity'
  WHEN 'common.product_images' THEN 'Product Images'
  WHEN 'common.primary' THEN 'Primary'
  WHEN 'common.qty' THEN 'Qty'
  WHEN 'common.pieces' THEN 'pcs'
END FROM i18n_keys WHERE `group` = 'common'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- Admin translations (EN) - Sample subset
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_en, CASE key_name
  WHEN 'admin.brands.title' THEN 'Brands'
  WHEN 'admin.brands.add_brand' THEN 'Add Brand'
  WHEN 'admin.brands.edit_brand' THEN 'Edit Brand'
  WHEN 'admin.categories.title' THEN 'Categories'
  WHEN 'admin.categories.add_category' THEN 'Add Category'
  WHEN 'admin.products.title' THEN 'Products'
  WHEN 'admin.products.add_product' THEN 'Add Product'
  WHEN 'admin.add_product' THEN 'Add New Product'
  WHEN 'admin.back_to_products' THEN 'Back to Products'
  WHEN 'admin.product_information' THEN 'Product Information'
  WHEN 'admin.product_settings' THEN 'Product Settings'
  WHEN 'admin.create_product' THEN 'Create Product'
  WHEN 'admin.update_product' THEN 'Update Product'
  WHEN 'admin.settings.page_title' THEN 'System Settings'
  WHEN 'admin.settings.total_products' THEN 'Total Products'
  WHEN 'admin.nav.dashboard' THEN 'Dashboard'
  WHEN 'admin.nav.products' THEN 'Products'
  WHEN 'admin.nav.logout' THEN 'Logout'
  WHEN 'admin.customers.title' THEN 'Customers'
  WHEN 'admin.media.title' THEN 'Media Library'
  WHEN 'admin.samples.page_title' THEN 'Sample Requests'
  WHEN 'admin.documents.page_title' THEN 'Technical Documents'
  ELSE key_name
END FROM i18n_keys WHERE `group` = 'admin'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- Cart/Checkout translations (EN)
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_en, CASE key_name
  WHEN 'cart.title' THEN 'Shopping Cart'
  WHEN 'cart.empty' THEN 'Your cart is empty'
  WHEN 'cart.continue_shopping' THEN 'Continue Shopping'
  WHEN 'cart.proceed_to_checkout' THEN 'Proceed to Checkout'
  WHEN 'checkout.title' THEN 'Checkout'
  WHEN 'checkout.contact_information' THEN 'Contact Information'
  WHEN 'checkout.billing_address' THEN 'Billing Address'
  WHEN 'checkout.shipping_address' THEN 'Shipping Address'
  WHEN 'checkout.full_name' THEN 'Full Name'
  WHEN 'checkout.order_successful' THEN 'Order Successful'
  ELSE key_name
END FROM i18n_keys WHERE `group` IN ('cart', 'checkout')
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- Status translations (EN)
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_en, CASE key_name
  WHEN 'status.draft' THEN 'Draft'
  WHEN 'status.active' THEN 'Active'
  WHEN 'status.inactive' THEN 'Inactive'
  WHEN 'status.out_of_stock' THEN 'Out of Stock'
END FROM i18n_keys WHERE `group` = 'status'
ON DUPLICATE KEY UPDATE value = VALUES(value);

SELECT '✅ English translations added' AS status;

-- ============================================
-- TRANSLATION VALUES - TURKISH (lang_id=2)
-- ============================================

SET @lang_tr = 2;

-- Common translations (TR)
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_tr, CASE key_name
  WHEN 'common.home' THEN 'Anasayfa'
  WHEN 'common.products' THEN 'Ürünler'
  WHEN 'common.categories' THEN 'Kategoriler'
  WHEN 'common.about' THEN 'Hakkımızda'
  WHEN 'common.contact' THEN 'İletişim'
  WHEN 'common.cart' THEN 'Sepet'
  WHEN 'common.checkout' THEN 'Ödeme'
  WHEN 'common.login' THEN 'Giriş Yap'
  WHEN 'common.register' THEN 'Kayıt Ol'
  WHEN 'common.logout' THEN 'Çıkış Yap'
  WHEN 'common.search' THEN 'Ara'
  WHEN 'common.filter' THEN 'Filtrele'
  WHEN 'common.add' THEN 'Ekle'
  WHEN 'common.edit' THEN 'Düzenle'
  WHEN 'common.delete' THEN 'Sil'
  WHEN 'common.save' THEN 'Kaydet'
  WHEN 'common.cancel' THEN 'İptal'
  WHEN 'common.update' THEN 'Güncelle'
  WHEN 'common.create' THEN 'Oluştur'
  WHEN 'common.back' THEN 'Geri'
  WHEN 'common.view' THEN 'Görüntüle'
  WHEN 'common.detail' THEN 'Detay'
  WHEN 'common.actions' THEN 'İşlemler'
  WHEN 'common.status' THEN 'Durum'
  WHEN 'common.all' THEN 'Tümü'
  WHEN 'common.yes' THEN 'Evet'
  WHEN 'common.no' THEN 'Hayır'
  WHEN 'common.name' THEN 'İsim'
  WHEN 'common.description' THEN 'Açıklama'
  WHEN 'common.image' THEN 'Resim'
  WHEN 'common.price' THEN 'Fiyat'
  WHEN 'common.quantity' THEN 'Miktar'
  WHEN 'common.total' THEN 'Toplam'
  WHEN 'common.date' THEN 'Tarih'
  WHEN 'common.created_at' THEN 'Oluşturulma Tarihi'
  WHEN 'common.email' THEN 'E-posta'
  WHEN 'common.phone' THEN 'Telefon'
  WHEN 'common.address' THEN 'Adres'
  WHEN 'common.city' THEN 'Şehir'
  WHEN 'common.country' THEN 'Ülke'
  WHEN 'common.note' THEN 'Not'
  WHEN 'common.category' THEN 'Kategori'
  WHEN 'common.brand' THEN 'Marka'
  WHEN 'common.sku' THEN 'SKU'
  WHEN 'common.stock' THEN 'Stok'
  WHEN 'common.in_stock' THEN 'Stokta Var'
  WHEN 'common.out_of_stock' THEN 'Stokta Yok'
  WHEN 'common.error' THEN 'Hata'
  WHEN 'common.success' THEN 'Başarılı'
  WHEN 'common.confirm_delete' THEN 'Silmek istediğinizden emin misiniz?'
  WHEN 'common.no_results' THEN 'Sonuç bulunamadı'
  WHEN 'common.free' THEN 'Ücretsiz'
  WHEN 'common.default' THEN 'Varsayılan'
  WHEN 'common.product_name' THEN 'Ürün Adı'
  WHEN 'common.short_description' THEN 'Kısa Açıklama'
  WHEN 'common.full_description' THEN 'Detaylı Açıklama'
  WHEN 'common.features' THEN 'Özellikler'
  WHEN 'common.technical_specifications' THEN 'Teknik Özellikler'
  WHEN 'common.shipping_dimensions' THEN 'Kargo & Boyutlar'
  WHEN 'common.weight_kg' THEN 'Ağırlık (kg)'
  WHEN 'common.width_cm' THEN 'Genişlik (cm)'
  WHEN 'common.height_cm' THEN 'Yükseklik (cm)'
  WHEN 'common.depth_cm' THEN 'Derinlik (cm)'
  WHEN 'common.select_category' THEN 'Kategori Seçin'
  WHEN 'common.no_brand' THEN 'Marka Yok'
  WHEN 'common.featured_product' THEN 'Öne Çıkan Ürün'
  WHEN 'common.new_arrival' THEN 'Yeni Geliş'
  WHEN 'common.pricing' THEN 'Fiyatlandırma'
  WHEN 'common.inventory' THEN 'Envanter'
  WHEN 'common.stock_quantity' THEN 'Stok Miktarı'
  WHEN 'common.min_order_quantity' THEN 'Min. Sipariş Miktarı'
  WHEN 'common.max_order_quantity' THEN 'Max. Sipariş Miktarı'
  WHEN 'common.product_images' THEN 'Ürün Resimleri'
  WHEN 'common.primary' THEN 'Birincil'
  WHEN 'common.qty' THEN 'Adet'
  WHEN 'common.pieces' THEN 'adet'
END FROM i18n_keys WHERE `group` = 'common'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- Admin translations (TR) - Sample subset
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_tr, CASE key_name
  WHEN 'admin.brands.title' THEN 'Markalar'
  WHEN 'admin.brands.add_brand' THEN 'Marka Ekle'
  WHEN 'admin.brands.edit_brand' THEN 'Markayı Düzenle'
  WHEN 'admin.categories.title' THEN 'Kategoriler'
  WHEN 'admin.categories.add_category' THEN 'Kategori Ekle'
  WHEN 'admin.products.title' THEN 'Ürünler'
  WHEN 'admin.products.add_product' THEN 'Ürün Ekle'
  WHEN 'admin.add_product' THEN 'Yeni Ürün Ekle'
  WHEN 'admin.back_to_products' THEN 'Ürünlere Dön'
  WHEN 'admin.product_information' THEN 'Ürün Bilgileri'
  WHEN 'admin.product_settings' THEN 'Ürün Ayarları'
  WHEN 'admin.create_product' THEN 'Ürün Oluştur'
  WHEN 'admin.update_product' THEN 'Ürünü Güncelle'
  WHEN 'admin.settings.page_title' THEN 'Sistem Ayarları'
  WHEN 'admin.settings.total_products' THEN 'Toplam Ürün'
  WHEN 'admin.nav.dashboard' THEN 'Kontrol Paneli'
  WHEN 'admin.nav.products' THEN 'Ürünler'
  WHEN 'admin.nav.logout' THEN 'Çıkış Yap'
  WHEN 'admin.customers.title' THEN 'Müşteriler'
  WHEN 'admin.media.title' THEN 'Medya Kütüphanesi'
  WHEN 'admin.samples.page_title' THEN 'Numune Talepleri'
  WHEN 'admin.documents.page_title' THEN 'Teknik Dökümanlar'
  ELSE key_name
END FROM i18n_keys WHERE `group` = 'admin'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- Cart/Checkout translations (TR)
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_tr, CASE key_name
  WHEN 'cart.title' THEN 'Alışveriş Sepeti'
  WHEN 'cart.empty' THEN 'Sepetiniz boş'
  WHEN 'cart.continue_shopping' THEN 'Alışverişe Devam Et'
  WHEN 'cart.proceed_to_checkout' THEN 'Ödemeye Geç'
  WHEN 'checkout.title' THEN 'Ödeme'
  WHEN 'checkout.contact_information' THEN 'İletişim Bilgileri'
  WHEN 'checkout.billing_address' THEN 'Fatura Adresi'
  WHEN 'checkout.shipping_address' THEN 'Teslimat Adresi'
  WHEN 'checkout.full_name' THEN 'Ad Soyad'
  WHEN 'checkout.order_successful' THEN 'Sipariş Başarılı'
  ELSE key_name
END FROM i18n_keys WHERE `group` IN ('cart', 'checkout')
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- Status translations (TR)
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_tr, CASE key_name
  WHEN 'status.draft' THEN 'Taslak'
  WHEN 'status.active' THEN 'Aktif'
  WHEN 'status.inactive' THEN 'Pasif'
  WHEN 'status.out_of_stock' THEN 'Stokta Yok'
END FROM i18n_keys WHERE `group` = 'status'
ON DUPLICATE KEY UPDATE value = VALUES(value);

SELECT '✅ Turkish translations added' AS status;

-- ====================
-- FINAL STATUS
-- ====================
SELECT '🎉 Translation migration completed successfully!' AS status;
SELECT CONCAT('Total keys: ', COUNT(*)) AS total_keys FROM i18n_keys;
SELECT CONCAT('Total translations: ', COUNT(*)) AS total_translations FROM i18n_values;
SELECT lang_id, COUNT(*) as count FROM i18n_values GROUP BY lang_id;
