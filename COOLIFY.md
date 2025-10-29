# Coolify Deployment Rehberi 🚀

Bu proje Coolify ile tek tıkla deploy edilebilir. Tüm ayarlar Dockerfile içinde yapılmış durumda.

---

## Hızlı Kurulum

### 1. Coolify'da Yeni Proje Oluştur

1. **Coolify Dashboard** → **New Resource** → **Public Repository**
2. Repository URL: `https://github.com/makorkut/twilio.git`
3. Branch: `main` (veya kullandığınız branch)
4. Build Pack: **Dockerfile**
5. Dockerfile: `Dockerfile.coolify`

### 2. Environment Variables Ayarla

Coolify'da **Environment Variables** bölümüne şunları ekle:

```bash
# Application
APP_NAME=Polyurethane E-Commerce
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (Coolify Managed Database kullanıyorsanız otomatik gelir)
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=polyurethane_ecommerce
DB_USERNAME=ecommerce_user
DB_PASSWORD=very_strong_password_here

# Optional: Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-mail-password
MAIL_FROM_ADDRESS=noreply@yourdomain.com

# Optional: Webhook Secrets
LOCAL_CRM_WEBHOOK_SECRET=random_secret_32_chars
LOCAL_CRM_ENABLED=true
LOCAL_CRM_BASE_URL=https://crm.yourdomain.com

# Optional: Feature Flags
FEATURE_B2B=true
FEATURE_WAREHOUSE=true

# Optional: Rate Limiting
RATE_LIMIT_MAX_ATTEMPTS=60
RATE_LIMIT_DECAY_MINUTES=1
```

### 3. Database Oluştur

**İki seçenek:**

#### A) Coolify Managed Database (Önerilen)
1. **New Resource** → **Database** → **MySQL 8.0**
2. Database oluştur
3. Connection bilgilerini otomatik alır

#### B) External Database
Manuel olarak MySQL database oluşturun ve credentials'ları environment variables'a girin.

### 4. Deploy Et!

**Deploy** butonuna bas. İşte bu kadar! ✅

Coolify otomatik olarak:
- ✅ Dockerfile.coolify'ı build eder
- ✅ Nginx + PHP-FPM + Worker'ı başlatır
- ✅ Database'e bağlanır
- ✅ Migration'ları çalıştırır
- ✅ Production ayarlarını uygular
- ✅ SSL sertifikası ekler (varsa)

---

## Port ve Network Ayarları

### Port
- **Container Port**: 80 (Nginx dinler)
- **Coolify otomatik olarak**: 443 (HTTPS) veya özel port'a yönlendirir

### Health Check
Built-in health check var:
```bash
curl http://localhost/api/v1/health
```

Coolify bunu otomatik kontrol eder her 30 saniyede.

---

## Dockerfile.coolify Özellikleri

### ✅ Tamamen Kurulu Geliyor:
- PHP 8.2 FPM + Nginx (tek container)
- Tüm PHP extensions (GD, MySQL, ZIP, OPcache, etc.)
- Composer bağımlılıkları (production-only)
- SCSS compiled → CSS
- Migration'lar otomatik çalışır
- Queue worker aktif
- Production PHP ayarları (OPcache, memory, upload limits)
- Production Nginx ayarları (gzip, cache, security headers)
- Log dosyaları hazır

### ⚡ Performans Optimizasyonları:
- OPcache enabled (validate_timestamps=0)
- Nginx gzip compression
- Static asset caching (1 year)
- PHP-FPM dynamic process management
- Multi-stage build (küçük image size)

### 🔒 Güvenlik:
- Security headers (X-Frame-Options, CSP, etc.)
- Sensitive file protection (.env, composer.json)
- PHP expose_php = Off
- Session security (httponly, secure, samesite)

---

## Deploy Sonrası Kontrol

### 1. Container Logları
Coolify Dashboard'da **Logs** sekmesine bak:

