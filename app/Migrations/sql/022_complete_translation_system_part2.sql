-- ============================================================================
-- Migration 022: Complete Translation System - Admin Panel Keys
-- Date: 2025-11-05
-- Description: Add all admin panel translation keys
-- ============================================================================

SET NAMES utf8mb4;

-- ==========================
-- ADMIN - DASHBOARD
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('admin.dashboard.page_title', 'admin', 'Dashboard page title'),
('admin.dashboard.welcome', 'admin', 'Welcome message'),
('admin.dashboard.total_orders', 'admin', 'Total orders'),
('admin.dashboard.total_revenue', 'admin', 'Total revenue'),
('admin.dashboard.total_products', 'admin', 'Total products'),
('admin.dashboard.total_customers', 'admin', 'Total customers'),
('admin.dashboard.pending_orders', 'admin', 'Pending orders'),
('admin.dashboard.recent_orders', 'admin', 'Recent orders'),
('admin.dashboard.view_all', 'admin', 'View all link'),
('admin.dashboard.order_number', 'admin', 'Order number'),
('admin.dashboard.customer', 'admin', 'Customer'),
('admin.dashboard.total', 'admin', 'Total'),
('admin.dashboard.status', 'admin', 'Status'),
('admin.dashboard.date', 'admin', 'Date'),
('admin.dashboard.low_stock_products', 'admin', 'Low stock products'),
('admin.dashboard.stock_level', 'admin', 'Stock level');

-- ==========================
-- ADMIN - PRODUCTS
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('admin.products.page_title', 'admin', 'Products page title'),
('admin.products.list_title', 'admin', 'Products list title'),
('admin.products.add_new', 'admin', 'Add new product button'),
('admin.products.edit', 'admin', 'Edit product'),
('admin.products.delete', 'admin', 'Delete product'),
('admin.products.name', 'admin', 'Product name'),
('admin.products.sku', 'admin', 'SKU'),
('admin.products.category', 'admin', 'Category'),
('admin.products.price', 'admin', 'Price'),
('admin.products.stock', 'admin', 'Stock'),
('admin.products.status', 'admin', 'Status'),
('admin.products.actions', 'admin', 'Actions'),
('admin.products.active', 'admin', 'Active'),
('admin.products.inactive', 'admin', 'Inactive'),
('admin.products.basic_info', 'admin', 'Basic information'),
('admin.products.pricing', 'admin', 'Pricing'),
('admin.products.inventory', 'admin', 'Inventory'),
('admin.products.images', 'admin', 'Images'),
('admin.products.seo', 'admin', 'SEO'),
('admin.products.specifications', 'admin', 'Specifications'),
('admin.products.delete_confirm', 'admin', 'Delete confirmation message'),
('admin.products.save_success', 'admin', 'Product saved successfully'),
('admin.products.delete_success', 'admin', 'Product deleted successfully'),
('admin.products.featured', 'admin', 'Featured'),
('admin.products.b2b_only', 'admin', 'B2B only'),
('admin.products.allow_samples', 'admin', 'Allow samples'),
('admin.products.calculator_type', 'admin', 'Calculator type'),
('admin.products.coverage_per_unit', 'admin', 'Coverage per unit'),
('admin.products.main_image', 'admin', 'Main image'),
('admin.products.gallery_images', 'admin', 'Gallery images'),
('admin.products.meta_title', 'admin', 'Meta title'),
('admin.products.meta_description', 'admin', 'Meta description'),
('admin.products.meta_keywords', 'admin', 'Meta keywords');

-- ==========================
-- ADMIN - CATEGORIES
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('admin.categories.page_title', 'admin', 'Categories page title'),
('admin.categories.list_title', 'admin', 'Categories list title'),
('admin.categories.add_new', 'admin', 'Add new category'),
('admin.categories.edit', 'admin', 'Edit category'),
('admin.categories.delete', 'admin', 'Delete category'),
('admin.categories.name', 'admin', 'Category name'),
('admin.categories.slug', 'admin', 'Slug'),
('admin.categories.parent', 'admin', 'Parent category'),
('admin.categories.products_count', 'admin', 'Products count'),
('admin.categories.actions', 'admin', 'Actions'),
('admin.categories.no_parent', 'admin', 'No parent (root)'),
('admin.categories.delete_confirm', 'admin', 'Delete confirmation'),
('admin.categories.save_success', 'admin', 'Category saved successfully'),
('admin.categories.delete_success', 'admin', 'Category deleted successfully'),
('admin.categories.description', 'admin', 'Description'),
('admin.categories.image', 'admin', 'Category image');

-- ==========================
-- ADMIN - ORDERS
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('admin.orders.page_title', 'admin', 'Orders page title'),
('admin.orders.list_title', 'admin', 'Orders list title'),
('admin.orders.view', 'admin', 'View order'),
('admin.orders.order_number', 'admin', 'Order number'),
('admin.orders.customer', 'admin', 'Customer'),
('admin.orders.date', 'admin', 'Order date'),
('admin.orders.total', 'admin', 'Total'),
('admin.orders.status', 'admin', 'Status'),
('admin.orders.payment_status', 'admin', 'Payment status'),
('admin.orders.actions', 'admin', 'Actions'),
('admin.orders.details', 'admin', 'Order details'),
('admin.orders.items', 'admin', 'Order items'),
('admin.orders.billing_address', 'admin', 'Billing address'),
('admin.orders.shipping_address', 'admin', 'Shipping address'),
('admin.orders.product', 'admin', 'Product'),
('admin.orders.quantity', 'admin', 'Quantity'),
('admin.orders.price', 'admin', 'Price'),
('admin.orders.subtotal', 'admin', 'Subtotal'),
('admin.orders.discount', 'admin', 'Discount'),
('admin.orders.tax', 'admin', 'Tax'),
('admin.orders.shipping', 'admin', 'Shipping'),
('admin.orders.grand_total', 'admin', 'Grand total'),
('admin.orders.update_status', 'admin', 'Update status'),
('admin.orders.status_pending', 'admin', 'Pending'),
('admin.orders.status_processing', 'admin', 'Processing'),
('admin.orders.status_shipped', 'admin', 'Shipped'),
('admin.orders.status_delivered', 'admin', 'Delivered'),
('admin.orders.status_cancelled', 'admin', 'Cancelled'),
('admin.orders.payment_pending', 'admin', 'Payment pending'),
('admin.orders.payment_paid', 'admin', 'Paid'),
('admin.orders.payment_failed', 'admin', 'Payment failed'),
('admin.orders.payment_refunded', 'admin', 'Refunded');

