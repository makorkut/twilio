#!/bin/bash

# ============================================
# Otomatik Kurulum Scripti
# ============================================
# Bu script database'i tamamen sıfırdan kurar
# Hiçbir manuel işlem gerektirmez!
# ============================================

set -e  # Hata durumunda dur

# Renkler
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "============================================"
echo "  🚀 E-Commerce Platform - Otomatik Kurulum"
echo "============================================"
echo -e "${NC}"

# ============================================
# 1. Ortam Kontrolü
# ============================================
echo -e "${YELLOW}▶ Ortam kontrol ediliyor...${NC}"

if [ ! -f ".env" ]; then
    echo -e "${RED}✗ .env dosyası bulunamadı!${NC}"
    echo -e "${YELLOW}  .env.example'dan kopyalanıyor...${NC}"
    cp .env.example .env
    echo -e "${GREEN}✓ .env oluşturuldu (lütfen database ayarlarını kontrol edin)${NC}"
fi

# .env dosyasını yükle
export $(cat .env | grep -v '^#' | xargs)

echo -e "${GREEN}✓ .env dosyası yüklendi${NC}"

# ============================================
# 2. Gerekli Klasörleri Oluştur
# ============================================
echo -e "${YELLOW}▶ Storage klasörleri oluşturuluyor...${NC}"

mkdir -p storage/{logs,uploads,cache,catalogs,qrcodes,documents,sessions,temp}
chmod -R 777 storage

echo -e "${GREEN}✓ Storage klasörleri hazır${NC}"

# ============================================
# 3. Composer Bağımlılıkları
# ============================================
echo -e "${YELLOW}▶ Composer bağımlılıkları kontrol ediliyor...${NC}"

if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}  vendor klasörü yok, composer install çalıştırılıyor...${NC}"
    composer install --no-dev --optimize-autoloader
    echo -e "${GREEN}✓ Composer bağımlılıkları yüklendi${NC}"
else
    echo -e "${GREEN}✓ vendor klasörü mevcut${NC}"
fi

# ============================================
# 4. Database Bağlantı Testi
# ============================================
echo -e "${YELLOW}▶ Database bağlantısı test ediliyor...${NC}"