```bash
✅ Database is ready!
✅ .env file created
✅ Migrations completed successfully
✅ Permissions set
✅ Application Started Successfully!
```

### 2. API Test
```bash
curl https://your-domain.com/api/v1/health
```

Beklenen yanıt:
```json
{
  "status": "ok",
  "timestamp": 1730208000
}
```

### 3. Frontend Test
Browser'da aç:
```
https://your-domain.com
```

### 4. PHPMyAdmin (opsiyonel)
Eğer database management UI istiyorsanız:
1. Coolify'da yeni **PHPMyAdmin** service ekle
2. Database'e bağla

---

## Sorun Giderme

### 1. "Database connection failed"

**Çözüm:**
```bash
# Environment variables kontrol et
DB_HOST=correct-db-host
DB_PORT=3306
DB_DATABASE=correct-db-name
DB_USERNAME=correct-user
DB_PASSWORD=correct-password
```

Coolify'da **Restart** butonuna bas.

### 2. "502 Bad Gateway"

**Sebep:** PHP-FPM veya Nginx çöktü.

**Çözüm:**
```bash
# Logs kontrol et
Coolify Dashboard → Logs

# Container'ı restart et
Coolify Dashboard → Restart
```

### 3. "Migration failed"

**Sebep:** Database henüz hazır değil veya credentials yanlış.

**Çözüm:**
- Database service'in çalıştığından emin ol
- Environment variables'ı kontrol et
- Manuel migration çalıştır:

```bash
# Coolify Terminal'de
php /var/www/html/app/Migrations/apply.php
```

### 4. "Permission denied"

**Sebep:** Storage klasörleri yazılabilir değil.

**Çözüm:**
```bash
# Coolify Terminal'de
chown -R www-data:www-data /var/www/html/storage
chmod -R 755 /var/www/html/storage
```

### 5. "Blank page / White screen"

**Sebep:** PHP error, log'lara bak.

**Çözüm:**
```bash
# Log kontrol et
tail -f /var/www/html/storage/logs/php_errors.log

# veya Coolify Dashboard → Logs
```

---

## Custom Domain Ayarları

