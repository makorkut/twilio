<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * I18n Service - Language ID Based Translation System
 *
 * Uses lang_id (INT) instead of lang_code (VARCHAR) for better performance and referential integrity
 * Language IDs: 1=EN, 2=TR
 */
class I18nService
{
    protected Database $db;
    protected array $translations = [];
    protected int $currentLangId;
    protected int $fallbackLangId;
    protected bool $cacheLoaded = false;

    // Language ID constants
    public const LANG_EN = 1;
    public const LANG_TR = 2;

    public function __construct(Database $db, ?int $langId = null)
    {
        $this->db = $db;
        $this->currentLangId = $langId ?? (int) (env('DEFAULT_LANG_ID', self::LANG_TR));
        $this->fallbackLangId = (int) (env('FALLBACK_LANG_ID', self::LANG_EN));
    }

    /**
     * Set current language by ID
     */
    public function setLanguage(int $langId): void
    {
        $this->currentLangId = $langId;
        $this->cacheLoaded = false;
        $this->translations = [];
    }

    /**
     * Get current language ID
     */
    public function getCurrentLanguageId(): int
    {
        return $this->currentLangId;
    }

    /**
     * Get current language details
     */
    public function getCurrentLanguage(): ?array
    {
        return $this->getLanguageById($this->currentLangId);
    }

    /**
     * Get translation by key
     *
     * @param string $key Translation key (e.g., 'common.home', 'admin.products.title')
     * @param int|null $langId Language ID (1=EN, 2=TR). If null, uses current language
     * @param array $params Parameters to replace in translation {key} format
     * @return string Translated text or key if not found
     */
    public function get(string $key, ?int $langId = null, array $params = []): string
    {
        $langId = $langId ?? $this->currentLangId;

        // Load translations if not loaded
        if (!$this->cacheLoaded) {
            $this->loadTranslations($langId);
        }

        // Get translation
        $translation = $this->translations[$key] ?? null;

        // Fallback to fallback language
        if ($translation === null && $langId !== $this->fallbackLangId) {
            $this->loadTranslations($this->fallbackLangId);
            $translation = $this->translations[$key] ?? null;
        }

        // Fallback to key if not found
        if ($translation === null) {
            $translation = $key;
        }

        // Replace parameters {key} format
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $translation = str_replace("{{$param}}", (string) $value, $translation);
                $translation = str_replace("%{$param}%", (string) $value, $translation);
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
    protected function loadTranslations(int $langId): void
    {
        // Try to load from cache file
        $cacheFile = cache_path("i18n/lang_{$langId}.php");

        if (file_exists($cacheFile)) {
            $this->translations = require $cacheFile;
            $this->cacheLoaded = true;
            return;
        }

        // Load from database using lang_id
        $results = $this->db->query(
            "SELECT k.key_name, v.value
             FROM i18n_keys k
             LEFT JOIN i18n_values v ON v.key_id = k.id AND v.lang_id = ?
             WHERE k.is_active = 1",
            [$langId]
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
        $this->saveCacheFile($langId, $translations);
    }

    /**
     * Save translations to cache file
     */
    protected function saveCacheFile(int $langId, array $translations): void
    {
        $cacheDir = cache_path('i18n');

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cacheFile = $cacheDir . "/lang_{$langId}.php";

        $content = "<?php\n\n// Translation cache for lang_id={$langId}\n// Generated: " . date('Y-m-d H:i:s') . "\n\nreturn " . var_export($translations, true) . ";\n";

        file_put_contents($cacheFile, $content);
    }

    /**
     * Clear translation cache
     */
    public function clearCache(?int $langId = null): void
    {
        $cacheDir = cache_path('i18n');

        if ($langId) {
            $cacheFile = $cacheDir . "/lang_{$langId}.php";
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        } else {
            // Clear all language caches
            if (is_dir($cacheDir)) {
                $files = glob($cacheDir . '/lang_*.php');
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
            "SELECT * FROM languages WHERE status = 1 ORDER BY language_order ASC"
        );
    }

    /**
     * Get default language (usually TR = id 2)
     */
    public function getDefaultLanguage(): ?array
    {
        // For now, default is always TR (id=2)
        return $this->getLanguageById(self::LANG_TR);
    }

    /**
     * Get language by ID
     */
    public function getLanguageById(int $langId): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM languages WHERE id = ? LIMIT 1",
            [$langId]
        );

        return $result[0] ?? null;
    }

    /**
     * Get language by short code (en, tr)
     */
    public function getLanguageByCode(string $shortCode): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM languages WHERE short_form = ? LIMIT 1",
            [$shortCode]
        );