-- ==========================
-- ADMIN - CUSTOMERS
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('admin.customers.page_title', 'admin', 'Customers page title'),
('admin.customers.list_title', 'admin', 'Customers list title'),
('admin.customers.add_new', 'admin', 'Add new customer'),
('admin.customers.edit', 'admin', 'Edit customer'),
('admin.customers.view', 'admin', 'View customer'),
('admin.customers.name', 'admin', 'Customer name'),
('admin.customers.email', 'admin', 'Email'),
('admin.customers.phone', 'admin', 'Phone'),
('admin.customers.group', 'admin', 'Customer group'),
('admin.customers.orders_count', 'admin', 'Orders count'),
('admin.customers.total_spent', 'admin', 'Total spent'),
('admin.customers.status', 'admin', 'Status'),
('admin.customers.actions', 'admin', 'Actions'),
('admin.customers.details', 'admin', 'Customer details'),
('admin.customers.order_history', 'admin', 'Order history'),
('admin.customers.addresses', 'admin', 'Addresses');

-- ==========================
-- ADMIN - SETTINGS
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('admin.settings.page_title', 'admin', 'Settings page title'),
('admin.settings.general', 'admin', 'General settings'),
('admin.settings.site_name', 'admin', 'Site name'),
('admin.settings.site_description', 'admin', 'Site description'),
('admin.settings.currency', 'admin', 'Currency'),
('admin.settings.tax_rate', 'admin', 'Tax rate'),
('admin.settings.shipping', 'admin', 'Shipping settings'),
('admin.settings.payment', 'admin', 'Payment settings'),
('admin.settings.email', 'admin', 'Email settings'),
('admin.settings.save', 'admin', 'Save settings'),
('admin.settings.save_success', 'admin', 'Settings saved successfully');

-- ==========================
-- ADMIN - TRANSLATIONS
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('admin.translations.page_title', 'admin', 'Translations page title'),
('admin.translations.list_title', 'admin', 'Translations list'),
('admin.translations.key', 'admin', 'Translation key'),
('admin.translations.group', 'admin', 'Group'),
('admin.translations.description', 'admin', 'Description'),
('admin.translations.turkish', 'admin', 'Turkish'),
('admin.translations.english', 'admin', 'English'),
('admin.translations.actions', 'admin', 'Actions'),
('admin.translations.edit', 'admin', 'Edit translation'),
('admin.translations.add_new', 'admin', 'Add new translation'),
('admin.translations.save_success', 'admin', 'Translation saved successfully'),
('admin.translations.delete_success', 'admin', 'Translation deleted successfully'),
('admin.translations.delete_confirm', 'admin', 'Delete confirmation'),
('admin.translations.filter_by_group', 'admin', 'Filter by group'),
('admin.translations.all_groups', 'admin', 'All groups'),
('admin.translations.search_placeholder', 'admin', 'Search translations...');

-- ==========================
-- ADMIN - USERS
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('admin.users.page_title', 'admin', 'Users page title'),
('admin.users.list_title', 'admin', 'Users list'),
('admin.users.add_new', 'admin', 'Add new user'),
('admin.users.edit', 'admin', 'Edit user'),
('admin.users.name', 'admin', 'Name'),
('admin.users.email', 'admin', 'Email'),
('admin.users.role', 'admin', 'Role'),
('admin.users.status', 'admin', 'Status'),
('admin.users.actions', 'admin', 'Actions'),
('admin.users.delete_confirm', 'admin', 'Delete confirmation'),
('admin.users.save_success', 'admin', 'User saved successfully'),
('admin.users.delete_success', 'admin', 'User deleted successfully');

-- ==========================
-- ADMIN - COMMON
-- ==========================

INSERT INTO i18n_keys (key_name, `group`, description) VALUES
('admin.common.dashboard', 'admin', 'Dashboard menu'),
('admin.common.products', 'admin', 'Products menu'),
('admin.common.categories', 'admin', 'Categories menu'),
('admin.common.orders', 'admin', 'Orders menu'),
('admin.common.customers', 'admin', 'Customers menu'),
('admin.common.users', 'admin', 'Users menu'),
('admin.common.settings', 'admin', 'Settings menu'),
('admin.common.translations', 'admin', 'Translations menu'),
('admin.common.logout', 'admin', 'Logout'),
('admin.common.welcome', 'admin', 'Welcome message'),
('admin.common.search', 'admin', 'Search'),
('admin.common.filter', 'admin', 'Filter'),
('admin.common.export', 'admin', 'Export'),
('admin.common.import', 'admin', 'Import'),
('admin.common.bulk_actions', 'admin', 'Bulk actions'),
('admin.common.select_all', 'admin', 'Select all'),
('admin.common.no_results', 'admin', 'No results found'),
('admin.common.loading', 'admin', 'Loading...'),
('admin.common.confirm_action', 'admin', 'Confirm action');
