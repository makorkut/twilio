-- ============================================================================
-- Migration 021: Complete Translation System - All Frontend & Admin Keys
-- Date: 2025-11-05
-- Description: Add all translation keys for multi-language support
--              Total: 450+ keys with Turkish (TR) and English (EN) translations
-- ============================================================================

SET NAMES utf8mb4;

-- ==========================
-- COMMON TRANSLATIONS
-- ==========================

-- Common actions
INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('common.save', 'common', 'Save button'),
('common.cancel', 'common', 'Cancel button'),
('common.delete', 'common', 'Delete button'),
('common.edit', 'common', 'Edit button'),
('common.view', 'common', 'View button'),
('common.search', 'common', 'Search button'),
('common.send', 'common', 'Send button'),
('common.filter', 'common', 'Filter button'),
('common.close', 'common', 'Close button'),
('common.yes', 'common', 'Yes'),
('common.no', 'common', 'No'),
('common.loading', 'common', 'Loading text'),
('common.error', 'common', 'Error'),
('common.success', 'common', 'Success'),
('common.warning', 'common', 'Warning'),
('common.info', 'common', 'Info'),
('common.update', 'common', 'Update button'),
('common.publish', 'common', 'Publish button');

-- Common navigation
INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('common.products', 'common', 'Products navigation'),
('common.categories', 'common', 'Categories navigation'),
('common.projects', 'common', 'Projects navigation'),
('common.about', 'common', 'About navigation'),
('common.contact', 'common', 'Contact navigation'),
('common.careers', 'common', 'Careers navigation'),
('common.faq', 'common', 'FAQ navigation');

-- Language names
INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('common.lang_tr', 'common', 'Turkish language name'),
('common.lang_en', 'common', 'English language name'),
('common.lang_de', 'common', 'German language name'),
('common.lang_fr', 'common', 'French language name');

-- ==========================
-- FRONTEND - HOME PAGE
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('frontend.home.page_title', 'frontend', 'Home page title'),
('frontend.home.hero_title', 'frontend', 'Hero section title'),
('frontend.home.hero_subtitle', 'frontend', 'Hero section subtitle'),
('frontend.home.explore_products', 'frontend', 'Explore products button'),
('frontend.home.categories_title', 'frontend', 'Categories section title'),
('frontend.home.product_count_suffix', 'frontend', 'Product count suffix (product/products)'),
('frontend.home.featured_products', 'frontend', 'Featured products title'),
('frontend.home.no_featured_products', 'frontend', 'No featured products message'),
('frontend.home.b2b_cta_title', 'frontend', 'B2B call-to-action title'),
('frontend.home.b2b_cta_subtitle', 'frontend', 'B2B call-to-action subtitle'),
('frontend.home.dealer_application', 'frontend', 'Dealer application button');

-- ==========================
-- FRONTEND - PRODUCTS PAGE
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('frontend.products.page_title', 'frontend', 'Products page title'),
('frontend.products.heading', 'frontend', 'Products page heading'),
('frontend.products.categories', 'frontend', 'Categories filter label'),
('frontend.products.all', 'frontend', 'All categories option'),
('frontend.products.showing_count', 'frontend', 'Showing X products text'),
('frontend.products.sort_newest', 'frontend', 'Sort by newest'),
('frontend.products.sort_price_low_high', 'frontend', 'Sort by price low to high'),
('frontend.products.sort_price_high_low', 'frontend', 'Sort by price high to low'),
('frontend.products.sort_name_az', 'frontend', 'Sort by name A-Z'),
('frontend.products.no_products_found', 'frontend', 'No products found'),
('frontend.products.no_matching_products', 'frontend', 'No matching products message');

