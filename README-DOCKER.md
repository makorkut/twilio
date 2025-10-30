# 🚀 Docker Compose Deployment Guide

Complete guide for deploying the E-Commerce platform using Docker Compose with **zero manual configuration**.

## ✨ Features

- 🐳 **One-Command Deployment** - Everything configured automatically
- 🗄️ **MySQL 8.0 Database** - Included in the stack
- 🔄 **Auto Migrations** - Database schema created automatically
- 👤 **Auto Admin User** - Login credentials created on first run
- ⚡ **Background Worker** - Queue processing starts automatically
- 📦 **Persistent Data** - Volumes for database, storage, and uploads
- 🔒 **Secure by Default** - Strong passwords and proper permissions

## 🎯 Quick Start

### 1. Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- Git

### 2. Clone & Deploy

```bash
# Clone repository
git clone <your-repo-url>
cd twilio

# Start everything
docker-compose up -d

# Watch logs (optional)
docker-compose logs -f app
```

**That's it!** The system will:
1. ✅ Build the application container
2. ✅ Start MySQL database
3. ✅ Wait for database to be ready
4. ✅ Run all migrations automatically
5. ✅ Create admin user
6. ✅ Start the web server
7. ✅ Start background worker

### 3. Access the Application

- **Website**: http://localhost:3000
- **Admin Panel**: http://localhost:3000/admin
- **Health Check**: http://localhost:3000/healthz
- **API Health**: http://localhost:3000/api/v1/health

### 4. Admin Login

**Login URL**: http://localhost:3000/admin

Default credentials (created automatically):
- **Email**: `admin@polyes.tr`
- **Password**: `Admin123!S3cur3`

⚠️ **IMPORTANT**: Change the admin password after first login!

**What you can do in Admin Panel:**
- View system status and statistics
- Quick links to health checks
- Logout functionality
- (More features will be added as development continues)

## 🎨 Configuration

All configuration is in `docker-compose.yml`. Default values work out of the box, but you can customize:

### Option 1: Edit docker-compose.yml

```yaml
services:
  app:
    environment:
      # Change these values directly
      ADMIN_EMAIL: your@email.com
      ADMIN_PASSWORD: YourSecurePassword123!
      APP_URL: https://yourdomain.com
```

### Option 2: Use Environment Variables

```bash
# Create .env file
cp .env.example .env

# Edit .env with your values
nano .env

# Docker Compose will automatically use these values
docker-compose up -d
```

## 📋 Environment Variables

### Required (Auto-configured)

These are set automatically with secure defaults:

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_ROOT_PASSWORD` | `R00t!S3cur3P@ss2024` | MySQL root password |
| `DB_DATABASE` | `ecommerce_db` | Database name |
| `DB_USERNAME` | `ecommerce_user` | Database user |
| `DB_PASSWORD` | `Ec0mm3rc3!S3cur3P@ss` | Database password |
| `ADMIN_EMAIL` | `admin@polyes.tr` | Admin user email |
| `ADMIN_PASSWORD` | `Admin123!S3cur3` | Admin user password |

### Optional

| Variable | Default | Description |
|----------|---------|-------------|
| `AUTO_MIGRATE` | `true` | Auto-run database migrations |
| `AUTO_SEED` | `true` | Auto-create admin user |
| `AUTO_START_WORKER` | `true` | Auto-start background worker |
| `APP_PORT` | `3000` | External port to expose |
| `APP_ENV` | `production` | Application environment |
| `APP_DEBUG` | `false` | Enable debug mode |

## 🔧 Common Tasks

### View Logs

```bash
# All services
docker-compose logs -f

# Just application
docker-compose logs -f app

# Just database
docker-compose logs -f mysql
```

### Restart Services

```bash
# Restart everything
docker-compose restart

# Restart just app
docker-compose restart app
```

### Stop Services

```bash
# Stop (keeps data)
docker-compose stop

# Stop and remove containers (keeps data in volumes)
docker-compose down

# Stop and remove everything INCLUDING DATA
docker-compose down -v
```

### Access Container Shell

```bash
# Application container
docker-compose exec app bash

# MySQL container
docker-compose exec mysql bash

# Or use MySQL client directly
docker-compose exec mysql mysql -u ecommerce_user -p ecommerce_db
```

### Manual Migration

Migrations run automatically, but if needed:

```bash
docker-compose exec app php /var/www/html/app/Migrations/apply.php
```

### Create Additional Admin User

```bash
docker-compose exec app php /var/www/html/app/Migrations/seed-admin.php
```

### Check Worker Status

```bash
# View worker logs
docker-compose exec app tail -f /var/www/html/storage/logs/worker-stdout.log

# Check if worker is running
docker-compose exec app supervisorctl status worker

