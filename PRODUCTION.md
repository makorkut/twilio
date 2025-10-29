# Production Ayarları ve Optimizasyon

## Kurulum Sonrası Yapılacaklar

Docker container'ları başladıktan sonra production için gerekli ayarlar.

---

## 1. İlk Kontroller ✅

### Container'ların Durumu
```bash
# Tüm container'ların çalıştığını kontrol edin
docker-compose ps

# Beklenen çıktı:
# NAME                    STATE    PORTS
# polyurethane_app        Up       9000/tcp
# polyurethane_nginx      Up       0.0.0.0:8000->80/tcp
# polyurethane_mysql      Up       0.0.0.0:3306->3306/tcp
# polyurethane_phpmyadmin Up       0.0.0.0:8080->80/tcp
# polyurethane_worker     Up
# polyurethane_node       Up
```

### Log Kontrolü
```bash
# Hata var mı kontrol edin
docker-compose logs --tail=100

# Migration başarılı mı?
docker-compose logs app | grep -i migration
```

### Test
```bash
# API health check
curl http://localhost:8000/api/v1/health

# Beklenen: {"status":"ok","timestamp":...}
```

---

## 2. Nginx Ayarları 🔧

### Özel Domain İçin (Production)

`docker/nginx/default.conf` dosyasını düzenleyin:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;  # Değiştir

    # HTTP'den HTTPS'e yönlendir
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;

    server_name yourdomain.com www.yourdomain.com;  # Değiştir
    root /var/www/html/public;
    index index.php index.html;

    # SSL Sertifikaları
    ssl_certificate /etc/nginx/ssl/fullchain.pem;
    ssl_certificate_key /etc/nginx/ssl/privkey.pem;

    # SSL Ayarları (Modern)
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers off;

    # SSL Session Cache
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # HSTS (6 ay)
    add_header Strict-Transport-Security "max-age=15768000; includeSubDomains" always;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Logging
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;

    # Client body size (file uploads)
    client_max_body_size 100M;

    # Timeouts
    client_body_timeout 30;
    client_header_timeout 30;

    # Gzip
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript
               application/x-javascript application/xml+rss
               application/json application/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;

        # PHP-FPM timeouts
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;

        # Buffer sizes
        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 16k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~ /\.env {
        deny all;
    }
}
```

### SSL Sertifikası (Let's Encrypt)

```bash
# Certbot ile SSL al
docker run -it --rm \
  -v ./docker/nginx/ssl:/etc/letsencrypt \
  -p 80:80 -p 443:443 \
  certbot/certbot certonly --standalone \
  -d yourdomain.com \
  -d www.yourdomain.com

# Sertifikayı Nginx'e mount et (docker-compose.yml)
nginx:
  volumes:
    - ./docker/nginx/ssl:/etc/nginx/ssl
```

### Nginx Reload
```bash
# Ayarları değiştirdikten sonra
docker-compose exec nginx nginx -t  # Test
docker-compose exec nginx nginx -s reload  # Reload
```

---

## 3. PHP-FPM Optimizasyonu ⚡

`docker/php/www.conf` oluşturun:

```ini
[www]
user = www-data
group = www-data

listen = 9000

; Process Manager
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

; Timeouts
request_terminate_timeout = 300s

; Logging
php_admin_value[error_log] = /var/www/html/storage/logs/php-fpm.log
php_admin_flag[log_errors] = on
```

`docker/php/php.ini` oluşturun:

```ini
[PHP]
; Basic
memory_limit = 256M
max_execution_time = 300
max_input_time = 300

; File Uploads
upload_max_filesize = 100M
post_max_size = 100M

; Error Handling (production)
display_errors = Off
display_startup_errors = Off
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
log_errors = On
error_log = /var/www/html/storage/logs/php_errors.log

; OPcache
opcache.enable = 1
opcache.enable_cli = 0
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
opcache.validate_timestamps = 0  ; Production'da 0

; Session
session.save_handler = files
session.save_path = "/var/www/html/storage/sessions"
session.cookie_httponly = 1
session.cookie_secure = 1  ; HTTPS için
session.cookie_samesite = "Lax"

; Security
expose_php = Off
disable_functions = exec,passthru,shell_exec,system,proc_open,popen

