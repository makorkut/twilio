-- ============================================================================
-- Migration 015: Fix Cart Tables Schema Mismatches
-- Date: 2025-11-05
-- Description: Add missing columns to cart and cart_items tables
-- ============================================================================

SET NAMES utf8mb4;

-- Add missing column to cart table (NOT carts - migration 010 creates "cart" table)
ALTER TABLE cart
ADD COLUMN IF NOT EXISTS coupon_id INT DEFAULT NULL COMMENT 'Applied coupon ID' AFTER currency_code;

CREATE INDEX IF NOT EXISTS idx_cart_coupon_id ON cart(coupon_id);

-- Add options_json column to cart_items (note: price column doesn't exist, using total_price)
ALTER TABLE cart_items
ADD COLUMN IF NOT EXISTS options_json JSON DEFAULT NULL COMMENT 'Cart item options (duplicate of custom_options for compatibility)' AFTER custom_options;

-- Note: Migration 010 creates table named "cart" not "carts"
-- Code should reference "cart" table, not "carts"