php -r "
try {
    \$pdo = new PDO(
        'mysql:host=${DB_HOST};port=${DB_PORT:-3306};dbname=${DB_DATABASE};charset=utf8mb4',
        '${DB_USERNAME}',
        '${DB_PASSWORD}',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo '✓ Database bağlantısı başarılı' . PHP_EOL;
} catch (PDOException \$e) {
    echo '✗ Database bağlantısı başarısız: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"

if [ $? -ne 0 ]; then
    echo -e "${RED}Database bağlantısı kurulamadı! .env dosyasını kontrol edin.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Database bağlantısı OK${NC}"

# ============================================
# 5. Mevcut Migration Kaydını Temizle
# ============================================
echo -e "${YELLOW}▶ Migration geçmişi temizleniyor...${NC}"

mysql -h"${DB_HOST}" -P"${DB_PORT:-3306}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" <<EOF
DROP TABLE IF EXISTS migrations;
EOF

echo -e "${GREEN}✓ Migration geçmişi temizlendi${NC}"

# ============================================
# 6. Migration'ları Çalıştır
# ============================================
echo -e "${YELLOW}▶ Database migration'ları çalıştırılıyor...${NC}"
echo ""

php app/Migrations/apply.php

if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Migration hatası! Yukarıdaki hata mesajını kontrol edin.${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✓ Tüm migration'lar başarıyla tamamlandı${NC}"

# ============================================
# 7. Admin Kullanıcı Oluştur
# ============================================
echo -e "${YELLOW}▶ Admin kullanıcı oluşturuluyor...${NC}"

if [ -f "app/Migrations/seed-admin.php" ]; then
    php app/Migrations/seed-admin.php
else
    echo -e "${YELLOW}  seed-admin.php bulunamadı, manuel oluşturulacak...${NC}"

    ADMIN_EMAIL="${ADMIN_EMAIL:-admin@polyes.tr}"
    ADMIN_PASSWORD="${ADMIN_PASSWORD:-Admin123!}"
    ADMIN_NAME="${ADMIN_NAME:-Admin User}"
    ADMIN_HASH=$(php -r "echo password_hash('${ADMIN_PASSWORD}', PASSWORD_BCRYPT);")

    mysql -h"${DB_HOST}" -P"${DB_PORT:-3306}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" <<EOF
INSERT IGNORE INTO users (name, email, password, role, is_active, email_verified_at)
VALUES ('${ADMIN_NAME}', '${ADMIN_EMAIL}', '${ADMIN_HASH}', 'admin', 1, NOW());
EOF
fi

echo -e "${GREEN}✓ Admin kullanıcı oluşturuldu${NC}"

# ============================================
# 8. Temel Ayarları Oluştur
# ============================================
echo -e "${YELLOW}▶ Temel ayarlar yapılandırılıyor...${NC}"

mysql -h"${DB_HOST}" -P"${DB_PORT:-3306}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" <<'SQLEOF'
CREATE TABLE IF NOT EXISTS settings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  `key` VARCHAR(255) NOT NULL UNIQUE,
  value TEXT,
  type ENUM('string','text','number','boolean','json') DEFAULT 'string',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO settings (`key`, value, type) VALUES
('site_name', 'E-Commerce Platform', 'string'),
('site_description', 'Professional e-commerce platform', 'text'),
('default_language', 'tr', 'string'),
('default_currency', 'TRY', 'string'),
('vat_rate', '20', 'number'),
('items_per_page', '24', 'number'),
('enable_registration', 'true', 'boolean'),
('enable_reviews', 'true', 'boolean'),
('enable_wishlist', 'true', 'boolean'),
('maintenance_mode', 'false', 'boolean');
SQLEOF

echo -e "${GREEN}✓ Temel ayarlar yapılandırıldı${NC}"

# ============================================
# 9. Özet
# ============================================
echo ""
echo -e "${BLUE}============================================${NC}"
echo -e "${GREEN}✓ Kurulum başarıyla tamamlandı!${NC}"
echo -e "${BLUE}============================================${NC}"
echo ""
echo -e "${YELLOW}📊 Oluşturulan Tablolar:${NC}"

TABLE_COUNT=$(mysql -h"${DB_HOST}" -P"${DB_PORT:-3306}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '${DB_DATABASE}';" -sN)

echo -e "   ${GREEN}$TABLE_COUNT tablo oluşturuldu${NC}"
echo ""

echo -e "${YELLOW}👤 Admin Giriş Bilgileri:${NC}"
echo -e "   Email:    ${GREEN}${ADMIN_EMAIL:-admin@polyes.tr}${NC}"
echo -e "   Password: ${GREEN}${ADMIN_PASSWORD:-Admin123!}${NC}"
echo ""

echo -e "${YELLOW}🌐 Uygulama URL'leri:${NC}"
echo -e "   Homepage:  ${BLUE}${APP_URL:-http://localhost}${NC}"
echo -e "   Admin:     ${BLUE}${APP_URL:-http://localhost}/admin${NC}"
echo ""

echo -e "${YELLOW}📝 Sıradaki Adımlar:${NC}"
echo -e "   1. ${APP_URL}/diagnostic.php adresini ziyaret edin"
echo -e "   2. Tüm testlerin yeşil olduğunu kontrol edin"
echo -e "   3. Admin panele giriş yapın: ${APP_URL}/admin"
echo -e "   4. Güvenlik için diagnostic.php dosyasını silin:"
echo -e "      ${GREEN}rm public/diagnostic.php${NC}"
echo ""

echo -e "${GREEN}✨ Hazırsınız! Uygulamanız kullanıma hazır.${NC}"
echo ""
