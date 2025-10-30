-- ============================================
-- Migration 001: Core i18n System
-- ============================================

-- Languages table
CREATE TABLE IF NOT EXISTS languages (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  language_code VARCHAR(5) NOT NULL UNIQUE,
  locale_code VARCHAR(10) DEFAULT NULL,
  text_editor_lang VARCHAR(50) DEFAULT NULL,
  status TINYINT(1) DEFAULT 1,
  is_default TINYINT(1) DEFAULT 0,
  date_format VARCHAR(50) DEFAULT 'd/m/Y',
  time_format VARCHAR(20) DEFAULT 'H:i',
  decimal_separator CHAR(1) DEFAULT ',',
  thousands_separator CHAR(1) DEFAULT '.',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_language_code (language_code),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default languages if not exists
INSERT IGNORE INTO languages (name, language_code, locale_code, text_editor_lang, status, is_default) VALUES
('Türkçe', 'tr', 'tr_TR', 'tr', 1, 1),
('English', 'en', 'en_US', 'en', 1, 0);

-- Ensure only one default language
CREATE TRIGGER IF NOT EXISTS ensure_one_default_language
BEFORE UPDATE ON languages
FOR EACH ROW
BEGIN
  IF NEW.is_default = 1 AND OLD.is_default = 0 THEN
    UPDATE languages SET is_default = 0 WHERE id != NEW.id;
  END IF;
END;

-- i18n keys table (UI translations)
CREATE TABLE IF NOT EXISTS i18n_keys (
  id INT PRIMARY KEY AUTO_INCREMENT,
  `group` VARCHAR(100) NOT NULL,
  dot_key VARCHAR(255) NOT NULL,
  description VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_group_key (`group`, dot_key),
  INDEX idx_group (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- i18n values table
CREATE TABLE IF NOT EXISTS i18n_values (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  key_id INT NOT NULL,
  lang_code VARCHAR(5) NOT NULL,
  value_text TEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_key_lang (key_id, lang_code),
  FOREIGN KEY (key_id) REFERENCES i18n_keys(id) ON DELETE CASCADE,

  INDEX idx_lang_code (lang_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed basic i18n keys
INSERT INTO i18n_keys (`group`, dot_key, description) VALUES
('common', 'home', 'Home link'),
('common', 'products', 'Products link'),
('common', 'categories', 'Categories link'),
('common', 'cart', 'Shopping cart'),
('common', 'checkout', 'Checkout'),
('common', 'login', 'Login'),
('common', 'register', 'Register'),
('common', 'logout', 'Logout'),
('common', 'search', 'Search placeholder'),
('common', 'add_to_cart', 'Add to cart button'),
('common', 'buy_now', 'Buy now button'),
('common', 'view_more', 'View more link'),
('product', 'description', 'Description tab'),
('product', 'specifications', 'Specifications tab'),
('product', 'reviews', 'Reviews tab'),
('product', 'technical_docs', 'Technical documents tab'),
('product', 'out_of_stock', 'Out of stock message'),
('product', 'in_stock', 'In stock message'),
('cart', 'empty', 'Empty cart message'),
('cart', 'subtotal', 'Subtotal label'),
('cart', 'total', 'Total label'),
('checkout', 'billing_address', 'Billing address'),
('checkout', 'shipping_address', 'Shipping address'),
('checkout', 'payment_method', 'Payment method'),
('checkout', 'place_order', 'Place order button')
ON DUPLICATE KEY UPDATE description=VALUES(description);
