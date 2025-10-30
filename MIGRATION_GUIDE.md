# 📊 Database Migration Guide

## Quick Start

### Via SSH (Recommended)

1. **Connect to your production server:**
   ```bash
   ssh user@site.polyes.tr
   ```

2. **Navigate to project directory:**
   ```bash
   cd /var/www/html
   ```

3. **Run migrations:**
   ```bash
   php app/Migrations/apply.php
   ```

4. **Verify tables created:**
   ```bash
   mysql -u USERNAME -p -e "USE DATABASE_NAME; SHOW TABLES;"
   ```

### Expected Output

```
✓ Database connection successful

Found 12 migration files
============================================================

▶ Executing 000_users_table.sql...
  ✓ Success

▶ Executing 001_core_i18n_system.sql...
  ✓ Success

▶ Executing 002_multi_currency_pricing.sql...
  ✓ Success

▶ Executing 003_media_library_system.sql...
  ✓ Success

▶ Executing 004_b2b_features.sql...
  ✓ Success

▶ Executing 005_automation_webhooks.sql...
  ✓ Success

▶ Executing 006_seo_advanced_features.sql...
  ✓ Success

▶ Executing 007_security_audit_analytics.sql...
  ✓ Success

▶ Executing 008_categories_products.sql...
  ✓ Success

▶ Executing 009_b2b_pricing.sql...
  ✓ Success

▶ Executing 010_orders_cart.sql...
  ✓ Success

▶ Executing 011_webhooks_api.sql...
  ✓ Success

============================================================
✓ All migrations completed successfully!
```

### Tables That Will Be Created

After running migrations, you will have **50+ tables** including:

**Core Tables:**
- `users` - User accounts
- `settings` - Application settings
- `migrations` - Migration tracking

**Products & Catalog:**
- `products` - Product information
- `categories` - Product categories
- `product_images` - Product images
- `product_variants` - Product variants (size, color, etc.)
- `product_attributes` - Custom attributes
- `product_specifications` - Technical specifications
- `product_stock` - Inventory tracking
- `product_colors` - RAL color system
- `product_calculators` - Calculator configurations
- `technical_documents` - CAD, MSDS, TDS files
- `qr_codes` - QR code data

**Pricing:**
- `prices` - Multi-currency pricing
- `price_history` - Price change tracking
- `tier_pricing` - Volume-based pricing
- `customer_group_pricing` - Group-specific pricing

**Orders & Cart:**
- `orders` - Order information
- `order_items` - Order line items
- `carts` - Shopping carts
- `cart_items` - Cart contents

**B2B Features:**
- `customer_groups` - B2B customer groups
- `credit_limits` - Credit limit management
- `sample_orders` - Sample request system
- `dealer_applications` - Dealer application workflow
- `rfq` - Request for Quote system

**Media:**
- `media` - Media library
- `media_folders` - Folder organization

**SEO & Content:**
- `seo_meta` - SEO metadata
- `redirects` - URL redirects
- `blog_posts` - Blog system
- `blog_categories` - Blog categories

**Integrations:**
- `webhooks` - Webhook configurations
- `webhook_logs` - Webhook delivery logs
- `api_tokens` - API authentication
- `api_logs` - API request logs

**Analytics & Audit:**
- `audit_logs` - User activity tracking
- `page_views` - Page view analytics
- `search_logs` - Search query tracking

**Automation:**
- `queue_jobs` - Background job queue
- `email_templates` - Email templates
- `notifications` - User notifications

**Localization:**
- `translations` - Multi-language content
- `currencies` - Currency definitions
- `exchange_rates` - Exchange rate tracking

## Troubleshooting

### Error: "vendor/autoload.php not found"

**Solution:** Install composer dependencies first:
```bash
cd /var/www/html
composer install --no-dev --optimize-autoloader
```

### Error: "Database connection failed"

**Solution:** Check `.env` file credentials:
```bash
nano .env

# Verify these settings:
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Test connection manually:
```bash
mysql -h 127.0.0.1 -u USERNAME -p DATABASE_NAME
```

### Error: "Access denied for user"

**Solution:** Grant proper MySQL permissions:
```sql
GRANT ALL PRIVILEGES ON database_name.* TO 'username'@'localhost';
FLUSH PRIVILEGES;
```

### Error: "Migration already executed"

This is normal! The script automatically skips migrations that were already run. You'll see:
```
⊘ Skipping 000_users_table.sql (already executed)
```

### Re-running Migrations

If you need to re-run all migrations (⚠️ **WARNING: This will DROP all tables and data**):

```bash
# 1. Drop all tables
mysql -u USERNAME -p DATABASE_NAME -e "DROP DATABASE DATABASE_NAME; CREATE DATABASE DATABASE_NAME;"

# 2. Re-run migrations
php app/Migrations/apply.php
```

## Alternative: Manual Import via phpMyAdmin

If you don't have SSH access:

1. **Download migration SQL files** from:
   - `app/Migrations/sql/000_users_table.sql`
   - `app/Migrations/sql/001_core_i18n_system.sql`
   - ... (all 12 files)

2. **Login to phpMyAdmin**

3. **Select your database**

4. **Go to Import tab**

5. **Import files one by one in order** (000, 001, 002, ...)

## Alternative: Coolify Console

If deployed on Coolify:

1. **Open Coolify Dashboard**
2. **Go to your application**
3. **Click "Console" or "Terminal"**
4. **Run:**
   ```bash
   php app/Migrations/apply.php
   ```

## After Migration

1. **Verify tables created:**
   ```bash
   mysql -u USERNAME -p DATABASE_NAME -e "SHOW TABLES;"
   ```

2. **Check diagnostic again:**
   ```
   https://site.polyes.tr/diagnostic.php
   ```
   Should now show: **12 Passed, 0 Warnings**

3. **Test homepage:**
   ```
   https://site.polyes.tr/
   ```
   Should load without errors!

4. **Create admin user:**
   ```bash
   php app/Migrations/seed-admin.php
   ```
   Or use credentials from `.env`:
   - Email: `ADMIN_EMAIL` from .env
   - Password: `ADMIN_PASSWORD` from .env

5. **Delete diagnostic.php (security):**
   ```bash
   rm /var/www/html/public/diagnostic.php
   ```

## Migration File Details

Each migration file handles specific functionality:

- **000_users_table.sql** - User authentication system
- **001_core_i18n_system.sql** - Multi-language support
- **002_multi_currency_pricing.sql** - Currency system
- **003_media_library_system.sql** - Media management
- **004_b2b_features.sql** - B2B customer features
- **005_automation_webhooks.sql** - Automation & webhooks
- **006_seo_advanced_features.sql** - SEO tools
- **007_security_audit_analytics.sql** - Security & analytics
- **008_categories_products.sql** - Product catalog
- **009_b2b_pricing.sql** - B2B pricing tiers
- **010_orders_cart.sql** - Shopping cart & orders
- **011_webhooks_api.sql** - API & webhook system

## Support

If migrations fail:

1. Check error message carefully
2. Verify database credentials in `.env`
3. Ensure MySQL user has CREATE TABLE permissions
4. Check MySQL error log: `/var/log/mysql/error.log`
5. Try importing one migration file manually to see detailed error

---

**Last Updated:** October 30, 2024
