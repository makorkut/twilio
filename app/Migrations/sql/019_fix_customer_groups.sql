-- ============================================================================
-- Migration 019: Fix Customer Groups Table
-- Date: 2025-11-05
-- Description: Add sample-related columns to customer_groups table
-- ============================================================================

SET NAMES utf8mb4;

-- Add missing columns to customer_groups table
ALTER TABLE customer_groups
ADD COLUMN IF NOT EXISTS allow_samples TINYINT(1) DEFAULT 1 COMMENT 'Allow sample orders for this group' AFTER payment_term_days,
ADD COLUMN IF NOT EXISTS max_samples_per_month INT DEFAULT 10 COMMENT 'Maximum samples per month' AFTER allow_samples;

-- Add indexes
CREATE INDEX IF NOT EXISTS idx_allow_samples ON customer_groups(allow_samples);
