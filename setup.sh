#!/bin/bash

# E-Commerce Project Quick Setup Script
# This script automates the initial setup process

set -e

echo "🚀 E-Commerce Project Setup"
echo "============================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if PHP is installed
echo "📋 Checking requirements..."
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP is not installed${NC}"
    echo "Please install PHP 8.0+ and try again"
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo -e "${GREEN}✅ PHP $PHP_VERSION found${NC}"

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Composer is not installed${NC}"
    echo "Please install Composer and try again"
    exit 1
fi
echo -e "${GREEN}✅ Composer found${NC}"

# Check if MySQL is available
if ! command -v mysql &> /dev/null; then
    echo -e "${YELLOW}⚠️  MySQL client not found. You'll need to create database manually${NC}"
    MYSQL_AVAILABLE=false
else
    echo -e "${GREEN}✅ MySQL found${NC}"
    MYSQL_AVAILABLE=true
fi

echo ""

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
if [ ! -d "vendor" ]; then
    composer install --no-interaction
    echo -e "${GREEN}✅ Dependencies installed${NC}"
else
    echo -e "${YELLOW}⚠️  Dependencies already installed (skipping)${NC}"
fi

echo ""

# Create .env file
echo "⚙️  Setting up environment..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo -e "${GREEN}✅ .env file created${NC}"
    echo -e "${YELLOW}⚠️  Please edit .env with your database credentials${NC}"
else
    echo -e "${YELLOW}⚠️  .env already exists (skipping)${NC}"
fi

echo ""

# Create storage directories
echo "📁 Creating storage directories..."
mkdir -p storage/cache/i18n
mkdir -p storage/cache/rate_limits
mkdir -p storage/logs
mkdir -p storage/uploads/media
mkdir -p public/uploads/media

chmod -R 755 storage
chmod -R 755 public/uploads

echo -e "${GREEN}✅ Storage directories created${NC}"

echo ""

# Ask if user wants to create database
if [ "$MYSQL_AVAILABLE" = true ]; then
    echo "🗄️  Database Setup"
    read -p "Do you want to create the database now? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        read -p "MySQL root password: " -s MYSQL_ROOT_PASSWORD
        echo
        read -p "Database name (default: polyurethane_ecommerce): " DB_NAME
        DB_NAME=${DB_NAME:-polyurethane_ecommerce}

        read -p "Database user (default: ecommerce_user): " DB_USER
        DB_USER=${DB_USER:-ecommerce_user}

        read -p "Database password: " -s DB_PASSWORD
        echo

        # Create database
        mysql -u root -p"$MYSQL_ROOT_PASSWORD" <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✅ Database created successfully${NC}"

            # Update .env file
            sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
            sed -i.bak "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
            sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
            rm .env.bak

            echo -e "${GREEN}✅ .env updated with database credentials${NC}"
        else
            echo -e "${RED}❌ Failed to create database${NC}"
        fi
    fi
fi

echo ""

# Ask if user wants to run migrations
echo "🔧 Database Migrations"
read -p "Do you want to run migrations now? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php app/Migrations/apply.php
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Migrations completed successfully${NC}"
    else
        echo -e "${RED}❌ Migration failed${NC}"
    fi
fi

echo ""

# Compile SCSS (optional)
echo "🎨 Frontend Assets"
if command -v sass &> /dev/null; then
    read -p "Do you want to compile SCSS to CSS? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        mkdir -p public/assets/css
        sass public/assets/scss/main.scss public/assets/css/main.css
        echo -e "${GREEN}✅ SCSS compiled${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  Sass not found. Install with: npm install -g sass${NC}"
fi

echo ""
echo "✅ Setup Complete!"
echo ""
echo "📝 Next steps:"
echo "1. Edit .env file with your settings"
echo "2. Start the development server:"
echo "   ${GREEN}php -S localhost:8000 -t public/${NC}"
echo ""
echo "3. (Optional) Start the queue worker:"
echo "   ${GREEN}php worker.php --daemon${NC}"
echo ""
echo "4. Open in browser:"
echo "   ${GREEN}http://localhost:8000${NC}"
echo ""
echo "📖 For detailed instructions, see SETUP.md"
echo ""
