-- ============================================================================
-- Database Initialization Script
-- Docker container başlarken otomatik çalışır
-- ============================================================================

-- UTF8MB4 character set ayarla (emoji ve özel karakterler için)
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Timezone ayarla
SET time_zone = '+03:00';

-- Test için basit bir tablo oluştur (migrations tarafından silinecek)
CREATE TABLE IF NOT EXISTS _db_init_check (
    id INT AUTO_INCREMENT PRIMARY KEY,
    initialized_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    message VARCHAR(255) DEFAULT 'Database initialized successfully'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _db_init_check (message) VALUES ('Database ready for migrations');

-- Yetkileri kontrol et
SHOW GRANTS FOR 'ecommerce_user'@'%';

SELECT '✅ Database initialization completed successfully!' AS status;