-- ==========================
-- FRONTEND - PRODUCT DETAIL
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('frontend.product.new_badge', 'frontend', 'New product badge'),
('frontend.product.add_to_cart', 'frontend', 'Add to cart button'),
('frontend.product.out_of_stock', 'frontend', 'Out of stock message'),
('frontend.product.stock', 'frontend', 'Stock label'),
('frontend.product.pieces', 'frontend', 'Pieces unit'),
('frontend.product.qr_title', 'frontend', 'QR code section title'),
('frontend.product.qr_description', 'frontend', 'QR code description'),
('frontend.product.technical_specs', 'frontend', 'Technical specifications title'),
('frontend.product.length', 'frontend', 'Length'),
('frontend.product.width', 'frontend', 'Width'),
('frontend.product.height', 'frontend', 'Height'),
('frontend.product.weight', 'frontend', 'Weight'),
('frontend.product.package_quantity', 'frontend', 'Package quantity'),
('frontend.product.description', 'frontend', 'Product description title');

-- ==========================
-- FRONTEND - CART
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('frontend.cart.title', 'frontend', 'Cart page title'),
('frontend.cart.empty', 'frontend', 'Empty cart message'),
('frontend.cart.start_shopping', 'frontend', 'Start shopping message'),
('frontend.cart.browse_products', 'frontend', 'Browse products button'),
('frontend.cart.remove', 'frontend', 'Remove from cart button'),
('frontend.cart.order_summary', 'frontend', 'Order summary title'),
('frontend.cart.subtotal', 'frontend', 'Subtotal'),
('frontend.cart.discount', 'frontend', 'Discount'),
('frontend.cart.tax', 'frontend', 'Tax/VAT'),
('frontend.cart.shipping', 'frontend', 'Shipping'),
('frontend.cart.free', 'frontend', 'Free'),
('frontend.cart.total', 'frontend', 'Total'),
('frontend.cart.proceed_to_checkout', 'frontend', 'Proceed to checkout button'),
('frontend.cart.continue_shopping', 'frontend', 'Continue shopping button');

-- ==========================
-- FRONTEND - ABOUT PAGE
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('frontend.about.page_title', 'frontend', 'About page title'),
('frontend.about.meta_description', 'frontend', 'About page meta description'),
('frontend.about.heading', 'frontend', 'About page heading'),
('frontend.about.hero_text', 'frontend', 'About hero text'),
('frontend.about.our_mission', 'frontend', 'Our mission title'),
('frontend.about.mission_text', 'frontend', 'Mission text'),
('frontend.about.our_vision', 'frontend', 'Our vision title'),
('frontend.about.vision_text', 'frontend', 'Vision text'),
('frontend.about.quality_assurance', 'frontend', 'Quality assurance'),
('frontend.about.quality_assurance_text', 'frontend', 'Quality assurance text'),
('frontend.about.fast_delivery', 'frontend', 'Fast delivery'),
('frontend.about.fast_delivery_text', 'frontend', 'Fast delivery text'),
('frontend.about.professional_support', 'frontend', 'Professional support'),
('frontend.about.professional_support_text', 'frontend', 'Professional support text'),
('frontend.about.wide_product_range', 'frontend', 'Wide product range'),
('frontend.about.wide_product_range_text', 'frontend', 'Wide product range text'),
('frontend.about.b2b_solutions', 'frontend', 'B2B solutions'),
('frontend.about.b2b_solutions_text', 'frontend', 'B2B solutions text'),
('frontend.about.technical_support', 'frontend', 'Technical support'),
('frontend.about.technical_support_text', 'frontend', 'Technical support text');

