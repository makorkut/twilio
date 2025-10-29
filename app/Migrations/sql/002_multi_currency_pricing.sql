-- ============================================
-- Migration 002: Multi-Currency Pricing System
-- ============================================

-- Extend currencies table (mevcut)
ALTER TABLE currencies
ADD COLUMN IF NOT EXISTS `format_template` VARCHAR(50) DEFAULT '{symbol}{amount}' AFTER `symbol_direction`,
ADD COLUMN IF NOT EXISTS `decimal_places` TINYINT DEFAULT 2 AFTER `format_template`,
ADD COLUMN IF NOT EXISTS `is_default` TINYINT(1) DEFAULT 0 AFTER `status`,
ADD COLUMN IF NOT EXISTS `last_rate_update` TIMESTAMP NULL AFTER `exchange_rate`;

-- Product multi-currency prices
CREATE TABLE IF NOT EXISTS product_prices_currency (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  currency_code VARCHAR(3) NOT NULL,

  -- Pricing
  base_price DECIMAL(18,4) NOT NULL,
  compare_at_price DECIMAL(18,4) DEFAULT NULL,
  cost_price DECIMAL(18,4) DEFAULT NULL COMMENT 'Admin only',

  -- VAT
  vat_rate DECIMAL(5,2) DEFAULT 0,
  price_includes_vat TINYINT(1) DEFAULT 1,

  -- B2B Tier Pricing (Wholesale)
  tier_1_min_qty INT DEFAULT NULL,
  tier_1_price DECIMAL(18,4) DEFAULT NULL,
  tier_2_min_qty INT DEFAULT NULL,
  tier_2_price DECIMAL(18,4) DEFAULT NULL,
  tier_3_min_qty INT DEFAULT NULL,
  tier_3_price DECIMAL(18,4) DEFAULT NULL,

  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_product_currency (product_id, currency_code),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,

  INDEX idx_currency_code (currency_code),
  INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exchange rates history
CREATE TABLE IF NOT EXISTS exchange_rates (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  from_currency VARCHAR(3) NOT NULL,
  to_currency VARCHAR(3) NOT NULL,
  rate DECIMAL(18,6) NOT NULL,
  source VARCHAR(50) DEFAULT 'manual' COMMENT 'manual, tcmb, ecb, api',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_currencies (from_currency, to_currency),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tax classes
CREATE TABLE IF NOT EXISTS tax_classes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  rate_percent DECIMAL(5,2) NOT NULL,
  is_default TINYINT(1) DEFAULT 0,
  country_code VARCHAR(2) DEFAULT NULL COMMENT 'ISO 3166-1 alpha-2',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_is_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Extend products table
ALTER TABLE products
ADD COLUMN IF NOT EXISTS `vat_class_id` INT DEFAULT NULL AFTER `vat_rate`,
ADD COLUMN IF NOT EXISTS `price_visibility` ENUM('visible','hidden','login_required') DEFAULT 'visible' AFTER `vat_class_id`,
ADD COLUMN IF NOT EXISTS `compare_at_price` BIGINT DEFAULT NULL AFTER `price`,
ADD COLUMN IF NOT EXISTS `cost_price` BIGINT DEFAULT NULL AFTER `compare_at_price`,
ADD COLUMN IF NOT EXISTS `selling_unit` ENUM('piece','meter','m2','package') DEFAULT 'piece' AFTER `stock`,
ADD COLUMN IF NOT EXISTS `package_quantity` INT DEFAULT 1 AFTER `selling_unit`,
ADD COLUMN IF NOT EXISTS `length_mm` DECIMAL(10,2) DEFAULT NULL AFTER `package_quantity`,
ADD COLUMN IF NOT EXISTS `width_mm` DECIMAL(10,2) DEFAULT NULL AFTER `length_mm`,
ADD COLUMN IF NOT EXISTS `height_mm` DECIMAL(10,2) DEFAULT NULL AFTER `width_mm`,
ADD COLUMN IF NOT EXISTS `weight_kg` DECIMAL(10,3) DEFAULT NULL AFTER `height_mm`;

-- Add foreign key if not exists
SET @fk_check = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                 WHERE CONSTRAINT_NAME='fk_products_vat_class'
                 AND TABLE_NAME='products');

SET @sql = IF(@fk_check = 0,
  'ALTER TABLE products ADD CONSTRAINT fk_products_vat_class FOREIGN KEY (vat_class_id) REFERENCES tax_classes(id) ON DELETE SET NULL',
  'SELECT "FK already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Seed default tax classes
INSERT INTO tax_classes (name, rate_percent, is_default) VALUES
('Standart KDV (TR)', 20.00, 1),
('İndirimli KDV (TR)', 10.00, 0),
('İstisna', 0.00, 0),
('VAT 21% (EU)', 21.00, 0),
('No Tax (USA)', 0.00, 0)
ON DUPLICATE KEY UPDATE rate_percent=VALUES(rate_percent);
