-- ============================================
-- Migration 011: Webhooks & API System
-- ============================================

-- Webhook Subscriptions (Outgoing)
CREATE TABLE IF NOT EXISTS webhook_subscriptions (
  id INT PRIMARY KEY AUTO_INCREMENT,

  -- Destination
  url VARCHAR(500) NOT NULL,
  secret_key VARCHAR(255) DEFAULT NULL COMMENT 'For HMAC signature verification',

  -- Events to subscribe
  events JSON NOT NULL COMMENT 'Array of event names: ["order.created", "product.updated"]',

  -- HTTP Settings
  http_method VARCHAR(10) DEFAULT 'POST',
  headers JSON DEFAULT NULL COMMENT 'Custom headers',
  timeout_seconds INT DEFAULT 30,

  -- Retry Logic
  retry_enabled TINYINT(1) DEFAULT 1,
  retry_max_attempts INT DEFAULT 3,
  retry_delay_seconds INT DEFAULT 60,

  -- Status
  is_active TINYINT(1) DEFAULT 1,
  last_triggered_at TIMESTAMP NULL DEFAULT NULL,
  last_success_at TIMESTAMP NULL DEFAULT NULL,
  last_failure_at TIMESTAMP NULL DEFAULT NULL,
  failure_count INT DEFAULT 0,

  -- Metadata
  description TEXT DEFAULT NULL,
  created_by_user_id INT DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_is_active (is_active),
  INDEX idx_last_triggered_at (last_triggered_at),
  FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Webhook Deliveries (Log)
CREATE TABLE IF NOT EXISTS webhook_deliveries (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  webhook_subscription_id INT NOT NULL,

  -- Event
  event_name VARCHAR(100) NOT NULL,
  event_payload JSON NOT NULL COMMENT 'The data sent',

  -- Request
  request_url VARCHAR(500) NOT NULL,
  request_method VARCHAR(10) NOT NULL,
  request_headers JSON DEFAULT NULL,
  request_body MEDIUMTEXT DEFAULT NULL,
  request_sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  -- Response
  response_status_code INT DEFAULT NULL,
  response_headers JSON DEFAULT NULL,
  response_body TEXT DEFAULT NULL,
  response_time_ms INT DEFAULT NULL COMMENT 'Response time in milliseconds',
  response_received_at TIMESTAMP NULL DEFAULT NULL,

  -- Status
  status ENUM('pending','success','failed','retrying') DEFAULT 'pending',
  error_message TEXT DEFAULT NULL,

  -- Retry
  attempt_number INT DEFAULT 1,
  next_retry_at TIMESTAMP NULL DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_webhook_subscription_id (webhook_subscription_id),
  INDEX idx_event_name (event_name),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at),
  FOREIGN KEY (webhook_subscription_id) REFERENCES webhook_subscriptions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Webhook Receivers (Incoming)
CREATE TABLE IF NOT EXISTS webhook_receivers (
  id INT PRIMARY KEY AUTO_INCREMENT,

  -- Identification
  name VARCHAR(100) NOT NULL UNIQUE COMMENT 'e.g., "local_crm", "erp_system"',
  endpoint_slug VARCHAR(100) NOT NULL UNIQUE COMMENT 'URL: /webhooks/receive/{slug}',

  -- Security
  secret_key VARCHAR(255) DEFAULT NULL,
  allowed_ip_addresses JSON DEFAULT NULL COMMENT 'Array of allowed IPs',
  signature_header VARCHAR(100) DEFAULT NULL COMMENT 'e.g., "X-Signature"',
  signature_algorithm VARCHAR(50) DEFAULT 'sha256' COMMENT 'hmac-sha256, sha256, etc.',

  -- Handler
  handler_class VARCHAR(255) DEFAULT NULL COMMENT 'PHP class to handle webhook',
  handler_method VARCHAR(100) DEFAULT 'handle',

  -- Status
  is_active TINYINT(1) DEFAULT 1,
  last_received_at TIMESTAMP NULL DEFAULT NULL,

  -- Metadata
  description TEXT DEFAULT NULL,
  created_by_user_id INT DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_endpoint_slug (endpoint_slug),
  INDEX idx_is_active (is_active),
  FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Webhook Received Events (Log)
CREATE TABLE IF NOT EXISTS webhook_received_events (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  webhook_receiver_id INT DEFAULT NULL,

  -- Request Info
  request_method VARCHAR(10) NOT NULL,
  request_headers JSON DEFAULT NULL,
  request_body MEDIUMTEXT DEFAULT NULL,
  request_ip VARCHAR(45) DEFAULT NULL,
  request_user_agent TEXT DEFAULT NULL,

  -- Processing
  status ENUM('received','processing','processed','failed') DEFAULT 'received',
  processed_at TIMESTAMP NULL DEFAULT NULL,
  error_message TEXT DEFAULT NULL,

  -- Response
  response_status_code INT DEFAULT 200,
  response_body TEXT DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_webhook_receiver_id (webhook_receiver_id),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at),
  FOREIGN KEY (webhook_receiver_id) REFERENCES webhook_receivers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Tokens
CREATE TABLE IF NOT EXISTS api_tokens (
  id INT PRIMARY KEY AUTO_INCREMENT,

  user_id INT DEFAULT NULL COMMENT 'NULL for app-level tokens',

  -- Token
  name VARCHAR(100) NOT NULL,
  token VARCHAR(100) NOT NULL UNIQUE COMMENT 'e.g., "pk_live_xxxxx"',
  token_type ENUM('public','secret','restricted') DEFAULT 'restricted',

  -- Permissions
  permissions JSON DEFAULT NULL COMMENT 'Array of allowed actions',
  scopes JSON DEFAULT NULL COMMENT 'Scope restrictions',

  -- Rate Limiting
  rate_limit_per_minute INT DEFAULT 60,
  rate_limit_per_day INT DEFAULT 10000,

  -- Status
  is_active TINYINT(1) DEFAULT 1,
  last_used_at TIMESTAMP NULL DEFAULT NULL,
  expires_at TIMESTAMP NULL DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_token (token),
  INDEX idx_user_id (user_id),
  INDEX idx_is_active (is_active),
  INDEX idx_expires_at (expires_at),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Request Log
CREATE TABLE IF NOT EXISTS api_request_log (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  api_token_id INT DEFAULT NULL,
  user_id INT DEFAULT NULL,

  -- Request
  endpoint VARCHAR(255) NOT NULL,
  method VARCHAR(10) NOT NULL,
  request_body MEDIUMTEXT DEFAULT NULL,
  request_headers JSON DEFAULT NULL,

  -- Response
  response_status_code INT DEFAULT NULL,
  response_time_ms INT DEFAULT NULL,
  response_body TEXT DEFAULT NULL,

  -- Client Info
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent TEXT DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_api_token_id (api_token_id),
  INDEX idx_user_id (user_id),
  INDEX idx_endpoint (endpoint),
  INDEX idx_created_at (created_at),
  FOREIGN KEY (api_token_id) REFERENCES api_tokens(id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- External System Mapping (for sync)
CREATE TABLE IF NOT EXISTS external_system_mappings (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  -- System
  system_name VARCHAR(100) NOT NULL COMMENT 'e.g., "local_crm", "erp"',

  -- Entity
  entity_type VARCHAR(50) NOT NULL COMMENT 'product, order, customer',
  internal_id INT NOT NULL COMMENT 'Our database ID',
  external_id VARCHAR(255) NOT NULL COMMENT 'ID in external system',

  -- Metadata
  sync_direction ENUM('inbound','outbound','bidirectional') DEFAULT 'bidirectional',
  last_synced_at TIMESTAMP NULL DEFAULT NULL,
  sync_status ENUM('synced','pending','failed') DEFAULT 'synced',
  sync_error TEXT DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_external_mapping (system_name, entity_type, external_id),
  INDEX idx_internal_id (entity_type, internal_id),
  INDEX idx_system_name (system_name),
  INDEX idx_last_synced_at (last_synced_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed: Sample Webhook Subscription
INSERT IGNORE INTO webhook_subscriptions (id, url, events, description, is_active) VALUES
(1, 'https://example.com/webhook/orders', '["order.created", "order.updated", "order.cancelled"]', 'Example webhook endpoint', 0);

-- Seed: Sample Webhook Receiver
INSERT IGNORE INTO webhook_receivers (id, name, endpoint_slug, description, is_active) VALUES
(1, 'Local CRM System', 'local-crm', 'Receives product updates from local CRM', 1),
(2, 'ERP System', 'erp-sync', 'Receives inventory updates from ERP', 1);
