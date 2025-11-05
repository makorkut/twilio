-- ============================================================================
-- Migration 020: Missing Support Tables
-- Date: 2025-11-05
-- Description: Create external_entity_mapping and slugs_history tables
-- ============================================================================

SET NAMES utf8mb4;

-- External entity mapping for external system integrations
CREATE TABLE IF NOT EXISTS external_entity_mapping (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  local_entity_type VARCHAR(50) NOT NULL COMMENT 'Local entity type (product, category, etc)',
  local_entity_id INT NOT NULL COMMENT 'Local entity ID',
  external_source VARCHAR(100) NOT NULL COMMENT 'External system name',
  external_entity_type VARCHAR(50) NOT NULL COMMENT 'External entity type',
  external_id VARCHAR(255) NOT NULL COMMENT 'External entity ID',
  sync_status ENUM('pending','synced','failed') DEFAULT 'pending',
  sync_error TEXT DEFAULT NULL,
  last_synced_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_mapping (local_entity_type, local_entity_id, external_source),
  INDEX idx_external_id (external_source, external_id),
  INDEX idx_sync_status (sync_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Slugs history for SEO 301 redirects
CREATE TABLE IF NOT EXISTS slugs_history (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  entity_type VARCHAR(50) NOT NULL COMMENT 'Entity type (product, category, etc)',
  entity_id INT NOT NULL COMMENT 'Entity ID',
  lang VARCHAR(5) NOT NULL COMMENT 'Language code',
  old_slug VARCHAR(255) NOT NULL COMMENT 'Old slug',
  new_slug VARCHAR(255) NOT NULL COMMENT 'New slug',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_old_slug (old_slug),
  INDEX idx_entity (entity_type, entity_id),
  INDEX idx_lang (lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
