-- ============================================================================
-- Database Setup Script
-- E-Commerce Platform - Database, User ve Şifre Oluşturma
-- ============================================================================

-- 1. Database Oluştur
CREATE DATABASE IF NOT EXISTS polyurethane_ecommerce
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- 2. Database User Oluştur ve Şifre Belirle
-- Şifreyi değiştirmeyi unutma!
CREATE USER IF NOT EXISTS 'ecommerce_user'@'%'
    IDENTIFIED BY 'P0lyur3th@n3!2024$ecur3';

-- 3. User'a Database Üzerinde Tüm Yetkileri Ver
GRANT ALL PRIVILEGES ON polyurethane_ecommerce.*
    TO 'ecommerce_user'@'%';

-- 4. Yetkileri Uygula
FLUSH PRIVILEGES;

-- 5. Kontrol Et
SELECT User, Host FROM mysql.user WHERE User = 'ecommerce_user';
SHOW DATABASES LIKE 'polyurethane_ecommerce';

-- ============================================================================
-- Kurulum Tamamlandı!
-- ============================================================================
-- Database: polyurethane_ecommerce
-- Username: ecommerce_user
-- Password: P0lyur3th@n3!2024$ecur3
-- Host: % (tüm hostlardan erişim)
-- ============================================================================
