#!/bin/bash
# =============================================================================
# E-Commerce Platform - CLI Installer
# Hızlı komut satırı kurulumu
# =============================================================================

set -e

# Renkler
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Banner
echo -e "${BLUE}"
echo "================================================"
echo "🚀 E-Commerce Platform - Kurulum Script"
echo "================================================"
echo -e "${NC}"

# Root kontrolü
if [ "$EUID" -eq 0 ]; then
    echo -e "${YELLOW}⚠️  Warning: Root olarak çalıştırılıyor${NC}"
fi

# Dizin kontrolü
if [ ! -f "composer.json" ]; then
    echo -e "${RED}❌ Hata: composer.json bulunamadı!${NC}"
    echo "Lütfen proje dizininde çalıştırın."
    exit 1
fi

echo -e "${GREEN}✓${NC} Proje dizini doğrulandı"
echo ""

# Kurulum tamamlandı mı kontrol
if [ -f ".installer.lock" ]; then
    echo -e "${YELLOW}"
    echo "⚠️  Kurulum zaten tamamlanmış görünüyor!"
    echo ""
    read -p "Yeniden kurmak istiyor musunuz? (y/n): " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "İptal edildi."
        exit 0
    fi
    rm -f .installer.lock
fi

# 1. Sistem Gereksinimleri Kontrolü
echo -e "${BLUE}📋 1/6: Sistem gereksinimleri kontrol ediliyor...${NC}"

# PHP versiyonu
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo -e "${GREEN}✓${NC} PHP Version: $PHP_VERSION"

# PHP Extensions
REQUIRED_EXTENSIONS=("pdo_mysql" "mbstring" "gd" "intl" "zip" "bcmath" "exif")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "^$ext$"; then
        echo -e "${GREEN}✓${NC} PHP Extension: $ext"
    else
        echo -e "${RED}✗${NC} PHP Extension eksik: $ext"
        exit 1
    fi
done

# Composer kontrol
if ! command -v composer &> /dev/null; then
    echo -e "${RED}✗ Composer bulunamadı!${NC}"
    echo "Composer yüklemek için: https://getcomposer.org/download/"
    exit 1
fi
echo -e "${GREEN}✓${NC} Composer mevcut"

echo ""

# 2. Composer Dependencies
echo -e "${BLUE}📦 2/6: Composer dependencies yükleniyor...${NC}"

