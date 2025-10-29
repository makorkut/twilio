# Kurulum ve Önizleme Rehberi

Bu projeyı lokal ortamınızda çalıştırmak için aşağıdaki adımları takip edin.

## Gereksinimler

- **PHP 8.0+** (8.1 veya 8.2 önerilir)
- **MariaDB/MySQL 8.0+**
- **Composer 2.x**
- **Node.js 16+** (SCSS compile için, opsiyonel)

## Kurulum Adımları

### 1. PHP ve Composer Kurulumu

#### macOS (Homebrew ile)
```bash
brew install php@8.2
brew install composer
```

#### Ubuntu/Debian
```bash
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl
sudo apt install composer
```

#### Windows
- PHP: https://windows.php.net/download/
- Composer: https://getcomposer.org/download/

### 2. Proje Bağımlılıklarını Yükleyin

```bash
cd /home/user/twilio
composer install
```

Bu komut şu paketleri yükler:
- `vlucas/phpdotenv` - Environment variables
- `symfony/http-foundation` - HTTP request/response
- `twig/twig` - Template engine
- `intervention/image` - Image processing
- `phpmailer/phpmailer` - Email sending
- `firebase/php-jwt` - JWT authentication
- ve diğerleri...

### 3. Database Kurulumu

#### MariaDB/MySQL başlatın:

**macOS:**
```bash
brew services start mysql
```

**Ubuntu:**
```bash
sudo systemctl start mysql
```

#### Database oluşturun:

```bash
mysql -u root -p
```

MySQL içinde:
```sql
CREATE DATABASE polyurethane_ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ecommerce_user'@'localhost' IDENTIFIED BY 'güçlü_şifre_buraya';
GRANT ALL PRIVILEGES ON polyurethane_ecommerce.* TO 'ecommerce_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Environment Yapılandırması

`.env` dosyasını oluşturun (zaten var ama kontrol edin):

```bash
cp .env.example .env
nano .env  # veya favori editörünüz
```

**Minimum gerekli ayarlar:**
```env
# Application
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=polyurethane_ecommerce
DB_USERNAME=ecommerce_user
DB_PASSWORD=güçlü_şifre_buraya

# Default Settings
DEFAULT_LANG=tr
DEFAULT_CURRENCY=TRY
```

### 5. Database Migration'larını Çalıştırın

```bash
php app/Migrations/apply.php
```

Bu komut:
- ✅ 7 migration dosyasını çalıştırır
- ✅ 50+ tablo oluşturur
- ✅ Trigger'ları ekler (SKU auto-attachment)
- ✅ Seed data'yı yükler

Başarılı olduğunda şunu göreceksiniz:
```
Migration applied: 001_core_i18n_system.sql
Migration applied: 002_multi_currency_pricing.sql
...
All migrations completed successfully!
```

### 6. Storage Klasörlerini Oluşturun

```bash
mkdir -p storage/cache/i18n
mkdir -p storage/cache/rate_limits
mkdir -p storage/logs
mkdir -p storage/uploads/media
mkdir -p public/uploads/media

