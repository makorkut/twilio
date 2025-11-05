-- ============================================================================
-- Migration 014: Add Calculator Fields to Products Table
-- Date: 2025-11-05
-- Description: Add coverage_per_unit, calculator_type, and coverage_unit columns
--              to products table for calculator functionality
-- ============================================================================

SET NAMES utf8mb4;

-- Add calculator-related columns to products table
ALTER TABLE products
ADD COLUMN IF NOT EXISTS coverage_per_unit DECIMAL(10,4) DEFAULT NULL COMMENT 'Coverage per unit for calculator (e.g., m² per liter)',
ADD COLUMN IF NOT EXISTS calculator_type ENUM('area','length','volume','weight') DEFAULT NULL COMMENT 'Type of calculator (area, length, etc)',
ADD COLUMN IF NOT EXISTS coverage_unit VARCHAR(20) DEFAULT NULL COMMENT 'Unit for coverage (m², m, kg, etc)';

-- Add index for calculator fields
CREATE INDEX IF NOT EXISTS idx_calculator_type ON products(calculator_type);
