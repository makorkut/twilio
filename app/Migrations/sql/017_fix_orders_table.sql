-- ============================================================================
-- Migration 017: Fix Orders Table Schema Mismatches
-- Date: 2025-11-05
-- Description: Add missing columns to orders and order_items tables
-- ============================================================================

SET NAMES utf8mb4;

-- Add missing columns to orders table
ALTER TABLE orders
ADD COLUMN IF NOT EXISTS billing_address_json JSON DEFAULT NULL COMMENT 'Billing address as JSON' AFTER billing_address_id,
ADD COLUMN IF NOT EXISTS shipping_address_json JSON DEFAULT NULL COMMENT 'Shipping address as JSON' AFTER shipping_address_id,
ADD COLUMN IF NOT EXISTS coupon_id INT DEFAULT NULL COMMENT 'Applied coupon ID' AFTER grand_total,
ADD COLUMN IF NOT EXISTS payment_data_json JSON DEFAULT NULL COMMENT 'Payment gateway response data' AFTER payment_reference,
ADD COLUMN IF NOT EXISTS shipping_carrier VARCHAR(100) DEFAULT NULL COMMENT 'Shipping carrier name' AFTER shipping_method;

-- Add indexes for orders
CREATE INDEX IF NOT EXISTS idx_coupon_id ON orders(coupon_id);
CREATE INDEX IF NOT EXISTS idx_shipping_carrier ON orders(shipping_carrier);

-- Add missing columns to order_items table
ALTER TABLE order_items
ADD COLUMN IF NOT EXISTS options_json JSON DEFAULT NULL COMMENT 'Product options selected' AFTER custom_options,
ADD COLUMN IF NOT EXISTS product_snapshot_json JSON DEFAULT NULL COMMENT 'Product snapshot at order time' AFTER options_json;

-- Create order_refunds table for refund management
CREATE TABLE IF NOT EXISTS order_refunds (
  id INT PRIMARY KEY AUTO_INCREMENT,
  order_id INT NOT NULL,
  amount DECIMAL(18,4) NOT NULL,
  currency_code VARCHAR(3) NOT NULL,
  reason TEXT DEFAULT NULL,
  status ENUM('pending','approved','rejected','completed') DEFAULT 'pending',
  refund_method ENUM('original_payment','store_credit','bank_transfer') DEFAULT 'original_payment',
  refund_reference VARCHAR(255) DEFAULT NULL COMMENT 'Payment gateway refund reference',
  processed_by INT DEFAULT NULL COMMENT 'Admin user ID who processed refund',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  processed_at TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  INDEX idx_order_id (order_id),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