        return $result[0] ?? null;
    }

    /**
     * Convert lang_code to lang_id (for backward compatibility)
     */
    public function getLangIdByCode(string $shortCode): int
    {
        $lang = $this->getLanguageByCode($shortCode);
        return $lang ? (int) $lang['id'] : $this->fallbackLangId;
    }

    /**
     * Create translation key
     */
    public function createKey(string $keyName, ?string $group = null, ?string $description = null): int
    {
        // Extract group from key_name if not provided
        if (!$group && strpos($keyName, '.') !== false) {
            $group = explode('.', $keyName)[0];
        }

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
            'group' => $group ?? 'common',
            'description' => $description,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return $this->db->insert('i18n_keys', $data);
    }

    /**
     * Set translation value using lang_id
     */
    public function setValue(string $keyName, int $langId, string $value): void
    {
        // Get or create key
        $keyId = $this->createKey($keyName);

        // Check if value exists
        $existing = $this->db->query(
            "SELECT id FROM i18n_values WHERE key_id = ? AND lang_id = ? LIMIT 1",
            [$keyId, $langId]
        );

        if (!empty($existing)) {
            // Update existing
            $this->db->update('i18n_values', [
                'value' => $value,
                'updated_at' => date('Y-m-d H:i:s'),
            ], [
                'key_id' => $keyId,
                'lang_id' => $langId,
            ]);
        } else {
            // Insert new
            $this->db->insert('i18n_values', [
                'key_id' => $keyId,
                'lang_id' => $langId,
                'value' => $value,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Clear cache for this language
        $this->clearCache($langId);
    }

    /**
     * Get all translations for a key (all languages)
     */
    public function getKeyTranslations(string $keyName): array
    {
        $result = $this->db->query(
            "SELECT v.lang_id, v.value, l.short_form, l.name
             FROM i18n_keys k
             INNER JOIN i18n_values v ON v.key_id = k.id
             INNER JOIN languages l ON l.id = v.lang_id
             WHERE k.key_name = ?",
            [$keyName]
        );

        $translations = [];
        foreach ($result as $row) {
            $translations[$row['lang_id']] = [
                'lang_id' => $row['lang_id'],
                'short_form' => $row['short_form'],
                'name' => $row['name'],
                'value' => $row['value'],
            ];
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
     *
     * @param array $translations Array of ['key' => 'value']
     * @param int $langId Language ID
     * @return int Number of keys imported
     */
    public function import(array $translations, int $langId): int
    {
        $count = 0;

        foreach ($translations as $keyName => $value) {
            try {
                $this->setValue($keyName, $langId, $value);
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
    public function export(int $langId, ?string $group = null): array
    {
        $query = "SELECT k.key_name, v.value
                  FROM i18n_keys k
                  LEFT JOIN i18n_values v ON v.key_id = k.id AND v.lang_id = ?
                  WHERE k.is_active = 1";

        $params = [$langId];

        if ($group) {
            $query .= " AND k.`group` = ?";
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
    public function getMissingTranslations(int $langId): array
    {
        $results = $this->db->query(
            "SELECT k.*
             FROM i18n_keys k
             LEFT JOIN i18n_values v ON v.key_id = k.id AND v.lang_id = ?
             WHERE k.is_active = 1
             AND (v.id IS NULL OR v.value = '' OR v.value IS NULL)
             ORDER BY k.`group` ASC, k.key_name ASC",
            [$langId]
        );

        return $results;
    }

    /**
     * Get translation completion percentage
     */
    public function getCompletionPercentage(int $langId): float
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
             INNER JOIN i18n_values v ON v.key_id = k.id AND v.lang_id = ?
             WHERE k.is_active = 1
             AND v.value IS NOT NULL
             AND v.value != ''",
            [$langId]
        )[0]['count'];

        return ($translatedKeys / $totalKeys) * 100;
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
                $this->clearCache($language['id']);
                $this->loadTranslations($language['id']);
                $results[$language['id']] = 'success';
            } catch (\Exception $e) {
                $results[$language['id']] = 'error: ' . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Search translations
     */
    public function search(string $query, ?int $langId = null): array
    {
        $langId = $langId ?? $this->currentLangId;

        return $this->db->query(
            "SELECT k.key_name, k.`group`, v.value
             FROM i18n_keys k
             LEFT JOIN i18n_values v ON v.key_id = k.id AND v.lang_id = ?
             WHERE k.is_active = 1
             AND (k.key_name LIKE ? OR v.value LIKE ?)
             ORDER BY k.key_name ASC
             LIMIT 100",
            [$langId, "%{$query}%", "%{$query}%"]
        );
    }
}
