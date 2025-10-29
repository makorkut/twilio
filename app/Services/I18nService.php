<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class I18nService
{
    protected Database $db;
    protected array $translations = [];
    protected string $currentLang;
    protected string $fallbackLang;
    protected bool $cacheLoaded = false;

    public function __construct(Database $db, ?string $lang = null)
    {
        $this->db = $db;
        $this->currentLang = $lang ?? env('DEFAULT_LANG', 'tr');
        $this->fallbackLang = env('DEFAULT_LANG', 'tr');
    }

    /**
     * Set current language
     */
    public function setLanguage(string $langCode): void
    {
        $this->currentLang = $langCode;
        $this->cacheLoaded = false;
        $this->translations = [];
    }

    /**
     * Get current language
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLang;
    }

    /**
     * Get translation by key
     */
    public function get(string $key, ?string $lang = null, array $params = []): string
    {
        $lang = $lang ?? $this->currentLang;

        // Load translations if not loaded
        if (!$this->cacheLoaded) {
            $this->loadTranslations($lang);
        }

        // Get translation
        $translation = $this->translations[$key] ?? null;

        // Fallback to fallback language
        if ($translation === null && $lang !== $this->fallbackLang) {
            $this->loadTranslations($this->fallbackLang);
            $translation = $this->translations[$key] ?? null;
        }

        // Fallback to key if not found
        if ($translation === null) {
            $translation = $key;
        }

        // Replace parameters
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $translation = str_replace("{{$param}}", $value, $translation);
            }
        }

        return $translation;
    }

    /**
     * Shorthand for get()
     */
    public function t(string $key, array $params = []): string
    {
        return $this->get($key, null, $params);
    }

    /**
     * Load all translations for a language
     */
    protected function loadTranslations(string $langCode): void
    {
        // Try to load from cache file
        $cacheFile = cache_path("i18n/{$langCode}.php");

        if (file_exists($cacheFile)) {
            $this->translations = require $cacheFile;
            $this->cacheLoaded = true;
            return;
        }

        // Load from database
        $results = $this->db->query(
            "SELECT k.key_name, v.value
             FROM i18n_keys k
             LEFT JOIN i18n_values v ON v.key_id = k.id AND v.lang = ?
             WHERE k.is_active = 1",
            [$langCode]
        );

        $translations = [];
        foreach ($results as $row) {
            if ($row['value']) {
                $translations[$row['key_name']] = $row['value'];
            }
        }

        $this->translations = $translations;
        $this->cacheLoaded = true;

        // Save to cache
        $this->saveCacheFile($langCode, $translations);
    }

    /**
     * Save translations to cache file
     */
    protected function saveCacheFile(string $langCode, array $translations): void
    {
        $cacheDir = cache_path('i18n');

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cacheFile = $cacheDir . "/{$langCode}.php";

        $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";

        file_put_contents($cacheFile, $content);
    }

    /**
     * Clear translation cache
     */
    public function clearCache(?string $langCode = null): void
    {
        $cacheDir = cache_path('i18n');

        if ($langCode) {
            $cacheFile = $cacheDir . "/{$langCode}.php";
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        } else {
            // Clear all language caches
            if (is_dir($cacheDir)) {
                $files = glob($cacheDir . '/*.php');
                foreach ($files as $file) {
                    unlink($file);
                }
            }
        }

        $this->cacheLoaded = false;
        $this->translations = [];
    }

    /**
     * Get all available languages
     */
    public function getAvailableLanguages(): array
    {
        return $this->db->query(
            "SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order ASC"
        );
    }

    /**
     * Get default language
     */
    public function getDefaultLanguage(): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM languages WHERE is_default = 1 LIMIT 1"
        );

        return $result[0] ?? null;
    }

    /**
     * Get language by code
     */
    public function getLanguage(string $langCode): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM languages WHERE code = ? LIMIT 1",
            [$langCode]
        );

        return $result[0] ?? null;
    }

    /**
     * Create translation key
     */
    public function createKey(string $keyName, ?string $group = null, ?string $description = null): int
    {
        // Check if key exists
        $existing = $this->db->query(
            "SELECT id FROM i18n_keys WHERE key_name = ? LIMIT 1",
            [$keyName]
        );

        if (!empty($existing)) {
            return (int) $existing[0]['id'];
        }

        $data = [
            'key_name' => $keyName,
            'group' => $group,
            'description' => $description,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return $this->db->insert('i18n_keys', $data);
    }

    /**
     * Set translation value
     */
    public function setValue(string $keyName, string $langCode, string $value): void
    {
        // Get or create key
        $keyId = $this->createKey($keyName);

        // Check if value exists
        $existing = $this->db->query(
            "SELECT id FROM i18n_values WHERE key_id = ? AND lang = ? LIMIT 1",
            [$keyId, $langCode]
        );

        if (!empty($existing)) {
            // Update existing
            $this->db->update('i18n_values', [
                'value' => $value,
                'updated_at' => date('Y-m-d H:i:s'),
            ], [
                'key_id' => $keyId,
                'lang' => $langCode,
            ]);
        } else {
            // Insert new
            $this->db->insert('i18n_values', [
                'key_id' => $keyId,
                'lang' => $langCode,
                'value' => $value,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Clear cache for this language
        $this->clearCache($langCode);
    }

    /**
     * Get all translations for a key (all languages)
     */
    public function getKeyTranslations(string $keyName): array
    {
        $result = $this->db->query(
            "SELECT v.lang, v.value
             FROM i18n_keys k
             INNER JOIN i18n_values v ON v.key_id = k.id
             WHERE k.key_name = ?",
            [$keyName]
        );

        $translations = [];
        foreach ($result as $row) {
            $translations[$row['lang']] = $row['value'];
        }

        return $translations;
    }

    /**
     * Get all keys in a group
     */
    public function getGroupKeys(string $group): array
    {
        return $this->db->query(
            "SELECT * FROM i18n_keys WHERE `group` = ? ORDER BY key_name ASC",
            [$group]
        );
    }

    /**
     * Import translations from array
     */
    public function import(array $translations, string $langCode): int
    {
        $count = 0;

        foreach ($translations as $keyName => $value) {
            try {
                $this->setValue($keyName, $langCode, $value);
                $count++;
            } catch (\Exception $e) {
                // Continue on error
            }
        }

        return $count;
    }

    /**
     * Export translations to array
     */
    public function export(string $langCode, ?string $group = null): array
    {
        $query = "SELECT k.key_name, v.value
                  FROM i18n_keys k
                  LEFT JOIN i18n_values v ON v.key_id = k.id AND v.lang = ?
                  WHERE k.is_active = 1";

        $params = [$langCode];

        if ($group) {
            $query .= " AND k.group = ?";
            $params[] = $group;
        }

        $query .= " ORDER BY k.key_name ASC";

        $results = $this->db->query($query, $params);

        $translations = [];
        foreach ($results as $row) {
            $translations[$row['key_name']] = $row['value'] ?? '';
        }

        return $translations;
    }

    /**
     * Get missing translations (keys without values for a language)
     */
    public function getMissingTranslations(string $langCode): array
    {
        $results = $this->db->query(
            "SELECT k.*
             FROM i18n_keys k
             LEFT JOIN i18n_values v ON v.key_id = k.id AND v.lang = ?
             WHERE k.is_active = 1
             AND (v.id IS NULL OR v.value = '' OR v.value IS NULL)
             ORDER BY k.group ASC, k.key_name ASC",
            [$langCode]
        );

        return $results;
    }

    /**
     * Get translation completion percentage
     */
    public function getCompletionPercentage(string $langCode): float
    {
        $totalKeys = $this->db->query(
            "SELECT COUNT(*) as count FROM i18n_keys WHERE is_active = 1"
        )[0]['count'];

        if ($totalKeys == 0) {
            return 100;
        }

        $translatedKeys = $this->db->query(
            "SELECT COUNT(*) as count
             FROM i18n_keys k
             INNER JOIN i18n_values v ON v.key_id = k.id AND v.lang = ?
             WHERE k.is_active = 1
             AND v.value IS NOT NULL
             AND v.value != ''",
            [$langCode]
        )[0]['count'];

        return ($translatedKeys / $totalKeys) * 100;
    }

    /**
     * Format date according to language settings
     */
    public function formatDate(string $date, ?string $langCode = null): string
    {
        $langCode = $langCode ?? $this->currentLang;

        $language = $this->getLanguage($langCode);
        if (!$language || !$language['date_format']) {
            return date('Y-m-d', strtotime($date));
        }

        return date($language['date_format'], strtotime($date));
    }

    /**
     * Format number according to language settings
     */
    public function formatNumber(float $number, int $decimals = 2, ?string $langCode = null): string
    {
        $langCode = $langCode ?? $this->currentLang;

        $language = $this->getLanguage($langCode);
        if (!$language) {
            return number_format($number, $decimals);
        }

        $decimalSep = $language['decimal_separator'] ?? '.';
        $thousandsSep = $language['thousands_separator'] ?? ',';

        return number_format($number, $decimals, $decimalSep, $thousandsSep);
    }

    /**
     * Detect language from browser
     */
    public function detectLanguageFromBrowser(?string $acceptLanguageHeader = null): string
    {
        $acceptLanguageHeader = $acceptLanguageHeader ?? ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');

        if (empty($acceptLanguageHeader)) {
            return $this->fallbackLang;
        }

        // Parse Accept-Language header
        preg_match_all('/([a-z]{2})(?:-[A-Z]{2})?(?:;q=([0-9.]+))?/', $acceptLanguageHeader, $matches);

        if (empty($matches[1])) {
            return $this->fallbackLang;
        }

        // Get available language codes
        $availableLanguages = $this->getAvailableLanguages();
        $availableCodes = array_column($availableLanguages, 'code');

        // Find best match
        foreach ($matches[1] as $browserLang) {
            if (in_array($browserLang, $availableCodes)) {
                return $browserLang;
            }
        }

        return $this->fallbackLang;
    }

    /**
     * Get language from URL path
     */
    public function getLanguageFromPath(string $path): ?string
    {
        // Extract language code from path (e.g., /tr/products -> tr)
        if (preg_match('#^/([a-z]{2})(?:/|$)#', $path, $matches)) {
            $langCode = $matches[1];

            // Verify it's a valid language
            $language = $this->getLanguage($langCode);
            if ($language) {
                return $langCode;
            }
        }

        return null;
    }

    /**
     * Get localized URL
     */
    public function localizeUrl(string $path, ?string $langCode = null): string
    {
        $langCode = $langCode ?? $this->currentLang;
        $defaultLang = $this->getDefaultLanguage();

        // Remove existing language code from path
        $path = preg_replace('#^/[a-z]{2}(?=/|$)#', '', $path);

        // Add language code if not default language
        if (!$defaultLang || $langCode !== $defaultLang['code']) {
            $path = '/' . $langCode . $path;
        }

        return $path;
    }

    /**
     * Get alternative language URLs for a path (for hreflang tags)
     */
    public function getAlternateUrls(string $path): array
    {
        $languages = $this->getAvailableLanguages();
        $urls = [];

        foreach ($languages as $language) {
            $urls[$language['code']] = $this->localizeUrl($path, $language['code']);
        }

        return $urls;
    }

    /**
     * Rebuild cache for all languages
     */
    public function rebuildAllCaches(): array
    {
        $languages = $this->getAvailableLanguages();
        $results = [];

        foreach ($languages as $language) {
            try {
                $this->clearCache($language['code']);
                $this->loadTranslations($language['code']);
                $results[$language['code']] = 'success';
            } catch (\Exception $e) {
                $results[$language['code']] = 'error: ' . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Search translations
     */
    public function search(string $query, ?string $langCode = null): array
    {
        $langCode = $langCode ?? $this->currentLang;

        return $this->db->query(
            "SELECT k.key_name, k.group, v.value
             FROM i18n_keys k
             LEFT JOIN i18n_values v ON v.key_id = k.id AND v.lang = ?
             WHERE k.is_active = 1
             AND (k.key_name LIKE ? OR v.value LIKE ?)
             ORDER BY k.key_name ASC
             LIMIT 100",
            [$langCode, "%{$query}%", "%{$query}%"]
        );
    }
}
