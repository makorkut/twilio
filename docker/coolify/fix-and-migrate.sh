#!/bin/bash
# =============================================================================
# Migration Fix & Re-run Script
# Çalıştırma: docker exec -it <container> /var/www/html/docker/coolify/fix-and-migrate.sh
# =============================================================================

set -e
cd /var/www/html

echo "================================================"
echo "🔧 Migration Fix & Re-run"
echo "================================================"
echo ""

# Database credentials from .env
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_NAME="${DB_DATABASE:-polyurethane_ecommerce}"
DB_USER="${DB_USERNAME:-ecommerce_user}"
DB_PASS="${DB_PASSWORD:-Ec0mm3rc3!S3cur3P@ss}"

echo "📋 Step 1: Dropping migrations table..."
mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" <<'SQL'
DROP TABLE IF EXISTS migrations;
SQL
echo "   ✅ Migrations table dropped"
echo ""

echo "📋 Step 2: Dropping problematic tables..."
mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" <<'SQL'
DROP TABLE IF EXISTS product_prices_currency;
DROP TABLE IF EXISTS exchange_rates;
DROP TABLE IF EXISTS tax_classes;
SQL
echo "   ✅ Problematic tables dropped"
echo ""

echo "📋 Step 3: Re-running migrations..."
php app/Migrations/apply.php

if [ $? -eq 0 ]; then
    echo ""
    echo "================================================"
    echo "✅ SUCCESS! All migrations completed!"
    echo "================================================"
    echo ""
    
    # Count tables
    TABLE_COUNT=$(mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '${DB_NAME}';" -sN)
    echo "📊 Total tables created: ${TABLE_COUNT}"
    echo ""
    
    # Show all tables
    echo "📋 Tables in database:"
    mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "SHOW TABLES;" | tail -n +2 | nl
    
    exit 0
else
    echo ""
    echo "================================================"
    echo "❌ MIGRATION FAILED!"
    echo "================================================"
    exit 1
fi
