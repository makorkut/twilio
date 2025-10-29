#!/bin/bash

set -e

echo "🚀 Starting E-Commerce Application..."

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
while ! nc -z mysql 3306; do
  sleep 1
done
echo "✅ MySQL is ready!"

# Create .env if it doesn't exist
if [ ! -f .env ]; then
    echo "⚙️  Creating .env file..."
    cp .env.example .env

    # Update database credentials from environment variables
    sed -i "s/DB_HOST=.*/DB_HOST=${DB_HOST}/" .env
    sed -i "s/DB_PORT=.*/DB_PORT=${DB_PORT}/" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE}/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME}/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env

    echo "✅ .env file created"
fi

# Run migrations if not already done
MIGRATION_FLAG="/var/www/html/storage/.migrations_done"
if [ ! -f "$MIGRATION_FLAG" ]; then
    echo "🔧 Running database migrations..."

    # Wait a bit more to ensure MySQL is fully initialized
    sleep 5

    if php app/Migrations/apply.php; then
        echo "✅ Migrations completed successfully"
        touch "$MIGRATION_FLAG"
    else
        echo "⚠️  Migrations failed or already applied"
    fi
else
    echo "ℹ️  Migrations already applied (skipping)"
fi

# Set proper permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads
chmod -R 755 /var/www/html/storage /var/www/html/public/uploads

echo "✅ Application is ready!"
echo "🌐 Access the application at: http://localhost:8000"
echo "🗄️  PHPMyAdmin available at: http://localhost:8080"

# Execute the CMD from Dockerfile
exec "$@"
