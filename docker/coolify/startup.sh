#!/bin/bash
set -e

echo "================================================"
echo "🚀 Starting E-Commerce Application (Coolify)"
echo "================================================"
echo ""

# Function to initialize MariaDB
initialize_mariadb() {
    echo "🔧 Initializing MariaDB..."

    # Check if MySQL data directory is already initialized
    if [ ! -d "/var/lib/mysql/mysql" ]; then
        echo "📦 First run - initializing MySQL data directory..."
        mysql_install_db --user=mysql --datadir=/var/lib/mysql --skip-test-db
        echo "✅ MySQL data directory initialized"
    else
        echo "✅ MySQL data directory already exists"
    fi
}

# Function to setup database and user
setup_database() {
    echo "🗄️  Setting up database and user..."

    local db_name="${DB_DATABASE:-ecommerce_db}"
    local db_user="${DB_USERNAME:-ecommerce_user}"
    local db_pass="${DB_PASSWORD:-Ec0mm3rc3!S3cur3P@ss}"
    local db_root_pass="${DB_ROOT_PASSWORD:-R00t!S3cur3P@ss2024}"

    # Wait a moment for MariaDB to fully start
    sleep 5

    # Create database and user (MariaDB 10.x compatible)
    mysql -u root <<-EOSQL 2>/dev/null || true
        CREATE DATABASE IF NOT EXISTS \`${db_name}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

        -- Create user for localhost (drop first if exists)
        DROP USER IF EXISTS '${db_user}'@'localhost';
        CREATE USER '${db_user}'@'localhost' IDENTIFIED BY '${db_pass}';
        GRANT ALL PRIVILEGES ON \`${db_name}\`.* TO '${db_user}'@'localhost';

        -- Set root password
        ALTER USER 'root'@'localhost' IDENTIFIED BY '${db_root_pass}';

        FLUSH PRIVILEGES;
EOSQL

    echo "✅ Database '${db_name}' and user '${db_user}' created successfully"
}

# Function to wait for database
wait_for_database() {
    echo "⏳ Waiting for database to be ready..."

    local max_attempts=30
    local attempt=1

    while [ $attempt -le $max_attempts ]; do
        # Use PHP to test MySQL connection (more reliable than nc)
        if php -r "
            \$host = '${DB_HOST:-localhost}';
            \$port = ${DB_PORT:-3306};
            \$timeout = 1;
            \$socket = @fsockopen(\$host, \$port, \$errno, \$errstr, \$timeout);
            if (\$socket) {
                fclose(\$socket);
                exit(0);
            }
            exit(1);
        " 2>/dev/null; then
            echo "✅ Database is ready!"
            sleep 2  # Extra wait for MySQL to be fully initialized
            return 0
        fi

        echo "   Attempt $attempt/$max_attempts: Database not ready yet..."
        sleep 2
        attempt=$((attempt + 1))
    done

    echo "❌ Database connection timeout!"
    return 1
}

# Create .env file from environment variables
create_env_file() {
    echo "⚙️  Creating .env file from environment variables..."

    cat > /var/www/html/.env <<EOF
# Application
APP_NAME=${APP_NAME:-E-Commerce}
APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}

# Database
DB_CONNECTION=mysql
DB_HOST=${DB_HOST:-localhost}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-ecommerce_db}
DB_USERNAME=${DB_USERNAME:-ecommerce_user}
DB_PASSWORD=${DB_PASSWORD:-Ec0mm3rc3!S3cur3P@ss}

# Default Settings
DEFAULT_LANG=${DEFAULT_LANG:-tr}
DEFAULT_CURRENCY=${DEFAULT_CURRENCY:-TRY}

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=database

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=${LOG_LEVEL:-error}

# Mail (Configure for production)
MAIL_MAILER=${MAIL_MAILER:-smtp}
MAIL_HOST=${MAIL_HOST:-localhost}
MAIL_PORT=${MAIL_PORT:-587}
MAIL_USERNAME=${MAIL_USERNAME:-}
MAIL_PASSWORD=${MAIL_PASSWORD:-}
MAIL_ENCRYPTION=${MAIL_ENCRYPTION:-tls}
MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS:-noreply@example.com}
MAIL_FROM_NAME=\${APP_NAME}

# Webhooks
LOCAL_CRM_WEBHOOK_SECRET=${LOCAL_CRM_WEBHOOK_SECRET:-}
LOCAL_CRM_ENABLED=${LOCAL_CRM_ENABLED:-false}
LOCAL_CRM_BASE_URL=${LOCAL_CRM_BASE_URL:-}

# Rate Limiting
RATE_LIMIT_MAX_ATTEMPTS=${RATE_LIMIT_MAX_ATTEMPTS:-60}
RATE_LIMIT_DECAY_MINUTES=${RATE_LIMIT_DECAY_MINUTES:-1}

# Features
FEATURE_B2B=${FEATURE_B2B:-true}
FEATURE_WAREHOUSE=${FEATURE_WAREHOUSE:-true}
FEATURE_PROJECTS=${FEATURE_PROJECTS:-true}

# Pricing
PRICE_VISIBILITY=${PRICE_VISIBILITY:-visible}
PRICE_INCLUDES_VAT=${PRICE_INCLUDES_VAT:-true}
DEFAULT_VAT_RATE=${DEFAULT_VAT_RATE:-20}

# CORS
CORS_ALLOWED_ORIGINS=${CORS_ALLOWED_ORIGINS:-*}
EOF

    echo "✅ .env file created"
}

# Run database migrations
run_migrations() {
    local migration_flag="/var/www/html/storage/.migrations_done"

    if [ -f "$migration_flag" ]; then
        echo "ℹ️  Migrations already applied (skipping)"
        return 0
    fi

    echo "🔧 Running database migrations..."

    if php /var/www/html/app/Migrations/apply.php; then
        echo "✅ Migrations completed successfully"
        touch "$migration_flag"
        return 0
    else
        echo "⚠️  Migration failed or already applied"
        # Don't fail the container, continue anyway
        return 0
    fi
}

# Set proper permissions
set_permissions() {
    echo "🔐 Setting proper permissions..."
    chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads
    chmod -R 755 /var/www/html/storage /var/www/html/public/uploads
    echo "✅ Permissions set"
}

# Clear caches
clear_caches() {
    echo "🧹 Clearing application caches..."

    # Clear OPcache (will be regenerated)
    if [ -f /var/run/php-fpm.pid ]; then
        kill -USR2 $(cat /var/run/php-fpm.pid) 2>/dev/null || true
    fi

    # Clear file cache
    find /var/www/html/storage/cache -type f -name "*.php" -delete 2>/dev/null || true

    echo "✅ Caches cleared"
}

# Display startup info
display_info() {
    echo ""
    echo "================================================"
    echo "✅ Application Started Successfully!"
    echo "================================================"
    echo ""
    echo "📊 Configuration:"
    echo "   Environment: ${APP_ENV:-production}"
    echo "   Debug Mode: ${APP_DEBUG:-false}"
    echo "   Database: ${DB_HOST:-localhost}:${DB_PORT:-3306} (MariaDB)"
    echo "   URL: ${APP_URL:-http://localhost}"
    echo ""
    echo "🌐 Services:"
    echo "   ✅ MariaDB (Port 3306)"
    echo "   ✅ Nginx (Port 3000)"
    echo "   ✅ PHP-FPM (Port 9000)"
    echo "   ✅ Queue Worker (starts after DB ready)"
    echo ""
    echo "📝 Logs:"
    echo "   Application: storage/logs/"
    echo "   MariaDB: storage/logs/mariadb-*.log"
    echo "   Nginx: storage/logs/nginx-*.log"
    echo "   PHP-FPM: storage/logs/php-fpm-*.log"
    echo "   Worker: storage/logs/worker-*.log"
    echo ""
    echo "🚀 Ready to serve requests!"
    echo "================================================"
    echo ""
}

# Main execution
main() {
    # Initialize MariaDB data directory (first time only)
    initialize_mariadb

    # Create .env file immediately (don't wait for database)
    create_env_file

    # Set permissions
    set_permissions

    # Display info
    display_info

    # Start supervisor (MariaDB + Nginx + PHP-FPM + Worker)
    echo "🎬 Starting services with Supervisor..."
    echo "   (MariaDB will start first, then migrations will run)"

    # Start supervisor in background to let MariaDB start
    /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf &
    SUPERVISOR_PID=$!

    # Wait for MariaDB to actually be ready (not just started)
    echo "⏳ Waiting for MariaDB to be ready..."
    local max_wait=30
    local count=0
    while [ $count -lt $max_wait ]; do
        if mysqladmin ping -h localhost --silent 2>/dev/null; then
            echo "✅ MariaDB is ready!"
            break
        fi
        echo "   Attempt $((count+1))/$max_wait: MariaDB not ready yet..."
        sleep 2
        count=$((count+1))
    done

    if [ $count -ge $max_wait ]; then
        echo "❌ MariaDB failed to start in time!"
        exit 1
    fi

    # Extra wait for MariaDB to be fully initialized
    sleep 3

    # Setup database and user (first time or if not exists)
    setup_database

    # Run database setup
    (
        # Wait for database
        if wait_for_database; then
            echo "✅ Database connected!"

            # Run migrations if AUTO_MIGRATE is enabled
            if [ "${AUTO_MIGRATE:-false}" = "true" ]; then
                echo "🔧 Running database migrations..."
                run_migrations
            fi

            # Seed admin user if AUTO_SEED is enabled
            if [ "${AUTO_SEED:-false}" = "true" ]; then
                echo "👤 Creating admin user..."
                php /var/www/html/app/Migrations/seed-admin.php
            fi

            # Clear caches
            clear_caches

            # Start worker if AUTO_START_WORKER is enabled
            if [ "${AUTO_START_WORKER:-false}" = "true" ]; then
                echo "🚀 Starting worker..."
                sleep 2  # Wait a bit for supervisor to be ready
                /usr/bin/supervisorctl start worker
                echo "✅ Worker started!"
            fi

            echo "✅ Database setup completed!"
        else
            echo "⚠️  Could not connect to database"
            echo "   Configure DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD in environment"
        fi
    ) &

    # Start worker if AUTO_START_WORKER is enabled
    if [ "${AUTO_START_WORKER:-false}" = "true" ]; then
        echo "🔄 AUTO_START_WORKER enabled, worker will start after database is ready"
    fi

    # Wait for supervisor process
    wait $SUPERVISOR_PID
}

# Run main
main
