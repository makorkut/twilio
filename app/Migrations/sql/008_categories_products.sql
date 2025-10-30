-- ============================================
-- Migration 008: Categories & Products
-- ============================================

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

-- Add FK from products to tax_classes
ALTER TABLE products
ADD CONSTRAINT fk_products_tax_class
FOREIGN KEY (tax_class_id) REFERENCES tax_classes(id) ON DELETE SET NULL;

-- Add FK from product_prices_currency to products
ALTER TABLE product_prices_currency
ADD CONSTRAINT fk_product_prices_product
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;

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
-- Add Foreign Keys from earlier migrations
-- ============================================

-- From migration 004: customer_group_prices -> products
ALTER TABLE customer_group_prices
ADD CONSTRAINT fk_customer_group_prices_product
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;

-- From migration 004: sample_order_items -> products
ALTER TABLE sample_order_items
ADD CONSTRAINT fk_sample_order_items_product
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;

-- From migration 006: product_reviews -> products
ALTER TABLE product_reviews
ADD CONSTRAINT fk_product_reviews_product
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;

-- From migration 006: product_questions -> products
ALTER TABLE product_questions
ADD CONSTRAINT fk_product_questions_product
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;

-- From migration 006: product_comparisons -> products
ALTER TABLE product_comparisons
ADD CONSTRAINT fk_product_comparisons_product
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;

-- From migration 007: wishlists -> products
ALTER TABLE wishlists
ADD CONSTRAINT fk_wishlists_product
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;

-- From migration 007: product_views -> products
ALTER TABLE product_views
ADD CONSTRAINT fk_product_views_product
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;

-- From migration 007: search_logs -> products (nullable)
ALTER TABLE search_logs
ADD CONSTRAINT fk_search_logs_product
FOREIGN KEY (clicked_product_id) REFERENCES products(id) ON DELETE SET NULL;

-- From migration 007: fraud_checks -> products (nullable)
ALTER TABLE fraud_checks
ADD CONSTRAINT fk_fraud_checks_product
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL;