# Start/stop worker manually
docker-compose exec app supervisorctl start worker
docker-compose exec app supervisorctl stop worker
```

## 📊 Architecture

```
┌─────────────────────────────────────────┐
│  Docker Compose Stack                   │
├─────────────────────────────────────────┤
│                                         │
│  ┌──────────────┐    ┌──────────────┐  │
│  │     App      │───▶│    MySQL     │  │
│  │              │    │   Database   │  │
│  │  - Nginx     │    │              │  │
│  │  - PHP-FPM   │    │  Port 3306   │  │
│  │  - Worker    │    └──────────────┘  │
│  │              │                       │
│  │  Port 3000   │    ┌──────────────┐  │
│  └──────────────┘    │   Volumes    │  │
│                      │              │  │
│                      │  - mysql_data│  │
│                      │  - storage   │  │
│                      │  - uploads   │  │
│                      └──────────────┘  │
│                                         │
└─────────────────────────────────────────┘
```

## 🗄️ Data Persistence

Data is stored in Docker volumes:

- `mysql_data` - Database files
- `storage_data` - Application logs, cache, sessions
- `uploads_data` - User uploaded files

### Backup Data

```bash
# Backup database
docker-compose exec mysql mysqldump -u ecommerce_user -p ecommerce_db > backup.sql

# Backup volumes
docker run --rm \
  -v twilio_mysql_data:/data \
  -v $(pwd):/backup \
  alpine tar czf /backup/mysql_data.tar.gz /data
```

### Restore Data

```bash
# Restore database
docker-compose exec -T mysql mysql -u ecommerce_user -p ecommerce_db < backup.sql

# Restore volume
docker run --rm \
  -v twilio_mysql_data:/data \
  -v $(pwd):/backup \
  alpine tar xzf /backup/mysql_data.tar.gz -C /
```

## 🔒 Security Checklist

- [ ] Change `ADMIN_PASSWORD` before deployment
- [ ] Change `DB_ROOT_PASSWORD` and `DB_PASSWORD`
- [ ] Set `APP_DEBUG=false` in production
- [ ] Use HTTPS with SSL/TLS (configure reverse proxy)
- [ ] Regularly update Docker images
- [ ] Enable firewall on host machine
- [ ] Backup data regularly
- [ ] Review logs for suspicious activity

## 🌐 Production Deployment

### With Reverse Proxy (Recommended)

Use Traefik, Nginx Proxy Manager, or Coolify:

```yaml
services:
  app:
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.ecommerce.rule=Host(`yourdomain.com`)"
      - "traefik.http.routers.ecommerce.tls=true"
      - "traefik.http.services.ecommerce.loadbalancer.server.port=3000"
```

### Environment-Specific .env

```bash
# Production
cp .env.example .env.production
# Edit .env.production with production values

# Staging
cp .env.example .env.staging
# Edit .env.staging with staging values

# Use specific env file
docker-compose --env-file .env.production up -d
```

## 🐛 Troubleshooting

### Database Connection Failed

**Symptoms**: "Connection timed out" or "Connection refused"

**Solution**:
```bash
# Check if MySQL is running
docker-compose ps

# Check MySQL logs
docker-compose logs mysql

# Wait for health check
docker-compose exec mysql mysqladmin ping -h localhost -u root -p
```

### Application Won't Start

**Symptoms**: Container exits immediately

**Solution**:
```bash
# Check application logs
docker-compose logs app

# Check for port conflicts
lsof -i :3000

# Rebuild with no cache
docker-compose build --no-cache
docker-compose up -d
```

### Migrations Not Running

**Symptoms**: Database tables don't exist

**Solution**:
```bash
# Run migrations manually
docker-compose exec app php /var/www/html/app/Migrations/apply.php

# Check if AUTO_MIGRATE is set
docker-compose exec app env | grep AUTO_MIGRATE
```

### Worker Not Processing Jobs

**Symptoms**: Background jobs stuck in queue

**Solution**:
```bash
# Check worker status
docker-compose exec app supervisorctl status worker

# View worker logs
docker-compose exec app cat /var/www/html/storage/logs/worker-stdout.log

# Restart worker
docker-compose exec app supervisorctl restart worker
```

### Permission Denied Errors

**Symptoms**: "Permission denied" in logs

**Solution**:
```bash
# Fix permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads
docker-compose exec app chmod -R 755 /var/www/html/storage /var/www/html/public/uploads
```

## 📚 Additional Resources

- [Main Documentation](README.md)
- [Deployment Guide](DEPLOYMENT.md)
- [API Documentation](API.md)
- [Docker Compose Documentation](https://docs.docker.com/compose/)

## 🆘 Support

If you encounter issues:

1. Check logs: `docker-compose logs -f`
2. Review this troubleshooting section
3. Check GitHub Issues
4. Contact support

## 📝 License

[Your License]

---

**Ready to deploy?** Run `docker-compose up -d` and you're done! 🚀
