# 📦 Manuel Kurulum Kılavuzu

E-Commerce platformunu kendi sunucunuza manuel olarak kurma rehberi.

---

## 🚀 Hızlı Başlangıç

### Yöntem 1: Web Installer (Tavsiye - En Kolay)

```bash
# 1. Dosyaları sunucuya yükle
scp -r * user@your-server:/var/www/html/

# 2. Composer dependencies yükle
composer install --no-dev --optimize-autoloader

# 3. Web tarayıcıda installer'ı aç
https://yourdomain.com/installer.php
```

**Adımları takip et, 2 dakikada kurulum tamamlanır!** ✅

---

### Yöntem 2: CLI Installer (Terminal)

```bash
# 1. Dosyaları sunucuya yükle
scp -r * user@your-server:/var/www/html/

# 2. SSH ile bağlan
ssh user@your-server
cd /var/www/html/

# 3. Installer script'i çalıştır
chmod +x install.sh
./install.sh
```

Script soru-cevap şeklinde kurulumu tamamlar.

---

### Yöntem 3: Manuel Kurulum

Tüm adımları manuel yapmak istiyorsanız:

#### 1. Composer Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

#### 2. .env Dosyası Oluştur

```bash
cp .env.example .env
nano .env
```

**Düzenle:**
```env
APP_URL=https://yourdomain.com
APP_KEY=base64:BURAYA_RANDOM_KEY

DB_HOST=localhost
DB_DATABASE=polyurethane_ecommerce
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

**APP_KEY oluştur:**
```bash
openssl rand -base64 32
```

#### 3. Dizinler ve İzinler

```bash
mkdir -p storage/cache/i18n
mkdir -p storage/logs
mkdir -p storage/sessions
mkdir -p public/uploads/media
mkdir -p bootstrap/cache

chmod -R 755 storage
chmod -R 755 public/uploads
chmod -R 755 bootstrap/cache

chown -R www-data:www-data storage public/uploads bootstrap/cache
```

#### 4. Database Oluştur

```sql
CREATE DATABASE polyurethane_ecommerce
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON polyurethane_ecommerce.*
  TO 'ecommerce_user'@'localhost'
  IDENTIFIED BY 'your_password';

FLUSH PRIVILEGES;
```

#### 5. Migrations Çalıştır

```bash
php app/Migrations/apply.php
```

---

## 🌐 Web Sunucu Yapılandırması

### Nginx (Tavsiye)

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/public;
    index index.php index.html;

    # PHP-FPM
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Pretty URLs
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Security
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

**Nginx restart:**
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### Apache

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Pretty URLs
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/ecommerce_error.log
    CustomLog ${APACHE_LOG_DIR}/ecommerce_access.log combined
</VirtualHost>
```

**Apache restart:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## ✅ Kurulum Sonrası

### 1. GÜVENLİK

```bash
# Installer dosyalarını SİL!
rm installer.php
rm install.sh
rm INSTALL.md
```

### 2. SSL Sertifikası (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

### 3. Cron Jobs (Opsiyonel)

Queue worker için:
```bash
# crontab -e
* * * * * php /var/www/html/worker.php >> /dev/null 2>&1
```

### 4. İlk Test

```bash
# Health check
curl https://yourdomain.com/api/v1/health

# Response:
{"status":"ok","timestamp":1730208000}
```

---

## 🔧 Sorun Giderme

### "500 Internal Server Error"

```bash
# PHP error logları kontrol et
tail -f /var/www/html/storage/logs/php-errors.log
tail -f /var/log/nginx/error.log

# Permissions kontrol
ls -la storage/
ls -la public/uploads/
```

### "Database connection failed"

```bash
# .env dosyasını kontrol et
cat .env | grep DB_

# MySQL bağlantı testi
mysql -h localhost -u your_user -p your_database
```

### "Class not found"

```bash
# Composer autoload yeniden oluştur
composer dump-autoload --optimize
```

### "Permission denied"

```bash
# Tüm izinleri düzelt
sudo chown -R www-data:www-data /var/www/html/storage
sudo chown -R www-data:www-data /var/www/html/public/uploads
sudo chmod -R 755 /var/www/html/storage
sudo chmod -R 755 /var/www/html/public/uploads
```

---

## 📚 Sistem Gereksinimleri

### Zorunlu:
- **PHP:** 8.2 veya üzeri
- **MySQL:** 8.0+ veya MariaDB 10.5+
- **Composer:** 2.x
- **Web Server:** Nginx veya Apache

### PHP Extensions:
- pdo_mysql
- mbstring
- gd
- intl
- zip
- bcmath
- exif
- opcache (tavsiye)

### Sunucu:
- RAM: Minimum 512MB (1GB+ tavsiye)
- Disk: 500MB+ boş alan
- SSL Sertifikası (Let's Encrypt)

---

## 🎯 Sonraki Adımlar

1. ✅ Admin paneline giriş: `/admin`
2. ✅ İlk ürünleri ekle
3. ✅ Kategori yapısını oluştur
4. ✅ Tema ve tasarımı özelleştir
5. ✅ Webhook'ları yapılandır
6. ✅ SMTP mail ayarlarını yap

---

## 🆘 Destek

Sorun yaşıyorsanız:

1. **Logları kontrol et:**
   - `storage/logs/php-errors.log`
   - `/var/log/nginx/error.log`
   - `/var/log/apache2/error.log`

2. **Debug mode aç (geçici):**
   ```bash
   # .env dosyasında
   APP_DEBUG=true
   ```

3. **GitHub Issues:** [github.com/makorkut/twilio/issues](https://github.com/makorkut/twilio/issues)

---

## 📝 Notlar

- **Production'da** `APP_DEBUG=false` olmalı
- **Database backup** düzenli alın
- **Log dosyaları** düzenli temizleyin
- **Composer dependencies** güncelleyin

Başarılı kurulumlar! 🚀