if [ ! -d "vendor" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
    echo -e "${GREEN}✓${NC} Composer packages yüklendi"
else
    echo -e "${YELLOW}⚠${NC}  vendor/ zaten mevcut, atlanıyor"
fi

echo ""

# 3. Database Bilgileri
echo -e "${BLUE}🗄️  3/6: Database ayarları${NC}"
echo ""

read -p "Database Host [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

read -p "Database Port [3306]: " DB_PORT
DB_PORT=${DB_PORT:-3306}

read -p "Database Name [polyurethane_ecommerce]: " DB_DATABASE
DB_DATABASE=${DB_DATABASE:-polyurethane_ecommerce}

read -p "Database Username [root]: " DB_USERNAME
DB_USERNAME=${DB_USERNAME:-root}

read -sp "Database Password: " DB_PASSWORD
echo ""

# Database bağlantı testi
echo -e "\n${BLUE}🔍 Database bağlantısı test ediliyor...${NC}"
if mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -e "SELECT 1;" &>/dev/null; then
    echo -e "${GREEN}✓${NC} Database bağlantısı başarılı"
else
    echo -e "${RED}✗ Database bağlantı hatası!${NC}"
    echo "Lütfen database bilgilerini kontrol edin."
    exit 1
fi

echo ""

# 4. Uygulama Ayarları
echo -e "${BLUE}⚙️  4/6: Uygulama ayarları${NC}"
echo ""

# Site URL otomatik tespit
if [ -n "$SERVER_NAME" ]; then
    DEFAULT_URL="https://$SERVER_NAME"
else
    DEFAULT_URL="http://localhost"
fi

read -p "Site URL [$DEFAULT_URL]: " APP_URL
APP_URL=${APP_URL:-$DEFAULT_URL}

read -p "Varsayılan Dil (tr/en) [tr]: " DEFAULT_LANG
DEFAULT_LANG=${DEFAULT_LANG:-tr}

read -p "Varsayılan Para Birimi (TRY/USD/EUR) [TRY]: " DEFAULT_CURRENCY
DEFAULT_CURRENCY=${DEFAULT_CURRENCY:-TRY}

echo ""

# 5. .env dosyası oluştur
echo -e "${BLUE}📝 5/6: .env dosyası oluşturuluyor...${NC}"

# APP_KEY oluştur
APP_KEY=$(openssl rand -base64 32)
APP_KEY="base64:$APP_KEY"

cat > .env <<EOF
# Application
APP_NAME=Polyurethane E-Commerce
APP_ENV=production
APP_DEBUG=false
APP_URL=$APP_URL
APP_KEY=$APP_KEY
APP_TIMEZONE=Europe/Istanbul

# Database
DB_CONNECTION=mysql
DB_HOST=$DB_HOST
DB_PORT=$DB_PORT
DB_DATABASE=$DB_DATABASE
DB_USERNAME=$DB_USERNAME
DB_PASSWORD=$DB_PASSWORD

# Defaults
DEFAULT_LANG=$DEFAULT_LANG
DEFAULT_CURRENCY=$DEFAULT_CURRENCY

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=database

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error

# Features
FEATURE_B2B=true
FEATURE_WAREHOUSE=true
FEATURE_PROJECTS=true

# Pricing
PRICE_VISIBILITY=visible
PRICE_INCLUDES_VAT=true
DEFAULT_VAT_RATE=20

# CORS
CORS_ALLOWED_ORIGINS=*
EOF

echo -e "${GREEN}✓${NC} .env dosyası oluşturuldu"
echo ""

# 6. Dizinler ve İzinler
echo -e "${BLUE}📁 6/6: Dizinler ve izinler ayarlanıyor...${NC}"

# Dizinleri oluştur
mkdir -p storage/cache/i18n
mkdir -p storage/cache/rate_limits
mkdir -p storage/logs
mkdir -p storage/sessions
mkdir -p storage/uploads/media
mkdir -p public/uploads/media
mkdir -p bootstrap/cache

# İzinleri ayarla
chmod -R 755 storage
chmod -R 755 public/uploads
chmod -R 755 bootstrap/cache

echo -e "${GREEN}✓${NC} Dizinler oluşturuldu"
echo ""

# 7. Database Migrations
echo -e "${BLUE}🔧 Database migrations çalıştırılıyor...${NC}"

if [ -f "app/Migrations/apply.php" ]; then
    php app/Migrations/apply.php
    echo -e "${GREEN}✓${NC} Migrations tamamlandı"
else
    echo -e "${YELLOW}⚠${NC}  Migrations dosyası bulunamadı, atlanıyor"
fi

echo ""

# Lock dosyası oluştur
touch .installer.lock
echo "$(date '+%Y-%m-%d %H:%M:%S')" > .installer.lock

# Tamamlandı
echo -e "${GREEN}"
echo "================================================"
echo "✅ KURULUM BAŞARIYLA TAMAMLANDI!"
echo "================================================"
echo -e "${NC}"
echo ""
echo "📊 Kurulum Özeti:"
echo "  • Site URL: $APP_URL"
echo "  • Database: $DB_DATABASE@$DB_HOST"
echo "  • Varsayılan Dil: $DEFAULT_LANG"
echo "  • Varsayılan Para Birimi: $DEFAULT_CURRENCY"
echo ""
echo "🎯 Sonraki Adımlar:"
echo "  1. Web sunucunuzu yapılandırın (Nginx/Apache)"
echo "  2. public/ klasörünü document root olarak ayarlayın"
echo "  3. $APP_URL adresini ziyaret edin"
echo ""
echo -e "${YELLOW}⚠️  GÜVENLİK: installer.php ve install.sh dosyalarını silin!${NC}"
echo ""
echo "🚀 Hazırsınız! İyi kullanımlar."
echo ""
