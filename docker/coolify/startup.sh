#!/bin/bash
set -e  # Exit on any error

echo "================================================"
echo "🚀 E-Commerce Application - Single Container"
echo "================================================"
echo ""

# ============================================
# Step 1: Initialize MariaDB Data Directory
# ============================================
echo "📦 Step 1: Initializing MariaDB..."

if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "   First run - creating MySQL data directory..."
    mysql_install_db --user=mysql --datadir=/var/lib/mysql --skip-test-db
    echo "   ✅ MySQL data directory created"
else
    echo "   ✅ MySQL data directory already exists"
fi

# ============================================
# Step 2: Start MariaDB
# ============================================
echo ""
echo "🔧 Step 2: Starting MariaDB..."

# Start MariaDB in background
mysqld_safe --datadir=/var/lib/mysql --user=mysql &
MYSQL_PID=$!

# Wait for MariaDB to be ready
echo "   Waiting for MariaDB to start..."
for i in {1..30}; do
    if mysqladmin ping -h localhost --silent 2>/dev/null; then
        echo "   ✅ MariaDB is running!"
        break
    fi
    if [ $i -eq 30 ]; then
        echo "   ❌ MariaDB failed to start!"
        exit 1
    fi
    sleep 2
done

# Extra wait for full initialization
sleep 2

# ============================================
# Step 3: Create Database and User
# ============================================
echo ""
echo "🗄️  Step 3: Creating database and user..."

DB_NAME="${DB_DATABASE:-ecommerce_db}"
DB_USER="${DB_USERNAME:-ecommerce_user}"
DB_PASS="${DB_PASSWORD:-Ec0mm3rc3!S3cur3P@ss}"
DB_ROOT_PASS="${DB_ROOT_PASSWORD:-R00t!S3cur3P@ss2024}"

# Create database and user
mysql -u root -h localhost <<EOF
-- Create database
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
DROP USER IF EXISTS '${DB_USER}'@'localhost';
CREATE USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';

-- Grant privileges
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';

-- Set root password
ALTER USER 'root'@'localhost' IDENTIFIED BY '${DB_ROOT_PASS}';

FLUSH PRIVILEGES;

-- Verify
SELECT User, Host FROM mysql.user WHERE User IN ('root', '${DB_USER}');
SHOW DATABASES LIKE '${DB_NAME}';
EOF

if [ $? -eq 0 ]; then
    echo "   ✅ Database '${DB_NAME}' created"
    echo "   ✅ User '${DB_USER}' created"
else
    echo "   ❌ Failed to create database!"
    exit 1
fi

# ============================================
# Step 4: Create .env File
# ============================================
echo ""
echo "⚙️  Step 4: Creating .env file..."

cat > /var/www/html/.env <<ENVEOF
# Application
APP_NAME=${APP_NAME:-E-Commerce}
APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}
APP_TIMEZONE=Europe/Istanbul

# Database (MariaDB on localhost)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

# Localization
DEFAULT_LANG=tr
DEFAULT_CURRENCY=TRY

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
ENVEOF

echo "   ✅ .env file created"

# ============================================
# Step 5: Set Permissions
# ============================================
echo ""
echo "🔐 Step 5: Setting permissions..."

chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads 2>/dev/null || true
chmod -R 755 /var/www/html/storage /var/www/html/public/uploads 2>/dev/null || true

echo "   ✅ Permissions set"

# ============================================
# Step 6: Run Database Migrations
# ============================================
echo ""
echo "🔄 Step 6: Running database migrations..."

cd /var/www/html

if php app/Migrations/apply.php 2>&1 | tee /var/www/html/storage/logs/migrations.log; then
    echo "   ✅ Migrations completed"
else
    echo "   ⚠️  Migrations failed (check logs/migrations.log)"
fi

# ============================================
# Step 7: Create Admin User
# ============================================
echo ""
echo "👤 Step 7: Creating admin user..."

ADMIN_EMAIL="${ADMIN_EMAIL:-admin@polyes.tr}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-Admin123!S3cur3}"
ADMIN_NAME="${ADMIN_NAME:-Admin User}"

export ADMIN_EMAIL ADMIN_PASSWORD ADMIN_NAME

if php app/Migrations/seed-admin.php 2>&1 | tee /var/www/html/storage/logs/seed-admin.log; then
    echo "   ✅ Admin user created"
    echo ""
    echo "   📋 Admin Credentials:"
    echo "   Email: ${ADMIN_EMAIL}"
    echo "   Password: ${ADMIN_PASSWORD}"
else
    echo "   ⚠️  Admin user creation failed (check logs/seed-admin.log)"
fi

# ============================================
# Step 8: Stop MariaDB (Supervisor will manage it)
# ============================================
echo ""
echo "⏸️  Step 8: Stopping MariaDB (Supervisor will restart)..."

mysqladmin -u root -p"${DB_ROOT_PASS}" shutdown 2>/dev/null || killall mysqld 2>/dev/null || true
sleep 2

echo "   ✅ MariaDB stopped"

# ============================================
# Step 9: Start Supervisor (manages all services)
# ============================================
echo ""
echo "🎯 Step 9: Starting Supervisor..."
echo ""
echo "================================================"
echo "✅ DATABASE SETUP COMPLETED!"
echo "================================================"
echo ""
echo "🗄️  Database:"
echo "   Name: ${DB_NAME}"
echo "   User: ${DB_USER}"
echo "   Host: localhost"
echo ""
echo "👤 Admin Panel:"
echo "   URL: ${APP_URL}/admin"
echo "   Email: ${ADMIN_EMAIL}"
echo "   Password: ${ADMIN_PASSWORD}"
echo ""
echo "📊 Supervisor will now start:"
echo "   ✅ MariaDB    - localhost:3306"
echo "   ✅ PHP-FPM    - 127.0.0.1:9000"
echo "   ✅ Nginx      - 0.0.0.0:3000"
echo "   ⏸️  Worker    - Manual start (supervisorctl start worker)"
echo ""
echo "📝 Logs: /var/www/html/storage/logs/"
echo ""
echo "🚀 System Ready!"
echo "================================================"
echo ""

# Enable worker if requested
if [ "${AUTO_START_WORKER:-true}" = "true" ]; then
    echo "⚙️  Worker will be started via Supervisor..."
    # Update supervisor config to autostart worker
    sed -i '/\[program:worker\]/,/user=www-data/ s/autostart=false/autostart=true/' /etc/supervisor/conf.d/services.conf
fi

# Start Supervisor (this will start MariaDB, PHP-FPM, Nginx, and optionally Worker)
exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
