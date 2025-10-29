<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class ProductService
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new product
     */
    public function create(array $data): int
    {
        $this->db->beginTransaction();

        try {
            // Insert main product record
            $productData = [
                'sku' => $data['sku'] ?? null,
                'skud' => $data['skud'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'type' => $data['type'] ?? 'simple',
                'status' => $data['status'] ?? 'draft',
                'stock_status' => $data['stock_status'] ?? 'in_stock',
                'stock_quantity' => $data['stock_quantity'] ?? 0,
                'vat_class_id' => $data['vat_class_id'] ?? null,
                'price_visibility' => $data['price_visibility'] ?? 'visible',
                'selling_unit' => $data['selling_unit'] ?? 'pcs',
                'length_mm' => $data['length_mm'] ?? null,
                'width_mm' => $data['width_mm'] ?? null,
                'height_mm' => $data['height_mm'] ?? null,
                'weight_grams' => $data['weight_grams'] ?? null,
                'auto_update_title' => $data['auto_update_title'] ?? true,
                'auto_update_description' => $data['auto_update_description'] ?? true,
                'auto_update_price' => $data['auto_update_price'] ?? true,
                'auto_update_images' => $data['auto_update_images'] ?? true,
                'auto_update_stock' => $data['auto_update_stock'] ?? true,
                'sync_status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $productId = $this->db->insert('products', $productData);

            // Insert multi-language data
            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $langCode => $translation) {
                    $this->createTranslation($productId, $langCode, $translation);
                }
            }

            // Insert multi-currency prices
            if (!empty($data['prices'])) {
                foreach ($data['prices'] as $currencyCode => $priceData) {
                    $this->createPrice($productId, $currencyCode, $priceData);
                }
            }

            // Map external entity if provided
            if (!empty($data['external_id']) && !empty($data['external_source'])) {
                $this->mapExternalEntity($productId, $data['external_source'], $data['external_id']);
            }

            // Calculate sync hash
            $this->updateSyncHash($productId);

            $this->db->commit();

            return $productId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing product
     */
    public function update(int $productId, array $data): bool
    {
        $this->db->beginTransaction();

        try {
            // Get current product data
            $product = $this->findById($productId);
            if (!$product) {
                throw new \Exception("Product not found: {$productId}");
            }

            // Build update data based on auto_update flags
            $updateData = ['updated_at' => date('Y-m-d H:i:s')];

            // Update basic fields
            $allowedFields = ['category_id', 'brand_id', 'type', 'status', 'stock_status',
                              'stock_quantity', 'vat_class_id', 'price_visibility', 'selling_unit',
                              'length_mm', 'width_mm', 'height_mm', 'weight_grams', 'sku', 'skud'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            // Update auto_update flags if provided
            $autoUpdateFields = ['auto_update_title', 'auto_update_description', 'auto_update_price',
                                 'auto_update_images', 'auto_update_stock'];
            foreach ($autoUpdateFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            // Update product record
            if (!empty($updateData)) {
                $this->db->update('products', $updateData, ['id' => $productId]);
            }

            // Update translations if auto_update_title or auto_update_description is enabled
            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $langCode => $translation) {
                    if ($product['auto_update_title'] || $product['auto_update_description']) {
                        $this->updateTranslation($productId, $langCode, $translation, $product);
                    }
                }
            }

            // Update prices if auto_update_price is enabled
            if (!empty($data['prices']) && $product['auto_update_price']) {
                foreach ($data['prices'] as $currencyCode => $priceData) {
                    $this->updatePrice($productId, $currencyCode, $priceData);
                }
            }

            // Update sync status
            $updateData = [
                'sync_status' => 'synced',
                'last_synced_at' => date('Y-m-d H:i:s'),
            ];
            $this->db->update('products', $updateData, ['id' => $productId]);

            // Calculate new sync hash
            $this->updateSyncHash($productId);

            $this->db->commit();

            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Find product by ID
     */
    public function findById(int $productId): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM products WHERE id = ? LIMIT 1",
            [$productId]
        );

        return $result[0] ?? null;
    }

    /**
     * Find product by SKU
     */
    public function findBySku(string $sku): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM products WHERE sku = ? OR skud = ? LIMIT 1",
            [$sku, $sku]
        );

        return $result[0] ?? null;
    }

    /**
     * Find product by external ID
     */
    public function findByExternalId(string $externalSource, string $externalId): ?array
    {
        $result = $this->db->query(
            "SELECT p.* FROM products p
             INNER JOIN external_entity_mapping m
                ON m.local_entity_type = 'product'
                AND m.local_entity_id = p.id
             WHERE m.external_source = ?
                AND m.external_id = ?
             LIMIT 1",
            [$externalSource, $externalId]
        );

        return $result[0] ?? null;
    }

    /**
     * Get product with all translations
     */
    public function getWithTranslations(int $productId): ?array
    {
        $product = $this->findById($productId);
        if (!$product) {
            return null;
        }

        // Get translations
        $translations = $this->db->query(
            "SELECT * FROM product_lang WHERE product_id = ?",
            [$productId]
        );

        $product['translations'] = [];
        foreach ($translations as $trans) {
            $product['translations'][$trans['lang']] = $trans;
        }

        return $product;
    }

    /**
     * Get product with prices
     */
    public function getWithPrices(int $productId, ?string $currencyCode = null): ?array
    {
        $product = $this->findById($productId);
        if (!$product) {
            return null;
        }

        // Get prices
        $query = "SELECT * FROM product_prices_currency WHERE product_id = ?";
        $params = [$productId];

        if ($currencyCode) {
            $query .= " AND currency_code = ?";
            $params[] = $currencyCode;
        }

        $prices = $this->db->query($query, $params);

        $product['prices'] = [];
        foreach ($prices as $price) {
            $product['prices'][$price['currency_code']] = $price;
        }

        return $product;
    }

    /**
     * Create product translation
     */
    protected function createTranslation(int $productId, string $langCode, array $translation): void
    {
        $slug = $this->generateSlug($translation['name'] ?? '', $langCode, $productId);

        $data = [
            'product_id' => $productId,
            'lang' => $langCode,
            'name' => $translation['name'] ?? '',
            'slug' => $slug,
            'short_description' => $translation['short_description'] ?? null,
            'description' => $translation['description'] ?? null,
            'technical_specs' => $translation['technical_specs'] ?? null,
            'application_areas' => $translation['application_areas'] ?? null,
            'meta_title' => $translation['meta_title'] ?? null,
            'meta_description' => $translation['meta_description'] ?? null,
        ];

        $this->db->insert('product_lang', $data);
    }

    /**
     * Update product translation
     */
    protected function updateTranslation(int $productId, string $langCode, array $translation, array $product): void
    {
        // Check if translation exists
        $existing = $this->db->query(
            "SELECT * FROM product_lang WHERE product_id = ? AND lang = ? LIMIT 1",
            [$productId, $langCode]
        );

        $data = [];

        if ($product['auto_update_title'] && isset($translation['name'])) {
            $data['name'] = $translation['name'];
            // Generate new slug
            $data['slug'] = $this->generateSlug($translation['name'], $langCode, $productId);
        }

        if ($product['auto_update_description']) {
            if (isset($translation['short_description'])) {
                $data['short_description'] = $translation['short_description'];
            }
            if (isset($translation['description'])) {
                $data['description'] = $translation['description'];
            }
            if (isset($translation['technical_specs'])) {
                $data['technical_specs'] = $translation['technical_specs'];
            }
            if (isset($translation['application_areas'])) {
                $data['application_areas'] = $translation['application_areas'];
            }
        }

        if (isset($translation['meta_title'])) {
            $data['meta_title'] = $translation['meta_title'];
        }
        if (isset($translation['meta_description'])) {
            $data['meta_description'] = $translation['meta_description'];
        }

        if (!empty($data)) {
            if (!empty($existing)) {
                // Update existing
                $this->db->update('product_lang', $data, [
                    'product_id' => $productId,
                    'lang' => $langCode
                ]);

                // Track slug change for 301 redirects
                if (isset($data['slug']) && $data['slug'] !== $existing[0]['slug']) {
                    $this->trackSlugChange($productId, 'product', $langCode, $existing[0]['slug'], $data['slug']);
                }
            } else {
                // Create new
                $data['product_id'] = $productId;
                $data['lang'] = $langCode;
                $this->db->insert('product_lang', $data);
            }
        }
    }

    /**
     * Create product price
     */
    protected function createPrice(int $productId, string $currencyCode, array $priceData): void
    {
        $data = [
            'product_id' => $productId,
            'currency_code' => $currencyCode,
            'base_price' => $priceData['base_price'],
            'compare_at_price' => $priceData['compare_at_price'] ?? null,
            'cost_price' => $priceData['cost_price'] ?? null,
            'tier_1_min_qty' => $priceData['tier_1_min_qty'] ?? null,
            'tier_1_price' => $priceData['tier_1_price'] ?? null,
            'tier_2_min_qty' => $priceData['tier_2_min_qty'] ?? null,
            'tier_2_price' => $priceData['tier_2_price'] ?? null,
            'tier_3_min_qty' => $priceData['tier_3_min_qty'] ?? null,
            'tier_3_price' => $priceData['tier_3_price'] ?? null,
        ];

        $this->db->insert('product_prices_currency', $data);
    }

    /**
     * Update product price
     */
    protected function updatePrice(int $productId, string $currencyCode, array $priceData): void
    {
        // Check if price exists
        $existing = $this->db->query(
            "SELECT * FROM product_prices_currency WHERE product_id = ? AND currency_code = ? LIMIT 1",
            [$productId, $currencyCode]
        );

        $data = [
            'base_price' => $priceData['base_price'],
            'compare_at_price' => $priceData['compare_at_price'] ?? null,
            'cost_price' => $priceData['cost_price'] ?? null,
            'tier_1_min_qty' => $priceData['tier_1_min_qty'] ?? null,
            'tier_1_price' => $priceData['tier_1_price'] ?? null,
            'tier_2_min_qty' => $priceData['tier_2_min_qty'] ?? null,
            'tier_2_price' => $priceData['tier_2_price'] ?? null,
            'tier_3_min_qty' => $priceData['tier_3_min_qty'] ?? null,
            'tier_3_price' => $priceData['tier_3_price'] ?? null,
        ];

        if (!empty($existing)) {
            $this->db->update('product_prices_currency', $data, [
                'product_id' => $productId,
                'currency_code' => $currencyCode
            ]);
        } else {
            $data['product_id'] = $productId;
            $data['currency_code'] = $currencyCode;
            $this->db->insert('product_prices_currency', $data);
        }
    }

    /**
     * Map product to external entity
     */
    protected function mapExternalEntity(int $productId, string $externalSource, string $externalId): void
    {
        $data = [
            'local_entity_type' => 'product',
            'local_entity_id' => $productId,
            'external_source' => $externalSource,
            'external_entity_type' => 'product',
            'external_id' => $externalId,
            'sync_status' => 'synced',
            'last_synced_at' => date('Y-m-d H:i:s'),
        ];

        // Use INSERT ... ON DUPLICATE KEY UPDATE
        $this->db->query(
            "INSERT INTO external_entity_mapping
                (local_entity_type, local_entity_id, external_source, external_entity_type, external_id, sync_status, last_synced_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                local_entity_id = VALUES(local_entity_id),
                sync_status = VALUES(sync_status),
                last_synced_at = VALUES(last_synced_at)",
            [
                $data['local_entity_type'],
                $data['local_entity_id'],
                $data['external_source'],
                $data['external_entity_type'],
                $data['external_id'],
                $data['sync_status'],
                $data['last_synced_at'],
            ]
        );
    }

    /**
     * Generate unique slug
     */
    protected function generateSlug(string $text, string $langCode, int $productId): string
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

        // Check uniqueness
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $existing = $this->db->query(
                "SELECT id FROM product_lang WHERE slug = ? AND lang = ? AND product_id != ? LIMIT 1",
                [$slug, $langCode, $productId]
            );

            if (empty($existing)) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Track slug change for 301 redirects
     */
    protected function trackSlugChange(int $entityId, string $entityType, string $langCode, string $oldSlug, string $newSlug): void
    {
        $data = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'lang' => $langCode,
            'old_slug' => $oldSlug,
            'new_slug' => $newSlug,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('slugs_history', $data);
    }

    /**
     * Update sync hash
     */
    protected function updateSyncHash(int $productId): void
    {
        // Get all product data
        $product = $this->getWithTranslations($productId);
        $product = $this->getWithPrices($productId);

        // Generate hash
        $hashData = json_encode($product);
        $hash = hash('sha256', $hashData);

        // Update product
        $this->db->update('products', ['sync_hash' => $hash], ['id' => $productId]);
    }

    /**
     * Check if product needs sync (hash comparison)
     */
    public function needsSync(int $productId, string $remoteHash): bool
    {
        $product = $this->findById($productId);
        if (!$product) {
            return true;
        }

        return $product['sync_hash'] !== $remoteHash;
    }

    /**
     * Delete product (soft delete)
     */
    public function delete(int $productId): bool
    {
        $updateData = [
            'status' => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s'),
        ];

        return $this->db->update('products', $updateData, ['id' => $productId]);
    }

    /**
     * Get products for sync (pending status)
     */
    public function getPendingSync(int $limit = 100): array
    {
        return $this->db->query(
            "SELECT * FROM products WHERE sync_status = 'pending' LIMIT ?",
            [$limit]
        );
    }

    /**
     * Calculate price for quantity (B2B tier pricing)
     */
    public function calculatePrice(int $productId, string $currencyCode, int $quantity = 1): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM product_prices_currency WHERE product_id = ? AND currency_code = ? LIMIT 1",
            [$productId, $currencyCode]
        );

        if (empty($result)) {
            return null;
        }

        $priceData = $result[0];
        $finalPrice = $priceData['base_price'];

        // Check tier pricing
        if ($priceData['tier_3_min_qty'] && $quantity >= $priceData['tier_3_min_qty']) {
            $finalPrice = $priceData['tier_3_price'];
        } elseif ($priceData['tier_2_min_qty'] && $quantity >= $priceData['tier_2_min_qty']) {
            $finalPrice = $priceData['tier_2_price'];
        } elseif ($priceData['tier_1_min_qty'] && $quantity >= $priceData['tier_1_min_qty']) {
            $finalPrice = $priceData['tier_1_price'];
        }

        return [
            'price' => $finalPrice,
            'base_price' => $priceData['base_price'],
            'currency' => $currencyCode,
            'quantity' => $quantity,
            'tier_applied' => $finalPrice !== $priceData['base_price'],
        ];
    }
}
