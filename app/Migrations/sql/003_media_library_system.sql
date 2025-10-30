-- ============================================
-- Migration 003: Advanced Media Library
-- ============================================

-- Main media table (C_img_glr modernized)
CREATE TABLE IF NOT EXISTS media (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  -- External Mapping (Local CRM)
  external_id VARCHAR(255) DEFAULT NULL COMMENT 'localid from CRM',
  external_source VARCHAR(100) DEFAULT NULL COMMENT 'local-crm, erp',

  -- File Information
  original_filename VARCHAR(255) NOT NULL,
  storage_driver VARCHAR(20) DEFAULT 'local' COMMENT 'local, s3, cdn',

  -- Multiple Sizes (C_img_glr compat)
  file_default VARCHAR(500) NOT NULL,
  file_big VARCHAR(500) DEFAULT NULL,
  file_small VARCHAR(500) DEFAULT NULL,
  file_tiny VARCHAR(500) DEFAULT NULL,
  file_thumb VARCHAR(500) DEFAULT NULL,
  file_webp VARCHAR(500) DEFAULT NULL,
  file_avif VARCHAR(500) DEFAULT NULL,

  -- Metadata
  mime_type VARCHAR(100) DEFAULT NULL,
  file_size_kb INT DEFAULT NULL,
  width INT DEFAULT NULL,
  height INT DEFAULT NULL,

  -- Visibility
  status ENUM('active','hidden','processing','failed') DEFAULT 'active',
  visibility ENUM('public','private','unlisted') DEFAULT 'public',

  -- Display
  display_order INT DEFAULT 1,
  featured_on_homepage TINYINT(1) DEFAULT 0 COMMENT 'mainpage',

  -- Custom Fields (C_img_glr compat)
  color_code INT DEFAULT NULL COMMENT 'renkkod',
  special_field INT DEFAULT 1 COMMENT 'ozelalan',
  project_name VARCHAR(255) DEFAULT NULL COMMENT 'proje',
  year INT DEFAULT NULL,
  location VARCHAR(255) DEFAULT NULL,

  -- Timestamps
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,

  INDEX idx_external_id (external_id),
  INDEX idx_status (status),
  INDEX idx_featured (featured_on_homepage),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Multi-language media details (C_img_glr_details compat)
CREATE TABLE IF NOT EXISTS media_lang (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  media_id BIGINT NOT NULL,
  lang_code VARCHAR(5) NOT NULL,

  -- Content
  title VARCHAR(500) DEFAULT NULL,
  slug VARCHAR(500) DEFAULT NULL COMMENT 'Gallery URL',
  description TEXT DEFAULT NULL,
  alt_text VARCHAR(255) DEFAULT NULL,
  caption VARCHAR(1000) DEFAULT NULL,

  -- SEO
  seo_title VARCHAR(255) DEFAULT NULL,
  seo_description VARCHAR(500) DEFAULT NULL,
  keywords VARCHAR(500) DEFAULT NULL,

  UNIQUE KEY uniq_media_lang (media_id, lang_code),
  UNIQUE KEY uniq_slug_lang (slug, lang_code),
  FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,

  INDEX idx_slug (slug),
  FULLTEXT idx_search (title, description, keywords)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Media categories (C_img_glr_cats compat)
CREATE TABLE IF NOT EXISTS media_categories (
  id INT PRIMARY KEY AUTO_INCREMENT,
  parent_id INT DEFAULT 0,
  slug VARCHAR(255) NOT NULL,

  -- Type
  category_type VARCHAR(100) DEFAULT NULL COMMENT 'tip',
  type_id INT DEFAULT NULL COMMENT 'tipid',

  -- Cover
  cover_image VARCHAR(500) DEFAULT NULL,

  -- Display
  visibility TINYINT(1) DEFAULT 1,
  display_order INT DEFAULT 1,

  -- Unique Key
  unique_key VARCHAR(60) NOT NULL UNIQUE COMMENT 'uniq',

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_parent_id (parent_id),
  INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Media categories lang
CREATE TABLE IF NOT EXISTS media_categories_lang (
  id INT PRIMARY KEY AUTO_INCREMENT,
  category_id INT NOT NULL,
  lang_code VARCHAR(5) NOT NULL,

  name VARCHAR(255) NOT NULL,
  short_title VARCHAR(100) DEFAULT NULL,
  description TEXT DEFAULT NULL,

  -- SEO
  seo_title VARCHAR(255) DEFAULT NULL,
  seo_description VARCHAR(500) DEFAULT NULL,
  keywords VARCHAR(500) DEFAULT NULL,

  UNIQUE KEY uniq_cat_lang (category_id, lang_code),
  FOREIGN KEY (category_id) REFERENCES media_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Media-Category relations
CREATE TABLE IF NOT EXISTS media_category_relations (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  media_id BIGINT NOT NULL,
  category_id INT NOT NULL,
  category_level ENUM('main','sub','type') DEFAULT 'main',

  UNIQUE KEY uniq_media_cat_level (media_id, category_id, category_level),
  FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES media_categories(id) ON DELETE CASCADE,

  INDEX idx_category_id (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Media tags
CREATE TABLE IF NOT EXISTS media_tags (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  tag_type ENUM('general','sku','color','material','size') DEFAULT 'general',

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_slug (slug),
  INDEX idx_tag_type (tag_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Media-Tag relations
CREATE TABLE IF NOT EXISTS media_tag_relations (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  media_id BIGINT NOT NULL,
  tag_id INT NOT NULL,

  UNIQUE KEY uniq_media_tag (media_id, tag_id),
  FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id) REFERENCES media_tags(id) ON DELETE CASCADE,

  INDEX idx_tag_id (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Media usage (multi-entity)
CREATE TABLE IF NOT EXISTS media_usage (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  media_id BIGINT NOT NULL,

  -- Entity
  entity_type ENUM('product','post','page','category','tag','banner','user','project') NOT NULL,
  entity_id INT NOT NULL,

  -- Usage
  usage_type ENUM('main','gallery','thumbnail','cover','slider','logo') DEFAULT 'gallery',
  display_order INT DEFAULT 1,

  attached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,

  INDEX idx_entity (entity_type, entity_id),
  INDEX idx_media_id (media_id),
  INDEX idx_usage_type (usage_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Standalone galleries
CREATE TABLE IF NOT EXISTS media_galleries (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(500) NOT NULL,
  slug VARCHAR(500) NOT NULL UNIQUE,
  description TEXT DEFAULT NULL,
  visibility ENUM('public','private','unlisted') DEFAULT 'public',
  featured TINYINT(1) DEFAULT 0,
  cover_media_id BIGINT DEFAULT NULL,
  display_order INT DEFAULT 1,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (cover_media_id) REFERENCES media(id) ON DELETE SET NULL,

  INDEX idx_slug (slug),
  INDEX idx_featured (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gallery items
CREATE TABLE IF NOT EXISTS media_gallery_items (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  gallery_id INT NOT NULL,
  media_id BIGINT NOT NULL,
  display_order INT DEFAULT 1,
  caption TEXT DEFAULT NULL,

  FOREIGN KEY (gallery_id) REFERENCES media_galleries(id) ON DELETE CASCADE,
  FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,

  INDEX idx_gallery_id (gallery_id),
  INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AUTO ATTACH TRIGGER: SKU tag → Product
-- Note: Trigger will be created after products table exists (migration 008)
-- Uncomment after migration 008 is complete