; Timezone
date.timezone = Europe/Istanbul
```

Dockerfile'a ekleyin:

```dockerfile
# PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Create sessions directory
RUN mkdir -p /var/www/html/storage/sessions \
    && chown -R www-data:www-data /var/www/html/storage/sessions
```

---

## 4. MySQL Optimizasyonu 🗄️

`docker/mysql/my.cnf` oluşturun:

```ini
[mysqld]
# InnoDB Settings
innodb_buffer_pool_size = 1G  # RAM'in %70'i
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Connection Settings
max_connections = 200
max_connect_errors = 100000

# Query Cache (MySQL 5.7 için, 8.0'da yok)
# query_cache_type = 1
# query_cache_size = 64M

# Buffer Settings
key_buffer_size = 32M
sort_buffer_size = 2M
read_buffer_size = 2M
read_rnd_buffer_size = 8M

# Temp Table
tmp_table_size = 64M
max_heap_table_size = 64M

# Binary Logging
binlog_format = ROW
expire_logs_days = 7
max_binlog_size = 100M

# Character Set
character_set_server = utf8mb4
collation_server = utf8mb4_unicode_ci

# Slow Query Log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2

# General Log (Disable in production)
general_log = 0
```

docker-compose.yml'e ekleyin:

```yaml
mysql:
  volumes:
    - ./docker/mysql/my.cnf:/etc/mysql/conf.d/custom.cnf
    - ./docker/mysql/logs:/var/log/mysql
```

Container'ı yeniden başlatın:
```bash
docker-compose restart mysql
```

---

## 5. .env Production Ayarları 🔐

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database (Güçlü şifreler!)
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=polyurethane_ecommerce
DB_USERNAME=ecommerce_user
DB_PASSWORD=VeryStr0ng_P@ssw0rd_H3r3!
DB_ROOT_PASSWORD=R00t_VeryStr0ng_P@ssw0rd!

# Security
APP_KEY=base64:RANDOM_32_CHARACTER_STRING_HERE

# Cache
CACHE_DRIVER=file
SESSION_DRIVER=file

# Queue
QUEUE_CONNECTION=database
QUEUE_WORKER_SLEEP=3

# Rate Limiting
RATE_LIMIT_MAX_ATTEMPTS=60
RATE_LIMIT_DECAY_MINUTES=1
API_RATE_LIMIT_MAX_ATTEMPTS=100

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error

# Email (Production için yapılandırın)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Webhook Secrets (Güçlü random string'ler)
LOCAL_CRM_WEBHOOK_SECRET=random_secret_here_32_chars_min
STRIPE_WEBHOOK_SECRET=whsec_your_stripe_secret
IYZICO_WEBHOOK_SECRET=your_iyzico_secret

# External Integrations
LOCAL_CRM_ENABLED=true
LOCAL_CRM_BASE_URL=https://crm.yourdomain.com
LOCAL_CRM_API_KEY=your_api_key_here

# CORS
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://www.yourdomain.com

# Features
FEATURE_B2B=true
FEATURE_WAREHOUSE=true
FEATURE_PROJECTS=true

# Pricing
PRICE_VISIBILITY=visible
PRICE_INCLUDES_VAT=true
DEFAULT_VAT_RATE=20
```

---

## 6. Güvenlik Sertleştirme 🔒

### Firewall (Host Sunucuda)

```bash
# UFW (Ubuntu)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp  # SSH
sudo ufw enable

# iptables
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT
```

### Fail2ban (Brute Force Koruması)

`/etc/fail2ban/jail.local`:
```ini
[nginx-limit-req]
enabled = true
filter = nginx-limit-req
action = iptables-multiport[name=ReqLimit, port="http,https"]
logpath = /var/log/nginx/error.log
findtime = 600
bantime = 7200
maxretry = 10
```

### Container Security

docker-compose.yml'de:
```yaml
services:
  app:
    # Security opts
    security_opt:
      - no-new-privileges:true

    # Read-only root filesystem (dikkatli kullan)
    # read_only: true

    # Capabilities drop
    cap_drop:
      - ALL
    cap_add:
      - CHOWN
      - SETGID
      - SETUID
```

---

## 7. Monitoring ve Logging 📊

### Log Dosyaları

