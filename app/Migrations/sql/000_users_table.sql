-- ============================================
-- Migration 000: Users Table
-- ============================================
-- This migration creates the users table for authentication
-- and authorization.
-- ============================================

CREATE TABLE IF NOT EXISTS users (
  id INT PRIMARY KEY AUTO_INCREMENT,

  -- Basic Info
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,

  -- Role & Permissions
  role ENUM('admin', 'manager', 'user') DEFAULT 'user',
  permissions JSON DEFAULT NULL COMMENT 'Custom permissions array',

  -- Status
  is_active TINYINT(1) DEFAULT 1,
  email_verified_at TIMESTAMP NULL DEFAULT NULL,

  -- Profile
  phone VARCHAR(50) DEFAULT NULL,
  avatar VARCHAR(500) DEFAULT NULL,
  company_name VARCHAR(255) DEFAULT NULL,

  -- Security
  remember_token VARCHAR(100) DEFAULT NULL,
  two_factor_secret VARCHAR(255) DEFAULT NULL,
  two_factor_enabled TINYINT(1) DEFAULT 0,

  -- Timestamps
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  last_login_at TIMESTAMP NULL DEFAULT NULL,

  INDEX idx_email (email),
  INDEX idx_role (role),
  INDEX idx_is_active (is_active),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin user will be created by seed-admin.php
-- This allows credentials to be set via environment variables:
-- ADMIN_EMAIL, ADMIN_PASSWORD, ADMIN_NAME
