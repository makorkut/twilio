# Deployment Guide

## Coolify Deployment

This application is configured for deployment on Coolify using Docker.

### Prerequisites

1. **Database Server**
   - MySQL 8.0 or compatible
   - Accessible from your Coolify server
   - Firewall configured to allow connections

### Configuration

#### Environment Variables

Create a `.env` file in Coolify with the following variables:

```env
# Application
APP_NAME=E-Commerce
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=Europe/Istanbul

# Database
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=your-database-name
DB_USERNAME=your-database-user
DB_PASSWORD=your-database-password

# Storage
STORAGE_DRIVER=local
STORAGE_PATH=/var/www/html/storage

# Queue
QUEUE_DRIVER=database
QUEUE_WORKER_SLEEP=3

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### Database Connection Options

You have several options for database connectivity:

#### Option 1: Coolify Managed Database (Recommended)

1. In Coolify, go to **Resources** → **New Database** → **MySQL 8.0**
2. Create the database
3. Copy the connection details
4. Update your `.env` file with the provided credentials
5. Redeploy the application

**Advantages:**
- Automatic networking (no firewall issues)
- Managed backups
- Same data center (low latency)

#### Option 2: External Database Server

If using an external database (e.g., 78.189.76.176):

1. **Configure Firewall Rules**
   - Find your Coolify server's IP address
   - Add it to your database server's firewall whitelist
   - Allow connections on port 3306 (MySQL default)

2. **Test Connection**
   ```bash
   # From your Coolify server
   mysql -h 78.189.76.176 -P 3306 -u username -p database_name
   ```

3. **Common Issues:**
   - **Connection timeout**: Firewall blocking connection
   - **Access denied**: Wrong credentials or host not allowed
   - **Name resolution**: Use IP address instead of hostname

### Health Checks

The application uses a simple static health check at `/healthz` that doesn't require database connectivity.

- **Endpoint**: `https://your-domain.com/healthz`
- **Expected Response**: `OK`
- **Docker Health Check**: Every 30s, 3s timeout

### Application Structure

```
/var/www/html/
├── public/          # Web root
│   ├── index.php    # Main entry point
│   ├── healthz      # Health check endpoint
│   └── test.php     # PHP info (remove in production)
├── app/             # Application code
├── routes/          # Route definitions
├── storage/         # Logs, cache, uploads
├── themes/          # Frontend themes
└── worker.php       # Background job processor
```

### Deployment Process

1. **Push Code**
   ```bash
   git push origin your-branch
   ```

2. **Coolify Auto-Deploy**
   - Coolify detects the push
   - Builds new Docker image
   - Runs health checks
   - Switches to new container if healthy
   - Rolls back if health checks fail

3. **Verify Deployment**
   ```bash
   # Check container is running
   docker ps | grep your-app-name

   # Check health
   curl https://your-domain.com/healthz

   # Check logs
   docker logs container-id
   ```

### Troubleshooting

#### Site Returns 500 Error

1. **Check Logs**
   ```bash
   # Get container ID
   docker ps | grep your-app-name

   # View logs
   docker logs container-id

   # Or exec into container
   docker exec -it container-id bash
   cat /var/www/html/storage/logs/php_errors.log
   ```

2. **Common Causes:**
   - Database connection failed
   - Missing .env variables
   - File permission issues
   - PHP errors

#### Database Connection Timeout

**Symptoms:**
```
PHP Fatal error: Database connection failed: SQLSTATE[HY000] [2002] Connection timed out
```

**Solutions:**

1. **Check Database Server is Running**
   ```bash
   # From database server
   systemctl status mysql
   ```

2. **Check Firewall Rules**
   ```bash
   # Allow from Coolify server IP
   ufw allow from COOLIFY_SERVER_IP to any port 3306
   ```

3. **Check MySQL User Permissions**
   ```sql
   -- On database server
   GRANT ALL PRIVILEGES ON database_name.* TO 'username'@'%' IDENTIFIED BY 'password';
   FLUSH PRIVILEGES;
   ```

4. **Use Coolify Managed Database** (easiest solution)

#### Worker Not Processing Jobs

The background worker requires database connectivity. If database is unavailable:

```
❌ Database connection failed: SQLSTATE[HY000] [2002] Connection timed out

Please check:
  1. Database server is running
  2. DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD in .env are correct
  3. Database server allows connections from this IP
  4. Firewall rules permit access to database port

Worker cannot run without database connection.
```

**Note:** The main website will still work for basic pages (home, about, contact) even without database, but dynamic content (products, etc.) requires database.

### Performance Optimization

1. **Enable OPcache** (already configured in Dockerfile)
2. **Use Production .env** (`APP_DEBUG=false`)
3. **Set up CDN** for static assets
4. **Configure Redis** for sessions and cache (optional)

### Security Checklist

- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong `DB_PASSWORD`
- [ ] Remove `test.php` from production
- [ ] Configure SSL/TLS (Coolify handles this)
- [ ] Set up regular database backups
- [ ] Keep Docker images updated

### Maintenance Mode

To enable maintenance mode:

```bash
# Create maintenance file
touch .maintenance

# Site will show maintenance page to all users except localhost
# To bypass: https://your-domain.com/?bypass_maintenance
```

To disable:
```bash
rm .maintenance
```

## Support

For issues or questions:
1. Check application logs
2. Check Coolify deployment logs
3. Review this documentation
4. Check database connectivity

## License

[Your License]
