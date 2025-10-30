-- ============================================
-- Migration 009: B2B Pricing & Customer Groups
-- ============================================

-- Customer Groups
CREATE TABLE IF NOT EXISTS customer_groups (
  id INT PRIMARY KEY AUTO_INCREMENT,

  -- Group Info
  name VARCHAR(100) NOT NULL UNIQUE,
  code VARCHAR(50) NOT NULL UNIQUE COMMENT 'e.g., "retail", "wholesale", "distributor"',
  description TEXT DEFAULT NULL,

  -- Discounts
  default_discount_percent DECIMAL(5,2) DEFAULT 0 COMMENT 'General discount %',

  -- B2B Features
  credit_limit_enabled TINYINT(1) DEFAULT 0,
  default_credit_limit DECIMAL(18,2) DEFAULT 0,
  payment_terms_days INT DEFAULT 0 COMMENT '0=immediate, 30=net30, etc.',

  -- Visibility
  show_prices TINYINT(1) DEFAULT 1 COMMENT 'Show prices on site',
  require_approval TINYINT(1) DEFAULT 0 COMMENT 'New customers need approval',

  -- Status
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_code (code),
  INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
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

  UNIQUE KEY uniq_product_currency (product_id, currency_code),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tax Classes
CREATE TABLE IF NOT EXISTS tax_classes (
  id INT PRIMARY KEY AUTO_INCREMENT,

  name VARCHAR(100) NOT NULL,
  code VARCHAR(50) NOT NULL UNIQUE,
  default_rate DECIMAL(5,2) NOT NULL COMMENT 'e.g., 20.00 for 20% VAT',

  is_active TINYINT(1) DEFAULT 1,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- Update products table foreign key
ALTER TABLE products
ADD CONSTRAINT fk_products_tax_class
FOREIGN KEY (tax_class_id) REFERENCES tax_classes(id) ON DELETE SET NULL;

-- Seed: Customer Groups
INSERT IGNORE INTO customer_groups (id, name, code, default_discount_percent, credit_limit_enabled, default_credit_limit, payment_terms_days, sort_order) VALUES
(1, 'Bireysel Müşteri', 'retail', 0, 0, 0, 0, 1),
(2, 'Kurumsal Müşteri', 'corporate', 5, 1, 50000, 30, 2),
(3, 'Bayi / Distribütör', 'distributor', 15, 1, 150000, 60, 3),
(4, 'VIP Müşteri', 'vip', 10, 1, 100000, 45, 4);

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

-- Update users table to link customer groups
ALTER TABLE users
ADD COLUMN IF NOT EXISTS customer_group_id INT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS credit_limit DECIMAL(18,2) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS credit_used DECIMAL(18,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS payment_terms_days INT DEFAULT 0,
ADD CONSTRAINT fk_users_customer_group
FOREIGN KEY (customer_group_id) REFERENCES customer_groups(id) ON DELETE SET NULL;
