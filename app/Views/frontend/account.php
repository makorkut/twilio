<?php
$pageTitle = 'Hesabım - Polyurethane';
$metaDescription = 'Kullanıcı hesap yönetimi.';

ob_start();
?>

<style>
    .account-hero {
        background: linear-gradient(135deg, var(--color-bg-gray) 0%, white 100%);
        padding: 80px 0;
        text-align: center;
    }

    .account-hero h1 {
        font-size: 48px;
        font-weight: 300;
        margin-bottom: 16px;
    }

    .account-section {
        padding: 60px 0;
    }

    .login-prompt {
        max-width: 500px;
        margin: 0 auto;
        text-align: center;
        padding: 60px 20px;
    }

    .login-prompt h2 {
        font-size: 24px;
        margin-bottom: 16px;
        color: var(--color-primary);
    }

    .login-prompt p {
        color: var(--color-text-light);
        margin-bottom: 30px;
    }

    .btn-group {
        display: flex;
        gap: 16px;
        justify-content: center;
    }
</style>

<!-- Hero Section -->
<section class="account-hero">
    <div class="container-narrow">
        <h1>Hesabım</h1>
        <p style="font-size: 18px; color: var(--color-text-light);">Sipariş takibi, adres yönetimi ve hesap ayarları</p>
    </div>
</section>

<!-- Account Section -->
<section class="account-section">
    <div class="container-narrow">
        <div class="login-prompt">
            <h2>👤 Giriş Yapın</h2>
            <p>Hesap bilgilerinizi görüntülemek ve siparişlerinizi takip etmek için giriş yapmanız gerekmektedir.</p>

            <div class="btn-group">
                <a href="/admin/login" class="btn">Giriş Yap</a>
                <a href="/contact" class="btn btn-secondary">Yardım</a>
            </div>

            <div style="margin-top: 40px; padding-top: 40px; border-top: 1px solid var(--color-border);">
                <h3 style="font-size: 18px; margin-bottom: 16px; color: var(--color-primary);">Hesap Özellikleri</h3>
                <ul style="text-align: left; max-width: 400px; margin: 0 auto; color: var(--color-text-light); line-height: 2;">
                    <li>✓ Sipariş geçmişi ve takibi</li>
                    <li>✓ Adres defteri yönetimi</li>
                    <li>✓ Favori ürünler listesi</li>
                    <li>✓ Özel fiyatlar ve kampanyalar</li>
                    <li>✓ Hızlı sipariş oluşturma</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
