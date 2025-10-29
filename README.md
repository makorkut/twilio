# 🏗️ Poliüretan E-Commerce + CMS

> **Advanced Multi-language E-commerce Platform with Automation, B2B Features, and Media Library**

Modern, scalable e-commerce and CMS system built with **PHP 8.x**, **MariaDB**, and **MCP Architecture** (Model-Controller-Presenter).

---

## ✨ Features

### 🌍 **Core Features**
- ✅ Multi-language (DB-based i18n system)
- ✅ Multi-currency pricing (EUR/USD/TRY with different prices per currency)
- ✅ Responsive design (Orac Decor inspired, minimal & modern)
- ✅ SEO optimized (Schema.org, OpenGraph, Sitemaps, 301 redirects)
- ✅ PWA ready
- ✅ Shared hosting compatible

### 🛒 **E-Commerce**
- ✅ Products (variants, custom fields, tier pricing, reviews)
- ✅ Categories (unlimited nesting)
- ✅ Shopping cart (guest + member checkout)
- ✅ Payment gateways (Stripe + İyzico with 3D Secure)
- ✅ Coupons & Promotions
- ✅ Tax management (multiple VAT classes)
- ✅ Order management (status workflow, tracking, invoices)

### 💼 **B2B Features**
- ✅ Customer groups (Individual, Corporate, Dealer, VIP)
- ✅ Tier pricing (wholesale pricing by quantity)
- ✅ Credit limits & payment terms (Net 30/60/90)
- ✅ Sample orders
- ✅ Dealer application system
- ✅ Technical documents (CAD, MSDS, TDS, certificates)
- ✅ Project references

### 🖼️ **Advanced Media Library**
- ✅ Central media pool (independent entity like pages)
- ✅ 5 image sizes (default, big, small, tiny, thumb) + WebP + AVIF
- ✅ **SKU-based auto-attachment** (tag image with SKU → auto-attach to product!)
- ✅ Hierarchical categories
- ✅ Tag system (general, SKU, color, material)
- ✅ Multi-entity usage (1 image → multiple products/posts/categories)
- ✅ Standalone galleries

### 🤖 **Automation & Integrations**
- ✅ REST API v1 (80+ endpoints, OpenAPI 3.1)
- ✅ Webhook receivers (local CRM → site)
- ✅ Webhook senders (site → external systems)
- ✅ WebSocket server (real-time sync status)
- ✅ Queue system (background jobs: bulk import, image processing)
- ✅ External ID mapping (sync with local CRM/ERP)
- ✅ Idempotent webhook processing

### 🎨 **CMS**
- ✅ Blog system (posts, categories, tags, comments)
- ✅ Pages (custom pages, templates)
- ✅ Custom fields (flexible metadata)
- ✅ Landing page builder
- ✅ Slider management

### 🔒 **Security**
- ✅ CSRF protection
- ✅ XSS prevention
- ✅ RBAC (Role-Based Access Control)
- ✅ 2FA (TOTP)
- ✅ Rate limiting (API + login)
- ✅ Fraud detection
- ✅ IP blacklist
- ✅ Audit logs
- ✅ GDPR compliance (data export, deletion requests)

### 📊 **Analytics & Reporting**
- ✅ RFM analysis (Recency, Frequency, Monetary)
- ✅ Customer Lifetime Value (CLV)
- ✅ Search analytics
- ✅ Product views tracking
- ✅ Inventory forecasting
- ✅ Sales reports (PDF, XLSX, CSV export)

### 🌟 **Extra Features**
- ✅ Subscription billing (monthly/yearly)
- ✅ Multi-warehouse inventory
- ✅ Lot/batch tracking
- ✅ Product bundles & related products
- ✅ Color catalog (RAL codes, hex)
- ✅ Wishlist, comparison, reviews
- ✅ Affiliate program
- ✅ Flash sales
- ✅ Newsletter
- ✅ Abandoned cart recovery

---

## 📁 Project Structure

