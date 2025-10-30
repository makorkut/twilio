-- ============================================
-- Migration 008: Categories & Products
-- ============================================

-- Drop tables if they exist (for clean re-run after errors)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS product_custom_fields;
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS product_categories;
DROP TABLE IF EXISTS product_translations;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS category_translations;
DROP TABLE IF EXISTS categories;
SET FOREIGN_KEY_CHECKS = 1;

-- Categories (Hierarchical)
CREATE TABLE IF NOT EXISTS categories (
  id INT PRIMARY KEY AUTO_INCREMENT,
  parent_id INT DEFAULT NULL,

  -- Basic Info
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,

  -- Metadata
  meta_title VARCHAR(255) DEFAULT NULL,
  meta_description TEXT DEFAULT NULL,
  meta_keywords VARCHAR(500) DEFAULT NULL,

  -- Display
  icon VARCHAR(100) DEFAULT NULL,
  image_id BIGINT DEFAULT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  is_featured TINYINT(1) DEFAULT 0,

  -- Stats (cached)
  product_count INT DEFAULT 0,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_slug (slug),
  INDEX idx_parent_id (parent_id),
  INDEX idx_is_active (is_active),
  INDEX idx_sort_order (sort_order),
  FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Category Translations
CREATE TABLE IF NOT EXISTS category_translations (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  category_id INT NOT NULL,
  lang_code VARCHAR(5) NOT NULL,

  name VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  meta_title VARCHAR(255) DEFAULT NULL,
  meta_description TEXT DEFAULT NULL,

  UNIQUE KEY uniq_category_lang (category_id, lang_code),
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products
CREATE TABLE IF NOT EXISTS products (
  id INT PRIMARY KEY AUTO_INCREMENT,

  -- Basic Info
  sku VARCHAR(100) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  short_description TEXT DEFAULT NULL,
  description TEXT DEFAULT NULL,

  -- Pricing
  base_price DECIMAL(18,4) NOT NULL DEFAULT 0,
  compare_price DECIMAL(18,4) DEFAULT NULL COMMENT 'Original price (for discount display)',
  cost_price DECIMAL(18,4) DEFAULT NULL COMMENT 'Your cost (for margin calc)',
  price_visibility ENUM('visible','hidden','login_required') DEFAULT 'visible',

  -- Tax
  tax_class_id INT DEFAULT NULL,

  -- Inventory
  track_inventory TINYINT(1) DEFAULT 1,
  stock_quantity INT DEFAULT 0,
  low_stock_threshold INT DEFAULT 10,
  allow_backorder TINYINT(1) DEFAULT 0,

  -- Physical
  weight_kg DECIMAL(10,3) DEFAULT NULL,
  length_mm DECIMAL(10,2) DEFAULT NULL,
  width_mm DECIMAL(10,2) DEFAULT NULL,
  height_mm DECIMAL(10,2) DEFAULT NULL,

  -- Polyurethane Specific
  selling_unit ENUM('piece','meter','m2','package') DEFAULT 'piece',
  package_quantity INT DEFAULT 1 COMMENT 'How many units in 1 package',
  package_length_total_m DECIMAL(10,2) DEFAULT NULL COMMENT 'Total meters in 1 package',

  -- Status
  status ENUM('draft','active','inactive','archived') DEFAULT 'draft',
  is_featured TINYINT(1) DEFAULT 0,
  is_new TINYINT(1) DEFAULT 0,
  new_until DATE DEFAULT NULL,

  -- B2B
  is_b2b_only TINYINT(1) DEFAULT 0,
  min_order_quantity INT DEFAULT 1,
  max_order_quantity INT DEFAULT NULL,

  -- SEO
  meta_title VARCHAR(255) DEFAULT NULL,
  meta_description TEXT DEFAULT NULL,
  meta_keywords VARCHAR(500) DEFAULT NULL,

  -- Media
  main_image_id BIGINT DEFAULT NULL,

  -- Stats (cached)
  view_count INT DEFAULT 0,
  order_count INT DEFAULT 0,
  rating_avg DECIMAL(3,2) DEFAULT 0,
  review_count INT DEFAULT 0,

  -- Automation (from migration 005)
  auto_update_title TINYINT(1) DEFAULT 0,
  auto_update_description TINYINT(1) DEFAULT 0,
  auto_update_price TINYINT(1) DEFAULT 0,
  auto_update_images TINYINT(1) DEFAULT 0,
  auto_update_stock TINYINT(1) DEFAULT 0,
  sync_status ENUM('pending','synced','failed','outdated') DEFAULT 'synced',
  last_synced_at TIMESTAMP NULL,
  sync_hash VARCHAR(64) DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_sku (sku),
  INDEX idx_slug (slug),
  INDEX idx_status (status),
  INDEX idx_is_featured (is_featured),
  INDEX idx_created_at (created_at),
  FULLTEXT idx_search (name, short_description, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Translations
CREATE TABLE IF NOT EXISTS product_translations (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  lang_code VARCHAR(5) NOT NULL,

  name VARCHAR(255) NOT NULL,
  short_description TEXT DEFAULT NULL,
  description TEXT DEFAULT NULL,
  meta_title VARCHAR(255) DEFAULT NULL,
  meta_description TEXT DEFAULT NULL,

  UNIQUE KEY uniq_product_lang (product_id, lang_code),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FULLTEXT idx_search_trans (name, short_description, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Categories (Many-to-Many)
CREATE TABLE IF NOT EXISTS product_categories (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  category_id INT NOT NULL,
  is_primary TINYINT(1) DEFAULT 0 COMMENT 'Primary category for breadcrumbs',

  UNIQUE KEY uniq_product_category (product_id, category_id),
  INDEX idx_category_id (category_id),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Variants (Size, Color, etc.)
CREATE TABLE IF NOT EXISTS product_variants (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,

  -- Variant Info
  sku VARCHAR(100) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL COMMENT 'e.g., "Large / Red"',

  -- Attributes
  attributes JSON DEFAULT NULL COMMENT '{"size":"L","color":"Red","ral":"RAL9010"}',

  -- Pricing (override)
  price_adjustment DECIMAL(18,4) DEFAULT 0 COMMENT 'Add to base price',
  price_override DECIMAL(18,4) DEFAULT NULL COMMENT 'Or use fixed price',

  -- Inventory
  stock_quantity INT DEFAULT 0,

  -- Physical (override)
  weight_kg DECIMAL(10,3) DEFAULT NULL,
  length_mm DECIMAL(10,2) DEFAULT NULL,
  width_mm DECIMAL(10,2) DEFAULT NULL,
  height_mm DECIMAL(10,2) DEFAULT NULL,

  -- Media
  image_id BIGINT DEFAULT NULL,

  -- Status
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_product_id (product_id),
  INDEX idx_sku (sku),
  INDEX idx_is_active (is_active),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Custom Fields (Dynamic attributes)
CREATE TABLE IF NOT EXISTS product_custom_fields (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,

  field_name VARCHAR(100) NOT NULL COMMENT 'e.g., "fire_rating", "warranty_years"',
  field_value TEXT NOT NULL,
  field_type ENUM('text','number','boolean','date','json') DEFAULT 'text',

  -- Display
  display_label VARCHAR(255) DEFAULT NULL,
  display_group VARCHAR(100) DEFAULT 'general' COMMENT 'Group fields together',
  sort_order INT DEFAULT 0,

  UNIQUE KEY uniq_product_field (product_id, field_name),
  INDEX idx_field_name (field_name),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed: Sample Categories
INSERT IGNORE INTO categories (id, parent_id, name, slug, sort_order, is_active) VALUES
(1, NULL, 'Duvar Kaplamaları', 'duvar-kaplamalari', 1, 1),
(2, NULL, 'Tavan Kaplamaları', 'tavan-kaplamalari', 2, 1),
(3, NULL, 'Profiller', 'profiller', 3, 1),
(4, 1, 'Poliüretan Panel', 'poliuretan-panel', 1, 1),
(5, 1, '3D Duvar Panelleri', '3d-duvar-panelleri', 2, 1),
(6, 3, 'Köşe Profilleri', 'kose-profilleri', 1, 1);

-- Seed: Sample Product
INSERT IGNORE INTO products (id, sku, name, slug, short_description, base_price, stock_quantity, status, selling_unit) VALUES
(1, 'PU-PANEL-001', 'Klasik Poliüretan Duvar Paneli', 'klasik-poliuretan-duvar-paneli',
 'Yüksek kaliteli poliüretan duvar kaplama paneli. Kolay montaj, uzun ömürlü.',
 95.00, 150, 'active', 'piece');

-- Link product to category
INSERT IGNORE INTO product_categories (product_id, category_id, is_primary) VALUES
(1, 4, 1);

-- ============================================
-- Add Foreign Keys (delayed from migration 002)
-- ============================================

-- Add FK from products to tax_classes (with check)
SET @fk_check = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                 WHERE CONSTRAINT_NAME='fk_products_tax_class'
                 AND TABLE_SCHEMA = DATABASE());

SET @sql = IF(@fk_check = 0,
  'ALTER TABLE products ADD CONSTRAINT fk_products_tax_class FOREIGN KEY (tax_class_id) REFERENCES tax_classes(id) ON DELETE SET NULL',
  'SELECT "FK fk_products_tax_class already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add FK from product_prices_currency to products (with check)
SET @fk_check = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                 WHERE CONSTRAINT_NAME='fk_product_prices_product'
                 AND TABLE_SCHEMA = DATABASE());

SET @sql = IF(@fk_check = 0,
  'ALTER TABLE product_prices_currency ADD CONSTRAINT fk_product_prices_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE',
  'SELECT "FK fk_product_prices_product already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- Add Triggers (delayed from migration 003)
-- ============================================

-- AUTO ATTACH TRIGGER: SKU tag → Product
-- When a media is tagged with SKU, auto-attach to matching product
DROP TRIGGER IF EXISTS auto_attach_media_to_product_by_sku;

CREATE TRIGGER auto_attach_media_to_product_by_sku
AFTER INSERT ON media_tag_relations
FOR EACH ROW
BEGIN
  DECLARE product_sku VARCHAR(255);
  DECLARE product_found INT;

  -- Check if tag is SKU type
  SELECT name INTO product_sku
  FROM media_tags
  WHERE id = NEW.tag_id AND tag_type = 'sku';

  IF product_sku IS NOT NULL THEN
    -- Find product by SKU
    SELECT id INTO product_found
    FROM products
    WHERE sku = product_sku
    LIMIT 1;

    IF product_found IS NOT NULL THEN
      -- Auto-attach media to product
      INSERT IGNORE INTO media_usage (media_id, entity_type, entity_id, usage_type)
      VALUES (NEW.media_id, 'product', product_found, 'gallery');
    END IF;
  END IF;
END;

-- ============================================
-- Add Foreign Keys from earlier migrations (with checks)
-- ============================================

-- From migration 004: customer_group_prices -> products
SET @fk_check = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME='fk_customer_group_prices_product' AND TABLE_SCHEMA = DATABASE());
SET @sql = IF(@fk_check = 0, 'ALTER TABLE customer_group_prices ADD CONSTRAINT fk_customer_group_prices_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE', 'SELECT "FK already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- From migration 004: sample_order_items -> products
SET @fk_check = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME='fk_sample_order_items_product' AND TABLE_SCHEMA = DATABASE());
SET @sql = IF(@fk_check = 0, 'ALTER TABLE sample_order_items ADD CONSTRAINT fk_sample_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE', 'SELECT "FK already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- NOTE: Tables product_reviews, product_questions, product_comparisons, wishlists don't exist

-- From migration 007: product_views -> products
SET @fk_check = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME='fk_product_views_product' AND TABLE_SCHEMA = DATABASE());
SET @sql = IF(@fk_check = 0, 'ALTER TABLE product_views ADD CONSTRAINT fk_product_views_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE', 'SELECT "FK already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- From migration 007: search_logs -> products (nullable)
SET @fk_check = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME='fk_search_logs_product' AND TABLE_SCHEMA = DATABASE());
SET @sql = IF(@fk_check = 0, 'ALTER TABLE search_logs ADD CONSTRAINT fk_search_logs_product FOREIGN KEY (clicked_product_id) REFERENCES products(id) ON DELETE SET NULL', 'SELECT "FK already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- From migration 004: product_relations -> products (both directions)
SET @fk_check = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME='fk_product_relations_product' AND TABLE_SCHEMA = DATABASE());
SET @sql = IF(@fk_check = 0, 'ALTER TABLE product_relations ADD CONSTRAINT fk_product_relations_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE', 'SELECT "FK already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_check = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME='fk_product_relations_related' AND TABLE_SCHEMA = DATABASE());
SET @sql = IF(@fk_check = 0, 'ALTER TABLE product_relations ADD CONSTRAINT fk_product_relations_related FOREIGN KEY (related_product_id) REFERENCES products(id) ON DELETE CASCADE', 'SELECT "FK already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- NOTE: FK constraints for product_tier_prices and product_currency_prices
-- will be added in migration 009 after those tables are created