-- ==========================
-- FRONTEND - CONTACT PAGE
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('frontend.contact.page_title', 'frontend', 'Contact page title'),
('frontend.contact.meta_description', 'frontend', 'Contact meta description'),
('frontend.contact.heading', 'frontend', 'Contact heading'),
('frontend.contact.hero_text', 'frontend', 'Contact hero text'),
('frontend.contact.address', 'frontend', 'Address label'),
('frontend.contact.address_text', 'frontend', 'Address content'),
('frontend.contact.phone', 'frontend', 'Phone label'),
('frontend.contact.email', 'frontend', 'Email label'),
('frontend.contact.working_hours', 'frontend', 'Working hours label'),
('frontend.contact.working_hours_text', 'frontend', 'Working hours text'),
('frontend.contact.form_title', 'frontend', 'Contact form title'),
('frontend.contact.full_name', 'frontend', 'Full name field'),
('frontend.contact.email_field', 'frontend', 'Email field'),
('frontend.contact.phone_field', 'frontend', 'Phone field'),
('frontend.contact.subject', 'frontend', 'Subject field'),
('frontend.contact.message', 'frontend', 'Message field'),
('frontend.contact.live_support', 'frontend', 'Live support'),
('frontend.contact.live_support_text', 'frontend', 'Live support text'),
('frontend.contact.email_support', 'frontend', 'Email support'),
('frontend.contact.email_support_text', 'frontend', 'Email support text'),
('frontend.contact.technical_consulting', 'frontend', 'Technical consulting'),
('frontend.contact.technical_consulting_text', 'frontend', 'Technical consulting text');

-- ==========================
-- FRONTEND - SEARCH
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('frontend.search.heading', 'frontend', 'Search page heading'),
('frontend.search.placeholder', 'frontend', 'Search placeholder'),
('frontend.search.search_button', 'frontend', 'Search button'),
('frontend.search.results_found', 'frontend', 'Results found text'),
('frontend.search.unnamed_product', 'frontend', 'Unnamed product'),
('frontend.search.no_results', 'frontend', 'No results found'),
('frontend.search.no_results_text', 'frontend', 'No results help text'),
('frontend.search.product_search', 'frontend', 'Product search title'),
('frontend.search.search_prompt', 'frontend', 'Search prompt text');

-- ==========================
-- FRONTEND - LAYOUT
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('frontend.layout.free_shipping_banner', 'frontend', 'Free shipping banner'),
('frontend.layout.footer_description', 'frontend', 'Footer description'),
('frontend.layout.corporate', 'frontend', 'Corporate footer section'),
('frontend.layout.customer_service', 'frontend', 'Customer service footer section'),
('frontend.layout.shipping_delivery', 'frontend', 'Shipping & delivery'),
('frontend.layout.returns_exchanges', 'frontend', 'Returns & exchanges'),
('frontend.layout.sample_order', 'frontend', 'Sample order'),
('frontend.layout.dealer_application', 'frontend', 'Dealer application'),
('frontend.layout.wholesale_prices', 'frontend', 'Wholesale prices'),
('frontend.layout.pdf_catalog', 'frontend', 'PDF catalog'),
('frontend.layout.api_integration', 'frontend', 'API integration'),
('frontend.layout.copyright', 'frontend', 'Copyright text');

-- ==========================
-- FRONTEND - COMPONENTS
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('frontend.components.color_selector_title', 'frontend', 'Color selector title'),
('frontend.components.out_of_stock', 'frontend', 'Out of stock'),
('frontend.components.selected_color', 'frontend', 'Selected color label'),
('frontend.components.calculator_title', 'frontend', 'Calculator title'),
('frontend.components.calculator_subtitle', 'frontend', 'Calculator subtitle'),
('frontend.components.width_meters', 'frontend', 'Width in meters'),
('frontend.components.length_meters', 'frontend', 'Length in meters'),
('frontend.components.waste_rate', 'frontend', 'Waste rate'),
('frontend.components.waste_hint', 'frontend', 'Waste rate hint'),
('frontend.components.calculate_button', 'frontend', 'Calculate button'),
('frontend.components.area', 'frontend', 'Area'),
('frontend.components.area_with_waste', 'frontend', 'Area with waste'),
('frontend.components.required_quantity', 'frontend', 'Required quantity'),
('frontend.components.add_to_cart', 'frontend', 'Add to cart');

-- (Part 1 of migration - will continue in next message due to length)
