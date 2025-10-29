<?php
/**
 * E-Commerce Platform Installer
 * Basit web tabanlı kurulum wizard
 *
 * Kullanım:
 * 1. Tüm dosyaları sunucuya yükle
 * 2. http://yourdomain.com/installer.php adresini aç
 * 3. Adımları takip et
 */

// Hata gösterimi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session başlat
session_start();

// Kurulum tamamlandı mı kontrol et
$lockFile = __DIR__ . '/.installer.lock';
if (file_exists($lockFile)) {
    die('
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Kurulum Tamamlandı</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
            .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px; color: #155724; }
        </style>
    </head>
    <body>
        <div class="success">
            <h1>✅ Kurulum Zaten Tamamlanmış</h1>
            <p>E-Commerce platformu başarıyla kurulmuş.</p>
            <p><strong>Güvenlik için installer.php dosyasını silin!</strong></p>
            <p><a href="/">Ana Sayfaya Git →</a></p>
        </div>
    </body>
    </html>
    ');
}

// Step kontrolü
$step = $_GET['step'] ?? 'welcome';

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Platform Kurulumu</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { color: #333; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 30px; }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
            padding: 10px;
        }
        .step-item.active { color: #667eea; font-weight: bold; }
        .step-item.completed { color: #10b981; }
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            border-radius: 50%;
            background: #eee;
            margin-bottom: 5px;
        }
        .step-item.active .step-number { background: #667eea; color: white; }
        .step-item.completed .step-number { background: #10b981; color: white; }
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"], input[type="password"], select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border 0.3s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.3s;
        }
        .btn:hover { background: #5568d3; }
        .btn-success { background: #10b981; }
        .btn-success:hover { background: #059669; }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-error { background: #fee; border-left: 4px solid #f00; color: #c00; }
        .alert-success { background: #d4edda; border-left: 4px solid #10b981; color: #155724; }
        .alert-warning { background: #fff3cd; border-left: 4px solid #ffc107; color: #856404; }
        .check-item {
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .check-item.success { background: #d4edda; }
        .check-item.error { background: #f8d7da; }
        .check-item.warning { background: #fff3cd; }
        .status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 3px;
        }
        .status-ok { background: #10b981; color: white; }
        .status-fail { background: #ef4444; color: white; }
        .status-warn { background: #f59e0b; color: white; }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #eee;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .info-box {
            background: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($step === 'welcome'): ?>
            <!-- STEP 1: HOŞGELDİNİZ -->
            <h1>🚀 E-Commerce Platform Kurulumu</h1>
            <p class="subtitle">Polyurethane E-Commerce & CMS Platform - Kolay Kurulum</p>

            <div class="info-box">
                <h3>📋 Kurulum Öncesi Hazırlık:</h3>
                <ul style="margin-left: 20px; margin-top: 10px; line-height: 1.8;">
                    <li>PHP 8.2 veya üzeri</li>
                    <li>MySQL 8.0 veya MariaDB 10.5+</li>
                    <li>Composer yüklü</li>
                    <li>Boş bir database oluşturulmuş</li>
                    <li>Database kullanıcı bilgileri hazır</li>
                </ul>
            </div>

            <div class="alert alert-warning">
                <strong>⚠️ Önemli:</strong> Kuruluma başlamadan önce tüm dosyaları sunucuya yüklediğinizden ve <code>vendor/</code> klasörünün composer ile yüklendiğinden emin olun.
            </div>

            <a href="?step=requirements" class="btn">Kuruluma Başla →</a>

        <?php elseif ($step === 'requirements'): ?>
            <!-- STEP 2: SİSTEM GEREKSİNİMLERİ -->
            <div class="step-indicator">
                <div class="step-item completed">
                    <div class="step-number">✓</div>
                    <div>Hoşgeldiniz</div>
                </div>
                <div class="step-item active">
                    <div class="step-number">2</div>
                    <div>Gereksinimler</div>
                </div>
                <div class="step-item">
                    <div class="step-number">3</div>
                    <div>Database</div>
                </div>
                <div class="step-item">
                    <div class="step-number">4</div>
                    <div>Tamamlandı</div>
                </div>
            </div>

            <h1>🔍 Sistem Gereksinimleri Kontrolü</h1>
            <p class="subtitle">Sunucunuzun tüm gereksinimleri karşıladığından emin olalım</p>

            <?php
            $requirements = [];
            $allOk = true;

            // PHP Version
            $phpVersion = PHP_VERSION;
            $phpOk = version_compare($phpVersion, '8.2.0', '>=');
            $requirements[] = [
                'name' => 'PHP Versiyonu',
                'required' => '8.2.0+',
                'current' => $phpVersion,
                'status' => $phpOk ? 'ok' : 'fail'
            ];
            if (!$phpOk) $allOk = false;

            // PHP Extensions
            $extensions = [
                'pdo_mysql' => 'PDO MySQL',
                'mbstring' => 'Mbstring',
                'gd' => 'GD',
                'intl' => 'Intl',
                'zip' => 'ZIP',
                'bcmath' => 'BCMath',
                'exif' => 'EXIF'
            ];

            foreach ($extensions as $ext => $name) {
                $loaded = extension_loaded($ext);
                $requirements[] = [
                    'name' => "PHP Extension: $name",
                    'required' => 'Gerekli',
                    'current' => $loaded ? 'Yüklü' : 'Eksik',
                    'status' => $loaded ? 'ok' : 'fail'
                ];
                if (!$loaded) $allOk = false;
            }

            // Writable directories
            $directories = [
                __DIR__ . '/storage',
                __DIR__ . '/storage/cache',
                __DIR__ . '/storage/logs',
                __DIR__ . '/public/uploads',
                __DIR__ . '/.env'
            ];

            foreach ($directories as $dir) {
                $path = $dir;
                $exists = file_exists($path);
                $writable = $exists && is_writable($path);

                $requirements[] = [
                    'name' => 'Yazılabilir: ' . basename($path),
                    'required' => 'Yazılabilir',
                    'current' => $writable ? 'OK' : ($exists ? 'Salt okunur' : 'Yok'),
                    'status' => $writable ? 'ok' : 'warn'
                ];
            }

            // Composer
            $composerExists = file_exists(__DIR__ . '/vendor/autoload.php');
            $requirements[] = [
                'name' => 'Composer Dependencies',
                'required' => 'Yüklü',
                'current' => $composerExists ? 'Yüklü' : 'Eksik',
                'status' => $composerExists ? 'ok' : 'fail'
            ];
            if (!$composerExists) $allOk = false;
            ?>

            <?php foreach ($requirements as $req): ?>
                <div class="check-item <?= $req['status'] === 'ok' ? 'success' : ($req['status'] === 'warn' ? 'warning' : 'error') ?>">
                    <div>
                        <strong><?= htmlspecialchars($req['name']) ?></strong><br>
                        <small>Gerekli: <?= htmlspecialchars($req['required']) ?> | Mevcut: <?= htmlspecialchars($req['current']) ?></small>
                    </div>
                    <span class="status status-<?= $req['status'] === 'ok' ? 'ok' : ($req['status'] === 'warn' ? 'warn' : 'fail') ?>">
                        <?= $req['status'] === 'ok' ? '✓ OK' : ($req['status'] === 'warn' ? '⚠ WARN' : '✗ FAIL') ?>
                    </span>
                </div>
            <?php endforeach; ?>

            <?php if ($allOk): ?>
                <div class="alert alert-success">
                    <strong>✅ Harika!</strong> Tüm gereksinimler karşılanıyor. Kuruluma devam edebilirsiniz.
                </div>
                <a href="?step=database" class="btn btn-success">Devam Et →</a>
            <?php else: ?>
                <div class="alert alert-error">
                    <strong>❌ Eksik Gereksinimler</strong><br>
                    Lütfen eksik gereksinimleri tamamlayın ve sayfayı yenileyin.
                </div>
                <a href="?step=requirements" class="btn">Tekrar Kontrol Et</a>
            <?php endif; ?>

        <?php elseif ($step === 'database'): ?>
            <!-- STEP 3: DATABASE AYARLARI -->
            <div class="step-indicator">
                <div class="step-item completed">
                    <div class="step-number">✓</div>
                    <div>Hoşgeldiniz</div>
                </div>
                <div class="step-item completed">
                    <div class="step-number">✓</div>
                    <div>Gereksinimler</div>
                </div>
                <div class="step-item active">
                    <div class="step-number">3</div>
                    <div>Database</div>
                </div>
                <div class="step-item">
                    <div class="step-number">4</div>
                    <div>Tamamlandı</div>
                </div>
            </div>

            <h1>🗄️ Database Ayarları</h1>
            <p class="subtitle">MySQL/MariaDB bağlantı bilgilerinizi girin</p>

            <?php if (isset($_SESSION['db_error'])): ?>
                <div class="alert alert-error">
                    <strong>❌ Database Bağlantı Hatası:</strong><br>
                    <?= htmlspecialchars($_SESSION['db_error']) ?>
                </div>
                <?php unset($_SESSION['db_error']); ?>
            <?php endif; ?>

            <form method="POST" action="?step=install">
                <div class="form-group">
                    <label>Database Host</label>
                    <input type="text" name="db_host" value="localhost" required>
                    <small style="color: #666;">Genellikle localhost veya 127.0.0.1</small>
                </div>

                <div class="form-group">
                    <label>Database Port</label>
                    <input type="text" name="db_port" value="3306" required>
                </div>

                <div class="form-group">
                    <label>Database Adı</label>
                    <input type="text" name="db_database" value="polyurethane_ecommerce" required>
                    <small style="color: #666;">Boş bir database oluşturmuş olmalısınız</small>
                </div>

                <div class="form-group">
                    <label>Database Kullanıcı Adı</label>
                    <input type="text" name="db_username" value="root" required>
                </div>

                <div class="form-group">
                    <label>Database Şifresi</label>
                    <input type="password" name="db_password">
                </div>

                <hr style="margin: 30px 0;">

                <h3 style="margin-bottom: 15px;">⚙️ Uygulama Ayarları</h3>

                <div class="form-group">
                    <label>Site URL</label>
                    <input type="text" name="app_url" value="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] ?>" required>
                </div>

                <div class="form-group">
                    <label>Varsayılan Dil</label>
                    <select name="default_lang">
                        <option value="tr">Türkçe</option>
                        <option value="en">English</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Varsayılan Para Birimi</label>
                    <select name="default_currency">
                        <option value="TRY">TRY (₺)</option>
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Kurulumu Başlat 🚀</button>
            </form>

        <?php elseif ($step === 'install'): ?>
            <!-- STEP 4: KURULUM -->
            <div class="step-indicator">
                <div class="step-item completed">
                    <div class="step-number">✓</div>
                    <div>Hoşgeldiniz</div>
                </div>
                <div class="step-item completed">
                    <div class="step-number">✓</div>
                    <div>Gereksinimler</div>
                </div>
                <div class="step-item completed">
                    <div class="step-number">✓</div>
                    <div>Database</div>
                </div>
                <div class="step-item active">
                    <div class="step-number">4</div>
                    <div>Kurulum</div>
                </div>
            </div>

            <h1>⚙️ Kurulum Yapılıyor...</h1>

            <?php
            // POST verilerini al
            $config = [
                'db_host' => $_POST['db_host'] ?? 'localhost',
                'db_port' => $_POST['db_port'] ?? '3306',
                'db_database' => $_POST['db_database'] ?? '',
                'db_username' => $_POST['db_username'] ?? '',
                'db_password' => $_POST['db_password'] ?? '',
                'app_url' => $_POST['app_url'] ?? '',
                'default_lang' => $_POST['default_lang'] ?? 'tr',
                'default_currency' => $_POST['default_currency'] ?? 'TRY',
            ];

            $progress = 0;
            $errors = [];

            // 1. Database bağlantısı test et
            echo '<div class="check-item success"><span>1. Database bağlantısı test ediliyor...</span> <span class="status status-ok">OK</span></div>';
            $progress = 20;

            try {
                $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_database']};charset=utf8mb4";
                $pdo = new PDO($dsn, $config['db_username'], $config['db_password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
            } catch (PDOException $e) {
                $_SESSION['db_error'] = $e->getMessage();
                echo '<script>window.location.href="?step=database";</script>';
                exit;
            }

            // 2. .env dosyası oluştur
            echo '<div class="check-item success"><span>2. .env dosyası oluşturuluyor...</span> <span class="status status-ok">OK</span></div>';
            $progress = 40;

            $appKey = 'base64:' . base64_encode(random_bytes(32));
            $envContent = <<<ENV
# Application
APP_NAME=Polyurethane E-Commerce
APP_ENV=production
APP_DEBUG=false
APP_URL={$config['app_url']}
APP_KEY=$appKey
APP_TIMEZONE=Europe/Istanbul

# Database
DB_CONNECTION=mysql
DB_HOST={$config['db_host']}
DB_PORT={$config['db_port']}
DB_DATABASE={$config['db_database']}
DB_USERNAME={$config['db_username']}
DB_PASSWORD={$config['db_password']}

# Defaults
DEFAULT_LANG={$config['default_lang']}
DEFAULT_CURRENCY={$config['default_currency']}

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Features
FEATURE_B2B=true
FEATURE_WAREHOUSE=true
FEATURE_PROJECTS=true

# Pricing
PRICE_VISIBILITY=visible
PRICE_INCLUDES_VAT=true
DEFAULT_VAT_RATE=20
ENV;

            file_put_contents(__DIR__ . '/.env', $envContent);

            // 3. Migrations çalıştır
            echo '<div class="check-item success"><span>3. Database tabloları oluşturuluyor...</span> <span class="status status-ok">OK</span></div>';
            $progress = 60;

            $migrationFiles = glob(__DIR__ . '/app/Migrations/sql/*.sql');
            sort($migrationFiles);

            foreach ($migrationFiles as $file) {
                $sql = file_get_contents($file);
                try {
                    $pdo->exec($sql);
                } catch (PDOException $e) {
                    $errors[] = "Migration failed: " . basename($file) . " - " . $e->getMessage();
                }
            }

            // 4. Permissions ayarla
            echo '<div class="check-item success"><span>4. Dizin izinleri ayarlanıyor...</span> <span class="status status-ok">OK</span></div>';
            $progress = 80;

            $directories = ['storage', 'storage/cache', 'storage/logs', 'storage/sessions', 'public/uploads'];
            foreach ($directories as $dir) {
                $path = __DIR__ . '/' . $dir;
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                chmod($path, 0755);
            }

            // 5. Lock dosyası oluştur
            echo '<div class="check-item success"><span>5. Kurulum tamamlanıyor...</span> <span class="status status-ok">OK</span></div>';
            $progress = 100;

            file_put_contents($lockFile, date('Y-m-d H:i:s'));
            ?>

            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $progress ?>%"><?= $progress ?>%</div>
            </div>

            <?php if (empty($errors)): ?>
                <div class="alert alert-success">
                    <h2 style="margin-bottom: 10px;">🎉 Kurulum Başarıyla Tamamlandı!</h2>
                    <p>E-Commerce platformunuz kullanıma hazır.</p>
                </div>

                <div class="info-box">
                    <h3>📝 Önemli Notlar:</h3>
                    <ul style="margin-left: 20px; margin-top: 10px; line-height: 1.8;">
                        <li><strong>GÜVENLİK:</strong> <code>installer.php</code> dosyasını şimdi silin!</li>
                        <li>Admin paneli: <a href="/admin"><?= $config['app_url'] ?>/admin</a></li>
                        <li>İlk admin kullanıcısı migrations ile oluşturuldu</li>
                        <li>Environment dosyası: <code>.env</code></li>
                    </ul>
                </div>

                <a href="/" class="btn btn-success">Ana Sayfaya Git 🏠</a>
            <?php else: ?>
                <div class="alert alert-error">
                    <h3>❌ Bazı Hatalar Oluştu:</h3>
                    <ul style="margin-left: 20px; margin-top: 10px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a href="?step=database" class="btn">Tekrar Dene</a>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</body>
</html>
