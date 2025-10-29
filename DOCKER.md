# Docker Kurulum ve Kullanım Rehberi 🐳

Bu proje Docker ile tamamen otomatik kurulum ve çalıştırma desteği sunar. Tek bir komutla tüm bağımlılıklar (PHP, MySQL, Nginx, Node.js) kurulur ve uygulama çalışır hale gelir.

## Gereksinimler

Sadece Docker ve Docker Compose yüklü olmalı:

- **Docker Desktop** (Windows/Mac): https://www.docker.com/products/docker-desktop
- **Docker Engine** (Linux): https://docs.docker.com/engine/install/

Docker sürümünüzü kontrol edin:
```bash
docker --version
docker-compose --version
```

## Hızlı Başlangıç 🚀

### 1. Projeyi klonlayın
```bash
git clone https://github.com/makorkut/twilio.git
cd twilio
```

### 2. Environment ayarlarını yapın (opsiyonel)
```bash
cp .env.example .env
# .env dosyasını düzenleyin (varsayılan ayarlar çoğu durumda yeterli)
```

### 3. Docker container'ları başlatın
```bash
docker-compose up -d
```

Bu komut:
- ✅ PHP 8.2 FPM container'ını oluşturur ve başlatır
- ✅ MySQL 8.0 database'ini başlatır
- ✅ Nginx web server'ı yapılandırır
- ✅ Composer bağımlılıklarını yükler
- ✅ Database migration'larını otomatik çalıştırır
- ✅ Storage klasörlerini hazırlar
- ✅ Queue worker'ı başlatır
- ✅ SCSS'i compile eder (Node.js ile)

### 4. Uygulamayı tarayıcıda açın

🌐 **Frontend**: http://localhost:8000
🗄️ **PHPMyAdmin**: http://localhost:8080
📊 **API Health**: http://localhost:8000/api/v1/health

## Container'lar

Docker Compose aşağıdaki servisleri başlatır:

| Servis | Port | Açıklama |
|--------|------|----------|
| **app** | 9000 | PHP 8.2 FPM (uygulama) |
| **nginx** | 8000 | Nginx web server |
| **mysql** | 3306 | MySQL 8.0 database |
| **phpmyadmin** | 8080 | Database yönetim paneli |
| **worker** | - | Background job işleyici |
| **node** | - | SCSS compiler |

## Kullanım Komutları

### Container'ları Başlatma
```bash
# Detached mode (arka planda)
docker-compose up -d

# Foreground mode (log'ları görmek için)
docker-compose up

# Sadece belirli servisleri başlat
docker-compose up -d app nginx mysql
```

### Container'ları Durdurma
```bash
# Durdur (veriler korunur)
docker-compose stop

# Durdur ve kaldır (veriler volume'de kalır)
docker-compose down

# Durdur, kaldır ve volume'leri sil (DİKKAT: Tüm database verisi silinir!)
docker-compose down -v
```

### Log'ları İzleme
```bash
# Tüm container log'ları
docker-compose logs -f

# Sadece app log'ları
docker-compose logs -f app

# Sadece nginx log'ları
docker-compose logs -f nginx

# Son 100 satır
docker-compose logs --tail=100 -f
```

### Container İçinde Komut Çalıştırma
```bash
# PHP container'a bağlan
docker-compose exec app bash

# Composer komutları
docker-compose exec app composer install
docker-compose exec app composer update

# Migration çalıştır
docker-compose exec app php app/Migrations/apply.php

# Worker'ı manuel başlat
docker-compose exec app php worker.php --daemon
```

### Database İşlemleri

#### MySQL CLI
```bash
# MySQL container'a bağlan
docker-compose exec mysql mysql -u ecommerce_user -p
# Şifre: secret (veya .env'de ayarladığınız)
```

#### Database Backup
```bash
# Backup al
docker-compose exec mysql mysqldump -u ecommerce_user -psecret polyurethane_ecommerce > backup.sql

# Backup'ı geri yükle
docker-compose exec -T mysql mysql -u ecommerce_user -psecret polyurethane_ecommerce < backup.sql
```

