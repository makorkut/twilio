-- ============================================
-- Migration 005: Automation & Webhooks
-- ============================================

-- External entity mapping (sync system)
CREATE TABLE IF NOT EXISTS external_entity_mapping (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  -- Local entity
  local_entity_type ENUM('media','product','category','post','order','user') NOT NULL,
  local_entity_id INT NOT NULL,

  -- External entity
  external_source VARCHAR(100) NOT NULL COMMENT 'local-crm, erp-system',
  external_entity_type VARCHAR(100) NOT NULL,
  external_id VARCHAR(255) NOT NULL,

  -- Sync status
  sync_status ENUM('synced','pending','failed') DEFAULT 'synced',
  last_synced_at TIMESTAMP NULL,
  sync_hash VARCHAR(64) DEFAULT NULL COMMENT 'MD5 for change detection',

  UNIQUE KEY uniq_external (external_source, external_entity_type, external_id),

  INDEX idx_local_entity (local_entity_type, local_entity_id),
  INDEX idx_external_id (external_id),
  INDEX idx_sync_status (sync_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sync logs
CREATE TABLE IF NOT EXISTS sync_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  sync_type ENUM('webhook','api','cron','manual') DEFAULT 'api',
  entity_type ENUM('product','category','image','order','user') NOT NULL,
  entity_id INT DEFAULT NULL,

  -- Source
  external_source VARCHAR(100) NOT NULL,
  external_id VARCHAR(255) DEFAULT NULL,

  -- Operation
  operation ENUM('create','update','delete') NOT NULL,

  -- Status
  status ENUM('pending','processing','success','failed','skipped') DEFAULT 'pending',
  error_message TEXT DEFAULT NULL,

  -- Data
  request_payload LONGTEXT COMMENT 'JSON',
  response_data LONGTEXT COMMENT 'JSON',

  -- Performance
  processing_time_ms INT DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  processed_at TIMESTAMP NULL,

  INDEX idx_entity (entity_type, entity_id),
  INDEX idx_source (external_source),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Webhook events
CREATE TABLE IF NOT EXISTS webhook_events (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  direction ENUM('incoming','outgoing') NOT NULL,
  source VARCHAR(100) NOT NULL COMMENT 'supplier-a, erp, stripe, iyzico',
  endpoint_url VARCHAR(1000) DEFAULT NULL COMMENT 'For outgoing',

  -- Event
  event_type VARCHAR(100) NOT NULL COMMENT 'product.created, order.paid',
  event_data LONGTEXT COMMENT 'JSON payload',

  -- Status
  status ENUM('pending','processing','success','failed','retrying') DEFAULT 'pending',
  http_status_code INT DEFAULT NULL,
  error_message TEXT DEFAULT NULL,
  retry_count INT DEFAULT 0,
  max_retries INT DEFAULT 3,

  -- Security
  signature VARCHAR(255) DEFAULT NULL COMMENT 'HMAC signature',
  ip_address VARCHAR(45) DEFAULT NULL,

  -- Idempotency
  idempotency_key VARCHAR(255) DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  processed_at TIMESTAMP NULL,
  next_retry_at TIMESTAMP NULL,

  UNIQUE KEY uniq_idempotency (idempotency_key),
  INDEX idx_status (status),
  INDEX idx_event_type (event_type),
  INDEX idx_direction (direction),
  INDEX idx_next_retry (next_retry_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Queue jobs
CREATE TABLE IF NOT EXISTS queue_jobs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  job_type VARCHAR(100) NOT NULL COMMENT 'import_products, download_images, send_email',
  payload LONGTEXT NOT NULL COMMENT 'JSON data',
  priority TINYINT DEFAULT 5 COMMENT '1=highest, 10=lowest',

  -- Status
  status ENUM('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
  attempts INT DEFAULT 0,
  max_attempts INT DEFAULT 3,

  -- Progress
  progress_current INT DEFAULT 0,
  progress_total INT DEFAULT 0,
  progress_percent DECIMAL(5,2) DEFAULT 0,

  -- Result
  result_data LONGTEXT DEFAULT NULL COMMENT 'JSON',
  error_message TEXT DEFAULT NULL,

  -- Timestamps
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  started_at TIMESTAMP NULL,
  completed_at TIMESTAMP NULL,
  failed_at TIMESTAMP NULL,

  -- Lock (prevent duplicate processing)
  locked_until TIMESTAMP NULL,
  locked_by VARCHAR(255) DEFAULT NULL COMMENT 'Worker ID',

  INDEX idx_status (status),
  INDEX idx_priority (priority),
  INDEX idx_locked (locked_until),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Email queue
CREATE TABLE IF NOT EXISTS email_queue_new (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  to_email VARCHAR(255) NOT NULL,
  to_name VARCHAR(255) DEFAULT NULL,
  subject VARCHAR(500) NOT NULL,
  body_html LONGTEXT NOT NULL,
  body_text TEXT DEFAULT NULL,

  -- Attachments
  attachments TEXT DEFAULT NULL COMMENT 'JSON array',

  -- Priority
  priority TINYINT DEFAULT 5,

  -- Status
  status ENUM('pending','sending','sent','failed') DEFAULT 'pending',
  attempts INT DEFAULT 0,
  max_attempts INT DEFAULT 3,
  error_message TEXT DEFAULT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  sent_at TIMESTAMP NULL,

  INDEX idx_status (status),
  INDEX idx_priority (priority),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Note: Products table extension moved to migration 008
-- Following columns will be added when products table is created:
-- auto_update_title, auto_update_description, auto_update_price,
-- auto_update_images, auto_update_stock, sync_status, last_synced_at, sync_hash
