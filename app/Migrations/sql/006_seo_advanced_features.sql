-- ============================================
-- Migration 006: SEO & Advanced Features
-- ============================================

-- SEO settings per entity
CREATE TABLE IF NOT EXISTS seo_settings (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  entity_type ENUM('product','category','post','page','tag','user','media') NOT NULL,
  entity_id INT NOT NULL,
  lang_code VARCHAR(5) NOT NULL,

  -- Meta tags
  meta_title VARCHAR(255) DEFAULT NULL,
  meta_description VARCHAR(500) DEFAULT NULL,
  meta_keywords VARCHAR(500) DEFAULT NULL,

  -- OpenGraph
  og_title VARCHAR(255) DEFAULT NULL,
  og_description VARCHAR(500) DEFAULT NULL,
  og_image VARCHAR(500) DEFAULT NULL,
  og_type VARCHAR(50) DEFAULT 'website',

  -- Twitter
  twitter_card VARCHAR(50) DEFAULT 'summary_large_image',
  twitter_title VARCHAR(255) DEFAULT NULL,
  twitter_description VARCHAR(500) DEFAULT NULL,

  -- Schema.org
  schema_type VARCHAR(50) DEFAULT NULL COMMENT 'Product, BlogPosting, Organization',
  schema_json TEXT DEFAULT NULL COMMENT 'JSON-LD',

  -- Indexing
  robots VARCHAR(100) DEFAULT 'index,follow',
  canonical_url VARCHAR(500) DEFAULT NULL,

  -- Sitemap
  sitemap_priority DECIMAL(2,1) DEFAULT 0.5,
  sitemap_changefreq ENUM('always','hourly','daily','weekly','monthly','yearly','never') DEFAULT 'weekly',

  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_entity_lang (entity_type, entity_id, lang_code),
  INDEX idx_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Slug history (301 redirects)
CREATE TABLE IF NOT EXISTS slugs_history (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  entity_type ENUM('product','category','post','page','tag','media') NOT NULL,
  entity_id INT NOT NULL,
  lang_code VARCHAR(5) NOT NULL,

  old_slug VARCHAR(500) NOT NULL,
  new_slug VARCHAR(500) NOT NULL,
  redirect_type SMALLINT DEFAULT 301,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_old_slug (old_slug(255)),
  INDEX idx_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Technical documents
CREATE TABLE IF NOT EXISTS product_documents (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT,
  
  INDEX idx_product_id (product_id),

  document_type ENUM('cad','technical-drawing','installation-guide','certificate','msds','tds','warranty','manual','other') NOT NULL,
  title VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_size_kb INT DEFAULT NULL,
  file_extension VARCHAR(10) DEFAULT NULL,

  -- Access control
  visibility ENUM('public','b2b-only','private') DEFAULT 'public',

  -- Version
  version VARCHAR(50) DEFAULT NULL,
  language VARCHAR(5) DEFAULT NULL,

  display_order INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_product_id (product_id),
  INDEX idx_document_type (document_type),
  INDEX idx_visibility (visibility)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Color catalog
CREATE TABLE IF NOT EXISTS product_colors (
  id INT PRIMARY KEY AUTO_INCREMENT,

  name VARCHAR(255) NOT NULL,
  ral_code VARCHAR(20) DEFAULT NULL COMMENT 'RAL 9010',
  hex_code VARCHAR(7) DEFAULT NULL COMMENT '#FFFFFF',
  image_url VARCHAR(500) DEFAULT NULL COMMENT 'Texture image',

  is_standard TINYINT(1) DEFAULT 0,
  extra_cost DECIMAL(18,4) DEFAULT 0,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_ral_code (ral_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product-Color options
CREATE TABLE IF NOT EXISTS product_color_options (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT,
  
  INDEX idx_product_id (product_id),
  color_id INT NOT NULL,

  UNIQUE KEY uniq_product_color (product_id, color_id),
  FOREIGN KEY (color_id) REFERENCES product_colors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Projects / References
CREATE TABLE IF NOT EXISTS projects (
  id INT PRIMARY KEY AUTO_INCREMENT,

  title VARCHAR(500) NOT NULL,
  slug VARCHAR(500) NOT NULL UNIQUE,
  description TEXT DEFAULT NULL,

  project_type ENUM('hotel','villa','office','hospital','residential','commercial','other') DEFAULT 'other',
  location VARCHAR(255) DEFAULT NULL,
  completion_date DATE DEFAULT NULL,
  area_m2 INT DEFAULT NULL,

  cover_media_id BIGINT DEFAULT NULL,

  visibility TINYINT(1) DEFAULT 1,
  featured TINYINT(1) DEFAULT 0,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (cover_media_id) REFERENCES media(id) ON DELETE SET NULL,

  INDEX idx_slug (slug),
  INDEX idx_featured (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Project products
CREATE TABLE IF NOT EXISTS project_products (
  id INT PRIMARY KEY AUTO_INCREMENT,
  project_id INT NOT NULL,
  product_id INT,
  
  INDEX idx_product_id (product_id),
  quantity_used INT DEFAULT NULL,

  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,

  INDEX idx_project_id (project_id),
  INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Project media
CREATE TABLE IF NOT EXISTS project_media (
  id INT PRIMARY KEY AUTO_INCREMENT,
  project_id INT NOT NULL,
  media_id BIGINT NOT NULL,
  media_type ENUM('before','after','during','other') DEFAULT 'after',
  display_order INT DEFAULT 1,

  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Themes
CREATE TABLE IF NOT EXISTS themes (
  id INT PRIMARY KEY AUTO_INCREMENT,

  name VARCHAR(100) NOT NULL UNIQUE,
  slug VARCHAR(100) NOT NULL UNIQUE,
  directory VARCHAR(100) NOT NULL,

  is_active TINYINT(1) DEFAULT 0,
  is_default TINYINT(1) DEFAULT 0,

  -- Theme config (JSON)
  config TEXT DEFAULT NULL COMMENT 'Colors, fonts, layout settings',

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subscriptions (recurring billing)
CREATE TABLE IF NOT EXISTS subscriptions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  product_id INT,

  INDEX idx_product_id (product_id) DEFAULT NULL,

  plan_interval ENUM('monthly','yearly') DEFAULT 'monthly',
  status ENUM('active','paused','canceled','expired') DEFAULT 'active',

  current_period_start TIMESTAMP NOT NULL,
  current_period_end TIMESTAMP NOT NULL,
  cancel_at_period_end TINYINT(1) DEFAULT 0,

  -- Payment Gateway
  stripe_subscription_id VARCHAR(255) DEFAULT NULL,
  stripe_customer_id VARCHAR(255) DEFAULT NULL,

  next_billing_date TIMESTAMP DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  canceled_at TIMESTAMP NULL,

  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

  INDEX idx_user_id (user_id),
  INDEX idx_status (status),
  INDEX idx_next_billing (next_billing_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subscription invoices
CREATE TABLE IF NOT EXISTS subscription_invoices (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  subscription_id BIGINT NOT NULL,

  amount DECIMAL(18,4) NOT NULL,
  currency VARCHAR(3) NOT NULL,
  status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',

  stripe_invoice_id VARCHAR(255) DEFAULT NULL,
  invoice_pdf_url VARCHAR(1000) DEFAULT NULL,

  paid_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,

  INDEX idx_subscription_id (subscription_id),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default themes
INSERT INTO themes (name, slug, directory, is_active, is_default) VALUES
('Light', 'light', 'Light', 1, 1),
('Dark', 'dark', 'Dark', 0, 0)
ON DUPLICATE KEY UPDATE name=VALUES(name);
