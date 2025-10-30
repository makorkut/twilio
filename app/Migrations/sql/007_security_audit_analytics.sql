-- ============================================
-- Migration 007: Security, Audit & Analytics
-- ============================================

-- Audit logs
CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  user_id INT DEFAULT NULL,
  action VARCHAR(100) NOT NULL COMMENT 'create, update, delete, login, logout',
  entity_type VARCHAR(100) DEFAULT NULL,
  entity_id INT DEFAULT NULL,

  -- Changes (JSON)
  old_values TEXT DEFAULT NULL,
  new_values TEXT DEFAULT NULL,

  -- Request info
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(500) DEFAULT NULL,
  request_url VARCHAR(1000) DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_user_id (user_id),
  INDEX idx_action (action),
  INDEX idx_entity (entity_type, entity_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Login attempts (brute force protection)
CREATE TABLE IF NOT EXISTS login_attempts (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  email VARCHAR(255) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  success TINYINT(1) DEFAULT 0,
  user_agent VARCHAR(500) DEFAULT NULL,

  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_email (email),
  INDEX idx_ip_address (ip_address),
  INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- IP blacklist
CREATE TABLE IF NOT EXISTS ip_blacklist (
  id INT PRIMARY KEY AUTO_INCREMENT,

  ip_address VARCHAR(45) NOT NULL UNIQUE,
  reason TEXT DEFAULT NULL,
  expires_at TIMESTAMP NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_ip_address (ip_address),
  INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fraud logs
CREATE TABLE IF NOT EXISTS fraud_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  order_id BIGINT DEFAULT NULL,
  user_id INT DEFAULT NULL,

  fraud_type ENUM('velocity','suspicious-pattern','blacklisted-card','multiple-failed-payments','high-risk-order') NOT NULL,
  risk_score INT DEFAULT 0 COMMENT '0-100',

  details TEXT DEFAULT NULL COMMENT 'JSON',
  action_taken ENUM('flagged','blocked','manual-review') DEFAULT 'flagged',

  ip_address VARCHAR(45) DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_order_id (order_id),
  INDEX idx_user_id (user_id),
  INDEX idx_fraud_type (fraud_type),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Search logs (analytics)
CREATE TABLE IF NOT EXISTS search_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  query VARCHAR(500) NOT NULL,
  result_count INT DEFAULT 0,
  clicked_product_id INT DEFAULT NULL,

  user_id INT DEFAULT NULL,
  lang_code VARCHAR(5) DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_query (query(255)),
  INDEX idx_clicked_product (clicked_product_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product views (analytics)
CREATE TABLE IF NOT EXISTS product_views (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  product_id INT,
  user_id INT DEFAULT NULL,
  session_id VARCHAR(128) DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,

  viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_product_id (product_id),
  INDEX idx_user_id (user_id),
  INDEX idx_viewed_at (viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- API requests log (analytics)
CREATE TABLE IF NOT EXISTS api_requests (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  endpoint VARCHAR(500) NOT NULL,
  method VARCHAR(10) NOT NULL,
  user_id INT DEFAULT NULL,
  api_key VARCHAR(100) DEFAULT NULL,

  response_status INT DEFAULT NULL,
  response_time_ms INT DEFAULT NULL,

  ip_address VARCHAR(45) DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_endpoint (endpoint(255)),
  INDEX idx_user_id (user_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inventory forecasting
CREATE TABLE IF NOT EXISTS inventory_forecasts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT,

  avg_daily_sales DECIMAL(10,2) DEFAULT 0,
  current_stock INT DEFAULT 0,
  estimated_stockout_date DATE DEFAULT NULL,
  reorder_point INT DEFAULT NULL,

  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_product (product_id),
  INDEX idx_product_id (product_id),
  INDEX idx_estimated_stockout (estimated_stockout_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Warehouses (multi-location)
CREATE TABLE IF NOT EXISTS warehouses (
  id INT PRIMARY KEY AUTO_INCREMENT,

  name VARCHAR(255) NOT NULL,
  code VARCHAR(50) NOT NULL UNIQUE,
  address TEXT DEFAULT NULL,
  is_primary TINYINT(1) DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_code (code),
  INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product stock locations
CREATE TABLE IF NOT EXISTS product_stock_locations (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  product_id INT,
  warehouse_id INT NOT NULL,

  quantity INT DEFAULT 0,
  reserved_quantity INT DEFAULT 0,
  available_quantity INT GENERATED ALWAYS AS (quantity - reserved_quantity) STORED,

  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_product_warehouse (product_id, warehouse_id),
  INDEX idx_product_id (product_id),
  INDEX idx_warehouse_id (warehouse_id),
  FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product lots (batch tracking)
CREATE TABLE IF NOT EXISTS product_lots (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  product_id INT,
  warehouse_id INT NOT NULL,

  lot_number VARCHAR(100) NOT NULL,
  quantity INT DEFAULT 0,

  production_date DATE DEFAULT NULL,
  expiry_date DATE DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_lot_warehouse (lot_number, warehouse_id),
  INDEX idx_product_id (product_id),
  INDEX idx_expiry_date (expiry_date),
  FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Stock movements
CREATE TABLE IF NOT EXISTS stock_movements (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  product_id INT,
  warehouse_id INT NOT NULL,

  movement_type ENUM('in','out','transfer','adjustment','return','damage') NOT NULL,
  quantity INT NOT NULL,
  reference_type VARCHAR(50) DEFAULT NULL COMMENT 'order, purchase, transfer',
  reference_id INT DEFAULT NULL,

  notes TEXT DEFAULT NULL,
  user_id INT DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_product_id (product_id),
  INDEX idx_warehouse_id (warehouse_id),
  INDEX idx_movement_type (movement_type),
  INDEX idx_created_at (created_at),
  FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- GDPR requests
CREATE TABLE IF NOT EXISTS gdpr_requests (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,

  request_type ENUM('data-export','data-deletion') NOT NULL,
  status ENUM('pending','processing','completed','rejected') DEFAULT 'pending',

  processed_at TIMESTAMP NULL,
  processed_by_user_id INT DEFAULT NULL,
  export_file_path VARCHAR(500) DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

  INDEX idx_user_id (user_id),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- GDPR consent logs
CREATE TABLE IF NOT EXISTS gdpr_consent_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,

  consent_type ENUM('marketing','analytics','cookies','third-party') NOT NULL,
  action ENUM('granted','revoked') NOT NULL,

  ip_address VARCHAR(45) DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

  INDEX idx_user_id (user_id),
  INDEX idx_consent_type (consent_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NOTE: Orders table extensions moved to migration 010
-- Following columns will be added when orders table is created:
-- settled_currency, display_rate_timestamp, fraud_score, risk_status

-- Seed default warehouse
INSERT INTO warehouses (name, code, is_primary, is_active) VALUES
('Ana Depo', 'MAIN', 1, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);
