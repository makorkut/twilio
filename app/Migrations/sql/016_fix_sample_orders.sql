-- ============================================================================
-- Migration 016: Fix Sample Orders Table
-- Date: 2025-11-05
-- Description: Add missing columns to sample_orders table
-- ============================================================================

SET NAMES utf8mb4;

-- Add missing columns to sample_orders
ALTER TABLE sample_orders
ADD COLUMN IF NOT EXISTS order_number VARCHAR(50) UNIQUE COMMENT 'Sample order number' AFTER id,
ADD COLUMN IF NOT EXISTS product_id INT DEFAULT NULL COMMENT 'Product ID for sample' AFTER user_id,
ADD COLUMN IF NOT EXISTS quantity INT DEFAULT 1 COMMENT 'Sample quantity' AFTER product_id,
ADD COLUMN IF NOT EXISTS purpose VARCHAR(255) DEFAULT NULL COMMENT 'Purpose of sample request' AFTER status,
ADD COLUMN IF NOT EXISTS project_details TEXT DEFAULT NULL COMMENT 'Project details' AFTER purpose,
ADD COLUMN IF NOT EXISTS shipping_address JSON DEFAULT NULL COMMENT 'Shipping address JSON' AFTER shipping_cost,
ADD COLUMN IF NOT EXISTS tracking_number VARCHAR(255) DEFAULT NULL COMMENT 'Shipping tracking number' AFTER shipping_address;

-- Add indexes
CREATE INDEX IF NOT EXISTS idx_order_number ON sample_orders(order_number);
CREATE INDEX IF NOT EXISTS idx_product_id ON sample_orders(product_id);

-- Create sample_order_history table for status tracking
CREATE TABLE IF NOT EXISTS sample_order_history (
  id INT PRIMARY KEY AUTO_INCREMENT,
  sample_order_id INT NOT NULL,
  status ENUM('pending','approved','preparing','shipped','delivered','rejected') NOT NULL,
  note TEXT DEFAULT NULL,
  created_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sample_order_id) REFERENCES sample_orders(id) ON DELETE CASCADE,
  INDEX idx_sample_order_id (sample_order_id),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
