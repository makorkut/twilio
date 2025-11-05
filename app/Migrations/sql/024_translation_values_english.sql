-- ============================================================================
-- Migration 024: English Translation Values
-- Date: 2025-11-05
-- Description: Add all English (EN) translation values
-- Language ID: 1 (English)
-- ============================================================================

SET NAMES utf8mb4;

-- ==========================
-- COMMON TRANSLATIONS (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    -- Common actions
    WHEN 'common.save' THEN 'Save'
    WHEN 'common.cancel' THEN 'Cancel'
    WHEN 'common.delete' THEN 'Delete'
    WHEN 'common.edit' THEN 'Edit'
    WHEN 'common.view' THEN 'View'
    WHEN 'common.search' THEN 'Search'
    WHEN 'common.send' THEN 'Send'
    WHEN 'common.filter' THEN 'Filter'
    WHEN 'common.close' THEN 'Close'
    WHEN 'common.yes' THEN 'Yes'
    WHEN 'common.no' THEN 'No'
    WHEN 'common.loading' THEN 'Loading...'
    WHEN 'common.error' THEN 'Error'
    WHEN 'common.success' THEN 'Success'
    WHEN 'common.warning' THEN 'Warning'
    WHEN 'common.info' THEN 'Info'
    WHEN 'common.update' THEN 'Update'
    WHEN 'common.publish' THEN 'Publish'
    -- Common navigation
    WHEN 'common.products' THEN 'Products'
    WHEN 'common.categories' THEN 'Categories'
    WHEN 'common.projects' THEN 'Projects'
    WHEN 'common.about' THEN 'About Us'
    WHEN 'common.contact' THEN 'Contact'
    WHEN 'common.careers' THEN 'Careers'
    WHEN 'common.faq' THEN 'FAQ'
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
-- FRONTEND - HOME PAGE (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'frontend.home.page_title' THEN 'Home - Polyurethane'
    WHEN 'frontend.home.hero_title' THEN 'Premium Polyurethane Products'
    WHEN 'frontend.home.hero_subtitle' THEN 'Add aesthetics and quality to your spaces. Find the most suitable solution for your needs with our wide product range.'
    WHEN 'frontend.home.explore_products' THEN 'Explore Products'
    WHEN 'frontend.home.categories_title' THEN 'Categories'
    WHEN 'frontend.home.product_count_suffix' THEN 'Products'
    WHEN 'frontend.home.featured_products' THEN 'Featured Products'
    WHEN 'frontend.home.no_featured_products' THEN 'No featured products available yet.'
    WHEN 'frontend.home.b2b_cta_title' THEN 'Looking for Wholesale?'
    WHEN 'frontend.home.b2b_cta_subtitle' THEN 'Apply now for special pricing and benefits for our dealers.'
    WHEN 'frontend.home.dealer_application' THEN 'Dealer Application'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.home.%';

-- ==========================
-- FRONTEND - PRODUCTS PAGE (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'frontend.products.page_title' THEN 'All Products'
    WHEN 'frontend.products.heading' THEN 'Our Products'
    WHEN 'frontend.products.categories' THEN 'Categories'
    WHEN 'frontend.products.all' THEN 'All'
    WHEN 'frontend.products.showing_count' THEN 'products showing'
    WHEN 'frontend.products.sort_newest' THEN 'Newest'
    WHEN 'frontend.products.sort_price_low_high' THEN 'Price: Low to High'
    WHEN 'frontend.products.sort_price_high_low' THEN 'Price: High to Low'
    WHEN 'frontend.products.sort_name_az' THEN 'Name: A-Z'
    WHEN 'frontend.products.no_products_found' THEN 'No products found'
    WHEN 'frontend.products.no_matching_products' THEN 'No products match your selected filters.'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.products.%';

-- ==========================
-- FRONTEND - PRODUCT DETAIL (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'frontend.product.new_badge' THEN 'New'
    WHEN 'frontend.product.add_to_cart' THEN 'Add to Cart'
    WHEN 'frontend.product.out_of_stock' THEN 'Out of Stock'
    WHEN 'frontend.product.stock' THEN 'Stock'
    WHEN 'frontend.product.pieces' THEN 'Pieces'
    WHEN 'frontend.product.qr_title' THEN 'Share with QR Code'
    WHEN 'frontend.product.qr_description' THEN 'Scan the QR code to share this product with your mobile device'
    WHEN 'frontend.product.technical_specs' THEN 'Technical Specifications'
    WHEN 'frontend.product.length' THEN 'Length'
    WHEN 'frontend.product.width' THEN 'Width'
    WHEN 'frontend.product.height' THEN 'Height'
    WHEN 'frontend.product.weight' THEN 'Weight'
    WHEN 'frontend.product.package_quantity' THEN 'Package Contents'
    WHEN 'frontend.product.description' THEN 'Product Description'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.product.%';

-- ==========================
-- FRONTEND - CART (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'frontend.cart.title' THEN 'My Cart'
    WHEN 'frontend.cart.empty' THEN 'Your cart is empty'
    WHEN 'frontend.cart.start_shopping' THEN 'Browse our products to start shopping'
    WHEN 'frontend.cart.browse_products' THEN 'Browse Products'
    WHEN 'frontend.cart.remove' THEN 'Remove'
    WHEN 'frontend.cart.order_summary' THEN 'Order Summary'
    WHEN 'frontend.cart.subtotal' THEN 'Subtotal'
    WHEN 'frontend.cart.discount' THEN 'Discount'
    WHEN 'frontend.cart.tax' THEN 'VAT'
    WHEN 'frontend.cart.shipping' THEN 'Shipping'
    WHEN 'frontend.cart.free' THEN 'Free'
    WHEN 'frontend.cart.total' THEN 'Total'
    WHEN 'frontend.cart.proceed_to_checkout' THEN 'Proceed to Checkout'
    WHEN 'frontend.cart.continue_shopping' THEN 'Continue Shopping'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.cart.%';

-- ==========================
-- FRONTEND - ABOUT PAGE (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'frontend.about.page_title' THEN 'About Us'
    WHEN 'frontend.about.meta_description' THEN 'Learn about our company specialized in polyurethane products'
    WHEN 'frontend.about.heading' THEN 'About Us'
    WHEN 'frontend.about.hero_text' THEN 'With our leading position in the polyurethane industry, we are with you with quality products and customer satisfaction-oriented service approach.'
    WHEN 'frontend.about.our_mission' THEN 'Our Mission'
    WHEN 'frontend.about.mission_text' THEN 'To offer the highest quality polyurethane products to our customers at reasonable prices and to be a pioneer in the industry.'
    WHEN 'frontend.about.our_vision' THEN 'Our Vision'
    WHEN 'frontend.about.vision_text' THEN 'To be the preferred brand in national and international markets by providing innovative solutions in the polyurethane industry.'
    WHEN 'frontend.about.quality_assurance' THEN 'Quality Assurance'
    WHEN 'frontend.about.quality_assurance_text' THEN 'All our products are manufactured in accordance with international quality standards.'
    WHEN 'frontend.about.fast_delivery' THEN 'Fast Delivery'
    WHEN 'frontend.about.fast_delivery_text' THEN 'Thanks to our wide stock network, we deliver your orders quickly.'
    WHEN 'frontend.about.professional_support' THEN 'Professional Support'
    WHEN 'frontend.about.professional_support_text' THEN 'Our expert team is always ready to provide you with the best service.'
    WHEN 'frontend.about.wide_product_range' THEN 'Wide Product Range'
    WHEN 'frontend.about.wide_product_range_text' THEN 'We have thousands of product options suitable for all kinds of needs.'
    WHEN 'frontend.about.b2b_solutions' THEN 'B2B Solutions'
    WHEN 'frontend.about.b2b_solutions_text' THEN 'We offer special pricing and payment options for your wholesale purchases.'
    WHEN 'frontend.about.technical_support' THEN 'Technical Support'
    WHEN 'frontend.about.technical_support_text' THEN 'We provide technical support in product selection and applications.'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.about.%';

-- ==========================
-- FRONTEND - CONTACT PAGE (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'frontend.contact.page_title' THEN 'Contact Us'
    WHEN 'frontend.contact.meta_description' THEN 'Contact us, we will answer your questions'
    WHEN 'frontend.contact.heading' THEN 'Contact Us'
    WHEN 'frontend.contact.hero_text' THEN 'Contact us for your questions, we are happy to help you.'
    WHEN 'frontend.contact.address' THEN 'Address'
    WHEN 'frontend.contact.address_text' THEN 'Sample Street, Polyurethane Avenue No:123, Istanbul, Turkey'
    WHEN 'frontend.contact.phone' THEN 'Phone'
    WHEN 'frontend.contact.email' THEN 'Email'
    WHEN 'frontend.contact.working_hours' THEN 'Working Hours'
    WHEN 'frontend.contact.working_hours_text' THEN 'Monday - Friday: 09:00 - 18:00'
    WHEN 'frontend.contact.form_title' THEN 'Send Us a Message'
    WHEN 'frontend.contact.full_name' THEN 'Full Name'
    WHEN 'frontend.contact.email_field' THEN 'Email Address'
    WHEN 'frontend.contact.phone_field' THEN 'Phone'
    WHEN 'frontend.contact.subject' THEN 'Subject'
    WHEN 'frontend.contact.message' THEN 'Your Message'
    WHEN 'frontend.contact.live_support' THEN 'Live Support'
    WHEN 'frontend.contact.live_support_text' THEN 'You can reach us through our 24/7 live support line.'
    WHEN 'frontend.contact.email_support' THEN 'Email Support'
    WHEN 'frontend.contact.email_support_text' THEN 'Send your questions via email, we will respond as soon as possible.'
    WHEN 'frontend.contact.technical_consulting' THEN 'Technical Consulting'
    WHEN 'frontend.contact.technical_consulting_text' THEN 'Get expert support on product selection and application.'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.contact.%';

-- ==========================
-- FRONTEND - SEARCH (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'frontend.search.heading' THEN 'Search Results'
    WHEN 'frontend.search.placeholder' THEN 'Search products...'
    WHEN 'frontend.search.search_button' THEN 'Search'
    WHEN 'frontend.search.results_found' THEN 'results found'
    WHEN 'frontend.search.unnamed_product' THEN 'Unnamed Product'
    WHEN 'frontend.search.no_results' THEN 'No results found'
    WHEN 'frontend.search.no_results_text' THEN 'No results found for your search term. Please try different keywords.'
    WHEN 'frontend.search.product_search' THEN 'Product Search'
    WHEN 'frontend.search.search_prompt' THEN 'Use the search box above to search for products'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.search.%';

-- ==========================
-- FRONTEND - LAYOUT (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'frontend.layout.free_shipping_banner' THEN 'FREE SHIPPING on Orders Over 1000 TL!'
    WHEN 'frontend.layout.footer_description' THEN 'Our leading company in the polyurethane industry is with you with quality products and customer satisfaction-oriented service approach.'
    WHEN 'frontend.layout.corporate' THEN 'Corporate'
    WHEN 'frontend.layout.customer_service' THEN 'Customer Service'
    WHEN 'frontend.layout.shipping_delivery' THEN 'Shipping & Delivery'
    WHEN 'frontend.layout.returns_exchanges' THEN 'Returns & Exchanges'
    WHEN 'frontend.layout.sample_order' THEN 'Sample Order'
    WHEN 'frontend.layout.dealer_application' THEN 'Dealer Application'
    WHEN 'frontend.layout.wholesale_prices' THEN 'Wholesale Price List'
    WHEN 'frontend.layout.pdf_catalog' THEN 'PDF Catalog'
    WHEN 'frontend.layout.api_integration' THEN 'API Integration'
    WHEN 'frontend.layout.copyright' THEN '© 2025 Polyurethane. All rights reserved.'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.layout.%';

-- ==========================
-- FRONTEND - COMPONENTS (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'frontend.components.color_selector_title' THEN 'Color Selection'
    WHEN 'frontend.components.out_of_stock' THEN 'Out of Stock'
    WHEN 'frontend.components.selected_color' THEN 'Selected Color'
    WHEN 'frontend.components.calculator_title' THEN 'Area Calculation'
    WHEN 'frontend.components.calculator_subtitle' THEN 'Calculate the amount of product you need'
    WHEN 'frontend.components.width_meters' THEN 'Width (meters)'
    WHEN 'frontend.components.length_meters' THEN 'Length (meters)'
    WHEN 'frontend.components.waste_rate' THEN 'Waste Rate (%)'
    WHEN 'frontend.components.waste_hint' THEN 'Usually 10% waste is calculated'
    WHEN 'frontend.components.calculate_button' THEN 'Calculate'
    WHEN 'frontend.components.area' THEN 'Area'
    WHEN 'frontend.components.area_with_waste' THEN 'Area with Waste'
    WHEN 'frontend.components.required_quantity' THEN 'Required Quantity'
    WHEN 'frontend.components.add_to_cart' THEN 'Add to Cart'
END
FROM i18n_keys
WHERE key_name LIKE 'frontend.components.%';

-- ==========================
-- ADMIN - DASHBOARD (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'admin.dashboard.page_title' THEN 'Admin Dashboard'
    WHEN 'admin.dashboard.welcome' THEN 'Welcome'
    WHEN 'admin.dashboard.total_orders' THEN 'Total Orders'
    WHEN 'admin.dashboard.total_revenue' THEN 'Total Revenue'
    WHEN 'admin.dashboard.total_products' THEN 'Total Products'
    WHEN 'admin.dashboard.total_customers' THEN 'Total Customers'
    WHEN 'admin.dashboard.pending_orders' THEN 'Pending Orders'
    WHEN 'admin.dashboard.recent_orders' THEN 'Recent Orders'
    WHEN 'admin.dashboard.view_all' THEN 'View All'
    WHEN 'admin.dashboard.order_number' THEN 'Order No.'
    WHEN 'admin.dashboard.customer' THEN 'Customer'
    WHEN 'admin.dashboard.total' THEN 'Total'
    WHEN 'admin.dashboard.status' THEN 'Status'
    WHEN 'admin.dashboard.date' THEN 'Date'
    WHEN 'admin.dashboard.low_stock_products' THEN 'Low Stock Products'
    WHEN 'admin.dashboard.stock_level' THEN 'Stock Level'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.dashboard.%';

-- ==========================
-- ADMIN - PRODUCTS (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'admin.products.page_title' THEN 'Product Management'
    WHEN 'admin.products.list_title' THEN 'Products'
    WHEN 'admin.products.add_new' THEN 'Add New Product'
    WHEN 'admin.products.edit' THEN 'Edit Product'
    WHEN 'admin.products.delete' THEN 'Delete Product'
    WHEN 'admin.products.name' THEN 'Product Name'
    WHEN 'admin.products.sku' THEN 'SKU'
    WHEN 'admin.products.category' THEN 'Category'
    WHEN 'admin.products.price' THEN 'Price'
    WHEN 'admin.products.stock' THEN 'Stock'
    WHEN 'admin.products.status' THEN 'Status'
    WHEN 'admin.products.actions' THEN 'Actions'
    WHEN 'admin.products.active' THEN 'Active'
    WHEN 'admin.products.inactive' THEN 'Inactive'
    WHEN 'admin.products.basic_info' THEN 'Basic Information'
    WHEN 'admin.products.pricing' THEN 'Pricing'
    WHEN 'admin.products.inventory' THEN 'Inventory'
    WHEN 'admin.products.images' THEN 'Images'
    WHEN 'admin.products.seo' THEN 'SEO'
    WHEN 'admin.products.specifications' THEN 'Specifications'
    WHEN 'admin.products.delete_confirm' THEN 'Are you sure you want to delete this product?'
    WHEN 'admin.products.save_success' THEN 'Product saved successfully'
    WHEN 'admin.products.delete_success' THEN 'Product deleted successfully'
    WHEN 'admin.products.featured' THEN 'Featured'
    WHEN 'admin.products.b2b_only' THEN 'B2B Only'
    WHEN 'admin.products.allow_samples' THEN 'Allow Samples'
    WHEN 'admin.products.calculator_type' THEN 'Calculator Type'
    WHEN 'admin.products.coverage_per_unit' THEN 'Coverage per Unit'
    WHEN 'admin.products.main_image' THEN 'Main Image'
    WHEN 'admin.products.gallery_images' THEN 'Gallery Images'
    WHEN 'admin.products.meta_title' THEN 'Meta Title'
    WHEN 'admin.products.meta_description' THEN 'Meta Description'
    WHEN 'admin.products.meta_keywords' THEN 'Meta Keywords'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.products.%';

-- ==========================
-- ADMIN - CATEGORIES (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'admin.categories.page_title' THEN 'Category Management'
    WHEN 'admin.categories.list_title' THEN 'Categories'
    WHEN 'admin.categories.add_new' THEN 'Add New Category'
    WHEN 'admin.categories.edit' THEN 'Edit Category'
    WHEN 'admin.categories.delete' THEN 'Delete Category'
    WHEN 'admin.categories.name' THEN 'Category Name'
    WHEN 'admin.categories.slug' THEN 'Slug'
    WHEN 'admin.categories.parent' THEN 'Parent Category'
    WHEN 'admin.categories.products_count' THEN 'Products Count'
    WHEN 'admin.categories.actions' THEN 'Actions'
    WHEN 'admin.categories.no_parent' THEN 'No Parent (Root)'
    WHEN 'admin.categories.delete_confirm' THEN 'Are you sure you want to delete this category?'
    WHEN 'admin.categories.save_success' THEN 'Category saved successfully'
    WHEN 'admin.categories.delete_success' THEN 'Category deleted successfully'
    WHEN 'admin.categories.description' THEN 'Description'
    WHEN 'admin.categories.image' THEN 'Category Image'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.categories.%';

-- ==========================
-- ADMIN - ORDERS (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'admin.orders.page_title' THEN 'Order Management'
    WHEN 'admin.orders.list_title' THEN 'Orders'
    WHEN 'admin.orders.view' THEN 'View Order'
    WHEN 'admin.orders.order_number' THEN 'Order No.'
    WHEN 'admin.orders.customer' THEN 'Customer'
    WHEN 'admin.orders.date' THEN 'Date'
    WHEN 'admin.orders.total' THEN 'Total'
    WHEN 'admin.orders.status' THEN 'Status'
    WHEN 'admin.orders.payment_status' THEN 'Payment Status'
    WHEN 'admin.orders.actions' THEN 'Actions'
    WHEN 'admin.orders.details' THEN 'Order Details'
    WHEN 'admin.orders.items' THEN 'Order Items'
    WHEN 'admin.orders.billing_address' THEN 'Billing Address'
    WHEN 'admin.orders.shipping_address' THEN 'Shipping Address'
    WHEN 'admin.orders.product' THEN 'Product'
    WHEN 'admin.orders.quantity' THEN 'Quantity'
    WHEN 'admin.orders.price' THEN 'Price'
    WHEN 'admin.orders.subtotal' THEN 'Subtotal'
    WHEN 'admin.orders.discount' THEN 'Discount'
    WHEN 'admin.orders.tax' THEN 'VAT'
    WHEN 'admin.orders.shipping' THEN 'Shipping'
    WHEN 'admin.orders.grand_total' THEN 'Grand Total'
    WHEN 'admin.orders.update_status' THEN 'Update Status'
    WHEN 'admin.orders.status_pending' THEN 'Pending'
    WHEN 'admin.orders.status_processing' THEN 'Processing'
    WHEN 'admin.orders.status_shipped' THEN 'Shipped'
    WHEN 'admin.orders.status_delivered' THEN 'Delivered'
    WHEN 'admin.orders.status_cancelled' THEN 'Cancelled'
    WHEN 'admin.orders.payment_pending' THEN 'Payment Pending'
    WHEN 'admin.orders.payment_paid' THEN 'Paid'
    WHEN 'admin.orders.payment_failed' THEN 'Payment Failed'
    WHEN 'admin.orders.payment_refunded' THEN 'Refunded'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.orders.%';

-- ==========================
-- ADMIN - CUSTOMERS (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'admin.customers.page_title' THEN 'Customer Management'
    WHEN 'admin.customers.list_title' THEN 'Customers'
    WHEN 'admin.customers.add_new' THEN 'Add New Customer'
    WHEN 'admin.customers.edit' THEN 'Edit Customer'
    WHEN 'admin.customers.view' THEN 'View Customer'
    WHEN 'admin.customers.name' THEN 'Customer Name'
    WHEN 'admin.customers.email' THEN 'Email'
    WHEN 'admin.customers.phone' THEN 'Phone'
    WHEN 'admin.customers.group' THEN 'Customer Group'
    WHEN 'admin.customers.orders_count' THEN 'Orders Count'
    WHEN 'admin.customers.total_spent' THEN 'Total Spent'
    WHEN 'admin.customers.status' THEN 'Status'
    WHEN 'admin.customers.actions' THEN 'Actions'
    WHEN 'admin.customers.details' THEN 'Customer Details'
    WHEN 'admin.customers.order_history' THEN 'Order History'
    WHEN 'admin.customers.addresses' THEN 'Addresses'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.customers.%';

-- ==========================
-- ADMIN - SETTINGS (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'admin.settings.page_title' THEN 'Settings'
    WHEN 'admin.settings.general' THEN 'General Settings'
    WHEN 'admin.settings.site_name' THEN 'Site Name'
    WHEN 'admin.settings.site_description' THEN 'Site Description'
    WHEN 'admin.settings.currency' THEN 'Currency'
    WHEN 'admin.settings.tax_rate' THEN 'Tax Rate'
    WHEN 'admin.settings.shipping' THEN 'Shipping Settings'
    WHEN 'admin.settings.payment' THEN 'Payment Settings'
    WHEN 'admin.settings.email' THEN 'Email Settings'
    WHEN 'admin.settings.save' THEN 'Save Settings'
    WHEN 'admin.settings.save_success' THEN 'Settings saved successfully'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.settings.%';

-- ==========================
-- ADMIN - TRANSLATIONS (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'admin.translations.page_title' THEN 'Translation Management'
    WHEN 'admin.translations.list_title' THEN 'Translations'
    WHEN 'admin.translations.key' THEN 'Translation Key'
    WHEN 'admin.translations.group' THEN 'Group'
    WHEN 'admin.translations.description' THEN 'Description'
    WHEN 'admin.translations.turkish' THEN 'Turkish'
    WHEN 'admin.translations.english' THEN 'English'
    WHEN 'admin.translations.actions' THEN 'Actions'
    WHEN 'admin.translations.edit' THEN 'Edit Translation'
    WHEN 'admin.translations.add_new' THEN 'Add New Translation'
    WHEN 'admin.translations.save_success' THEN 'Translation saved successfully'
    WHEN 'admin.translations.delete_success' THEN 'Translation deleted successfully'
    WHEN 'admin.translations.delete_confirm' THEN 'Are you sure you want to delete this translation?'
    WHEN 'admin.translations.filter_by_group' THEN 'Filter by Group'
    WHEN 'admin.translations.all_groups' THEN 'All Groups'
    WHEN 'admin.translations.search_placeholder' THEN 'Search translations...'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.translations.%';

-- ==========================
-- ADMIN - USERS (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'admin.users.page_title' THEN 'User Management'
    WHEN 'admin.users.list_title' THEN 'Users'
    WHEN 'admin.users.add_new' THEN 'Add New User'
    WHEN 'admin.users.edit' THEN 'Edit User'
    WHEN 'admin.users.name' THEN 'Name'
    WHEN 'admin.users.email' THEN 'Email'
    WHEN 'admin.users.role' THEN 'Role'
    WHEN 'admin.users.status' THEN 'Status'
    WHEN 'admin.users.actions' THEN 'Actions'
    WHEN 'admin.users.delete_confirm' THEN 'Are you sure you want to delete this user?'
    WHEN 'admin.users.save_success' THEN 'User saved successfully'
    WHEN 'admin.users.delete_success' THEN 'User deleted successfully'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.users.%';

-- ==========================
-- ADMIN - COMMON (EN)
-- ==========================

INSERT INTO i18n_values (key_id, lang_id, value)
SELECT id, 1, CASE key_name
    WHEN 'admin.common.dashboard' THEN 'Dashboard'
    WHEN 'admin.common.products' THEN 'Products'
    WHEN 'admin.common.categories' THEN 'Categories'
    WHEN 'admin.common.orders' THEN 'Orders'
    WHEN 'admin.common.customers' THEN 'Customers'
    WHEN 'admin.common.users' THEN 'Users'
    WHEN 'admin.common.settings' THEN 'Settings'
    WHEN 'admin.common.translations' THEN 'Translations'
    WHEN 'admin.common.logout' THEN 'Logout'
    WHEN 'admin.common.welcome' THEN 'Welcome'
    WHEN 'admin.common.search' THEN 'Search'
    WHEN 'admin.common.filter' THEN 'Filter'
    WHEN 'admin.common.export' THEN 'Export'
    WHEN 'admin.common.import' THEN 'Import'
    WHEN 'admin.common.bulk_actions' THEN 'Bulk Actions'
    WHEN 'admin.common.select_all' THEN 'Select All'
    WHEN 'admin.common.no_results' THEN 'No results found'
    WHEN 'admin.common.loading' THEN 'Loading...'
    WHEN 'admin.common.confirm_action' THEN 'Do you confirm the action?'
END
FROM i18n_keys
WHERE key_name LIKE 'admin.common.%';