#### PHPMyAdmin
Tarayıcıda: http://localhost:8080
- **Server**: mysql
- **Username**: ecommerce_user
- **Password**: secret (veya .env'de ayarladığınız)

### Container'ları Yeniden Oluşturma
```bash
# Değişikliklerden sonra yeniden oluştur
docker-compose up -d --build

# Cache'siz yeniden oluştur
docker-compose build --no-cache
docker-compose up -d
```

## Environment Değişkenleri

`.env` dosyasında aşağıdaki değişkenler ayarlanabilir:

```env
# Database
DB_DATABASE=polyurethane_ecommerce
DB_USERNAME=ecommerce_user
DB_PASSWORD=secret
DB_ROOT_PASSWORD=rootsecret

# Application
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

Docker Compose bu değişkenleri otomatik olarak container'lara aktarır.

## Geliştirme İş Akışı

### Kod Değişiklikleri
Kod değişiklikleri **anında** yansır çünkü dosyalar volume olarak mount edilmiştir:
```yaml
volumes:
  - ./:/var/www/html  # Host'taki kod container'da
```

Değişiklik yaptıktan sonra sadece tarayıcıyı yenileyin!

### Composer Paketleri Ekleme
```bash
# Yeni paket ekle
docker-compose exec app composer require vendor/package

# Paket kaldır
docker-compose exec app composer remove vendor/package
```

### Database Migration Ekleme
```bash
# Yeni migration dosyası oluşturun
nano app/Migrations/sql/008_yeni_ozellik.sql

# Migration'ı çalıştır
docker-compose exec app php app/Migrations/apply.php
```

### SCSS Değişiklikleri
Node container SCSS dosyalarını **watch** modunda dinler. `public/assets/scss/` altında değişiklik yaptığınızda otomatik compile edilir.

Manuel compile:
```bash
docker-compose exec node sass public/assets/scss/main.scss public/assets/css/main.css
```

## Sorun Giderme

### "Port already in use" hatası
```bash
# Portları kullanan process'i bul
lsof -i :8000  # veya :3306, :8080
# veya Windows'ta
netstat -ano | findstr :8000

# Docker container'ları durdur
docker-compose down

# Portu değiştir (docker-compose.yml'de)
ports:
  - "8001:80"  # 8000 yerine 8001
```

### Database bağlantı hatası
```bash
# MySQL container'ın çalıştığını kontrol et
docker-compose ps

# MySQL log'larını kontrol et
docker-compose logs mysql

# MySQL'in hazır olmasını bekle
docker-compose exec app bash
while ! nc -z mysql 3306; do sleep 1; done
echo "MySQL ready!"
```

### "Permission denied" hatası
```bash
# Storage klasörlerine izin ver
docker-compose exec app chmod -R 755 storage public/uploads
docker-compose exec app chown -R www-data:www-data storage public/uploads
```

### Migration hatası
```bash
# Migration flag'ini sıfırla
docker-compose exec app rm storage/.migrations_done

# Migration'ı tekrar çalıştır
docker-compose exec app php app/Migrations/apply.php
```

### Container çöktü / çalışmıyor
```bash
# Container durumunu kontrol et
docker-compose ps

# Hatalı container'ı yeniden başlat
docker-compose restart app

# Log'ları kontrol et
docker-compose logs app

# Container'ı yeniden oluştur
docker-compose up -d --force-recreate app
```

### Disk alanı problemi
```bash
# Kullanılmayan image'leri temizle
docker image prune -a

# Kullanılmayan volume'leri temizle (DİKKAT: Veriler silinir)
docker volume prune

# Her şeyi temizle (DİKKAT: Tüm veriler silinir!)
docker system prune -a --volumes
```

## Production Deployment

Production ortamında Docker kullanmak için:

### 1. Environment Ayarları
```bash
cp .env.example .env.production
nano .env.production
```

Production ayarları:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Güçlü şifreler
DB_PASSWORD=very_strong_password_here
DB_ROOT_PASSWORD=very_strong_root_password

# SSL/HTTPS
HTTPS_ENABLED=true
```

### 2. Docker Compose Override
```bash
# Production için özel docker-compose dosyası
cp docker-compose.yml docker-compose.prod.yml
```

`docker-compose.prod.yml` değişiklikleri:
```yaml
services:
  nginx:
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/ssl:/etc/nginx/ssl
```

### 3. SSL Sertifikası
```bash
# Let's Encrypt ile SSL
docker run -it --rm \
  -v /etc/letsencrypt:/etc/letsencrypt \
  certbot/certbot certonly --standalone \
  -d yourdomain.com
```

### 4. Production'da Başlatma
```bash
# Production compose dosyası ile başlat
docker-compose -f docker-compose.prod.yml up -d

# Log'ları izle
docker-compose -f docker-compose.prod.yml logs -f
```

### 5. Otomatik Yedekleme
```bash
# Günlük backup cron job
0 2 * * * docker-compose exec mysql mysqldump -u root -p$DB_ROOT_PASSWORD polyurethane_ecommerce > /backups/db_$(date +\%Y\%m\%d).sql
```

## Docker Network ve Volume'ler

### Network
```bash
# Network bilgilerini görüntüle
docker network inspect twilio_polyurethane_network

# Container'lar arası iletişim
# Container içinden: curl http://mysql:3306
```

### Volume'ler
```bash
# Volume'leri listele
docker volume ls

# Volume detayları
docker volume inspect twilio_mysql_data

# Volume'ü backup al
docker run --rm -v twilio_mysql_data:/data -v $(pwd):/backup alpine tar czf /backup/mysql_backup.tar.gz /data
```

## Performans Optimizasyonu

### PHP-FPM Ayarları
`docker/php/php.ini` oluşturun:
```ini
memory_limit = 256M
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
```

Dockerfile'a ekleyin:
```dockerfile
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
```

### Nginx Cache
`docker/nginx/default.conf` içinde:
```nginx
location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### MySQL Tuning
`docker/mysql/my.cnf` oluşturun:
```ini
[mysqld]
max_connections = 200
innodb_buffer_pool_size = 1G
```

docker-compose.yml'e ekleyin:
```yaml
mysql:
  volumes:
    - ./docker/mysql/my.cnf:/etc/mysql/conf.d/custom.cnf
```

## Monitoring

### Container Health Check
```bash
# Container sağlık durumu
docker-compose ps

# Detaylı health check
docker inspect --format='{{json .State.Health}}' polyurethane_mysql | jq
```

### Resource Usage
```bash
# CPU, RAM kullanımı
docker stats

# Sadece bu proje
docker stats $(docker-compose ps -q)
```

## Güvenlik

### Container Security
```bash
# Container'ları non-root kullanıcı ile çalıştır
USER www-data  # Dockerfile'da

# Read-only file system (mümkün olan yerlerde)
read_only: true
```

### Secrets Management
```bash
# Docker secrets kullan (Swarm mode)
echo "secret_password" | docker secret create db_password -

# docker-compose.yml'de
secrets:
  - db_password
```

### Network Isolation
```bash
# Internal network (sadece container'lar arası)
networks:
  internal:
    internal: true
```

## Faydalı Komutlar

```bash
# Tüm container'ları durdur
docker-compose stop

# Tüm container'ları sil (veriler korunur)
docker-compose down

# Cache temizliği (app container'da)
docker-compose exec app php -r "array_map('unlink', glob('storage/cache/**/*'));"

# Composer cache temizle
docker-compose exec app composer clear-cache

# Node cache temizle
docker-compose exec node npm cache clean --force

# Test çalıştır (PHPUnit kurulu ise)
docker-compose exec app vendor/bin/phpunit
```

## Sık Sorulan Sorular

**S: Docker olmadan çalıştırabilir miyim?**
C: Evet! `SETUP.md` dosyasında manuel kurulum adımları var.

**S: Windows'ta çalışır mı?**
C: Evet, Docker Desktop ile sorunsuz çalışır. WSL2 backend önerilir.

**S: Hangi PHP sürümü kullanılıyor?**
C: PHP 8.2 FPM. Dockerfile'da değiştirebilirsiniz: `FROM php:8.1-fpm`

**S: Database verileri nerede saklanıyor?**
C: Docker volume'de: `twilio_mysql_data`. Container silinse bile veriler kalır.

**S: Production'da kullanabilir miyim?**
C: Evet, ancak yukarıdaki "Production Deployment" bölümünü takip edin.

## İletişim ve Destek

- **GitHub Issues**: https://github.com/makorkut/twilio/issues
- **Documentation**: README.md, SETUP.md
- **Docker Hub**: (varsa registry bilgisi)

---

**Hızlı Referans:**
```bash
docker-compose up -d        # Başlat
docker-compose down         # Durdur
docker-compose logs -f      # Log'ları izle
docker-compose exec app bash  # Container'a gir
```

Happy Coding! 🚀🐳
