#!/bin/bash
set -e

echo "================================================"
echo "🚀 Starting E-Commerce Application (Coolify)"
echo "================================================"
echo ""

# Function to wait for database
wait_for_database() {
    echo "⏳ Waiting for database to be ready..."

    local max_attempts=30
    local attempt=1

    while [ $attempt -le $max_attempts ]; do
        # Use PHP to test MySQL connection (more reliable than nc)
        if php -r "
            \$host = '${DB_HOST:-mysql}';
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
DB_HOST=${DB_HOST:-mysql}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-polyurethane_ecommerce}
DB_USERNAME=${DB_USERNAME:-ecommerce_user}
DB_PASSWORD=${DB_PASSWORD:-secret}

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
    echo "   Database: ${DB_HOST:-mysql}:${DB_PORT:-3306}"
    echo "   URL: ${APP_URL:-http://localhost}"
    echo ""
    echo "🌐 Services:"
    echo "   ✅ Nginx (Port 3000)"
    echo "   ✅ PHP-FPM (Port 9000)"
    echo "   ✅ Queue Worker"
    echo ""
    echo "📝 Logs:"
    echo "   Application: storage/logs/"
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
    # Wait for database
    if ! wait_for_database; then
        echo "⚠️  Warning: Could not connect to database, but continuing anyway..."
        echo "   Make sure database service is running and environment variables are correct"
    fi

    # Create .env file
    create_env_file

    # Run migrations (only once)
    run_migrations

    # Set permissions
    set_permissions

    # Clear caches
    clear_caches

    # Display info
    display_info

    # Start supervisor (Nginx + PHP-FPM + Worker)
    echo "🎬 Starting services with Supervisor..."
    exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
}

# Run main
main