chmod -R 755 storage
chmod -R 755 public/uploads
```

### 7. SCSS'i CSS'e Derleyin (Opsiyonel)

#### Sass kurulumu:
```bash
npm install -g sass
```

#### Compile:
```bash
sass public/assets/scss/main.scss public/assets/css/main.css --watch
```

Veya tek seferlik:
```bash
sass public/assets/scss/main.scss public/assets/css/main.css
```

### 8. PHP Development Server'ı Başlatın

```bash
php -S localhost:8000 -t public/
```

Terminal'de şunu göreceksiniz:
```
[Wed Oct 29 2025 15:30:00] PHP 8.2.0 Development Server (http://localhost:8000) started
```

### 9. Browser'da Açın

🌐 **http://localhost:8000**

API health check:
```bash
curl http://localhost:8000/api/v1/health
```

Yanıt:
```json
{
  "status": "ok",
  "timestamp": 1730000000
}
```

## Önizleme URL'leri

### Frontend (Henüz veri yok, şablonları göreceksiniz)
- Ana sayfa: `http://localhost:8000/`
- Ürün kategorisi: `http://localhost:8000/tr/kategori/test`
- Hakkımızda: `http://localhost:8000/tr/about`

### API Endpoints (Test için)

**Products API:**
```bash
# Ürün listesi
curl http://localhost:8000/api/v1/products

# Kategori listesi
curl http://localhost:8000/api/v1/admin/categories

# Media istatistikleri
curl http://localhost:8000/api/v1/admin/media/statistics
```

### Admin API (Authentication gerekli - geliştirme aşamasında)
```bash
# Ürün ekleme
curl -X POST http://localhost:8000/api/v1/admin/products \
  -H "Content-Type: application/json" \
  -d '{
    "sku": "POLY-001",
    "status": "active",
    "translations": {
      "tr": {
        "name": "Test Ürün",
        "slug": "test-urun",
        "description": "Test açıklaması"
      }
    },
    "prices": {
      "TRY": {
        "base_price": 150.00
      }
    }
  }'
```

## Background Worker (Opsiyonel)

Queue worker'ı başlatmak için ayrı bir terminal açın:

```bash
php worker.php --daemon --sleep=3
```

Bu worker:
- ✅ Webhook'ları işler
- ✅ Ürün import'larını yapar
- ✅ Resim download'larını halleder
- ✅ Email gönderir

## Test Verisi Ekleme

### Test ürünü ekleyin:

```bash
mysql -u ecommerce_user -p polyurethane_ecommerce

INSERT INTO products (sku, type, status, stock_status, stock_quantity, created_at)
VALUES ('POLY-001', 'simple', 'active', 'in_stock', 100, NOW());

SET @product_id = LAST_INSERT_ID();

INSERT INTO product_lang (product_id, lang, name, slug, short_description, description)
VALUES
  (@product_id, 'tr', 'Poliüretan Köpük', 'poliuretan-kopuk', 'Yüksek kaliteli poliüretan köpük', 'Detaylı açıklama...'),
  (@product_id, 'en', 'Polyurethane Foam', 'polyurethane-foam', 'High quality foam', 'Detailed description...');

INSERT INTO product_prices_currency (product_id, currency_code, base_price)
VALUES
  (@product_id, 'TRY', 150.00),
  (@product_id, 'EUR', 5.00),
  (@product_id, 'USD', 5.50);
```

### Test kategorisi ekleyin:

```sql
INSERT INTO categories (type, status, sort_order, created_at)
VALUES ('product', 'active', 1, NOW());

SET @category_id = LAST_INSERT_ID();

INSERT INTO category_lang (category_id, lang, name, slug, description)
VALUES
  (@category_id, 'tr', 'Poliüretan Ürünler', 'poliuretan-urunler', 'Tüm poliüretan ürünlerimiz'),
  (@category_id, 'en', 'Polyurethane Products', 'polyurethane-products', 'All our polyurethane products');
```

Şimdi ürünü kategoriye taşıyın:
```sql
UPDATE products SET category_id = @category_id WHERE id = @product_id;
```

## Önizleme - Artık Çalışıyor!

Şimdi browser'da:
- ✅ `http://localhost:8000/tr/kategori/poliuretan-urunler` - Ürünü göreceksiniz
- ✅ `http://localhost:8000/tr/poliuretan-urunler/poliuretan-kopuk` - Detay sayfası

## Sorun Giderme

### 1. "Connection refused" hatası
```bash
# MySQL çalışıyor mu?
sudo systemctl status mysql

# Başlatın
sudo systemctl start mysql
```

### 2. "Access denied" hatası
```bash
# Database kullanıcısını yeniden oluşturun
mysql -u root -p
DROP USER 'ecommerce_user'@'localhost';
# Yukarıdaki CREATE USER komutlarını tekrar çalıştırın
```

### 3. "Class not found" hatası
```bash
# Composer autoload'u yenileyin
composer dump-autoload
```

### 4. "Permission denied" - storage klasörleri
```bash
chmod -R 755 storage
chmod -R 755 public/uploads
```

### 5. SCSS compile edilmiyor
```bash
# Node.js ve sass yükleyin
npm install -g sass

# Manuel compile
sass public/assets/scss/main.scss public/assets/css/main.css
```

## Production Deployment

Production'a almadan önce:

1. ✅ `.env` dosyasında `APP_ENV=production` yapın
2. ✅ `APP_DEBUG=false` yapın
3. ✅ Güçlü şifreler kullanın
4. ✅ HTTPS sertifikası ekleyin
5. ✅ PHP-FPM + Nginx kullanın (production için)
6. ✅ Composer'ı `--no-dev` ile çalıştırın
7. ✅ Cache'leri ayarlayın
8. ✅ Backup stratejisi oluşturun

## Daha Fazla Bilgi

- **README.md** - Proje genel bakış
- **API Documentation** - `docs/api.md` (yakında eklenecek)
- **Database Schema** - `app/Migrations/sql/` klasöründe

## İletişim

Sorularınız için:
- GitHub Issues: https://github.com/makorkut/twilio/issues
- Email: [your-email@example.com]