```bash
# Container log'ları
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f mysql

# Uygulama log'ları
docker-compose exec app tail -f storage/logs/php_errors.log
docker-compose exec app tail -f storage/logs/php-fpm.log

# Nginx log'ları
docker-compose exec nginx tail -f /var/log/nginx/access.log
docker-compose exec nginx tail -f /var/log/nginx/error.log

# MySQL slow queries
docker-compose exec mysql tail -f /var/log/mysql/slow-query.log
```

### Log Rotation

`docker/logrotate.conf`:
```
/var/www/html/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    sharedscripts
}
```

---

## 8. Backup Stratejisi 💾

### Otomatik Backup Script

`docker/backup.sh`:
```bash
#!/bin/bash
BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)

# Database backup
docker-compose exec -T mysql mysqldump \
  -u ecommerce_user \
  -p$DB_PASSWORD \
  polyurethane_ecommerce | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Uploads backup
tar czf $BACKUP_DIR/uploads_$DATE.tar.gz public/uploads/

# Keep last 7 days
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $DATE"
```

Cron job (her gün saat 02:00):
```bash
0 2 * * * /path/to/docker/backup.sh >> /var/log/backup.log 2>&1
```

---

## 9. Performance Monitoring 📈

### Container Resource Usage

```bash
# Real-time monitoring
docker stats

# Specific project
docker stats $(docker-compose ps -q)
```

### Application Performance

`docker-compose.yml` ekleyin:
```yaml
  # Prometheus (metrics)
  prometheus:
    image: prom/prometheus
    ports:
      - "9090:9090"
    volumes:
      - ./docker/prometheus/prometheus.yml:/etc/prometheus/prometheus.yml

  # Grafana (dashboards)
  grafana:
    image: grafana/grafana
    ports:
      - "3000:3000"
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=admin
```

---

## 10. Health Checks 🏥

docker-compose.yml'de:
```yaml
services:
  app:
    healthcheck:
      test: ["CMD", "php-fpm-healthcheck"]
      interval: 30s
      timeout: 3s
      retries: 3

  nginx:
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/api/v1/health"]
      interval: 30s
      timeout: 3s
      retries: 3
```

---

## 11. Production Checklist ✅

Canlıya almadan önce kontrol edin:

- [ ] **SSL/HTTPS** aktif ve çalışıyor
- [ ] **.env** production ayarları yapıldı
- [ ] **APP_DEBUG=false** ayarlandı
- [ ] **Güçlü şifreler** kullanıldı
- [ ] **Firewall** kuralları ayarlandı
- [ ] **Backup** sistemi çalışıyor
- [ ] **Monitoring** aktif
- [ ] **Log rotation** ayarlandı
- [ ] **Domain** DNS ayarları yapıldı
- [ ] **Email** servisi çalışıyor
- [ ] **Webhook secrets** ayarlandı
- [ ] **Rate limiting** aktif
- [ ] **Security headers** eklendi
- [ ] **CORS** doğru ayarlandı
- [ ] **Database** optimize edildi
- [ ] **PHP OPcache** aktif
- [ ] **Nginx caching** ayarlandı

---

## 12. Deployment Komutu 🚀

Production'a deploy:

```bash
# 1. Son kodu çek
git pull origin main

# 2. Container'ları güncelle
docker-compose down
docker-compose pull
docker-compose up -d --build

# 3. Composer güncelle (production)
docker-compose exec app composer install --no-dev --optimize-autoloader

# 4. Cache temizle
docker-compose exec app php -r "array_map('unlink', glob('storage/cache/**/*'));"

# 5. Migration çalıştır (gerekirse)
docker-compose exec app php app/Migrations/apply.php

# 6. Kontrol et
docker-compose ps
docker-compose logs --tail=100
curl https://yourdomain.com/api/v1/health

# 7. Backup al
./docker/backup.sh
```

---

## Sonuç

Artık production-ready bir sistemin var! 🎉

**Yapılması gerekenler sırası:**
1. ✅ Domain ve SSL ayarla
2. ✅ .env production ayarları
3. ✅ PHP/Nginx/MySQL optimize et
4. ✅ Güvenlik sertleştirmesi
5. ✅ Monitoring kur
6. ✅ Backup stratejisi
7. ✅ Deploy et!

Sorularınız için bana yazabilirsiniz! 🚀