### 1. Coolify'da Domain Ekle
1. Resource → **Domains**
2. Domain ekle: `yourdomain.com`
3. **Generate SSL** (Let's Encrypt)

### 2. DNS Ayarları
Domain registrar'ında A kaydı ekle:
```
Type: A
Name: @ (veya subdomain)
Value: your-coolify-server-ip
TTL: 3600
```

### 3. WWW Redirect (opsiyonel)
```
Type: CNAME
Name: www
Value: yourdomain.com
TTL: 3600
```

---

## Environment Variables Reference

### Zorunlu Olanlar:
| Variable | Örnek | Açıklama |
|----------|-------|----------|
| `APP_URL` | `https://domain.com` | Site URL'i |
| `DB_HOST` | `mysql` | Database host |
| `DB_DATABASE` | `ecommerce` | Database adı |
| `DB_USERNAME` | `user` | Database kullanıcı |
| `DB_PASSWORD` | `pass` | Database şifre |

### İsteğe Bağlı:
| Variable | Default | Açıklama |
|----------|---------|----------|
| `APP_ENV` | `production` | Environment |
| `APP_DEBUG` | `false` | Debug mode |
| `DEFAULT_LANG` | `tr` | Varsayılan dil |
| `DEFAULT_CURRENCY` | `TRY` | Varsayılan para birimi |
| `LOG_LEVEL` | `error` | Log seviyesi |

### Özellikler:
| Variable | Default | Açıklama |
|----------|---------|----------|
| `FEATURE_B2B` | `true` | B2B özellikleri |
| `FEATURE_WAREHOUSE` | `true` | Çoklu depo |
| `FEATURE_PROJECTS` | `true` | Proje yönetimi |

---

## Güncelleme (Redeploy)

Kod değişikliği yaptığınızda:

1. **Git push**
```bash
git push origin main
```

2. **Coolify'da Redeploy**
- Dashboard → **Redeploy** butonu
- veya otomatik deployment aktifse kendisi deploy eder

**Zero-downtime deployment:** Coolify eski container'ı yeni hazır olana kadar tutar.

---

## Monitoring

### Built-in Metrics
Coolify Dashboard'da:
- CPU usage
- Memory usage
- Network traffic
- Disk usage

### Application Logs
```bash
# Tüm log'lar storage/logs/ klasöründe:
- php_errors.log (PHP hataları)
- php-fpm.log (PHP-FPM)
- nginx-stdout.log (Nginx access)
- nginx-stderr.log (Nginx error)
- worker-stdout.log (Queue worker)
- supervisord.log (Supervisor)
```

Coolify Terminal'den:
```bash
tail -f storage/logs/php_errors.log
```

---

## Backup

### Database Backup (Coolify Managed Database)
Coolify otomatik backup yapar. Manuel backup için:

1. Dashboard → Database → **Backup Now**
2. veya scheduled backup ayarla

### Manuel Backup
```bash
# Coolify Terminal'de
mysqldump -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE | gzip > /tmp/backup.sql.gz

# Download
# Coolify → Terminal → File Manager
```

---

## Scaling

### Horizontal Scaling (Replica)
Coolify'da:
1. Resource → **Settings**
2. **Replicas**: 2 (veya daha fazla)
3. Load balancer otomatik devreye girer

### Vertical Scaling (Resources)
```yaml
# Coolify settings
CPU: 2 cores
Memory: 2GB
```

---

## Production Checklist

Canlıya almadan önce:

- [ ] **APP_ENV=production** ayarlandı
- [ ] **APP_DEBUG=false** ayarlandı
- [ ] **Güçlü database şifresi** kullanıldı
- [ ] **Custom domain** eklendi
- [ ] **SSL certificate** aktif (Coolify otomatik)
- [ ] **Environment variables** tamamlandı
- [ ] **Database backup** aktif
- [ ] **Health check** çalışıyor
- [ ] **Logs** kontrol edildi
- [ ] **API test** edildi
- [ ] **Frontend test** edildi

---

## Avantajlar

### Docker Compose vs Coolify:
| Özellik | Docker Compose | Coolify |
|---------|----------------|---------|
| Setup | Manuel | Otomatik |
| SSL | Manuel | Otomatik |
| Domain | Manuel | Otomatik |
| Deploy | Manuel | Git push |
| Monitoring | Yok | Built-in |
| Backup | Manuel script | Otomatik |
| Logs | docker logs | Dashboard |
| Updates | Manuel | Otomatik |
| Scaling | Manuel | Tek tık |

---

## Destek

### Coolify Issues:
- Container başlamıyor → Logs kontrol et
- 502 Error → Restart container
- Database bağlanamıyor → Env variables kontrol et
- Migration hatası → Manuel çalıştır
- Permission hatası → chown/chmod çalıştır

### Uygulama Issues:
- PHP error → `storage/logs/php_errors.log`
- Nginx error → `storage/logs/nginx-stderr.log`
- Worker error → `storage/logs/worker-stderr.log`

### Daha Fazla Yardım:
- Coolify Docs: https://coolify.io/docs
- GitHub Issues: https://github.com/makorkut/twilio/issues

---

## Özet

```bash
# 1. Coolify'da proje oluştur
Repository: https://github.com/makorkut/twilio.git
Dockerfile: Dockerfile.coolify

# 2. Environment variables ekle
DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL

# 3. Database oluştur (Managed veya External)

# 4. Deploy!

# 5. Domain ekle ve SSL aktif et

# Bitti! ✅
```

**Coolify ile deploy süresi: ~5 dakika** 🚀

---

Sorularınız için GitHub Issues kullanabilirsiniz!
