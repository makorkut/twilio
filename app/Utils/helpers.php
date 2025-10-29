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
