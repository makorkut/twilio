<?php

/**
 * Helper Functions
 */

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    function config(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $file = array_shift($keys);
        $configPath = CONFIG_PATH . "/{$file}.php";

        if (!file_exists($configPath)) {
            return $default;
        }

        $config = require $configPath;

        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                return $default;
            }
            $config = $config[$k];
        }

        return $config;
    }
}

if (!function_exists('dd')) {
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
        die();
    }
}

if (!function_exists('cache_path')) {
    function cache_path(string $file = ''): string
    {
        return STORAGE_PATH . '/cache/' . ltrim($file, '/');
    }
}

if (!function_exists('public_path')) {
    function public_path(string $file = ''): string
    {
        return PUBLIC_PATH . '/' . ltrim($file, '/');
    }
}

if (!function_exists('container')) {
    function container(): ?\App\Core\Container
    {
        global $app;
        return $app ? $app->getContainer() : null;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $code = 302): void
    {
        header("Location: {$url}", true, $code);
        exit;
    }
}

if (!function_exists('view')) {
    function view(string $file, array $data = []): string
    {
        $viewPath = APP_PATH . '/Views/' . $file;

        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$file}");
        }

        extract($data);
        ob_start();
        include $viewPath;
        return ob_get_clean();
    }
}

if (!function_exists('generateSlug')) {
    /**
     * Generate URL-friendly slug from text
     * Supports Turkish characters
     */
    function generateSlug(string $text): string
    {
        // Turkish character map
        $charMap = [
            'Ç' => 'C', 'ç' => 'c',
            'Ğ' => 'G', 'ğ' => 'g',
            'İ' => 'I', 'ı' => 'i',
            'Ö' => 'O', 'ö' => 'o',
            'Ş' => 'S', 'ş' => 's',
            'Ü' => 'U', 'ü' => 'u',
        ];

        $slug = strtr($text, $charMap);
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }
}

if (!function_exists('trans')) {
    /**
     * Translate a key using lang_id
     *
     * @param string $key Translation key (e.g., 'common.home', 'admin.products.title')
     * @param array $params Parameters to replace in translation {key} or %key% format
     * @param int|null $langId Language ID (1=EN, 2=TR). If null, uses session language
     * @return string Translated text
     */
    function trans(string $key, array $params = [], ?int $langId = null): string
    {
        static $i18nService = null;

        // Get lang_id from session if not provided
        if ($langId === null) {
            $langId = $_SESSION['language_id'] ?? 2; // Default to TR (2)
        }

        // Initialize I18nService on first call
        if ($i18nService === null) {
            $db = container()?->get(\App\Core\Database::class);
            if (!$db) {
                return $key; // Fallback if no DB connection
            }
            $i18nService = new \App\Services\I18nService($db, $langId);
        } else {
            // Update language if different
            if ($i18nService->getCurrentLanguageId() !== $langId) {
                $i18nService->setLanguage($langId);
            }
        }

        return $i18nService->get($key, $langId, $params);
    }
}

if (!function_exists('lang')) {
    /**
     * Alias for trans() - for backward compatibility
     */
    function lang(string $key, array $params = [], ?int $langId = null): string
    {
        return trans($key, $params, $langId);
    }
}

if (!function_exists('get_current_language_id')) {
    /**
     * Get current language ID from session
     *
     * @return int Language ID (1=EN, 2=TR)
     */
    function get_current_language_id(): int
    {
        return (int) ($_SESSION['language_id'] ?? 2); // Default to TR
    }
}

if (!function_exists('set_current_language_id')) {
    /**
     * Set current language ID in session
     *
     * @param int $langId Language ID (1=EN, 2=TR)
     */
    function set_current_language_id(int $langId): void
    {
        $_SESSION['language_id'] = $langId;
    }
}

if (!function_exists('get_language_short_form')) {
    /**
     * Get language short form from lang_id
     *
     * @param int $langId Language ID
     * @return string 'en' or 'tr'
     */
    function get_language_short_form(int $langId): string
    {
        return $langId === 1 ? 'en' : 'tr';
    }
}
