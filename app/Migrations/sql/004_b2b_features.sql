-- ============================================
-- Migration 004: B2B Features
-- ============================================

-- Customer groups
CREATE TABLE IF NOT EXISTS customer_groups (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  discount_percent DECIMAL(5,2) DEFAULT 0,
  credit_limit BIGINT DEFAULT 0 COMMENT 'Maximum credit limit',
  payment_term_days INT DEFAULT 0 COMMENT 'Net 30, 60, 90',
  min_order_amount BIGINT DEFAULT 0,
  is_default TINYINT(1) DEFAULT 0,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Extend users table for B2B
ALTER TABLE users
ADD COLUMN IF NOT EXISTS customer_group_id INT DEFAULT NULL COMMENT 'Customer group ID',
ADD COLUMN IF NOT EXISTS company_name VARCHAR(255) DEFAULT NULL COMMENT 'Company name for B2B',
ADD COLUMN IF NOT EXISTS tax_number VARCHAR(50) DEFAULT NULL COMMENT 'Tax ID number',
ADD COLUMN IF NOT EXISTS tax_office VARCHAR(255) DEFAULT NULL COMMENT 'Tax office name',
ADD COLUMN IF NOT EXISTS credit_limit BIGINT DEFAULT 0 COMMENT 'Credit limit amount',
ADD COLUMN IF NOT EXISTS credit_used BIGINT DEFAULT 0 COMMENT 'Used credit amount',
ADD COLUMN IF NOT EXISTS payment_term_days INT DEFAULT 0 COMMENT 'Payment terms in days',
ADD COLUMN IF NOT EXISTS is_verified_business TINYINT(1) DEFAULT 0 COMMENT 'Business verification status',
ADD COLUMN IF NOT EXISTS business_documents TEXT DEFAULT NULL COMMENT 'JSON array of document paths',
ADD COLUMN IF NOT EXISTS rfm_score INT DEFAULT 0 COMMENT 'RFM analysis score',
ADD COLUMN IF NOT EXISTS clv BIGINT DEFAULT 0 COMMENT 'Customer lifetime value',
ADD COLUMN IF NOT EXISTS totp_secret VARCHAR(255) DEFAULT NULL COMMENT '2FA TOTP secret';

-- Add FK for customer group
SET @fk_check = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                 WHERE CONSTRAINT_NAME='fk_users_customer_group'
                 AND TABLE_NAME='users');

SET @sql = IF(@fk_check = 0,
  'ALTER TABLE users ADD CONSTRAINT fk_users_customer_group FOREIGN KEY (customer_group_id) REFERENCES customer_groups(id) ON DELETE SET NULL',
  'SELECT "FK already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Customer group specific prices
CREATE TABLE IF NOT EXISTS customer_group_prices (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  customer_group_id INT NOT NULL,
  currency_code VARCHAR(3) NOT NULL,
  price DECIMAL(18,4) NOT NULL,

  UNIQUE KEY uniq_product_group_currency (product_id, customer_group_id, currency_code),
  INDEX idx_product_id (product_id),
  FOREIGN KEY (customer_group_id) REFERENCES customer_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='FK to products will be added in migration 008';

-- Sample orders (numune)
CREATE TABLE IF NOT EXISTS sample_orders (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  status ENUM('pending','approved','shipped','delivered','rejected') DEFAULT 'pending',
  notes TEXT DEFAULT NULL,
  shipping_cost DECIMAL(18,4) DEFAULT 0,
  is_free TINYINT(1) DEFAULT 0,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

  INDEX idx_user_id (user_id),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample order items
CREATE TABLE IF NOT EXISTS sample_order_items (
  id INT PRIMARY KEY AUTO_INCREMENT,
  sample_order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT DEFAULT 1,

  INDEX idx_product_id (product_id),
  FOREIGN KEY (sample_order_id) REFERENCES sample_orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='FK to products will be added in migration 008';

-- Dealer applications
CREATE TABLE IF NOT EXISTS dealer_applications (
  id INT PRIMARY KEY AUTO_INCREMENT,
  company_name VARCHAR(255) NOT NULL,
  contact_person VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(50) NOT NULL,
  tax_number VARCHAR(50) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  region VARCHAR(255) DEFAULT NULL,
  annual_revenue_target BIGINT DEFAULT NULL,
  documents VARCHAR(1000) DEFAULT NULL COMMENT 'JSON array',
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  notes TEXT DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewed_at TIMESTAMP NULL,
  reviewed_by INT DEFAULT NULL,

  INDEX idx_status (status),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product relations (bağlı ürünler, C_bagliurunler compat)
CREATE TABLE IF NOT EXISTS product_relations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  related_product_id INT NOT NULL,
  relation_type ENUM('related','cross-sell','up-sell','bundle','alternative') DEFAULT 'related',
  visibility TINYINT(1) DEFAULT 1,
  display_order INT DEFAULT 1,
  external_id VARCHAR(255) DEFAULT NULL COMMENT 'localid',

  UNIQUE KEY uniq_product_relation (product_id, related_product_id, relation_type),
  INDEX idx_product_id (product_id),
  INDEX idx_related (related_product_id),
  INDEX idx_relation_type (relation_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='FK to products will be added in migration 008';

-- Seed default customer groups
INSERT INTO customer_groups (name, slug, discount_percent, credit_limit, payment_term_days, is_default) VALUES
('Bireysel', 'individual', 0, 0, 0, 1),
('Kurumsal', 'corporate', 5, 50000, 30, 0),
('Bayi', 'dealer', 10, 100000, 60, 0),
('VIP', 'vip', 15, 200000, 90, 0)
ON DUPLICATE KEY UPDATE name=VALUES(name);