```
/
├── app/
│   ├── Core/                   # Framework core (Application, Router, Database, Container)
│   ├── Http/                   # HTTP layer (Request, Response, Middleware)
│   ├── Models/                 # Entities & Repositories
│   ├── Services/               # Business logic
│   ├── Controllers/            # Admin, Frontend, API controllers
│   ├── Presenters/             # ViewModel generators
│   ├── Security/               # CSRF, RBAC, RateLimit
│   ├── Utils/                  # Helpers
│   └── Migrations/             # Database migrations
│
├── config/                     # Configuration files
├── public/                     # Web root (index.php, assets, uploads)
├── storage/                    # Cache, logs, sessions, queue
├── themes/                     # Light & Dark themes
├── routes/                     # Route definitions
├── tests/                      # PHPUnit tests
├── .env.example                # Environment template
├── composer.json               # Dependencies
└── README.md                   # This file
```

---

## 🚀 Installation

### 1. **Requirements**
- PHP >= 8.1
- MariaDB >= 10.6 (or MySQL >= 8.0)
- Composer
- Extensions: `pdo`, `mbstring`, `intl`, `openssl`, `zip`, `fileinfo`, `exif`, `gd` (or `imagick`), `bcmath`

### 2. **Clone Repository**
```bash
git clone <repository-url>
cd twilio
```

### 3. **Install Dependencies**
```bash
composer install
```

### 4. **Configure Environment**
```bash
cp .env.example .env
nano .env  # Edit database credentials
```

### 5. **Run Migrations**
```bash
php app/Migrations/apply.php
```

### 6. **Set Permissions**
```bash
chmod -R 755 storage/
chmod -R 755 public/uploads/
```

### 7. **Configure Web Server**
Point your web server document root to `/public`

**Apache .htaccess** is already included.

For **Nginx**:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

---

## 📚 Database

**50+ Tables** created via migrations:

### Core
- `languages`, `i18n_keys`, `i18n_values`
- `currencies`, `exchange_rates`, `tax_classes`
- `themes`

### Products
- `products`, `product_details`, `product_prices_currency`
- `categories`, `categories_lang`
- `product_documents`, `product_colors`, `product_relations`

### Media Library
- `media`, `media_lang`
- `media_categories`, `media_categories_lang`
- `media_tags`, `media_tag_relations`
- `media_usage`, `media_galleries`

### Orders
- `orders`, `order_items`
- `coupons`, `coupons_used`

### B2B
- `customer_groups`, `customer_group_prices`
- `sample_orders`, `dealer_applications`

### Automation
- `external_entity_mapping`, `sync_logs`
- `webhook_events`, `queue_jobs`
- `email_queue_new`

### Security
- `audit_logs`, `login_attempts`, `ip_blacklist`, `fraud_logs`
- `gdpr_requests`, `gdpr_consent_logs`

### Analytics
- `search_logs`, `product_views`, `api_requests`

### Inventory
- `warehouses`, `product_stock_locations`, `product_lots`, `stock_movements`
- `inventory_forecasts`

### Subscriptions
- `subscriptions`, `subscription_invoices`

### Projects
- `projects`, `project_products`, `project_media`

---

## 🔧 Configuration

### Environment Variables

Key settings in `.env`:

```bash
# Application
APP_NAME="Poliüretan E-Commerce"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_HOST=localhost
DB_DATABASE=poliuretan_db
DB_USERNAME=db_user
DB_PASSWORD=your_password

# Localization
DEFAULT_LANG=tr
AVAILABLE_LANGS=tr,en,de,fr
DEFAULT_CURRENCY=TRY

# Pricing
PRICE_VISIBILITY=visible        # visible|hidden|login_required
PRICE_INCLUDES_VAT=true

# Payment Gateways
STRIPE_ENABLED=true
STRIPE_PUBLIC_KEY=pk_...
STRIPE_SECRET_KEY=sk_...

IYZICO_ENABLED=true
IYZICO_API_KEY=...
IYZICO_SECRET_KEY=...

# Storage
STORAGE_DRIVER=local            # local|s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_BUCKET=...
CDN_URL=https://cdn.example.com

# Features
FEATURE_B2B=true
FEATURE_SUBSCRIPTION=true
FEATURE_WAREHOUSE=true
FEATURE_PROJECTS=true
FEATURE_TECHNICAL_DOCS=true
```

---

## 🌐 API Documentation

### Base URL
```
https://yourdomain.com/api/v1
```

### Authentication
```bash
# JWT Token
Authorization: Bearer <token>

# Or API Key
X-API-Key: <your-api-key>
```

### Example Endpoints

