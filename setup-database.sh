#!/bin/bash
# ============================================================================
# E-Commerce Platform - Database Otomatik Kurulum Script
# ============================================================================

set -e

echo "================================================"
echo "🗄️  Database Kurulum Script"
echo "================================================"
echo ""

# Renkli output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
DB_NAME="polyurethane_ecommerce"
DB_USER="ecommerce_user"
DB_PASSWORD="P0lyur3th@n3!2024\$ecur3"
DB_HOST="localhost"
DB_PORT="3306"

# MySQL Root Credentials (kullanıcıdan sor)
echo -e "${YELLOW}MySQL root şifresini gir:${NC}"
read -s MYSQL_ROOT_PASSWORD
echo ""

echo "📝 Kurulum Bilgileri:"
echo "   Database: $DB_NAME"
echo "   User: $DB_USER"
echo "   Password: $DB_PASSWORD"
echo "   Host: $DB_HOST (% = tüm hostlar)"
echo "   Port: $DB_PORT"
echo ""

read -p "Devam etmek için Enter'a bas (Ctrl+C ile iptal)..."

echo ""
echo "🔧 Database oluşturuluyor..."

# Create database and user
mysql -h"$DB_HOST" -P"$DB_PORT" -uroot -p"$MYSQL_ROOT_PASSWORD" <<EOF
-- Database Oluştur
CREATE DATABASE IF NOT EXISTS $DB_NAME
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- User Oluştur
CREATE USER IF NOT EXISTS '$DB_USER'@'%'
    IDENTIFIED BY '$DB_PASSWORD';

-- Yetkileri Ver
GRANT ALL PRIVILEGES ON $DB_NAME.*
    TO '$DB_USER'@'%';

-- Yetkileri Uygula
FLUSH PRIVILEGES;
EOF

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Database başarıyla oluşturuldu!${NC}"
else
    echo -e "${RED}❌ Hata: Database oluşturulamadı!${NC}"
    exit 1
fi

echo ""
echo "🧪 Bağlantı test ediliyor..."

# Test connection
mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "SELECT 'Connection successful!' AS status;" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Bağlantı başarılı!${NC}"
else
    echo -e "${RED}❌ Hata: Database'e bağlanılamadı!${NC}"
    exit 1
fi

echo ""
echo "================================================"
echo -e "${GREEN}✅ Kurulum Tamamlandı!${NC}"
echo "================================================"
echo ""
echo "📋 Coolify Environment Variables için:"
echo ""
echo "DB_HOST=$DB_HOST"
echo "DB_PORT=$DB_PORT"
echo "DB_DATABASE=$DB_NAME"
echo "DB_USERNAME=$DB_USER"
echo "DB_PASSWORD=$DB_PASSWORD"
echo ""
echo "================================================"
