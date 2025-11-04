-- ============================================
-- Migration 012: COMPLETE Translation Keys
-- Phase 2: Admin Views Translation System
-- ============================================
-- Total: 565+ translation keys (EN=1, TR=2)
-- Organized by Phase 2 batches for clarity
-- ============================================

SET NAMES utf8mb4;
SET @lang_en = 1;
SET @lang_tr = 2;

-- ============================================
-- PART 1: COMMON NAMESPACE (100 keys)
-- Base common keys + extended from Phase 2
-- ============================================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
-- Navigation & Pages
('common.home', 'common', 'Home'),
('common.products', 'common', 'Products'),
('common.categories', 'common', 'Categories'),
('common.about', 'common', 'About'),
('common.contact', 'common', 'Contact'),
('common.cart', 'common', 'Cart'),
('common.checkout', 'common', 'Checkout'),

-- Auth
('common.login', 'common', 'Login'),
('common.register', 'common', 'Register'),
('common.logout', 'common', 'Logout'),

-- Actions
('common.search', 'common', 'Search'),
('common.filter', 'common', 'Filter'),
('common.sort', 'common', 'Sort'),
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
('common.submit', 'common', 'Submit'),
('common.close', 'common', 'Close'),
('common.confirm', 'common', 'Confirm'),
('common.download', 'common', 'Download'),
('common.upload', 'common', 'Upload'),
('common.select', 'common', 'Select'),
('common.print', 'common', 'Print'),
('common.preview', 'common', 'Preview'),
('common.hide', 'common', 'Hide'),
('common.show', 'common', 'Show'),

-- Status & States
('common.status', 'common', 'Status'),
('common.active', 'common', 'Active'),
('common.inactive', 'common', 'Inactive'),
('common.all', 'common', 'All'),
('common.yes', 'common', 'Yes'),
('common.no', 'common', 'No'),
('common.default', 'common', 'Default'),
('common.required', 'common', 'Required'),
('common.optional', 'common', 'Optional'),

-- Common Fields
('common.name', 'common', 'Name'),
('common.title', 'common', 'Title'),
('common.description', 'common', 'Description'),
('common.image', 'common', 'Image'),
('common.icon', 'common', 'Icon'),
('common.logo', 'common', 'Logo'),
('common.url', 'common', 'URL'),
('common.slug', 'common', 'Slug'),
('common.order', 'common', 'Order'),

-- Product Fields
('common.price', 'common', 'Price'),
('common.quantity', 'common', 'Quantity'),
('common.total', 'common', 'Total'),
('common.subtotal', 'common', 'Subtotal'),
('common.tax', 'common', 'Tax'),
('common.discount', 'common', 'Discount'),
('common.shipping', 'common', 'Shipping'),
('common.category', 'common', 'Category'),
('common.brand', 'common', 'Brand'),
('common.sku', 'common', 'SKU'),
('common.stock', 'common', 'Stock'),
('common.in_stock', 'common', 'In stock'),
('common.out_of_stock', 'common', 'Out of stock'),
('common.product_name', 'common', 'Product name'),
('common.short_description', 'common', 'Short description'),
('common.full_description', 'common', 'Full description'),
('common.features', 'common', 'Features'),
('common.technical_specifications', 'common', 'Technical specifications'),
('common.primary', 'common', 'Primary'),
('common.qty', 'common', 'Qty'),
('common.pieces', 'common', 'Pieces'),

-- Dimensions & Shipping
('common.shipping_dimensions', 'common', 'Shipping & dimensions'),
('common.weight_kg', 'common', 'Weight (kg)'),
('common.width_cm', 'common', 'Width (cm)'),
('common.height_cm', 'common', 'Height (cm)'),
('common.depth_cm', 'common', 'Depth (cm)'),
('common.length_cm', 'common', 'Length (cm)'),

-- Categories & Selection
('common.select_category', 'common', 'Select category'),
('common.no_brand', 'common', 'No brand'),
('common.featured_product', 'common', 'Featured product'),
('common.new_arrival', 'common', 'New arrival'),

-- Pricing & Inventory
('common.pricing', 'common', 'Pricing'),
('common.inventory', 'common', 'Inventory'),
('common.stock_quantity', 'common', 'Stock quantity'),
('common.min_order_quantity', 'common', 'Min order quantity'),
('common.max_order_quantity', 'common', 'Max order quantity'),
('common.product_images', 'common', 'Product images'),

-- Contact & Personal Info
('common.date', 'common', 'Date'),
('common.created_at', 'common', 'Created at'),
('common.updated_at', 'common', 'Updated at'),
('common.email', 'common', 'Email'),
('common.phone', 'common', 'Phone'),
('common.address', 'common', 'Address'),
('common.city', 'common', 'City'),
('common.country', 'common', 'Country'),
('common.postal_code', 'common', 'Postal code'),

-- Notes & Messages
('common.note', 'common', 'Note'),
('common.notes', 'common', 'Notes'),
('common.error', 'common', 'Error'),
('common.success', 'common', 'Success'),
('common.warning', 'common', 'Warning'),
('common.info', 'common', 'Info'),
('common.loading', 'common', 'Loading'),

-- Confirmations & States
('common.confirm_delete', 'common', 'Confirm delete'),
('common.no_results', 'common', 'No results'),
('common.free', 'common', 'Free'),
('common.amount', 'common', 'Amount'),

-- Shopping
('common.add_to_cart', 'common', 'Add to cart'),
('common.buy_now', 'common', 'Buy now'),
('common.view_more', 'common', 'View more'),
('common.show_less', 'common', 'Show less'),

-- Pagination
('common.page', 'common', 'Page'),
('common.of', 'common', 'Of'),
('common.showing', 'common', 'Showing'),
('common.per_page', 'common', 'Per page'),
('common.clear_filters', 'common', 'Clear filters'),
('common.previous', 'common', 'Previous'),
('common.next', 'common', 'Next')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- ============================================
-- PART 2: ADMIN NAMESPACE (250 keys)
-- Admin panel translations by module
-- ============================================

-- Admin - Brands (12 keys)
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

