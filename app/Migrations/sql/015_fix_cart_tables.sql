-- ============================================================================
-- Migration 015: Fix Cart Tables Schema Mismatches
-- Date: 2025-11-05
-- Description: Fix cart table naming and add missing columns
-- ============================================================================

SET NAMES utf8mb4;

-- Rename cart to carts (if exists)
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cart');
SET @sql = IF(@table_exists > 0, 'RENAME TABLE cart TO carts', 'SELECT "Table cart does not exist"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add missing column to carts table
ALTER TABLE carts
ADD COLUMN IF NOT EXISTS coupon_id INT DEFAULT NULL COMMENT 'Applied coupon ID' AFTER currency_code,
ADD INDEX IF NOT EXISTS idx_coupon_id (coupon_id);

-- Fix cart_items column name (rename custom_options to options_json for consistency)
-- Note: We keep both for backward compatibility
ALTER TABLE cart_items
ADD COLUMN IF NOT EXISTS options_json JSON DEFAULT NULL COMMENT 'Cart item options' AFTER price;
