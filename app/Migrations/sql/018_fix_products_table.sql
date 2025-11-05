-- ============================================================================
-- Migration 018: Fix Products Table Schema Mismatches
-- Date: 2025-11-05
-- Description: Add missing columns to products table
-- ============================================================================

SET NAMES utf8mb4;

-- Add missing columns to products table
ALTER TABLE products
ADD COLUMN IF NOT EXISTS skud VARCHAR(100) DEFAULT NULL COMMENT 'Secondary SKU/alternative SKU' AFTER sku,
ADD COLUMN IF NOT EXISTS type ENUM('simple','variable','bundle') DEFAULT 'simple' COMMENT 'Product type' AFTER slug,
ADD COLUMN IF NOT EXISTS stock_status ENUM('in_stock','out_of_stock','on_backorder') DEFAULT 'in_stock' COMMENT 'Stock availability status' AFTER stock_quantity,
ADD COLUMN IF NOT EXISTS brand_id INT DEFAULT NULL COMMENT 'Brand ID' AFTER tax_class_id,
ADD COLUMN IF NOT EXISTS allow_samples TINYINT(1) DEFAULT 0 COMMENT 'Allow sample orders' AFTER is_b2b_only,
ADD COLUMN IF NOT EXISTS max_sample_quantity INT DEFAULT 5 COMMENT 'Maximum sample quantity per order' AFTER allow_samples,
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete timestamp' AFTER updated_at;

-- Add indexes for products
CREATE INDEX IF NOT EXISTS idx_skud ON products(skud);
CREATE INDEX IF NOT EXISTS idx_type ON products(type);
CREATE INDEX IF NOT EXISTS idx_stock_status ON products(stock_status);
CREATE INDEX IF NOT EXISTS idx_brand_id ON products(brand_id);
CREATE INDEX IF NOT EXISTS idx_deleted_at ON products(deleted_at);