#### Products
```bash
GET    /api/v1/products              # List products
GET    /api/v1/products/{id}         # Get product
POST   /api/v1/products              # Create product
PUT    /api/v1/products/{id}         # Update product
DELETE /api/v1/products/{id}         # Delete product
```

#### Bulk Operations
```bash
POST   /api/v1/products/bulk         # Bulk create/update
POST   /api/v1/products/bulk/price-update
```

#### Media
```bash
GET    /api/v1/media                 # List media
POST   /api/v1/media/bulk-download   # Bulk image download
POST   /api/v1/media/{id}/attach     # Attach to entity
```

#### Webhooks
```bash
POST   /webhooks/local-crm/products  # CRM product sync
POST   /webhooks/stripe              # Stripe payment events
POST   /webhooks/iyzico              # İyzico payment events
```

**Full OpenAPI specification**: `/docs/api/openapi.yaml`

---

## 🤖 Automation

### SKU-Based Auto-Attachment

When you tag a media with a SKU (e.g., `POL-100`), it **automatically attaches** to the product with that SKU!

```php
// Via webhook from Local CRM:
POST /webhooks/local-crm/media
{
  "media": {
    "local_id": 98765,
    "file_url": "http://local-server/uploads/image.jpg",
    "sku_tags": ["POL-100", "POL-200"]
  }
}

// System automatically:
// 1. Downloads image
// 2. Creates 5 sizes + WebP
// 3. Creates SKU tags
// 4. Trigger fires → attaches to products with SKU=POL-100 and POL-200
```

### Queue System

Background jobs for heavy operations:

```bash
# Start queue worker
php cli.php queue:work
```

Job types:
- `import_products` - Bulk product import
- `download_images` - Image download & processing
- `send_email` - Email sending
- `send_webhook` - Webhook dispatch
- `export_data` - Large exports

---

## 🎨 Themes

### Active Themes
- **Light** (default, Orac Decor inspired)
- **Dark**

### Customize
```bash
themes/
├── Light/
│   ├── layouts/
│   ├── partials/
│   ├── components/
│   └── assets/
│       ├── scss/
│       └── js/
```

---

## 🔒 Security Best Practices

1. ✅ **Never commit `.env`** to Git
2. ✅ Change `APP_KEY` in production
3. ✅ Enable `HTTPS` (uncomment force HTTPS in `.htaccess`)
4. ✅ Set `APP_DEBUG=false` in production
5. ✅ Use strong database passwords
6. ✅ Enable 2FA for admin accounts
7. ✅ Configure firewall (allow only necessary ports)
8. ✅ Regular backups (database + `/uploads`)

---

## 📈 Performance

### Caching
- File cache (default)
- Redis (optional, faster)

### Image Optimization
- Automatic WebP conversion
- AVIF support (optional)
- CDN integration (S3/DigitalOcean Spaces)

### Database
- Proper indexes on all foreign keys
- Query caching
- Connection pooling

---

## 🧪 Testing

```bash
# Run PHPUnit tests
composer test

# Or manually
./vendor/bin/phpunit
```

---

## 📦 Deployment

### Shared Hosting (cPanel)

1. Upload files to `/public_html`
2. Move `/public` contents to `/public_html`
3. Move other directories outside `/public_html`
4. Update paths in `public/index.php`
5. Run migrations via browser or SSH

### VPS (Ubuntu/Debian)

```bash
# Install dependencies
sudo apt install php8.2 php8.2-{cli,fpm,mysql,mbstring,xml,gd,zip,intl,bcmath}
sudo apt install mariadb-server nginx composer

# Configure Nginx
sudo nano /etc/nginx/sites-available/yourdomain.com

# Restart services
sudo systemctl restart nginx php8.2-fpm
```

---

## 🤝 Support

For issues, feature requests, or questions:

- 📧 Email: support@yourdomain.com
- 📝 Documentation: `/docs`
- 🐛 Bug reports: Create an issue in Git

---

## 📜 License

Proprietary - All rights reserved

---

## 🙏 Credits

Built with ❤️ using:
- PHP 8.x
- MariaDB
- Composer
- Stripe & İyzico SDKs
- Intervention Image
- DomPDF
- PHPSpreadsheet
- Ratchet (WebSocket)

---

**🚀 Ready to launch your polyurethane e-commerce empire!**
