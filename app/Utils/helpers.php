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
