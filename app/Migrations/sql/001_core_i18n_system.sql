-- ============================================
-- Migration 001: Core i18n System
-- ============================================

-- Languages table
CREATE TABLE IF NOT EXISTS languages (
  id INT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  short_form VARCHAR(10) NOT NULL,
  language_code VARCHAR(10) NOT NULL UNIQUE,
  text_direction ENUM('ltr', 'rtl') DEFAULT 'ltr',
  status TINYINT(1) DEFAULT 1,
  language_order INT DEFAULT 1,
  text_editor_lang VARCHAR(50) DEFAULT NULL,
  flag_path VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_language_code (language_code),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default languages with explicit IDs (EN=1, TR=2)
INSERT INTO languages (id, name, short_form, language_code, text_direction, status, language_order, text_editor_lang, flag_path) VALUES
(1, 'ENGLISH', 'en', 'en-US', 'ltr', 1, 1, 'en', 'uploads/flags/gb.svg'),
(2, 'TÜRKÇE', 'tr', 'tr-TR', 'ltr', 1, 2, 'tr', 'uploads/flags/tr.svg')
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  short_form = VALUES(short_form),
  language_code = VALUES(language_code),
  status = VALUES(status);

-- Note: Default language enforcement moved to application layer
-- MySQL triggers cannot UPDATE the same table (mutating table error)
-- Use application logic to ensure only one default language

-- i18n keys table (UI translations)
CREATE TABLE IF NOT EXISTS i18n_keys (
  id INT PRIMARY KEY AUTO_INCREMENT,
  key_name VARCHAR(255) NOT NULL UNIQUE COMMENT 'Dot notation key: common.home, admin.products.title',
  `group` VARCHAR(100) NOT NULL COMMENT 'First part of key: common, admin, product',
  description VARCHAR(500) DEFAULT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_key_name (key_name),
  INDEX idx_group (`group`),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- i18n values table (NOW USES lang_id instead of lang_code)
CREATE TABLE IF NOT EXISTS i18n_values (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  key_id INT NOT NULL,
  lang_id INT NOT NULL COMMENT 'Foreign key to languages.id (1=EN, 2=TR)',
  value TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_key_lang (key_id, lang_id),
  FOREIGN KEY (key_id) REFERENCES i18n_keys(id) ON DELETE CASCADE,
  FOREIGN KEY (lang_id) REFERENCES languages(id) ON DELETE CASCADE,

  INDEX idx_lang_id (lang_id),
  INDEX idx_key_id (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: Translation keys will be seeded by separate migration (012_translation_keys.sql)

-- ============================================
-- Currencies Table (required by 002_multi_currency_pricing.sql)
-- ============================================
CREATE TABLE IF NOT EXISTS currencies (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(3) NOT NULL UNIQUE COMMENT 'ISO 4217 currency code',
  name VARCHAR(100) NOT NULL,
  symbol VARCHAR(10) NOT NULL,
  symbol_direction ENUM('left', 'right') DEFAULT 'left' COMMENT 'Symbol position',
  decimal_separator CHAR(1) DEFAULT ',',
  thousands_separator CHAR(1) DEFAULT '.',
  exchange_rate DECIMAL(18,6) DEFAULT 1.000000 COMMENT 'Rate to base currency',
  status TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_code (code),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default currencies
INSERT IGNORE INTO currencies (code, name, symbol, symbol_direction, exchange_rate, status) VALUES
('TRY', 'Turkish Lira', '₺', 'right', 1.000000, 1),
('EUR', 'Euro', '€', 'left', 0.030000, 1),
('USD', 'US Dollar', '$', 'left', 0.028000, 1),
('GBP', 'British Pound', '£', 'left', 0.025000, 1);
