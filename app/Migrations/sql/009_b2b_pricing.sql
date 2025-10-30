-- ============================================
-- Migration 009: B2B Pricing & Customer Groups
-- ============================================

-- NOTE: customer_groups table created in migration 004
-- Extending with additional columns if needed

ALTER TABLE customer_groups
ADD COLUMN IF NOT EXISTS code VARCHAR(50) AFTER name,
ADD COLUMN IF NOT EXISTS description TEXT AFTER code,
ADD COLUMN IF NOT EXISTS default_discount_percent DECIMAL(5,2) DEFAULT 0 AFTER description,
ADD COLUMN IF NOT EXISTS credit_limit_enabled TINYINT(1) DEFAULT 0 AFTER default_discount_percent,
ADD COLUMN IF NOT EXISTS default_credit_limit DECIMAL(18,2) DEFAULT 0 AFTER credit_limit_enabled,
ADD COLUMN IF NOT EXISTS show_prices TINYINT(1) DEFAULT 1 AFTER default_credit_limit,
ADD COLUMN IF NOT EXISTS require_approval TINYINT(1) DEFAULT 0 AFTER show_prices,
ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1 AFTER require_approval,
ADD COLUMN IF NOT EXISTS sort_order INT DEFAULT 0 AFTER is_active,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add unique code if it doesn't exist
SET @code_index_check = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                          WHERE TABLE_SCHEMA = DATABASE()
                          AND TABLE_NAME = 'customer_groups'
                          AND INDEX_NAME = 'code');

SET @sql = IF(@code_index_check = 0,
  'ALTER TABLE customer_groups ADD UNIQUE KEY code (code)',
  'SELECT "Index already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Tier Pricing (Quantity Discounts)
CREATE TABLE IF NOT EXISTS product_tier_prices (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  product_id INT NOT NULL,
  customer_group_id INT DEFAULT NULL COMMENT 'NULL = applies to all',

  -- Tier
  quantity_min INT NOT NULL COMMENT 'e.g., 10 units',
  quantity_max INT DEFAULT NULL COMMENT 'NULL = unlimited',

  -- Price
  price DECIMAL(18,4) NOT NULL,
  discount_percent DECIMAL(5,2) DEFAULT NULL COMMENT 'Alternative: % off base price',

  -- Priority (lower = higher priority)
  priority INT DEFAULT 100,

  -- Status
  is_active TINYINT(1) DEFAULT 1,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_product_id (product_id),
  INDEX idx_customer_group_id (customer_group_id),
  INDEX idx_quantity_min (quantity_min),
  FOREIGN KEY (customer_group_id) REFERENCES customer_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Multi-Currency Pricing
CREATE TABLE IF NOT EXISTS product_currency_prices (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  product_id INT NOT NULL,
  currency_code VARCHAR(3) NOT NULL COMMENT 'EUR, USD, TRY',

  -- Price
  price DECIMAL(18,4) NOT NULL,
  compare_price DECIMAL(18,4) DEFAULT NULL,

  -- Auto-update
  auto_update TINYINT(1) DEFAULT 0 COMMENT 'Update from exchange rates',
  exchange_rate DECIMAL(10,6) DEFAULT NULL COMMENT 'Rate used for conversion',

  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_product_currency (product_id, currency_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NOTE: tax_classes table created in migration 002
-- Extending with additional columns if needed

ALTER TABLE tax_classes
ADD COLUMN IF NOT EXISTS code VARCHAR(50) AFTER name,
ADD COLUMN IF NOT EXISTS default_rate DECIMAL(5,2) DEFAULT NULL AFTER code,
ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1 AFTER default_rate,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Update default_rate from rate_percent if needed
UPDATE tax_classes SET default_rate = rate_percent WHERE default_rate IS NULL;

-- Add unique code if it doesn't exist
SET @tax_code_index_check = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                          WHERE TABLE_SCHEMA = DATABASE()
                          AND TABLE_NAME = 'tax_classes'
                          AND INDEX_NAME = 'code');

SET @sql = IF(@tax_code_index_check = 0,
  'ALTER TABLE tax_classes ADD UNIQUE KEY code (code)',
  'SELECT "Index already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Tax Rates by Region
CREATE TABLE IF NOT EXISTS tax_rates (
  id INT PRIMARY KEY AUTO_INCREMENT,

  tax_class_id INT NOT NULL,

  -- Region
  country_code VARCHAR(2) NOT NULL COMMENT 'TR, DE, FR, etc.',
  region VARCHAR(100) DEFAULT NULL COMMENT 'State/Province (optional)',
  zip_code VARCHAR(20) DEFAULT NULL COMMENT 'Specific ZIP (optional)',

  -- Rate
  rate DECIMAL(5,2) NOT NULL,

  -- Priority (for overlapping rules)
  priority INT DEFAULT 100,

  is_active TINYINT(1) DEFAULT 1,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_tax_class_id (tax_class_id),
  INDEX idx_country_code (country_code),
  FOREIGN KEY (tax_class_id) REFERENCES tax_classes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Add Foreign Keys to products (created in migration 008)
-- ============================================

-- product_tier_prices -> products
SET @fk_check = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME='fk_product_tier_prices_product' AND TABLE_SCHEMA = DATABASE());
SET @sql = IF(@fk_check = 0, 'ALTER TABLE product_tier_prices ADD CONSTRAINT fk_product_tier_prices_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE', 'SELECT "FK already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- product_currency_prices -> products
SET @fk_check = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME='fk_product_currency_prices_product' AND TABLE_SCHEMA = DATABASE());
SET @sql = IF(@fk_check = 0, 'ALTER TABLE product_currency_prices ADD CONSTRAINT fk_product_currency_prices_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE', 'SELECT "FK already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- NOTE: Customer Groups seed data already exists in migration 004
-- Updating only if using new column names

-- Update customer groups with new columns (if they exist)
UPDATE customer_groups SET code = slug WHERE code IS NULL OR code = '';
UPDATE customer_groups SET default_discount_percent = discount_percent WHERE default_discount_percent IS NULL;
UPDATE customer_groups SET default_credit_limit = credit_limit WHERE default_credit_limit IS NULL;

-- Seed: Tax Classes
INSERT IGNORE INTO tax_classes (id, name, code, default_rate) VALUES
(1, 'Standart KDV', 'standard', 20.00),
(2, 'İndirimli KDV', 'reduced', 8.00),
(3, 'KDV İstisna', 'exempt', 0.00);

-- Seed: Tax Rates
INSERT IGNORE INTO tax_rates (tax_class_id, country_code, rate) VALUES
(1, 'TR', 20.00),
(2, 'TR', 8.00),
(1, 'DE', 19.00),
(1, 'FR', 20.00);

-- Seed: Tier Pricing Example
INSERT IGNORE INTO product_tier_prices (product_id, customer_group_id, quantity_min, quantity_max, price) VALUES
-- Retail: normal prices
(1, 1, 1, 9, 95.00),
(1, 1, 10, 49, 90.00),
(1, 1, 50, NULL, 85.00),

-- Distributor: better prices
(1, 3, 1, 9, 75.00),
(1, 3, 10, 49, 70.00),
(1, 3, 50, NULL, 65.00);

-- NOTE: users table customer_group columns already added in migration 004
-- No need to add again
