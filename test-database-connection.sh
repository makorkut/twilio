#!/bin/bash

# ============================================================================
# Database Connection Test Script
# ============================================================================
# Tests connection to MySQL database from Coolify server
# Usage: ./test-database-connection.sh
# ============================================================================

echo "================================================"
echo "🔍 Testing MySQL Connection"
echo "================================================"
echo ""

# Configuration (same as .env)
DB_HOST="78.189.76.176"
DB_PORT="3306"
DB_DATABASE="polyurethane_ecommerce"
DB_USERNAME="ecommerce_user"
DB_PASSWORD="P0lyur3th@n3!2024\$ecur3"

echo "📊 Configuration:"
echo "   Host: $DB_HOST"
echo "   Port: $DB_PORT"
echo "   Database: $DB_DATABASE"
echo "   Username: $DB_USERNAME"
echo ""

# Test 1: Port reachability
echo "🔌 Test 1: Checking if port $DB_PORT is reachable..."
if timeout 5 bash -c "cat < /dev/null > /dev/tcp/$DB_HOST/$DB_PORT" 2>/dev/null; then
    echo "✅ Port $DB_PORT is reachable"
else
    echo "❌ Cannot reach $DB_HOST:$DB_PORT"
    echo ""
    echo "Possible issues:"
    echo "  1. Database server is not running"
    echo "  2. Firewall is blocking port $DB_PORT"
    echo "  3. Incorrect IP address"
    echo ""
    exit 1
fi
echo ""

# Test 2: MySQL connection
echo "🔐 Test 2: Testing MySQL connection..."
if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1" "$DB_DATABASE" 2>/dev/null; then
    echo "✅ Successfully connected to MySQL database"
else
    echo "❌ MySQL connection failed"
    echo ""
    echo "Possible issues:"
    echo "  1. Wrong username or password"
    echo "  2. Database '$DB_DATABASE' does not exist"
    echo "  3. User does not have permission to access database"
    echo "  4. MySQL bind-address is set to 127.0.0.1"
    echo ""
    echo "To debug, run this manually:"
    echo "  mysql -h $DB_HOST -P $DB_PORT -u $DB_USERNAME -p $DB_DATABASE"
    echo ""
    exit 1
fi
echo ""

# Test 3: Check tables
echo "📋 Test 3: Checking database tables..."
TABLES=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -Nse "SHOW TABLES" "$DB_DATABASE" 2>/dev/null)
TABLE_COUNT=$(echo "$TABLES" | wc -l)

if [ -n "$TABLES" ]; then
    echo "✅ Found $TABLE_COUNT tables in database:"
    echo "$TABLES" | sed 's/^/     - /'
else
    echo "⚠️  Database is empty (no tables found)"
    echo "   This is normal if migrations haven't run yet"
fi
echo ""

# Test 4: Check permissions
echo "🔑 Test 4: Checking user permissions..."
GRANTS=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -Nse "SHOW GRANTS FOR CURRENT_USER" 2>/dev/null)
if [ -n "$GRANTS" ]; then
    echo "✅ User has the following grants:"
    echo "$GRANTS" | sed 's/^/     /'
else
    echo "❌ Could not retrieve user permissions"
fi
echo ""

# Summary
echo "================================================"
echo "✅ Connection Test Complete!"
echo "================================================"
echo ""
echo "Your database is ready to use. Update your Coolify"
echo "environment variables with these settings and redeploy."
echo ""
