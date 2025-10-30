-- ============================================
-- Migration 010: Orders & Shopping Cart
-- ============================================

-- Shopping Cart
CREATE TABLE IF NOT EXISTS cart (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  -- User or Session
  user_id INT DEFAULT NULL,
  session_id VARCHAR(100) DEFAULT NULL COMMENT 'For guest users',

  -- Pricing Context
  customer_group_id INT DEFAULT NULL,
  currency_code VARCHAR(3) DEFAULT 'TRY',

  -- Status
  status ENUM('active','abandoned','converted') DEFAULT 'active',
  converted_to_order_id INT DEFAULT NULL,

  -- Metadata
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent TEXT DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  abandoned_at TIMESTAMP NULL DEFAULT NULL,

  INDEX idx_user_id (user_id),
  INDEX idx_session_id (session_id),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cart Items
CREATE TABLE IF NOT EXISTS cart_items (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  cart_id BIGINT NOT NULL,
  product_id INT NOT NULL,
  variant_id INT DEFAULT NULL,

  -- Quantity
  quantity INT NOT NULL DEFAULT 1,

  -- Price (locked at add time)
  unit_price DECIMAL(18,4) NOT NULL,
  discount_amount DECIMAL(18,4) DEFAULT 0,
  tax_amount DECIMAL(18,4) DEFAULT 0,
  total_price DECIMAL(18,4) NOT NULL COMMENT 'unit_price * quantity - discount + tax',

  -- Custom Options (JSON)
  custom_options JSON DEFAULT NULL COMMENT 'e.g., {"engraving":"John Doe"}',

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_cart_id (cart_id),
  INDEX idx_product_id (product_id),
  FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders
CREATE TABLE IF NOT EXISTS orders (
  id INT PRIMARY KEY AUTO_INCREMENT,

  -- Order Number (public facing)
  order_number VARCHAR(50) NOT NULL UNIQUE COMMENT 'e.g., ORD-2025-00001',

  -- Customer
  user_id INT DEFAULT NULL COMMENT 'NULL for guest orders',
  customer_group_id INT DEFAULT NULL,

  customer_email VARCHAR(255) NOT NULL,
  customer_name VARCHAR(255) NOT NULL,
  customer_phone VARCHAR(50) DEFAULT NULL,

  -- Status
  status ENUM('pending','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
  payment_status ENUM('unpaid','paid','partially-paid','refunded','failed') DEFAULT 'unpaid',
  fulfillment_status ENUM('unfulfilled','partial','fulfilled') DEFAULT 'unfulfilled',

  -- Pricing
  currency_code VARCHAR(3) NOT NULL,
  exchange_rate DECIMAL(10,6) DEFAULT 1 COMMENT 'Rate at order time',
  settled_currency VARCHAR(3) DEFAULT 'TRY' COMMENT 'Settlement currency',
  display_rate_timestamp TIMESTAMP NULL COMMENT 'When rate was displayed',

  subtotal DECIMAL(18,4) NOT NULL COMMENT 'Sum of item totals',
  discount_amount DECIMAL(18,4) DEFAULT 0,
  tax_amount DECIMAL(18,4) NOT NULL,
  shipping_amount DECIMAL(18,4) DEFAULT 0,
  grand_total DECIMAL(18,4) NOT NULL,

  -- Fraud Detection (from migration 007)
  fraud_score INT DEFAULT 0 COMMENT 'Risk score 0-100',
  risk_status ENUM('low','medium','high') DEFAULT 'low' COMMENT 'Risk level',

  -- Payment
  payment_method VARCHAR(50) DEFAULT NULL COMMENT 'stripe, iyzico, bank_transfer',
  payment_reference VARCHAR(255) DEFAULT NULL COMMENT 'Transaction ID',
  paid_at TIMESTAMP NULL DEFAULT NULL,

  -- Shipping
  shipping_method VARCHAR(50) DEFAULT NULL,
  shipping_address_id BIGINT DEFAULT NULL,
  billing_address_id BIGINT DEFAULT NULL,

  tracking_number VARCHAR(255) DEFAULT NULL,
  shipped_at TIMESTAMP NULL DEFAULT NULL,
  delivered_at TIMESTAMP NULL DEFAULT NULL,

  -- B2B
  payment_terms_days INT DEFAULT 0 COMMENT 'Net payment terms',
  payment_due_date DATE DEFAULT NULL,
  credit_used DECIMAL(18,4) DEFAULT 0 COMMENT 'Credit limit used',

  -- Notes
  customer_notes TEXT DEFAULT NULL,
  admin_notes TEXT DEFAULT NULL,

  -- Metadata
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent TEXT DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  cancelled_at TIMESTAMP NULL DEFAULT NULL,

  INDEX idx_order_number (order_number),
  INDEX idx_user_id (user_id),
  INDEX idx_customer_email (customer_email),
  INDEX idx_status (status),
  INDEX idx_payment_status (payment_status),
  INDEX idx_created_at (created_at),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (customer_group_id) REFERENCES customer_groups(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Items
CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  order_id INT NOT NULL,
  product_id INT DEFAULT NULL COMMENT 'NULL if product deleted',
  variant_id INT DEFAULT NULL,

  -- Product Snapshot (at order time)
  product_sku VARCHAR(100) NOT NULL,
  product_name VARCHAR(255) NOT NULL,
  variant_name VARCHAR(255) DEFAULT NULL,

  -- Quantity
  quantity INT NOT NULL,
  quantity_shipped INT DEFAULT 0,
  quantity_refunded INT DEFAULT 0,

  -- Pricing (locked at order time)
  unit_price DECIMAL(18,4) NOT NULL,
  discount_amount DECIMAL(18,4) DEFAULT 0,
  tax_rate DECIMAL(5,2) DEFAULT 0,
  tax_amount DECIMAL(18,4) DEFAULT 0,
  row_total DECIMAL(18,4) NOT NULL COMMENT 'Final total for this line',

  -- Custom Options
  custom_options JSON DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_order_id (order_id),
  INDEX idx_product_id (product_id),
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Addresses
CREATE TABLE IF NOT EXISTS addresses (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  user_id INT DEFAULT NULL COMMENT 'NULL for guest addresses',

  -- Address Type
  type ENUM('billing','shipping','both') DEFAULT 'both',

  -- Contact
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  company_name VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(50) DEFAULT NULL,

  -- Address
  address_line1 VARCHAR(255) NOT NULL,
  address_line2 VARCHAR(255) DEFAULT NULL,
  city VARCHAR(100) NOT NULL,
  state_province VARCHAR(100) DEFAULT NULL,
  postal_code VARCHAR(20) NOT NULL,
  country_code VARCHAR(2) NOT NULL,

  -- Tax
  tax_id VARCHAR(50) DEFAULT NULL COMMENT 'VAT/Tax ID',

  -- Flags
  is_default TINYINT(1) DEFAULT 0,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_user_id (user_id),
  INDEX idx_country_code (country_code),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Address Snapshots
ALTER TABLE orders
ADD CONSTRAINT fk_orders_shipping_address
FOREIGN KEY (shipping_address_id) REFERENCES addresses(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_orders_billing_address
FOREIGN KEY (billing_address_id) REFERENCES addresses(id) ON DELETE SET NULL;

-- Order Status History
CREATE TABLE IF NOT EXISTS order_status_history (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  order_id INT NOT NULL,

  -- Status Change
  from_status ENUM('pending','processing','shipped','delivered','cancelled','refunded') DEFAULT NULL,
  to_status ENUM('pending','processing','shipped','delivered','cancelled','refunded') NOT NULL,

  -- Who Changed
  changed_by_user_id INT DEFAULT NULL COMMENT 'Admin or system',

  -- Notes
  note TEXT DEFAULT NULL,

  -- Notification
  customer_notified TINYINT(1) DEFAULT 0,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_order_id (order_id),
  INDEX idx_created_at (created_at),
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (changed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupons
CREATE TABLE IF NOT EXISTS coupons (
  id INT PRIMARY KEY AUTO_INCREMENT,

  code VARCHAR(50) NOT NULL UNIQUE,
  description TEXT DEFAULT NULL,

  -- Discount
  discount_type ENUM('percent','fixed','free_shipping') NOT NULL,
  discount_value DECIMAL(18,4) NOT NULL COMMENT 'Percent or amount',

  -- Conditions
  min_order_amount DECIMAL(18,4) DEFAULT NULL,
  max_discount_amount DECIMAL(18,4) DEFAULT NULL,

  -- Limits
  usage_limit_total INT DEFAULT NULL COMMENT 'Total uses across all customers',
  usage_limit_per_customer INT DEFAULT 1,
  usage_count INT DEFAULT 0,

  -- Validity
  valid_from TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  valid_until TIMESTAMP NULL DEFAULT NULL,

  -- Restrictions
  customer_group_ids JSON DEFAULT NULL COMMENT 'Array of group IDs',
  product_ids JSON DEFAULT NULL COMMENT 'Specific products',
  category_ids JSON DEFAULT NULL COMMENT 'Specific categories',

  is_active TINYINT(1) DEFAULT 1,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_code (code),
  INDEX idx_is_active (is_active),
  INDEX idx_valid_until (valid_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupon Usage
CREATE TABLE IF NOT EXISTS coupon_usage (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  coupon_id INT NOT NULL,
  order_id INT NOT NULL,
  user_id INT DEFAULT NULL,

  discount_amount DECIMAL(18,4) NOT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_coupon_id (coupon_id),
  INDEX idx_order_id (order_id),
  INDEX idx_user_id (user_id),
  FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