-- Admin - Categories (20 keys)
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
('admin.categories.subcategories', 'admin'),
('admin.categories.parent', 'admin'),
('admin.back_to_categories', 'admin'),
('admin.create_category', 'admin'),
('admin.update_category', 'admin'),
('admin.delete_category', 'admin'),
('admin.edit_category', 'admin'),
('admin.category_information', 'admin'),
('admin.category_settings', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin - Products (50 keys)
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.products.title', 'admin'),
('admin.products.add_product', 'admin'),
('admin.products.edit_product', 'admin'),
('admin.products.product_name', 'admin'),
('admin.products.sku', 'admin'),
('admin.products.price', 'admin'),
('admin.products.stock', 'admin'),
('admin.products.variant', 'admin'),
('admin.products.save_product', 'admin'),
('admin.products.delete_product', 'admin'),
('admin.products.no_products', 'admin'),
('admin.products.product_count', 'admin'),
('admin.products.add_new_product', 'admin'),
('admin.products.search_placeholder', 'admin'),
('admin.products.product', 'admin'),
('admin.products.featured', 'admin'),
('admin.products.variants_count', 'admin'),
('admin.products.add_first_product', 'admin'),
('admin.products.validation_alert', 'admin'),
('admin.products.save_draft', 'admin'),
('admin.products.publish', 'admin'),
('admin.products.update_info', 'admin'),
('admin.products.create_new', 'admin'),
('admin.products.basic_info', 'admin'),
('admin.products.sku_hint', 'admin'),
('admin.products.short_description', 'admin'),
('admin.products.short_description_hint', 'admin'),
('admin.products.description', 'admin'),
('admin.products.sale_price', 'admin'),
('admin.products.compare_price', 'admin'),
('admin.products.compare_price_hint', 'admin'),
('admin.products.cost_price', 'admin'),
('admin.products.cost_price_hint', 'admin'),
('admin.products.inventory_management', 'admin'),
('admin.products.track_inventory', 'admin'),
('admin.products.low_stock_threshold', 'admin'),
('admin.products.allow_backorder', 'admin'),
('admin.products.physical_attributes', 'admin'),
('admin.products.selling_unit', 'admin'),
('admin.products.package_quantity', 'admin'),
('admin.products.dimensions', 'admin'),
('admin.products.length_mm', 'admin'),
('admin.products.width_mm', 'admin'),
('admin.products.height_mm', 'admin'),
('admin.products.weight_kg', 'admin'),
('admin.products.b2b_settings', 'admin'),
('admin.products.b2b_only', 'admin'),
('admin.products.product_colors', 'admin'),
('admin.products.color_texture_management', 'admin'),
('admin.products.qr_management', 'admin'),
('admin.products.qr_code', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin - Common (15 keys)
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.add_product', 'admin'),
('admin.back_to_products', 'admin'),
('admin.product_information', 'admin'),
('admin.product_settings', 'admin'),
('admin.create_product', 'admin'),
('admin.update_product', 'admin'),
('admin.delete_product', 'admin'),
('admin.edit_product', 'admin'),
('admin.back_to_brands', 'admin'),
('admin.create_brand', 'admin'),
('admin.update_brand', 'admin'),
('admin.delete_brand', 'admin'),
('admin.edit_brand', 'admin'),
('admin.brand_information', 'admin'),
('admin.brand_settings', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin - Settings (30 keys)
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
('admin.settings.current_value', 'admin'),
('admin.settings.not_defined', 'admin'),
('admin.settings.default_language', 'admin'),
('admin.settings.timezone', 'admin'),
('admin.settings.currency', 'admin'),
('admin.settings.debug_mode', 'admin'),
('admin.settings.database_info', 'admin'),
('admin.settings.database_host', 'admin'),
('admin.settings.database_name', 'admin'),
('admin.settings.port', 'admin'),
('admin.settings.system_info', 'admin'),
('admin.settings.php_version', 'admin'),
('admin.settings.server', 'admin'),
('admin.settings.operating_system', 'admin'),
('admin.settings.unknown', 'admin'),
('admin.settings.email_settings', 'admin'),
('admin.settings.smtp_host', 'admin'),
('admin.settings.smtp_port', 'admin'),
('admin.settings.smtp_username', 'admin'),
('admin.settings.payment_settings', 'admin'),
('admin.settings.cache_settings', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin - Navigation (10 keys)
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.nav.dashboard', 'admin'),
('admin.nav.products', 'admin'),
('admin.nav.categories', 'admin'),
('admin.nav.brands', 'admin'),
('admin.nav.orders', 'admin'),
('admin.nav.customers', 'admin'),
('admin.nav.languages', 'admin'),
('admin.nav.settings', 'admin'),
('admin.nav.logout', 'admin'),
('admin.nav.reports', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin - Customers (30 keys)
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.customers.title', 'admin'),
('admin.customers.count', 'admin'),
('admin.customers.search_placeholder', 'admin'),
('admin.customers.customer_group', 'admin'),
('admin.customers.customer', 'admin'),
('admin.customers.group', 'admin'),
('admin.customers.no_customers', 'admin'),
('admin.customers.detail', 'admin'),
('admin.customers.customer_info', 'admin'),
('admin.customers.full_name', 'admin'),
('admin.customers.individual', 'admin'),
('admin.customers.b2b_info', 'admin'),
('admin.customers.credit_limit', 'admin'),
('admin.customers.credit_used', 'admin'),
('admin.customers.credit_available', 'admin'),
('admin.customers.payment_terms', 'admin'),
('admin.customers.addresses', 'admin'),
('admin.customers.order_history', 'admin'),
('admin.customers.no_orders', 'admin'),
('admin.customers.statistics', 'admin'),
('admin.customers.total_orders', 'admin'),
('admin.customers.total_spent', 'admin'),
('admin.customers.average_order', 'admin'),
('admin.customers.update_group', 'admin'),
('admin.customers.send_email', 'admin'),
('admin.customers.add_note', 'admin'),
('admin.customers.customer_since', 'admin'),
('admin.customers.last_order', 'admin'),
('admin.customers.lifetime_value', 'admin'),
('admin.customers.vip_status', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin - Media (15 keys)
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.media.title', 'admin'),
('admin.media.file_count', 'admin'),
('admin.media.upload_file', 'admin'),
('admin.media.filename_placeholder', 'admin'),
('admin.media.type', 'admin'),
('admin.media.type_image', 'admin'),
('admin.media.type_video', 'admin'),
('admin.media.type_document', 'admin'),
('admin.media.no_files', 'admin'),
('admin.media.upload_instruction', 'admin'),
('admin.media.select_file', 'admin'),
('admin.media.file_size', 'admin'),
('admin.media.uploaded_at', 'admin'),
('admin.media.dimensions', 'admin'),
('admin.media.delete_confirm', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin - Samples (35 keys)
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
('admin.samples.search_placeholder', 'admin'),
('admin.samples.no_samples', 'admin'),
('admin.samples.no_samples_desc', 'admin'),
('admin.samples.order_number', 'admin'),
('admin.samples.customer', 'admin'),
('admin.samples.product', 'admin'),
('admin.samples.quantity', 'admin'),
('admin.samples.shipping', 'admin'),
('admin.samples.request_date', 'admin'),
('admin.samples.free_shipping', 'admin'),
('admin.samples.detail_title', 'admin'),
('admin.samples.sample_request', 'admin'),
('admin.samples.requested_product', 'admin'),
('admin.samples.request_details', 'admin'),
('admin.samples.purpose', 'admin'),
('admin.samples.project_details', 'admin'),
('admin.samples.additional_notes', 'admin'),
('admin.samples.shipping_info', 'admin'),
('admin.samples.shipping_address', 'admin'),
('admin.samples.shipping_details', 'admin'),
('admin.samples.shipping_cost', 'admin'),
('admin.samples.tracking_number', 'admin'),
('admin.samples.status_history', 'admin'),
('admin.samples.updated_by', 'admin'),
('admin.samples.customer_info', 'admin'),
('admin.samples.customer_name', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- Admin - Documents (35 keys)
INSERT INTO i18n_keys (key_name, `group`) VALUES
('admin.documents.page_title', 'admin'),
('admin.documents.documents', 'admin'),
('admin.documents.upload_new', 'admin'),
('admin.documents.search_placeholder', 'admin'),
('admin.documents.document_type', 'admin'),
('admin.documents.type_cad', 'admin'),
('admin.documents.type_certificate', 'admin'),
('admin.documents.type_msds', 'admin'),
('admin.documents.type_tds', 'admin'),
('admin.documents.type_manual', 'admin'),
('admin.documents.type_specification', 'admin'),
('admin.documents.type_drawing', 'admin'),
('admin.documents.type_other', 'admin'),
('admin.documents.no_documents', 'admin'),
('admin.documents.no_documents_desc', 'admin'),
('admin.documents.upload_document', 'admin'),
('admin.documents.document', 'admin'),
('admin.documents.product', 'admin'),
('admin.documents.type', 'admin'),
('admin.documents.version', 'admin'),
('admin.documents.size', 'admin'),
('admin.documents.upload_date', 'admin'),
('admin.documents.delete_confirm', 'admin'),
('admin.documents.upload_title', 'admin'),
('admin.documents.upload_description', 'admin'),
('admin.documents.file_selection', 'admin'),
('admin.documents.max_file_size', 'admin'),
('admin.documents.allowed', 'admin'),
('admin.documents.document_info', 'admin'),
('admin.documents.related_product', 'admin'),
('admin.documents.document_title', 'admin'),
('admin.documents.visibility', 'admin'),
('admin.documents.public_access', 'admin'),
('admin.documents.public_access_help', 'admin'),
('admin.documents.tips', 'admin')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- ============================================
-- PART 3: CART NAMESPACE (15 keys)
-- ============================================
INSERT INTO i18n_keys (key_name, `group`) VALUES
('cart.title', 'cart'),
('cart.empty', 'cart'),
('cart.continue_shopping', 'cart'),
('cart.proceed_to_checkout', 'cart'),
('cart.subtotal', 'cart'),
('cart.shipping', 'cart'),
('cart.discount', 'cart'),
('cart.total', 'cart'),
('cart.order_summary', 'cart'),
('cart.item_count', 'cart'),
('cart.update_cart', 'cart'),
('cart.remove_item', 'cart'),
('cart.apply_coupon', 'cart'),
('cart.coupon_code', 'cart'),
('cart.estimated_total', 'cart')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- ============================================
-- PART 4: CHECKOUT NAMESPACE (40 keys)
-- ============================================
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
('checkout.state_province', 'checkout'),
('checkout.postal_code', 'checkout'),
('checkout.country', 'checkout'),
('checkout.same_as_billing', 'checkout'),
('checkout.shipping_method', 'checkout'),
('checkout.payment_method', 'checkout'),
('checkout.order_notes', 'checkout'),
('checkout.order_notes_placeholder', 'checkout'),
('checkout.place_order', 'checkout'),
('checkout.secure_checkout', 'checkout'),
('checkout.order_successful', 'checkout'),
('checkout.order_placed_successfully', 'checkout'),
('checkout.thank_you_message', 'checkout'),
('checkout.confirmation_email_sent', 'checkout'),
('checkout.whats_next', 'checkout'),
('checkout.whats_next_step1', 'checkout'),
('checkout.whats_next_step2', 'checkout'),
('checkout.whats_next_step3', 'checkout'),
('checkout.view_my_orders', 'checkout'),
('checkout.order_number', 'checkout'),
('checkout.order_date', 'checkout'),
('checkout.order_details', 'checkout'),
('checkout.billing_info', 'checkout'),
('checkout.shipping_info', 'checkout'),
('checkout.payment_info', 'checkout'),
('checkout.order_items', 'checkout'),
('checkout.item_price', 'checkout'),
('checkout.item_quantity', 'checkout'),
('checkout.item_total', 'checkout'),
('checkout.processing_payment', 'checkout'),
('checkout.payment_failed', 'checkout')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- ============================================
-- PART 5: STATUS NAMESPACE (10 keys)
-- ============================================
INSERT INTO i18n_keys (key_name, `group`) VALUES
('status.draft', 'status'),
('status.active', 'status'),
('status.inactive', 'status'),
('status.out_of_stock', 'status'),
('status.pending', 'status'),
('status.approved', 'status'),
('status.rejected', 'status'),
('status.completed', 'status'),
('status.cancelled', 'status'),
('status.archived', 'status')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- ============================================
-- PART 6: HELP NAMESPACE (15 keys)
-- ============================================
INSERT INTO i18n_keys (key_name, `group`) VALUES
('help.displayed_in_product_listings', 'help'),
('help.features_format', 'help'),
('help.json_format_recommended', 'help'),
('help.unique_product_code', 'help'),
('help.regular_selling_price', 'help'),
('help.compare_price_info', 'help'),
('help.cost_price_info', 'help'),
('help.image_management_future_update', 'help'),
('help.icon_help', 'help'),
('help.slug_help', 'help'),
('help.meta_title_help', 'help'),
('help.meta_description_help', 'help'),
('help.category_order_help', 'help'),
('help.brand_logo_help', 'help'),
('help.seo_keywords_help', 'help')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

-- ============================================
-- PART 7: MESSAGE NAMESPACE (10 keys)
-- ============================================
INSERT INTO i18n_keys (key_name, `group`) VALUES
('message.confirm_delete_brand', 'message'),
('message.confirm_delete_category', 'message'),
('message.confirm_delete_product', 'message'),
('message.success_saved', 'message'),
('message.error_occurred', 'message'),
('message.confirm_delete_item', 'message'),
('message.changes_saved', 'message'),
('message.upload_successful', 'message'),
('message.invalid_file_type', 'message'),
('message.required_fields_missing', 'message')
ON DUPLICATE KEY UPDATE `group` = VALUES(`group`);

SELECT '✅ All translation keys created' AS status;
SELECT CONCAT('Total keys: ', COUNT(*)) AS total FROM i18n_keys;

-- ============================================================
-- PART 2: TRANSLATION VALUES (EN + TR)
-- ============================================================

-- ------------------------------------------------------------
-- COMMON NAMESPACE - ENGLISH (lang_id = 1)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_en, CASE key_name
    WHEN 'common.home' THEN 'Home'
    WHEN 'common.dashboard' THEN 'Dashboard'
    WHEN 'common.products' THEN 'Products'
    WHEN 'common.categories' THEN 'Categories'
    WHEN 'common.brands' THEN 'Brands'
    WHEN 'common.orders' THEN 'Orders'
    WHEN 'common.customers' THEN 'Customers'
    WHEN 'common.settings' THEN 'Settings'
    WHEN 'common.logout' THEN 'Logout'
    WHEN 'common.login' THEN 'Login'
    WHEN 'common.register' THEN 'Register'
    WHEN 'common.search' THEN 'Search'
    WHEN 'common.add' THEN 'Add'
    WHEN 'common.edit' THEN 'Edit'
    WHEN 'common.delete' THEN 'Delete'
    WHEN 'common.save' THEN 'Save'
    WHEN 'common.cancel' THEN 'Cancel'
    WHEN 'common.back' THEN 'Back'
    WHEN 'common.actions' THEN 'Actions'
    WHEN 'common.status' THEN 'Status'
    WHEN 'common.active' THEN 'Active'
    WHEN 'common.inactive' THEN 'Inactive'
    WHEN 'common.yes' THEN 'Yes'
    WHEN 'common.no' THEN 'No'
    WHEN 'common.all' THEN 'All'
    WHEN 'common.none' THEN 'None'
    WHEN 'common.name' THEN 'Name'
    WHEN 'common.description' THEN 'Description'
    WHEN 'common.image' THEN 'Image'
    WHEN 'common.price' THEN 'Price'
    WHEN 'common.quantity' THEN 'Quantity'
    WHEN 'common.total' THEN 'Total'
    WHEN 'common.date' THEN 'Date'
    WHEN 'common.time' THEN 'Time'
    WHEN 'common.created_at' THEN 'Created At'
    WHEN 'common.updated_at' THEN 'Updated At'
    WHEN 'common.view' THEN 'View'
    WHEN 'common.details' THEN 'Details'
    WHEN 'common.loading' THEN 'Loading...'
    WHEN 'common.processing' THEN 'Processing...'
    WHEN 'common.please_wait' THEN 'Please wait...'
    WHEN 'common.submit' THEN 'Submit'
    WHEN 'common.reset' THEN 'Reset'
    WHEN 'common.clear' THEN 'Clear'
    WHEN 'common.filter' THEN 'Filter'
    WHEN 'common.export' THEN 'Export'
    WHEN 'common.import' THEN 'Import'
    WHEN 'common.download' THEN 'Download'
    WHEN 'common.upload' THEN 'Upload'
    WHEN 'common.file' THEN 'File'
    WHEN 'common.folder' THEN 'Folder'
    WHEN 'common.select' THEN 'Select'
    WHEN 'common.choose' THEN 'Choose'
    WHEN 'common.browse' THEN 'Browse'
    WHEN 'common.copy' THEN 'Copy'
    WHEN 'common.paste' THEN 'Paste'
    WHEN 'common.cut' THEN 'Cut'
    WHEN 'common.undo' THEN 'Undo'
    WHEN 'common.redo' THEN 'Redo'
    WHEN 'common.refresh' THEN 'Refresh'
    WHEN 'common.reload' THEN 'Reload'
    WHEN 'common.close' THEN 'Close'
    WHEN 'common.minimize' THEN 'Minimize'
    WHEN 'common.maximize' THEN 'Maximize'
    WHEN 'common.print' THEN 'Print'
    WHEN 'common.share' THEN 'Share'
    WHEN 'common.send' THEN 'Send'
    WHEN 'common.receive' THEN 'Receive'
    WHEN 'common.inbox' THEN 'Inbox'
    WHEN 'common.outbox' THEN 'Outbox'
    WHEN 'common.draft' THEN 'Draft'
    WHEN 'common.trash' THEN 'Trash'
    WHEN 'common.archive' THEN 'Archive'
    WHEN 'common.restore' THEN 'Restore'
    WHEN 'common.duplicate' THEN 'Duplicate'
    WHEN 'common.move' THEN 'Move'
    WHEN 'common.rename' THEN 'Rename'
    WHEN 'common.properties' THEN 'Properties'
    WHEN 'common.permissions' THEN 'Permissions'
    WHEN 'common.settings_general' THEN 'General Settings'
    WHEN 'common.advanced' THEN 'Advanced'
    WHEN 'common.help' THEN 'Help'
    WHEN 'common.about' THEN 'About'
    WHEN 'common.version' THEN 'Version'
    WHEN 'common.copyright' THEN 'Copyright'
    WHEN 'common.terms' THEN 'Terms'
    WHEN 'common.privacy' THEN 'Privacy'
    WHEN 'common.contact' THEN 'Contact'
    WHEN 'common.support' THEN 'Support'
    WHEN 'common.feedback' THEN 'Feedback'
    WHEN 'common.report' THEN 'Report'
    WHEN 'common.bug' THEN 'Bug'
    WHEN 'common.feature' THEN 'Feature'
    WHEN 'common.request' THEN 'Request'
    WHEN 'common.suggestion' THEN 'Suggestion'
    WHEN 'common.welcome' THEN 'Welcome'
    WHEN 'common.goodbye' THEN 'Goodbye'
    WHEN 'common.thankyou' THEN 'Thank You'
    WHEN 'common.confirm' THEN 'Confirm'
    WHEN 'common.warning' THEN 'Warning'
    WHEN 'common.error' THEN 'Error'
    WHEN 'common.success' THEN 'Success'
    WHEN 'common.info' THEN 'Information'
END
FROM i18n_keys WHERE `group` = 'common'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ------------------------------------------------------------
-- COMMON NAMESPACE - TURKISH (lang_id = 2)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_tr, CASE key_name
    WHEN 'common.home' THEN 'Anasayfa'
    WHEN 'common.dashboard' THEN 'Kontrol Paneli'
    WHEN 'common.products' THEN 'Ürünler'
    WHEN 'common.categories' THEN 'Kategoriler'
    WHEN 'common.brands' THEN 'Markalar'
    WHEN 'common.orders' THEN 'Siparişler'
    WHEN 'common.customers' THEN 'Müşteriler'
    WHEN 'common.settings' THEN 'Ayarlar'
    WHEN 'common.logout' THEN 'Çıkış'
    WHEN 'common.login' THEN 'Giriş'
    WHEN 'common.register' THEN 'Kayıt'
    WHEN 'common.search' THEN 'Ara'
    WHEN 'common.add' THEN 'Ekle'
    WHEN 'common.edit' THEN 'Düzenle'
    WHEN 'common.delete' THEN 'Sil'
    WHEN 'common.save' THEN 'Kaydet'
    WHEN 'common.cancel' THEN 'İptal'
    WHEN 'common.back' THEN 'Geri'
    WHEN 'common.actions' THEN 'İşlemler'
    WHEN 'common.status' THEN 'Durum'
    WHEN 'common.active' THEN 'Aktif'
    WHEN 'common.inactive' THEN 'Pasif'
    WHEN 'common.yes' THEN 'Evet'
    WHEN 'common.no' THEN 'Hayır'
    WHEN 'common.all' THEN 'Tümü'
    WHEN 'common.none' THEN 'Hiçbiri'
    WHEN 'common.name' THEN 'İsim'
    WHEN 'common.description' THEN 'Açıklama'
    WHEN 'common.image' THEN 'Resim'
    WHEN 'common.price' THEN 'Fiyat'
    WHEN 'common.quantity' THEN 'Miktar'
    WHEN 'common.total' THEN 'Toplam'
    WHEN 'common.date' THEN 'Tarih'
    WHEN 'common.time' THEN 'Zaman'
    WHEN 'common.created_at' THEN 'Oluşturulma'
    WHEN 'common.updated_at' THEN 'Güncellenme'
    WHEN 'common.view' THEN 'Görüntüle'
    WHEN 'common.details' THEN 'Detaylar'
    WHEN 'common.loading' THEN 'Yükleniyor...'
    WHEN 'common.processing' THEN 'İşleniyor...'
    WHEN 'common.please_wait' THEN 'Lütfen bekleyin...'
    WHEN 'common.submit' THEN 'Gönder'
    WHEN 'common.reset' THEN 'Sıfırla'
    WHEN 'common.clear' THEN 'Temizle'
    WHEN 'common.filter' THEN 'Filtrele'
    WHEN 'common.export' THEN 'Dışa Aktar'
    WHEN 'common.import' THEN 'İçe Aktar'
    WHEN 'common.download' THEN 'İndir'
    WHEN 'common.upload' THEN 'Yükle'
    WHEN 'common.file' THEN 'Dosya'
    WHEN 'common.folder' THEN 'Klasör'
    WHEN 'common.select' THEN 'Seç'
    WHEN 'common.choose' THEN 'Tercih Et'
    WHEN 'common.browse' THEN 'Gözat'
    WHEN 'common.copy' THEN 'Kopyala'
    WHEN 'common.paste' THEN 'Yapıştır'
    WHEN 'common.cut' THEN 'Kes'
    WHEN 'common.undo' THEN 'Geri Al'
    WHEN 'common.redo' THEN 'Yinele'
    WHEN 'common.refresh' THEN 'Yenile'
    WHEN 'common.reload' THEN 'Yeniden Yükle'
    WHEN 'common.close' THEN 'Kapat'
    WHEN 'common.minimize' THEN 'Simge Durumuna Küçült'
    WHEN 'common.maximize' THEN 'Ekranı Kapla'
    WHEN 'common.print' THEN 'Yazdır'
    WHEN 'common.share' THEN 'Paylaş'
    WHEN 'common.send' THEN 'Gönder'
    WHEN 'common.receive' THEN 'Al'
    WHEN 'common.inbox' THEN 'Gelen Kutusu'
    WHEN 'common.outbox' THEN 'Giden Kutusu'
    WHEN 'common.draft' THEN 'Taslak'
    WHEN 'common.trash' THEN 'Çöp Kutusu'
    WHEN 'common.archive' THEN 'Arşiv'
    WHEN 'common.restore' THEN 'Geri Yükle'
    WHEN 'common.duplicate' THEN 'Çoğalt'
    WHEN 'common.move' THEN 'Taşı'
    WHEN 'common.rename' THEN 'Yeniden Adlandır'
    WHEN 'common.properties' THEN 'Özellikler'
    WHEN 'common.permissions' THEN 'İzinler'
    WHEN 'common.settings_general' THEN 'Genel Ayarlar'
    WHEN 'common.advanced' THEN 'Gelişmiş'
    WHEN 'common.help' THEN 'Yardım'
    WHEN 'common.about' THEN 'Hakkında'
    WHEN 'common.version' THEN 'Sürüm'
    WHEN 'common.copyright' THEN 'Telif Hakkı'
    WHEN 'common.terms' THEN 'Şartlar'
    WHEN 'common.privacy' THEN 'Gizlilik'
    WHEN 'common.contact' THEN 'İletişim'
    WHEN 'common.support' THEN 'Destek'
    WHEN 'common.feedback' THEN 'Geri Bildirim'
    WHEN 'common.report' THEN 'Rapor'
    WHEN 'common.bug' THEN 'Hata'
    WHEN 'common.feature' THEN 'Özellik'
    WHEN 'common.request' THEN 'Talep'
    WHEN 'common.suggestion' THEN 'Öneri'
    WHEN 'common.welcome' THEN 'Hoş Geldiniz'
    WHEN 'common.goodbye' THEN 'Güle Güle'
    WHEN 'common.thankyou' THEN 'Teşekkürler'
    WHEN 'common.confirm' THEN 'Onayla'
    WHEN 'common.warning' THEN 'Uyarı'
    WHEN 'common.error' THEN 'Hata'
    WHEN 'common.success' THEN 'Başarılı'
    WHEN 'common.info' THEN 'Bilgi'
END
FROM i18n_keys WHERE `group` = 'common'
ON DUPLICATE KEY UPDATE value = VALUES(value);


-- ------------------------------------------------------------
-- ADMIN NAMESPACE - ENGLISH (lang_id = 1)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_en, CASE key_name
    -- Brands
    WHEN 'admin.brands.title' THEN 'Brands'
    WHEN 'admin.brands.add_brand' THEN 'Add New Brand'
    WHEN 'admin.brands.edit_brand' THEN 'Edit Brand'
    WHEN 'admin.brands.brand_name' THEN 'Brand Name'
    WHEN 'admin.brands.brand_logo' THEN 'Brand Logo'
    WHEN 'admin.brands.brand_description' THEN 'Brand Description'
    WHEN 'admin.brands.brand_url' THEN 'Brand URL'
    WHEN 'admin.brands.brand_order' THEN 'Display Order'
    WHEN 'admin.brands.is_active' THEN 'Is Active'
    WHEN 'admin.brands.created_date' THEN 'Created Date'
    WHEN 'admin.brands.actions' THEN 'Actions'
    WHEN 'admin.brands.no_brands' THEN 'No brands found'
    WHEN 'admin.brands.delete_confirm' THEN 'Are you sure you want to delete this brand?'
    WHEN 'admin.brands.save_success' THEN 'Brand saved successfully'
    WHEN 'admin.brands.delete_success' THEN 'Brand deleted successfully'
    WHEN 'admin.brands.list' THEN 'Brand List'
    
    -- Categories
    WHEN 'admin.categories.title' THEN 'Categories'
    WHEN 'admin.categories.add_category' THEN 'Add New Category'
    WHEN 'admin.categories.edit_category' THEN 'Edit Category'
    WHEN 'admin.categories.category_name' THEN 'Category Name'
    WHEN 'admin.categories.parent_category' THEN 'Parent Category'
    WHEN 'admin.categories.category_image' THEN 'Category Image'
    WHEN 'admin.categories.category_description' THEN 'Category Description'
    WHEN 'admin.categories.category_order' THEN 'Display Order'
    WHEN 'admin.categories.is_active' THEN 'Is Active'
    WHEN 'admin.categories.meta_title' THEN 'Meta Title'
    WHEN 'admin.categories.meta_description' THEN 'Meta Description'
    WHEN 'admin.categories.meta_keywords' THEN 'Meta Keywords'
    WHEN 'admin.categories.slug' THEN 'URL Slug'
    WHEN 'admin.categories.no_categories' THEN 'No categories found'
    WHEN 'admin.categories.select_parent' THEN 'Select Parent Category'
    WHEN 'admin.categories.none' THEN 'None (Top Level)'
    WHEN 'admin.categories.save_success' THEN 'Category saved successfully'
    WHEN 'admin.categories.delete_success' THEN 'Category deleted successfully'
    WHEN 'admin.categories.list' THEN 'Category List'
    
    -- Products
    WHEN 'admin.products.title' THEN 'Products'
    WHEN 'admin.products.add_product' THEN 'Add New Product'
    WHEN 'admin.products.edit_product' THEN 'Edit Product'
    WHEN 'admin.products.product_name' THEN 'Product Name'
    WHEN 'admin.products.product_code' THEN 'Product Code'
    WHEN 'admin.products.sku' THEN 'SKU'
    WHEN 'admin.products.barcode' THEN 'Barcode'
    WHEN 'admin.products.category' THEN 'Category'
    WHEN 'admin.products.brand' THEN 'Brand'
    WHEN 'admin.products.price' THEN 'Price'
    WHEN 'admin.products.cost_price' THEN 'Cost Price'
    WHEN 'admin.products.sale_price' THEN 'Sale Price'
    WHEN 'admin.products.stock_quantity' THEN 'Stock Quantity'
    WHEN 'admin.products.min_stock' THEN 'Minimum Stock'
    WHEN 'admin.products.max_stock' THEN 'Maximum Stock'
    WHEN 'admin.products.description' THEN 'Product Description'
    WHEN 'admin.products.short_description' THEN 'Short Description'
    WHEN 'admin.products.features' THEN 'Product Features'
    WHEN 'admin.products.technical_specs' THEN 'Technical Specifications'
    WHEN 'admin.products.images' THEN 'Product Images'
    WHEN 'admin.products.main_image' THEN 'Main Image'
    WHEN 'admin.products.gallery' THEN 'Image Gallery'
    WHEN 'admin.products.is_active' THEN 'Is Active'
    WHEN 'admin.products.is_featured' THEN 'Featured Product'
    WHEN 'admin.products.is_new' THEN 'New Product'
    WHEN 'admin.products.is_sale' THEN 'On Sale'
    WHEN 'admin.products.meta_title' THEN 'Meta Title'
    WHEN 'admin.products.meta_description' THEN 'Meta Description'
    WHEN 'admin.products.meta_keywords' THEN 'Meta Keywords'
    WHEN 'admin.products.slug' THEN 'URL Slug'
    WHEN 'admin.products.tags' THEN 'Product Tags'
    WHEN 'admin.products.weight' THEN 'Weight'
    WHEN 'admin.products.dimensions' THEN 'Dimensions'
    WHEN 'admin.products.length' THEN 'Length'
    WHEN 'admin.products.width' THEN 'Width'
    WHEN 'admin.products.height' THEN 'Height'
    WHEN 'admin.products.shipping_class' THEN 'Shipping Class'
    WHEN 'admin.products.tax_class' THEN 'Tax Class'
    WHEN 'admin.products.unit' THEN 'Unit'
    WHEN 'admin.products.manufacturer' THEN 'Manufacturer'
    WHEN 'admin.products.warranty' THEN 'Warranty Period'
    WHEN 'admin.products.colors' THEN 'Available Colors'
    WHEN 'admin.products.sizes' THEN 'Available Sizes'
    WHEN 'admin.products.variants' THEN 'Product Variants'
    WHEN 'admin.products.related_products' THEN 'Related Products'
    WHEN 'admin.products.upsell_products' THEN 'Upsell Products'
    WHEN 'admin.products.crosssell_products' THEN 'Cross-sell Products'
    WHEN 'admin.products.reviews' THEN 'Customer Reviews'
    WHEN 'admin.products.rating' THEN 'Average Rating'
    WHEN 'admin.products.review_count' THEN 'Review Count'
    WHEN 'admin.products.stock_status' THEN 'Stock Status'
    WHEN 'admin.products.in_stock' THEN 'In Stock'
    WHEN 'admin.products.out_of_stock' THEN 'Out of Stock'
    WHEN 'admin.products.backorder' THEN 'On Backorder'
    WHEN 'admin.products.preorder' THEN 'Pre-order'
    WHEN 'admin.products.track_inventory' THEN 'Track Inventory'
    WHEN 'admin.products.allow_backorder' THEN 'Allow Backorder'
    WHEN 'admin.products.sold_individually' THEN 'Sold Individually'
    WHEN 'admin.products.purchase_note' THEN 'Purchase Note'
    WHEN 'admin.products.download_files' THEN 'Downloadable Files'
    WHEN 'admin.products.download_limit' THEN 'Download Limit'
    WHEN 'admin.products.download_expiry' THEN 'Download Expiry'
    WHEN 'admin.products.basic_info' THEN 'Basic Information'
    WHEN 'admin.products.pricing' THEN 'Pricing'
    WHEN 'admin.products.inventory' THEN 'Inventory'
    WHEN 'admin.products.shipping' THEN 'Shipping'
    WHEN 'admin.products.attributes' THEN 'Attributes'
    WHEN 'admin.products.seo' THEN 'SEO Settings'
    WHEN 'admin.products.advanced' THEN 'Advanced Settings'
    WHEN 'admin.products.no_products' THEN 'No products found'
    WHEN 'admin.products.save_success' THEN 'Product saved successfully'
    WHEN 'admin.products.delete_success' THEN 'Product deleted successfully'
    WHEN 'admin.products.list' THEN 'Product List'
    WHEN 'admin.products.qrcode' THEN 'QR Code'
    WHEN 'admin.products.generate_qr' THEN 'Generate QR Code'
    WHEN 'admin.products.download_qr' THEN 'Download QR Code'
    WHEN 'admin.products.print_qr' THEN 'Print QR Code'
    WHEN 'admin.products.color_management' THEN 'Color Management'
    WHEN 'admin.products.add_color' THEN 'Add Color'
    WHEN 'admin.products.color_name' THEN 'Color Name'
    WHEN 'admin.products.color_code' THEN 'Color Code'
    WHEN 'admin.products.color_image' THEN 'Color Image'
    
    -- Settings
    WHEN 'admin.settings.title' THEN 'Settings'
    WHEN 'admin.settings.general' THEN 'General Settings'
    WHEN 'admin.settings.site_settings' THEN 'Site Settings'
    WHEN 'admin.settings.site_name' THEN 'Site Name'
    WHEN 'admin.settings.site_title' THEN 'Site Title'
    WHEN 'admin.settings.site_description' THEN 'Site Description'
    WHEN 'admin.settings.site_keywords' THEN 'Site Keywords'
    WHEN 'admin.settings.site_logo' THEN 'Site Logo'
    WHEN 'admin.settings.site_favicon' THEN 'Site Favicon'
    WHEN 'admin.settings.admin_email' THEN 'Admin Email'
    WHEN 'admin.settings.contact_email' THEN 'Contact Email'
    WHEN 'admin.settings.contact_phone' THEN 'Contact Phone'
    WHEN 'admin.settings.contact_address' THEN 'Contact Address'
    WHEN 'admin.settings.timezone' THEN 'Timezone'
    WHEN 'admin.settings.date_format' THEN 'Date Format'
    WHEN 'admin.settings.time_format' THEN 'Time Format'
    WHEN 'admin.settings.currency' THEN 'Currency'
    WHEN 'admin.settings.currency_symbol' THEN 'Currency Symbol'
    WHEN 'admin.settings.currency_position' THEN 'Currency Position'
    WHEN 'admin.settings.decimal_separator' THEN 'Decimal Separator'
    WHEN 'admin.settings.thousand_separator' THEN 'Thousand Separator'
    WHEN 'admin.settings.number_of_decimals' THEN 'Number of Decimals'
    WHEN 'admin.settings.language' THEN 'Default Language'
    WHEN 'admin.settings.maintenance_mode' THEN 'Maintenance Mode'
    WHEN 'admin.settings.maintenance_message' THEN 'Maintenance Message'
    WHEN 'admin.settings.allow_registration' THEN 'Allow User Registration'
    WHEN 'admin.settings.require_email_verification' THEN 'Require Email Verification'
    WHEN 'admin.settings.email_settings' THEN 'Email Settings'
    WHEN 'admin.settings.smtp_host' THEN 'SMTP Host'
    WHEN 'admin.settings.smtp_port' THEN 'SMTP Port'
    WHEN 'admin.settings.smtp_username' THEN 'SMTP Username'
    WHEN 'admin.settings.smtp_password' THEN 'SMTP Password'
    WHEN 'admin.settings.smtp_encryption' THEN 'SMTP Encryption'
    WHEN 'admin.settings.payment_settings' THEN 'Payment Settings'
    WHEN 'admin.settings.payment_methods' THEN 'Payment Methods'
    WHEN 'admin.settings.shipping_settings' THEN 'Shipping Settings'
    WHEN 'admin.settings.shipping_methods' THEN 'Shipping Methods'
    WHEN 'admin.settings.tax_settings' THEN 'Tax Settings'
    WHEN 'admin.settings.enable_tax' THEN 'Enable Tax'
    WHEN 'admin.settings.tax_rate' THEN 'Tax Rate'
    WHEN 'admin.settings.social_media' THEN 'Social Media'
    WHEN 'admin.settings.facebook_url' THEN 'Facebook URL'
    WHEN 'admin.settings.twitter_url' THEN 'Twitter URL'
    WHEN 'admin.settings.instagram_url' THEN 'Instagram URL'
    WHEN 'admin.settings.linkedin_url' THEN 'LinkedIn URL'
    WHEN 'admin.settings.youtube_url' THEN 'YouTube URL'
    WHEN 'admin.settings.analytics' THEN 'Analytics'
    WHEN 'admin.settings.google_analytics' THEN 'Google Analytics ID'
    WHEN 'admin.settings.facebook_pixel' THEN 'Facebook Pixel ID'
    WHEN 'admin.settings.save_success' THEN 'Settings saved successfully'
    WHEN 'admin.settings.cache_cleared' THEN 'Cache cleared successfully'
    WHEN 'admin.settings.clear_cache' THEN 'Clear Cache'
    
    -- Navigation
    WHEN 'admin.nav.dashboard' THEN 'Dashboard'
    WHEN 'admin.nav.products' THEN 'Products'
    WHEN 'admin.nav.categories' THEN 'Categories'
    WHEN 'admin.nav.brands' THEN 'Brands'
    WHEN 'admin.nav.orders' THEN 'Orders'
    WHEN 'admin.nav.customers' THEN 'Customers'
    WHEN 'admin.nav.reports' THEN 'Reports'
    WHEN 'admin.nav.settings' THEN 'Settings'
    WHEN 'admin.nav.media' THEN 'Media Library'
    WHEN 'admin.nav.users' THEN 'Users'
    WHEN 'admin.nav.roles' THEN 'Roles & Permissions'
    WHEN 'admin.nav.appearance' THEN 'Appearance'
    WHEN 'admin.nav.plugins' THEN 'Plugins'
    WHEN 'admin.nav.tools' THEN 'Tools'
    WHEN 'admin.nav.logout' THEN 'Logout'
    WHEN 'admin.nav.view_site' THEN 'View Site'
    WHEN 'admin.nav.samples' THEN 'Samples'
    WHEN 'admin.nav.documents' THEN 'Documents'
    
    -- Customers
    WHEN 'admin.customers.title' THEN 'Customers'
    WHEN 'admin.customers.list' THEN 'Customer List'
    WHEN 'admin.customers.detail' THEN 'Customer Details'
    WHEN 'admin.customers.add_customer' THEN 'Add New Customer'
    WHEN 'admin.customers.edit_customer' THEN 'Edit Customer'
    WHEN 'admin.customers.customer_name' THEN 'Customer Name'
    WHEN 'admin.customers.customer_email' THEN 'Email Address'
    WHEN 'admin.customers.customer_phone' THEN 'Phone Number'
    WHEN 'admin.customers.customer_address' THEN 'Address'
    WHEN 'admin.customers.customer_city' THEN 'City'
    WHEN 'admin.customers.customer_country' THEN 'Country'
    WHEN 'admin.customers.customer_postcode' THEN 'Postal Code'
    WHEN 'admin.customers.registration_date' THEN 'Registration Date'
    WHEN 'admin.customers.last_login' THEN 'Last Login'
    WHEN 'admin.customers.total_orders' THEN 'Total Orders'
    WHEN 'admin.customers.total_spent' THEN 'Total Spent'
    WHEN 'admin.customers.is_active' THEN 'Is Active'
    WHEN 'admin.customers.customer_group' THEN 'Customer Group'
    WHEN 'admin.customers.customer_notes' THEN 'Customer Notes'
    WHEN 'admin.customers.billing_address' THEN 'Billing Address'
    WHEN 'admin.customers.shipping_address' THEN 'Shipping Address'
    WHEN 'admin.customers.order_history' THEN 'Order History'
    WHEN 'admin.customers.no_customers' THEN 'No customers found'
    WHEN 'admin.customers.save_success' THEN 'Customer saved successfully'
    WHEN 'admin.customers.delete_success' THEN 'Customer deleted successfully'
    WHEN 'admin.customers.personal_info' THEN 'Personal Information'
    WHEN 'admin.customers.contact_info' THEN 'Contact Information'
    WHEN 'admin.customers.statistics' THEN 'Customer Statistics'
    
    -- Media Library
    WHEN 'admin.media.title' THEN 'Media Library'
    WHEN 'admin.media.upload' THEN 'Upload Files'
    WHEN 'admin.media.upload_new' THEN 'Upload New File'
    WHEN 'admin.media.select_files' THEN 'Select Files'
    WHEN 'admin.media.drop_files' THEN 'Drop files here or click to upload'
    WHEN 'admin.media.file_name' THEN 'File Name'
    WHEN 'admin.media.file_type' THEN 'File Type'
    WHEN 'admin.media.file_size' THEN 'File Size'
    WHEN 'admin.media.upload_date' THEN 'Upload Date'
    WHEN 'admin.media.file_url' THEN 'File URL'
    WHEN 'admin.media.delete_file' THEN 'Delete File'
    WHEN 'admin.media.delete_confirm' THEN 'Are you sure you want to delete this file?'
    WHEN 'admin.media.delete_success' THEN 'File deleted successfully'
    WHEN 'admin.media.upload_success' THEN 'File uploaded successfully'
    WHEN 'admin.media.no_files' THEN 'No files found'
    WHEN 'admin.media.search_files' THEN 'Search files...'
    WHEN 'admin.media.filter_by_type' THEN 'Filter by type'
    WHEN 'admin.media.all_files' THEN 'All Files'
    WHEN 'admin.media.images' THEN 'Images'
    WHEN 'admin.media.documents' THEN 'Documents'
    WHEN 'admin.media.videos' THEN 'Videos'
    WHEN 'admin.media.audio' THEN 'Audio'
    WHEN 'admin.media.grid_view' THEN 'Grid View'
    WHEN 'admin.media.list_view' THEN 'List View'
    WHEN 'admin.media.select_file' THEN 'Select File'
    WHEN 'admin.media.insert_file' THEN 'Insert File'
    
    -- Samples
    WHEN 'admin.samples.title' THEN 'Samples'
    WHEN 'admin.samples.list' THEN 'Sample List'
    WHEN 'admin.samples.detail' THEN 'Sample Details'
    WHEN 'admin.samples.add_sample' THEN 'Add New Sample'
    WHEN 'admin.samples.edit_sample' THEN 'Edit Sample'
    WHEN 'admin.samples.sample_code' THEN 'Sample Code'
    WHEN 'admin.samples.sample_name' THEN 'Sample Name'
    WHEN 'admin.samples.sample_type' THEN 'Sample Type'
    WHEN 'admin.samples.sample_status' THEN 'Sample Status'
    WHEN 'admin.samples.request_date' THEN 'Request Date'
    WHEN 'admin.samples.sent_date' THEN 'Sent Date'
    WHEN 'admin.samples.customer' THEN 'Customer'
    WHEN 'admin.samples.product' THEN 'Product'
    WHEN 'admin.samples.quantity' THEN 'Quantity'
    WHEN 'admin.samples.notes' THEN 'Notes'
    WHEN 'admin.samples.tracking_number' THEN 'Tracking Number'
    WHEN 'admin.samples.shipping_address' THEN 'Shipping Address'
    WHEN 'admin.samples.status_pending' THEN 'Pending'
    WHEN 'admin.samples.status_approved' THEN 'Approved'
    WHEN 'admin.samples.status_sent' THEN 'Sent'
    WHEN 'admin.samples.status_received' THEN 'Received'
    WHEN 'admin.samples.status_cancelled' THEN 'Cancelled'
    WHEN 'admin.samples.no_samples' THEN 'No samples found'
    WHEN 'admin.samples.save_success' THEN 'Sample saved successfully'
    WHEN 'admin.samples.delete_success' THEN 'Sample deleted successfully'
    
    -- Documents
    WHEN 'admin.documents.title' THEN 'Documents'
    WHEN 'admin.documents.list' THEN 'Document List'
    WHEN 'admin.documents.upload' THEN 'Upload Document'
    WHEN 'admin.documents.upload_new' THEN 'Upload New Document'
    WHEN 'admin.documents.document_name' THEN 'Document Name'
    WHEN 'admin.documents.document_type' THEN 'Document Type'
    WHEN 'admin.documents.document_category' THEN 'Document Category'
    WHEN 'admin.documents.document_description' THEN 'Description'
    WHEN 'admin.documents.upload_date' THEN 'Upload Date'
    WHEN 'admin.documents.file_size' THEN 'File Size'
    WHEN 'admin.documents.uploaded_by' THEN 'Uploaded By'
    WHEN 'admin.documents.download' THEN 'Download'
    WHEN 'admin.documents.preview' THEN 'Preview'
    WHEN 'admin.documents.delete_confirm' THEN 'Are you sure you want to delete this document?'
    WHEN 'admin.documents.delete_success' THEN 'Document deleted successfully'
    WHEN 'admin.documents.upload_success' THEN 'Document uploaded successfully'
    WHEN 'admin.documents.no_documents' THEN 'No documents found'
    WHEN 'admin.documents.select_file' THEN 'Select File'
    WHEN 'admin.documents.allowed_types' THEN 'Allowed file types'
    WHEN 'admin.documents.max_size' THEN 'Maximum file size'
END
FROM i18n_keys WHERE `group` = 'admin'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ------------------------------------------------------------
-- ADMIN NAMESPACE - TURKISH (lang_id = 2)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_tr, CASE key_name
    -- Brands
    WHEN 'admin.brands.title' THEN 'Markalar'
    WHEN 'admin.brands.add_brand' THEN 'Yeni Marka Ekle'
    WHEN 'admin.brands.edit_brand' THEN 'Marka Düzenle'
    WHEN 'admin.brands.brand_name' THEN 'Marka Adı'
    WHEN 'admin.brands.brand_logo' THEN 'Marka Logosu'
    WHEN 'admin.brands.brand_description' THEN 'Marka Açıklaması'
    WHEN 'admin.brands.brand_url' THEN 'Marka URL'
    WHEN 'admin.brands.brand_order' THEN 'Görüntüleme Sırası'
    WHEN 'admin.brands.is_active' THEN 'Aktif Mi'
    WHEN 'admin.brands.created_date' THEN 'Oluşturulma Tarihi'
    WHEN 'admin.brands.actions' THEN 'İşlemler'
    WHEN 'admin.brands.no_brands' THEN 'Marka bulunamadı'
    WHEN 'admin.brands.delete_confirm' THEN 'Bu markayı silmek istediğinizden emin misiniz?'
    WHEN 'admin.brands.save_success' THEN 'Marka başarıyla kaydedildi'
    WHEN 'admin.brands.delete_success' THEN 'Marka başarıyla silindi'
    WHEN 'admin.brands.list' THEN 'Marka Listesi'
    
    -- Categories
    WHEN 'admin.categories.title' THEN 'Kategoriler'
    WHEN 'admin.categories.add_category' THEN 'Yeni Kategori Ekle'
    WHEN 'admin.categories.edit_category' THEN 'Kategori Düzenle'
    WHEN 'admin.categories.category_name' THEN 'Kategori Adı'
    WHEN 'admin.categories.parent_category' THEN 'Üst Kategori'
    WHEN 'admin.categories.category_image' THEN 'Kategori Resmi'
    WHEN 'admin.categories.category_description' THEN 'Kategori Açıklaması'
    WHEN 'admin.categories.category_order' THEN 'Görüntüleme Sırası'
    WHEN 'admin.categories.is_active' THEN 'Aktif Mi'
    WHEN 'admin.categories.meta_title' THEN 'Meta Başlık'
    WHEN 'admin.categories.meta_description' THEN 'Meta Açıklama'
    WHEN 'admin.categories.meta_keywords' THEN 'Meta Anahtar Kelimeler'
    WHEN 'admin.categories.slug' THEN 'URL Slug'
    WHEN 'admin.categories.no_categories' THEN 'Kategori bulunamadı'
    WHEN 'admin.categories.select_parent' THEN 'Üst Kategori Seç'
    WHEN 'admin.categories.none' THEN 'Yok (Üst Seviye)'
    WHEN 'admin.categories.save_success' THEN 'Kategori başarıyla kaydedildi'
    WHEN 'admin.categories.delete_success' THEN 'Kategori başarıyla silindi'
    WHEN 'admin.categories.list' THEN 'Kategori Listesi'
    
    -- Products
    WHEN 'admin.products.title' THEN 'Ürünler'
    WHEN 'admin.products.add_product' THEN 'Yeni Ürün Ekle'
    WHEN 'admin.products.edit_product' THEN 'Ürün Düzenle'
    WHEN 'admin.products.product_name' THEN 'Ürün Adı'
    WHEN 'admin.products.product_code' THEN 'Ürün Kodu'
    WHEN 'admin.products.sku' THEN 'Stok Kodu'
    WHEN 'admin.products.barcode' THEN 'Barkod'
    WHEN 'admin.products.category' THEN 'Kategori'
    WHEN 'admin.products.brand' THEN 'Marka'
    WHEN 'admin.products.price' THEN 'Fiyat'
    WHEN 'admin.products.cost_price' THEN 'Maliyet Fiyatı'
    WHEN 'admin.products.sale_price' THEN 'İndirimli Fiyat'
    WHEN 'admin.products.stock_quantity' THEN 'Stok Miktarı'
    WHEN 'admin.products.min_stock' THEN 'Minimum Stok'
    WHEN 'admin.products.max_stock' THEN 'Maksimum Stok'
    WHEN 'admin.products.description' THEN 'Ürün Açıklaması'
    WHEN 'admin.products.short_description' THEN 'Kısa Açıklama'
    WHEN 'admin.products.features' THEN 'Ürün Özellikleri'
    WHEN 'admin.products.technical_specs' THEN 'Teknik Özellikler'
    WHEN 'admin.products.images' THEN 'Ürün Resimleri'
    WHEN 'admin.products.main_image' THEN 'Ana Resim'
    WHEN 'admin.products.gallery' THEN 'Resim Galerisi'
    WHEN 'admin.products.is_active' THEN 'Aktif Mi'
    WHEN 'admin.products.is_featured' THEN 'Öne Çıkan Ürün'
    WHEN 'admin.products.is_new' THEN 'Yeni Ürün'
    WHEN 'admin.products.is_sale' THEN 'İndirimde'
    WHEN 'admin.products.meta_title' THEN 'Meta Başlık'
    WHEN 'admin.products.meta_description' THEN 'Meta Açıklama'
    WHEN 'admin.products.meta_keywords' THEN 'Meta Anahtar Kelimeler'
    WHEN 'admin.products.slug' THEN 'URL Slug'
    WHEN 'admin.products.tags' THEN 'Ürün Etiketleri'
    WHEN 'admin.products.weight' THEN 'Ağırlık'
    WHEN 'admin.products.dimensions' THEN 'Boyutlar'
    WHEN 'admin.products.length' THEN 'Uzunluk'
    WHEN 'admin.products.width' THEN 'Genişlik'
    WHEN 'admin.products.height' THEN 'Yükseklik'
    WHEN 'admin.products.shipping_class' THEN 'Kargo Sınıfı'
    WHEN 'admin.products.tax_class' THEN 'Vergi Sınıfı'
    WHEN 'admin.products.unit' THEN 'Birim'
    WHEN 'admin.products.manufacturer' THEN 'Üretici'
    WHEN 'admin.products.warranty' THEN 'Garanti Süresi'
    WHEN 'admin.products.colors' THEN 'Mevcut Renkler'
    WHEN 'admin.products.sizes' THEN 'Mevcut Bedenler'
    WHEN 'admin.products.variants' THEN 'Ürün Varyantları'
    WHEN 'admin.products.related_products' THEN 'İlgili Ürünler'
    WHEN 'admin.products.upsell_products' THEN 'Üst Satış Ürünleri'
    WHEN 'admin.products.crosssell_products' THEN 'Çapraz Satış Ürünleri'
    WHEN 'admin.products.reviews' THEN 'Müşteri Yorumları'
    WHEN 'admin.products.rating' THEN 'Ortalama Puan'
    WHEN 'admin.products.review_count' THEN 'Yorum Sayısı'
    WHEN 'admin.products.stock_status' THEN 'Stok Durumu'
    WHEN 'admin.products.in_stock' THEN 'Stokta'
    WHEN 'admin.products.out_of_stock' THEN 'Stokta Yok'
    WHEN 'admin.products.backorder' THEN 'Ön Siparişte'
    WHEN 'admin.products.preorder' THEN 'Ön Sipariş'
    WHEN 'admin.products.track_inventory' THEN 'Stok Takibi'
    WHEN 'admin.products.allow_backorder' THEN 'Ön Siparişe İzin Ver'
    WHEN 'admin.products.sold_individually' THEN 'Tekil Satış'
    WHEN 'admin.products.purchase_note' THEN 'Satın Alma Notu'
    WHEN 'admin.products.download_files' THEN 'İndirilebilir Dosyalar'
    WHEN 'admin.products.download_limit' THEN 'İndirme Limiti'
    WHEN 'admin.products.download_expiry' THEN 'İndirme Süresi'
    WHEN 'admin.products.basic_info' THEN 'Temel Bilgiler'
    WHEN 'admin.products.pricing' THEN 'Fiyatlandırma'
    WHEN 'admin.products.inventory' THEN 'Envanter'
    WHEN 'admin.products.shipping' THEN 'Kargo'
    WHEN 'admin.products.attributes' THEN 'Özellikler'
    WHEN 'admin.products.seo' THEN 'SEO Ayarları'
    WHEN 'admin.products.advanced' THEN 'Gelişmiş Ayarlar'
    WHEN 'admin.products.no_products' THEN 'Ürün bulunamadı'
    WHEN 'admin.products.save_success' THEN 'Ürün başarıyla kaydedildi'
    WHEN 'admin.products.delete_success' THEN 'Ürün başarıyla silindi'
    WHEN 'admin.products.list' THEN 'Ürün Listesi'
    WHEN 'admin.products.qrcode' THEN 'QR Kod'
    WHEN 'admin.products.generate_qr' THEN 'QR Kod Oluştur'
    WHEN 'admin.products.download_qr' THEN 'QR Kod İndir'
    WHEN 'admin.products.print_qr' THEN 'QR Kod Yazdır'
    WHEN 'admin.products.color_management' THEN 'Renk Yönetimi'
    WHEN 'admin.products.add_color' THEN 'Renk Ekle'
    WHEN 'admin.products.color_name' THEN 'Renk Adı'
    WHEN 'admin.products.color_code' THEN 'Renk Kodu'
    WHEN 'admin.products.color_image' THEN 'Renk Resmi'
    
    -- Settings
    WHEN 'admin.settings.title' THEN 'Ayarlar'
    WHEN 'admin.settings.general' THEN 'Genel Ayarlar'
    WHEN 'admin.settings.site_settings' THEN 'Site Ayarları'
    WHEN 'admin.settings.site_name' THEN 'Site Adı'
    WHEN 'admin.settings.site_title' THEN 'Site Başlığı'
    WHEN 'admin.settings.site_description' THEN 'Site Açıklaması'
    WHEN 'admin.settings.site_keywords' THEN 'Site Anahtar Kelimeleri'
    WHEN 'admin.settings.site_logo' THEN 'Site Logosu'
    WHEN 'admin.settings.site_favicon' THEN 'Site Favicon'
    WHEN 'admin.settings.admin_email' THEN 'Yönetici E-posta'
    WHEN 'admin.settings.contact_email' THEN 'İletişim E-posta'
    WHEN 'admin.settings.contact_phone' THEN 'İletişim Telefonu'
    WHEN 'admin.settings.contact_address' THEN 'İletişim Adresi'
    WHEN 'admin.settings.timezone' THEN 'Zaman Dilimi'
    WHEN 'admin.settings.date_format' THEN 'Tarih Formatı'
    WHEN 'admin.settings.time_format' THEN 'Saat Formatı'
    WHEN 'admin.settings.currency' THEN 'Para Birimi'
    WHEN 'admin.settings.currency_symbol' THEN 'Para Birimi Sembolü'
    WHEN 'admin.settings.currency_position' THEN 'Para Birimi Konumu'
    WHEN 'admin.settings.decimal_separator' THEN 'Ondalık Ayırıcı'
    WHEN 'admin.settings.thousand_separator' THEN 'Binlik Ayırıcı'
    WHEN 'admin.settings.number_of_decimals' THEN 'Ondalık Basamak Sayısı'
    WHEN 'admin.settings.language' THEN 'Varsayılan Dil'
    WHEN 'admin.settings.maintenance_mode' THEN 'Bakım Modu'
    WHEN 'admin.settings.maintenance_message' THEN 'Bakım Mesajı'
    WHEN 'admin.settings.allow_registration' THEN 'Kullanıcı Kaydına İzin Ver'
    WHEN 'admin.settings.require_email_verification' THEN 'E-posta Doğrulaması Gerekli'
    WHEN 'admin.settings.email_settings' THEN 'E-posta Ayarları'
    WHEN 'admin.settings.smtp_host' THEN 'SMTP Host'
    WHEN 'admin.settings.smtp_port' THEN 'SMTP Port'
    WHEN 'admin.settings.smtp_username' THEN 'SMTP Kullanıcı Adı'
    WHEN 'admin.settings.smtp_password' THEN 'SMTP Şifre'
    WHEN 'admin.settings.smtp_encryption' THEN 'SMTP Şifreleme'
    WHEN 'admin.settings.payment_settings' THEN 'Ödeme Ayarları'
    WHEN 'admin.settings.payment_methods' THEN 'Ödeme Yöntemleri'
    WHEN 'admin.settings.shipping_settings' THEN 'Kargo Ayarları'
    WHEN 'admin.settings.shipping_methods' THEN 'Kargo Yöntemleri'
    WHEN 'admin.settings.tax_settings' THEN 'Vergi Ayarları'
    WHEN 'admin.settings.enable_tax' THEN 'Vergiyi Etkinleştir'
    WHEN 'admin.settings.tax_rate' THEN 'Vergi Oranı'
    WHEN 'admin.settings.social_media' THEN 'Sosyal Medya'
    WHEN 'admin.settings.facebook_url' THEN 'Facebook URL'
    WHEN 'admin.settings.twitter_url' THEN 'Twitter URL'
    WHEN 'admin.settings.instagram_url' THEN 'Instagram URL'
    WHEN 'admin.settings.linkedin_url' THEN 'LinkedIn URL'
    WHEN 'admin.settings.youtube_url' THEN 'YouTube URL'
    WHEN 'admin.settings.analytics' THEN 'Analitik'
    WHEN 'admin.settings.google_analytics' THEN 'Google Analytics ID'
    WHEN 'admin.settings.facebook_pixel' THEN 'Facebook Pixel ID'
    WHEN 'admin.settings.save_success' THEN 'Ayarlar başarıyla kaydedildi'
    WHEN 'admin.settings.cache_cleared' THEN 'Önbellek başarıyla temizlendi'
    WHEN 'admin.settings.clear_cache' THEN 'Önbelleği Temizle'
    
    -- Navigation
    WHEN 'admin.nav.dashboard' THEN 'Kontrol Paneli'
    WHEN 'admin.nav.products' THEN 'Ürünler'
    WHEN 'admin.nav.categories' THEN 'Kategoriler'
    WHEN 'admin.nav.brands' THEN 'Markalar'
    WHEN 'admin.nav.orders' THEN 'Siparişler'
    WHEN 'admin.nav.customers' THEN 'Müşteriler'
    WHEN 'admin.nav.reports' THEN 'Raporlar'
    WHEN 'admin.nav.settings' THEN 'Ayarlar'
    WHEN 'admin.nav.media' THEN 'Medya Kütüphanesi'
    WHEN 'admin.nav.users' THEN 'Kullanıcılar'
    WHEN 'admin.nav.roles' THEN 'Roller ve İzinler'
    WHEN 'admin.nav.appearance' THEN 'Görünüm'
    WHEN 'admin.nav.plugins' THEN 'Eklentiler'
    WHEN 'admin.nav.tools' THEN 'Araçlar'
    WHEN 'admin.nav.logout' THEN 'Çıkış'
    WHEN 'admin.nav.view_site' THEN 'Siteyi Görüntüle'
    WHEN 'admin.nav.samples' THEN 'Numuneler'
    WHEN 'admin.nav.documents' THEN 'Dökümanlar'
    
    -- Customers
    WHEN 'admin.customers.title' THEN 'Müşteriler'
    WHEN 'admin.customers.list' THEN 'Müşteri Listesi'
    WHEN 'admin.customers.detail' THEN 'Müşteri Detayları'
    WHEN 'admin.customers.add_customer' THEN 'Yeni Müşteri Ekle'
    WHEN 'admin.customers.edit_customer' THEN 'Müşteri Düzenle'
    WHEN 'admin.customers.customer_name' THEN 'Müşteri Adı'
    WHEN 'admin.customers.customer_email' THEN 'E-posta Adresi'
    WHEN 'admin.customers.customer_phone' THEN 'Telefon Numarası'
    WHEN 'admin.customers.customer_address' THEN 'Adres'
    WHEN 'admin.customers.customer_city' THEN 'Şehir'
    WHEN 'admin.customers.customer_country' THEN 'Ülke'
    WHEN 'admin.customers.customer_postcode' THEN 'Posta Kodu'
    WHEN 'admin.customers.registration_date' THEN 'Kayıt Tarihi'
    WHEN 'admin.customers.last_login' THEN 'Son Giriş'
    WHEN 'admin.customers.total_orders' THEN 'Toplam Sipariş'
    WHEN 'admin.customers.total_spent' THEN 'Toplam Harcama'
    WHEN 'admin.customers.is_active' THEN 'Aktif Mi'
    WHEN 'admin.customers.customer_group' THEN 'Müşteri Grubu'
    WHEN 'admin.customers.customer_notes' THEN 'Müşteri Notları'
    WHEN 'admin.customers.billing_address' THEN 'Fatura Adresi'
    WHEN 'admin.customers.shipping_address' THEN 'Teslimat Adresi'
    WHEN 'admin.customers.order_history' THEN 'Sipariş Geçmişi'
    WHEN 'admin.customers.no_customers' THEN 'Müşteri bulunamadı'
    WHEN 'admin.customers.save_success' THEN 'Müşteri başarıyla kaydedildi'
    WHEN 'admin.customers.delete_success' THEN 'Müşteri başarıyla silindi'
    WHEN 'admin.customers.personal_info' THEN 'Kişisel Bilgiler'
    WHEN 'admin.customers.contact_info' THEN 'İletişim Bilgileri'
    WHEN 'admin.customers.statistics' THEN 'Müşteri İstatistikleri'
    
    -- Media Library
    WHEN 'admin.media.title' THEN 'Medya Kütüphanesi'
    WHEN 'admin.media.upload' THEN 'Dosya Yükle'
    WHEN 'admin.media.upload_new' THEN 'Yeni Dosya Yükle'
    WHEN 'admin.media.select_files' THEN 'Dosya Seç'
    WHEN 'admin.media.drop_files' THEN 'Dosyaları buraya sürükleyin veya yüklemek için tıklayın'
    WHEN 'admin.media.file_name' THEN 'Dosya Adı'
    WHEN 'admin.media.file_type' THEN 'Dosya Tipi'
    WHEN 'admin.media.file_size' THEN 'Dosya Boyutu'
    WHEN 'admin.media.upload_date' THEN 'Yüklenme Tarihi'
    WHEN 'admin.media.file_url' THEN 'Dosya URL'
    WHEN 'admin.media.delete_file' THEN 'Dosyayı Sil'
    WHEN 'admin.media.delete_confirm' THEN 'Bu dosyayı silmek istediğinizden emin misiniz?'
    WHEN 'admin.media.delete_success' THEN 'Dosya başarıyla silindi'
    WHEN 'admin.media.upload_success' THEN 'Dosya başarıyla yüklendi'
    WHEN 'admin.media.no_files' THEN 'Dosya bulunamadı'
    WHEN 'admin.media.search_files' THEN 'Dosya ara...'
    WHEN 'admin.media.filter_by_type' THEN 'Tipine göre filtrele'
    WHEN 'admin.media.all_files' THEN 'Tüm Dosyalar'
    WHEN 'admin.media.images' THEN 'Resimler'
    WHEN 'admin.media.documents' THEN 'Dökümanlar'
    WHEN 'admin.media.videos' THEN 'Videolar'
    WHEN 'admin.media.audio' THEN 'Ses Dosyaları'
    WHEN 'admin.media.grid_view' THEN 'Izgara Görünümü'
    WHEN 'admin.media.list_view' THEN 'Liste Görünümü'
    WHEN 'admin.media.select_file' THEN 'Dosya Seç'
    WHEN 'admin.media.insert_file' THEN 'Dosya Ekle'
    
    -- Samples
    WHEN 'admin.samples.title' THEN 'Numuneler'
    WHEN 'admin.samples.list' THEN 'Numune Listesi'
    WHEN 'admin.samples.detail' THEN 'Numune Detayları'
    WHEN 'admin.samples.add_sample' THEN 'Yeni Numune Ekle'
    WHEN 'admin.samples.edit_sample' THEN 'Numune Düzenle'
    WHEN 'admin.samples.sample_code' THEN 'Numune Kodu'
    WHEN 'admin.samples.sample_name' THEN 'Numune Adı'
    WHEN 'admin.samples.sample_type' THEN 'Numune Tipi'
    WHEN 'admin.samples.sample_status' THEN 'Numune Durumu'
    WHEN 'admin.samples.request_date' THEN 'Talep Tarihi'
    WHEN 'admin.samples.sent_date' THEN 'Gönderim Tarihi'
    WHEN 'admin.samples.customer' THEN 'Müşteri'
    WHEN 'admin.samples.product' THEN 'Ürün'
    WHEN 'admin.samples.quantity' THEN 'Miktar'
    WHEN 'admin.samples.notes' THEN 'Notlar'
    WHEN 'admin.samples.tracking_number' THEN 'Takip Numarası'
    WHEN 'admin.samples.shipping_address' THEN 'Kargo Adresi'
    WHEN 'admin.samples.status_pending' THEN 'Beklemede'
    WHEN 'admin.samples.status_approved' THEN 'Onaylandı'
    WHEN 'admin.samples.status_sent' THEN 'Gönderildi'
    WHEN 'admin.samples.status_received' THEN 'Alındı'
    WHEN 'admin.samples.status_cancelled' THEN 'İptal Edildi'
    WHEN 'admin.samples.no_samples' THEN 'Numune bulunamadı'
    WHEN 'admin.samples.save_success' THEN 'Numune başarıyla kaydedildi'
    WHEN 'admin.samples.delete_success' THEN 'Numune başarıyla silindi'
    
    -- Documents
    WHEN 'admin.documents.title' THEN 'Dökümanlar'
    WHEN 'admin.documents.list' THEN 'Döküman Listesi'
    WHEN 'admin.documents.upload' THEN 'Döküman Yükle'
    WHEN 'admin.documents.upload_new' THEN 'Yeni Döküman Yükle'
    WHEN 'admin.documents.document_name' THEN 'Döküman Adı'
    WHEN 'admin.documents.document_type' THEN 'Döküman Tipi'
    WHEN 'admin.documents.document_category' THEN 'Döküman Kategorisi'
    WHEN 'admin.documents.document_description' THEN 'Açıklama'
    WHEN 'admin.documents.upload_date' THEN 'Yüklenme Tarihi'
    WHEN 'admin.documents.file_size' THEN 'Dosya Boyutu'
    WHEN 'admin.documents.uploaded_by' THEN 'Yükleyen'
    WHEN 'admin.documents.download' THEN 'İndir'
    WHEN 'admin.documents.preview' THEN 'Önizle'
    WHEN 'admin.documents.delete_confirm' THEN 'Bu dökümanı silmek istediğinizden emin misiniz?'
    WHEN 'admin.documents.delete_success' THEN 'Döküman başarıyla silindi'
    WHEN 'admin.documents.upload_success' THEN 'Döküman başarıyla yüklendi'
    WHEN 'admin.documents.no_documents' THEN 'Döküman bulunamadı'
    WHEN 'admin.documents.select_file' THEN 'Dosya Seç'
    WHEN 'admin.documents.allowed_types' THEN 'İzin verilen dosya tipleri'
    WHEN 'admin.documents.max_size' THEN 'Maksimum dosya boyutu'
END
FROM i18n_keys WHERE `group` = 'admin'
ON DUPLICATE KEY UPDATE value = VALUES(value);


-- ------------------------------------------------------------
-- CART NAMESPACE - ENGLISH (lang_id = 1)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_en, CASE key_name
    WHEN 'cart.title' THEN 'Shopping Cart'
    WHEN 'cart.empty' THEN 'Your cart is empty'
    WHEN 'cart.continue_shopping' THEN 'Continue Shopping'
    WHEN 'cart.proceed_checkout' THEN 'Proceed to Checkout'
    WHEN 'cart.update_cart' THEN 'Update Cart'
    WHEN 'cart.item' THEN 'Item'
    WHEN 'cart.items' THEN 'Items'
    WHEN 'cart.remove' THEN 'Remove'
    WHEN 'cart.quantity' THEN 'Quantity'
    WHEN 'cart.subtotal' THEN 'Subtotal'
    WHEN 'cart.total' THEN 'Total'
    WHEN 'cart.shipping' THEN 'Shipping'
    WHEN 'cart.tax' THEN 'Tax'
    WHEN 'cart.discount' THEN 'Discount'
    WHEN 'cart.coupon_code' THEN 'Coupon Code'
    WHEN 'cart.apply_coupon' THEN 'Apply Coupon'
    WHEN 'cart.remove_coupon' THEN 'Remove Coupon'
    WHEN 'cart.coupon_applied' THEN 'Coupon applied successfully'
    WHEN 'cart.invalid_coupon' THEN 'Invalid coupon code'
    WHEN 'cart.item_removed' THEN 'Item removed from cart'
    WHEN 'cart.cart_updated' THEN 'Cart updated successfully'
END
FROM i18n_keys WHERE `group` = 'cart'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ------------------------------------------------------------
-- CART NAMESPACE - TURKISH (lang_id = 2)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_tr, CASE key_name
    WHEN 'cart.title' THEN 'Alışveriş Sepeti'
    WHEN 'cart.empty' THEN 'Sepetiniz boş'
    WHEN 'cart.continue_shopping' THEN 'Alışverişe Devam Et'
    WHEN 'cart.proceed_checkout' THEN 'Ödemeye Geç'
    WHEN 'cart.update_cart' THEN 'Sepeti Güncelle'
    WHEN 'cart.item' THEN 'Ürün'
    WHEN 'cart.items' THEN 'Ürünler'
    WHEN 'cart.remove' THEN 'Kaldır'
    WHEN 'cart.quantity' THEN 'Miktar'
    WHEN 'cart.subtotal' THEN 'Ara Toplam'
    WHEN 'cart.total' THEN 'Toplam'
    WHEN 'cart.shipping' THEN 'Kargo'
    WHEN 'cart.tax' THEN 'Vergi'
    WHEN 'cart.discount' THEN 'İndirim'
    WHEN 'cart.coupon_code' THEN 'Kupon Kodu'
    WHEN 'cart.apply_coupon' THEN 'Kuponu Uygula'
    WHEN 'cart.remove_coupon' THEN 'Kuponu Kaldır'
    WHEN 'cart.coupon_applied' THEN 'Kupon başarıyla uygulandı'
    WHEN 'cart.invalid_coupon' THEN 'Geçersiz kupon kodu'
    WHEN 'cart.item_removed' THEN 'Ürün sepetten kaldırıldı'
    WHEN 'cart.cart_updated' THEN 'Sepet başarıyla güncellendi'
END
FROM i18n_keys WHERE `group` = 'cart'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ------------------------------------------------------------
-- CHECKOUT NAMESPACE - ENGLISH (lang_id = 1)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_en, CASE key_name
    WHEN 'checkout.title' THEN 'Checkout'
    WHEN 'checkout.billing_details' THEN 'Billing Details'
    WHEN 'checkout.shipping_details' THEN 'Shipping Details'
    WHEN 'checkout.payment_method' THEN 'Payment Method'
    WHEN 'checkout.order_summary' THEN 'Order Summary'
    WHEN 'checkout.first_name' THEN 'First Name'
    WHEN 'checkout.last_name' THEN 'Last Name'
    WHEN 'checkout.email' THEN 'Email Address'
    WHEN 'checkout.phone' THEN 'Phone Number'
    WHEN 'checkout.address' THEN 'Street Address'
    WHEN 'checkout.city' THEN 'City'
    WHEN 'checkout.state' THEN 'State / Province'
    WHEN 'checkout.postcode' THEN 'Postal Code'
    WHEN 'checkout.country' THEN 'Country'
    WHEN 'checkout.same_as_billing' THEN 'Same as billing address'
    WHEN 'checkout.order_notes' THEN 'Order Notes'
    WHEN 'checkout.order_notes_placeholder' THEN 'Notes about your order, e.g. special notes for delivery'
    WHEN 'checkout.place_order' THEN 'Place Order'
    WHEN 'checkout.processing' THEN 'Processing your order...'
    WHEN 'checkout.order_received' THEN 'Order Received'
    WHEN 'checkout.thank_you' THEN 'Thank you for your order!'
    WHEN 'checkout.order_number' THEN 'Order Number'
    WHEN 'checkout.order_date' THEN 'Order Date'
    WHEN 'checkout.payment_status' THEN 'Payment Status'
    WHEN 'checkout.shipping_method' THEN 'Shipping Method'
    WHEN 'checkout.payment_pending' THEN 'Payment Pending'
    WHEN 'checkout.payment_completed' THEN 'Payment Completed'
    WHEN 'checkout.payment_failed' THEN 'Payment Failed'
    WHEN 'checkout.required_field' THEN 'This field is required'
    WHEN 'checkout.invalid_email' THEN 'Please enter a valid email address'
    WHEN 'checkout.invalid_phone' THEN 'Please enter a valid phone number'
    WHEN 'checkout.select_payment_method' THEN 'Please select a payment method'
    WHEN 'checkout.select_shipping_method' THEN 'Please select a shipping method'
    WHEN 'checkout.terms_conditions' THEN 'I have read and agree to the terms and conditions'
    WHEN 'checkout.accept_terms' THEN 'You must accept the terms and conditions'
    WHEN 'checkout.order_confirmation' THEN 'Order Confirmation'
    WHEN 'checkout.order_details' THEN 'Order Details'
    WHEN 'checkout.customer_details' THEN 'Customer Details'
    WHEN 'checkout.payment_details' THEN 'Payment Details'
    WHEN 'checkout.success_message' THEN 'Your order has been received and is being processed'
    WHEN 'checkout.success_email' THEN 'A confirmation email has been sent to your email address'
END
FROM i18n_keys WHERE `group` = 'checkout'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ------------------------------------------------------------
-- CHECKOUT NAMESPACE - TURKISH (lang_id = 2)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_tr, CASE key_name
    WHEN 'checkout.title' THEN 'Ödeme'
    WHEN 'checkout.billing_details' THEN 'Fatura Bilgileri'
    WHEN 'checkout.shipping_details' THEN 'Teslimat Bilgileri'
    WHEN 'checkout.payment_method' THEN 'Ödeme Yöntemi'
    WHEN 'checkout.order_summary' THEN 'Sipariş Özeti'
    WHEN 'checkout.first_name' THEN 'Ad'
    WHEN 'checkout.last_name' THEN 'Soyad'
    WHEN 'checkout.email' THEN 'E-posta Adresi'
    WHEN 'checkout.phone' THEN 'Telefon Numarası'
    WHEN 'checkout.address' THEN 'Sokak Adresi'
    WHEN 'checkout.city' THEN 'Şehir'
    WHEN 'checkout.state' THEN 'İl / Eyalet'
    WHEN 'checkout.postcode' THEN 'Posta Kodu'
    WHEN 'checkout.country' THEN 'Ülke'
    WHEN 'checkout.same_as_billing' THEN 'Fatura adresi ile aynı'
    WHEN 'checkout.order_notes' THEN 'Sipariş Notları'
    WHEN 'checkout.order_notes_placeholder' THEN 'Siparişinizle ilgili notlar, örn. teslimat için özel notlar'
    WHEN 'checkout.place_order' THEN 'Siparişi Tamamla'
    WHEN 'checkout.processing' THEN 'Siparişiniz işleniyor...'
    WHEN 'checkout.order_received' THEN 'Sipariş Alındı'
    WHEN 'checkout.thank_you' THEN 'Siparişiniz için teşekkür ederiz!'
    WHEN 'checkout.order_number' THEN 'Sipariş Numarası'
    WHEN 'checkout.order_date' THEN 'Sipariş Tarihi'
    WHEN 'checkout.payment_status' THEN 'Ödeme Durumu'
    WHEN 'checkout.shipping_method' THEN 'Kargo Yöntemi'
    WHEN 'checkout.payment_pending' THEN 'Ödeme Beklemede'
    WHEN 'checkout.payment_completed' THEN 'Ödeme Tamamlandı'
    WHEN 'checkout.payment_failed' THEN 'Ödeme Başarısız'
    WHEN 'checkout.required_field' THEN 'Bu alan zorunludur'
    WHEN 'checkout.invalid_email' THEN 'Lütfen geçerli bir e-posta adresi girin'
    WHEN 'checkout.invalid_phone' THEN 'Lütfen geçerli bir telefon numarası girin'
    WHEN 'checkout.select_payment_method' THEN 'Lütfen bir ödeme yöntemi seçin'
    WHEN 'checkout.select_shipping_method' THEN 'Lütfen bir kargo yöntemi seçin'
    WHEN 'checkout.terms_conditions' THEN 'Şartlar ve koşulları okudum ve kabul ediyorum'
    WHEN 'checkout.accept_terms' THEN 'Şartlar ve koşulları kabul etmelisiniz'
    WHEN 'checkout.order_confirmation' THEN 'Sipariş Onayı'
    WHEN 'checkout.order_details' THEN 'Sipariş Detayları'
    WHEN 'checkout.customer_details' THEN 'Müşteri Detayları'
    WHEN 'checkout.payment_details' THEN 'Ödeme Detayları'
    WHEN 'checkout.success_message' THEN 'Siparişiniz alındı ve işleniyor'
    WHEN 'checkout.success_email' THEN 'E-posta adresinize bir onay e-postası gönderildi'
END
FROM i18n_keys WHERE `group` = 'checkout'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ------------------------------------------------------------
-- STATUS NAMESPACE - ENGLISH (lang_id = 1)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_en, CASE key_name
    WHEN 'status.active' THEN 'Active'
    WHEN 'status.inactive' THEN 'Inactive'
    WHEN 'status.pending' THEN 'Pending'
    WHEN 'status.approved' THEN 'Approved'
    WHEN 'status.rejected' THEN 'Rejected'
    WHEN 'status.published' THEN 'Published'
    WHEN 'status.draft' THEN 'Draft'
    WHEN 'status.archived' THEN 'Archived'
    WHEN 'status.deleted' THEN 'Deleted'
    WHEN 'status.completed' THEN 'Completed'
    WHEN 'status.processing' THEN 'Processing'
    WHEN 'status.cancelled' THEN 'Cancelled'
END
FROM i18n_keys WHERE `group` = 'status'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ------------------------------------------------------------
-- STATUS NAMESPACE - TURKISH (lang_id = 2)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_tr, CASE key_name
    WHEN 'status.active' THEN 'Aktif'
    WHEN 'status.inactive' THEN 'Pasif'
    WHEN 'status.pending' THEN 'Beklemede'
    WHEN 'status.approved' THEN 'Onaylandı'
    WHEN 'status.rejected' THEN 'Reddedildi'
    WHEN 'status.published' THEN 'Yayınlandı'
    WHEN 'status.draft' THEN 'Taslak'
    WHEN 'status.archived' THEN 'Arşivlendi'
    WHEN 'status.deleted' THEN 'Silindi'
    WHEN 'status.completed' THEN 'Tamamlandı'
    WHEN 'status.processing' THEN 'İşleniyor'
    WHEN 'status.cancelled' THEN 'İptal Edildi'
END
FROM i18n_keys WHERE `group` = 'status'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ------------------------------------------------------------
-- HELP NAMESPACE - ENGLISH (lang_id = 1)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_en, CASE key_name
    WHEN 'help.title' THEN 'Help & Support'
    WHEN 'help.documentation' THEN 'Documentation'
    WHEN 'help.faq' THEN 'Frequently Asked Questions'
    WHEN 'help.contact_support' THEN 'Contact Support'
    WHEN 'help.tutorials' THEN 'Tutorials'
    WHEN 'help.video_guides' THEN 'Video Guides'
    WHEN 'help.knowledge_base' THEN 'Knowledge Base'
    WHEN 'help.search_help' THEN 'Search help articles...'
    WHEN 'help.popular_topics' THEN 'Popular Topics'
    WHEN 'help.getting_started' THEN 'Getting Started'
    WHEN 'help.advanced_features' THEN 'Advanced Features'
    WHEN 'help.troubleshooting' THEN 'Troubleshooting'
    WHEN 'help.report_bug' THEN 'Report a Bug'
    WHEN 'help.feature_request' THEN 'Feature Request'
    WHEN 'help.community_forum' THEN 'Community Forum'
END
FROM i18n_keys WHERE `group` = 'help'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ------------------------------------------------------------
-- HELP NAMESPACE - TURKISH (lang_id = 2)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_tr, CASE key_name
    WHEN 'help.title' THEN 'Yardım ve Destek'
    WHEN 'help.documentation' THEN 'Dokümantasyon'
    WHEN 'help.faq' THEN 'Sıkça Sorulan Sorular'
    WHEN 'help.contact_support' THEN 'Destek ile İletişime Geç'
    WHEN 'help.tutorials' THEN 'Eğitimler'
    WHEN 'help.video_guides' THEN 'Video Rehberleri'
    WHEN 'help.knowledge_base' THEN 'Bilgi Bankası'
    WHEN 'help.search_help' THEN 'Yardım makalelerinde ara...'
    WHEN 'help.popular_topics' THEN 'Popüler Konular'
    WHEN 'help.getting_started' THEN 'Başlangıç'
    WHEN 'help.advanced_features' THEN 'Gelişmiş Özellikler'
    WHEN 'help.troubleshooting' THEN 'Sorun Giderme'
    WHEN 'help.report_bug' THEN 'Hata Bildir'
    WHEN 'help.feature_request' THEN 'Özellik Talebi'
    WHEN 'help.community_forum' THEN 'Topluluk Forumu'
END
FROM i18n_keys WHERE `group` = 'help'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ------------------------------------------------------------
-- MESSAGE NAMESPACE - ENGLISH (lang_id = 1)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_en, CASE key_name
    WHEN 'message.success' THEN 'Operation completed successfully'
    WHEN 'message.error' THEN 'An error occurred. Please try again'
    WHEN 'message.warning' THEN 'Warning'
    WHEN 'message.info' THEN 'Information'
    WHEN 'message.confirm_delete' THEN 'Are you sure you want to delete this item?'
    WHEN 'message.unsaved_changes' THEN 'You have unsaved changes. Are you sure you want to leave?'
    WHEN 'message.no_data' THEN 'No data available'
    WHEN 'message.loading' THEN 'Loading, please wait...'
    WHEN 'message.changes_saved' THEN 'Changes saved successfully'
    WHEN 'message.upload_successful' THEN 'File uploaded successfully'
    WHEN 'message.invalid_file_type' THEN 'Invalid file type'
    WHEN 'message.required_fields_missing' THEN 'Please fill in all required fields'
END
FROM i18n_keys WHERE `group` = 'message'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ------------------------------------------------------------
-- MESSAGE NAMESPACE - TURKISH (lang_id = 2)
-- ------------------------------------------------------------
INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, @lang_tr, CASE key_name
    WHEN 'message.success' THEN 'İşlem başarıyla tamamlandı'
    WHEN 'message.error' THEN 'Bir hata oluştu. Lütfen tekrar deneyin'
    WHEN 'message.warning' THEN 'Uyarı'
    WHEN 'message.info' THEN 'Bilgi'
    WHEN 'message.confirm_delete' THEN 'Bu öğeyi silmek istediğinizden emin misiniz?'
    WHEN 'message.unsaved_changes' THEN 'Kaydedilmemiş değişiklikleriniz var. Çıkmak istediğinizden emin misiniz?'
    WHEN 'message.no_data' THEN 'Veri bulunamadı'
    WHEN 'message.loading' THEN 'Yükleniyor, lütfen bekleyin...'
    WHEN 'message.changes_saved' THEN 'Değişiklikler başarıyla kaydedildi'
    WHEN 'message.upload_successful' THEN 'Dosya başarıyla yüklendi'
    WHEN 'message.invalid_file_type' THEN 'Geçersiz dosya tipi'
    WHEN 'message.required_fields_missing' THEN 'Lütfen tüm zorunlu alanları doldurun'
END
FROM i18n_keys WHERE `group` = 'message'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ============================================================
-- FINAL STATUS CHECK
-- ============================================================
SELECT '🎉 Translation migration completed successfully!' AS status;
SELECT CONCAT('Total keys: ', COUNT(*)) AS total_keys FROM i18n_keys;
SELECT CONCAT('Total translations: ', COUNT(*)) AS total_translations FROM i18n_values;
SELECT 
    l.name AS language, 
    COUNT(v.id) AS translation_count 
FROM languages l
LEFT JOIN i18n_values v ON v.lang_id = l.id
WHERE l.id IN (1, 2)
GROUP BY l.id, l.name;

