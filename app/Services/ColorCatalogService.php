<?php

namespace App\Services;

use App\Core\Database;

class ColorCatalogService
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Get all colors/textures for a product
     */
    public function getProductColors(int $productId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM product_colors
             WHERE product_id = ?
             ORDER BY sort_order ASC, name ASC",
            [$productId]
        );
    }

    /**
     * Add color/texture to product
     */
    public function addColor(int $productId, array $data): int
    {
        // Validate required fields
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Color name is required');
        }

        // Get next sort order
        $result = $this->db->fetch(
            "SELECT MAX(sort_order) as max_order FROM product_colors WHERE product_id = ?",
            [$productId]
        );
        $sortOrder = ($result['max_order'] ?? 0) + 1;

        return $this->db->insert('product_colors', [
            'product_id' => $productId,
            'name' => $data['name'],
            'color_code' => $data['color_code'] ?? null,
            'ral_code' => $data['ral_code'] ?? null,
            'hex_color' => $data['hex_color'] ?? null,
            'texture_type' => $data['texture_type'] ?? null,
            'finish_type' => $data['finish_type'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'swatch_image' => $data['swatch_image'] ?? null,
            'price_modifier' => !empty($data['price_modifier']) ? (float)$data['price_modifier'] : 0,
            'price_modifier_type' => $data['price_modifier_type'] ?? 'fixed',
            'stock_quantity' => (int)($data['stock_quantity'] ?? 0),
            'is_available' => (int)($data['is_available'] ?? 1),
            'sort_order' => $sortOrder,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update color/texture
     */
    public function updateColor(int $colorId, array $data): bool
    {
        $allowedFields = [
            'name', 'color_code', 'ral_code', 'hex_color', 'texture_type',
            'finish_type', 'image_url', 'swatch_image', 'price_modifier',
            'price_modifier_type', 'stock_quantity', 'is_available', 'sort_order'
        ];

        $updateData = [];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            return false;
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->update('product_colors', $updateData, ['id' => $colorId]);
    }

    /**
     * Delete color/texture
     */
    public function deleteColor(int $colorId): bool
    {
        return $this->db->delete('product_colors', ['id' => $colorId]);
    }

    /**
     * Get color by ID
     */
    public function getColorById(int $colorId): ?array
    {
        return $this->db->fetch(
            "SELECT pc.*, p.name as product_name
             FROM product_colors pc
             LEFT JOIN products p ON pc.product_id = p.id
             WHERE pc.id = ?",
            [$colorId]
        );
    }

    /**
     * Search RAL colors
     */
    public function searchRALColors(string $query = '', int $limit = 50): array
    {
        $sql = "SELECT * FROM ral_colors WHERE 1=1";
        $params = [];

        if (!empty($query)) {
            $sql .= " AND (ral_code LIKE ? OR name LIKE ? OR hex_color LIKE ?)";
            $searchTerm = '%' . $query . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY ral_code ASC LIMIT ?";
        $params[] = $limit;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get RAL color by code
     */
    public function getRALColor(string $ralCode): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM ral_colors WHERE ral_code = ?",
            [$ralCode]
        );
    }

    /**
     * Import standard RAL colors (RAL Classic)
     */
    public function importStandardRALColors(): int
    {
        $ralColors = $this->getStandardRALColors();
        $count = 0;

        foreach ($ralColors as $ral) {
            // Check if already exists
            $existing = $this->getRALColor($ral['code']);
            if (!$existing) {
                $this->db->insert('ral_colors', [
                    'ral_code' => $ral['code'],
                    'name' => $ral['name'],
                    'hex_color' => $ral['hex'],
                    'rgb_r' => $ral['rgb'][0],
                    'rgb_g' => $ral['rgb'][1],
                    'rgb_b' => $ral['rgb'][2],
                    'category' => $ral['category'] ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get standard RAL colors (sample set)
     */
    private function getStandardRALColors(): array
    {
        return [
            // Yellows & Beiges
            ['code' => 'RAL 1000', 'name' => 'Green beige', 'hex' => '#BEBD7F', 'rgb' => [190, 189, 127], 'category' => 'Yellow & Beige'],
            ['code' => 'RAL 1001', 'name' => 'Beige', 'hex' => '#C2B078', 'rgb' => [194, 176, 120], 'category' => 'Yellow & Beige'],
            ['code' => 'RAL 1002', 'name' => 'Sand yellow', 'hex' => '#C6A664', 'rgb' => [198, 166, 100], 'category' => 'Yellow & Beige'],
            ['code' => 'RAL 1003', 'name' => 'Signal yellow', 'hex' => '#E5BE01', 'rgb' => [229, 190, 1], 'category' => 'Yellow & Beige'],
            ['code' => 'RAL 1004', 'name' => 'Golden yellow', 'hex' => '#CDA434', 'rgb' => [205, 164, 52], 'category' => 'Yellow & Beige'],

            // Oranges
            ['code' => 'RAL 2000', 'name' => 'Yellow orange', 'hex' => '#ED760E', 'rgb' => [237, 118, 14], 'category' => 'Orange'],
            ['code' => 'RAL 2001', 'name' => 'Red orange', 'hex' => '#C93C20', 'rgb' => [201, 60, 32], 'category' => 'Orange'],
            ['code' => 'RAL 2002', 'name' => 'Vermillion', 'hex' => '#CB2821', 'rgb' => [203, 40, 33], 'category' => 'Orange'],
            ['code' => 'RAL 2003', 'name' => 'Pastel orange', 'hex' => '#FF7514', 'rgb' => [255, 117, 20], 'category' => 'Orange'],
            ['code' => 'RAL 2004', 'name' => 'Pure orange', 'hex' => '#F44611', 'rgb' => [244, 70, 17], 'category' => 'Orange'],

            // Reds
            ['code' => 'RAL 3000', 'name' => 'Flame red', 'hex' => '#AF2B1E', 'rgb' => [175, 43, 30], 'category' => 'Red'],
            ['code' => 'RAL 3001', 'name' => 'Signal red', 'hex' => '#A52019', 'rgb' => [165, 32, 25], 'category' => 'Red'],
            ['code' => 'RAL 3002', 'name' => 'Carmine red', 'hex' => '#A2231D', 'rgb' => [162, 35, 29], 'category' => 'Red'],
            ['code' => 'RAL 3003', 'name' => 'Ruby red', 'hex' => '#9B111E', 'rgb' => [155, 17, 30], 'category' => 'Red'],
            ['code' => 'RAL 3004', 'name' => 'Purple red', 'hex' => '#75151E', 'rgb' => [117, 21, 30], 'category' => 'Red'],

            // Violets
            ['code' => 'RAL 4001', 'name' => 'Red lilac', 'hex' => '#6D3F5B', 'rgb' => [109, 63, 91], 'category' => 'Violet'],
            ['code' => 'RAL 4002', 'name' => 'Red violet', 'hex' => '#922B3E', 'rgb' => [146, 43, 62], 'category' => 'Violet'],
            ['code' => 'RAL 4003', 'name' => 'Heather violet', 'hex' => '#DE4C8A', 'rgb' => [222, 76, 138], 'category' => 'Violet'],
            ['code' => 'RAL 4004', 'name' => 'Claret violet', 'hex' => '#641C34', 'rgb' => [100, 28, 52], 'category' => 'Violet'],

            // Blues
            ['code' => 'RAL 5001', 'name' => 'Green blue', 'hex' => '#1F3438', 'rgb' => [31, 52, 56], 'category' => 'Blue'],
            ['code' => 'RAL 5002', 'name' => 'Ultramarine blue', 'hex' => '#20214F', 'rgb' => [32, 33, 79], 'category' => 'Blue'],
            ['code' => 'RAL 5003', 'name' => 'Sapphire blue', 'hex' => '#1D1E33', 'rgb' => [29, 30, 51], 'category' => 'Blue'],
            ['code' => 'RAL 5004', 'name' => 'Black blue', 'hex' => '#18171C', 'rgb' => [24, 23, 28], 'category' => 'Blue'],
            ['code' => 'RAL 5005', 'name' => 'Signal blue', 'hex' => '#1E2460', 'rgb' => [30, 36, 96], 'category' => 'Blue'],

            // Greens
            ['code' => 'RAL 6000', 'name' => 'Patina green', 'hex' => '#316650', 'rgb' => [49, 102, 80], 'category' => 'Green'],
            ['code' => 'RAL 6001', 'name' => 'Emerald green', 'hex' => '#287233', 'rgb' => [40, 114, 51], 'category' => 'Green'],
            ['code' => 'RAL 6002', 'name' => 'Leaf green', 'hex' => '#2D572C', 'rgb' => [45, 87, 44], 'category' => 'Green'],
            ['code' => 'RAL 6003', 'name' => 'Olive green', 'hex' => '#424632', 'rgb' => [66, 70, 50], 'category' => 'Green'],
            ['code' => 'RAL 6004', 'name' => 'Blue green', 'hex' => '#1F3A3D', 'rgb' => [31, 58, 61], 'category' => 'Green'],

            // Greys
            ['code' => 'RAL 7000', 'name' => 'Squirrel grey', 'hex' => '#78858B', 'rgb' => [120, 133, 139], 'category' => 'Grey'],
            ['code' => 'RAL 7001', 'name' => 'Silver grey', 'hex' => '#8A9597', 'rgb' => [138, 149, 151], 'category' => 'Grey'],
            ['code' => 'RAL 7002', 'name' => 'Olive grey', 'hex' => '#7E7B52', 'rgb' => [126, 123, 82], 'category' => 'Grey'],
            ['code' => 'RAL 7003', 'name' => 'Moss grey', 'hex' => '#6C7156', 'rgb' => [108, 113, 86], 'category' => 'Grey'],
            ['code' => 'RAL 7004', 'name' => 'Signal grey', 'hex' => '#969992', 'rgb' => [150, 153, 146], 'category' => 'Grey'],

            // Browns
            ['code' => 'RAL 8000', 'name' => 'Green brown', 'hex' => '#826C34', 'rgb' => [130, 108, 52], 'category' => 'Brown'],
            ['code' => 'RAL 8001', 'name' => 'Ochre brown', 'hex' => '#955F20', 'rgb' => [149, 95, 32], 'category' => 'Brown'],
            ['code' => 'RAL 8002', 'name' => 'Signal brown', 'hex' => '#6C3B2A', 'rgb' => [108, 59, 42], 'category' => 'Brown'],
            ['code' => 'RAL 8003', 'name' => 'Clay brown', 'hex' => '#734222', 'rgb' => [115, 66, 34], 'category' => 'Brown'],

            // Black & White
            ['code' => 'RAL 9001', 'name' => 'Cream', 'hex' => '#FDF4E3', 'rgb' => [253, 244, 227], 'category' => 'White'],
            ['code' => 'RAL 9002', 'name' => 'Grey white', 'hex' => '#E7EBDA', 'rgb' => [231, 235, 218], 'category' => 'White'],
            ['code' => 'RAL 9003', 'name' => 'Signal white', 'hex' => '#F4F4F4', 'rgb' => [244, 244, 244], 'category' => 'White'],
            ['code' => 'RAL 9004', 'name' => 'Signal black', 'hex' => '#282828', 'rgb' => [40, 40, 40], 'category' => 'Black'],
            ['code' => 'RAL 9005', 'name' => 'Jet black', 'hex' => '#0A0A0A', 'rgb' => [10, 10, 10], 'category' => 'Black'],
            ['code' => 'RAL 9010', 'name' => 'Pure white', 'hex' => '#FFFFFF', 'rgb' => [255, 255, 255], 'category' => 'White'],
            ['code' => 'RAL 9011', 'name' => 'Graphite black', 'hex' => '#1C1C1C', 'rgb' => [28, 28, 28], 'category' => 'Black'],
        ];
    }

    /**
     * Get texture types
     */
    public static function getTextureTypes(): array
    {
        return [
            'smooth' => 'Pürüzsüz',
            'matte' => 'Mat',
            'glossy' => 'Parlak',
            'satin' => 'Saten',
            'textured' => 'Dokulu',
            'embossed' => 'Kabartmalı',
            'brushed' => 'Fırçalanmış',
            'hammered' => 'Dövme',
            'wood_grain' => 'Ahşap Doku',
            'stone' => 'Taş Doku',
            'fabric' => 'Kumaş Doku',
        ];
    }

    /**
     * Get finish types
     */
    public static function getFinishTypes(): array
    {
        return [
            'matte' => 'Mat',
            'semi_matte' => 'Yarı Mat',
            'satin' => 'Saten',
            'semi_gloss' => 'Yarı Parlak',
            'high_gloss' => 'Yüksek Parlak',
            'metallic' => 'Metalik',
            'pearl' => 'İnci',
            'chrome' => 'Krom',
            'powder_coated' => 'Elektrostatik Boyalı',
        ];
    }

    /**
     * Calculate price with modifier
     */
    public function calculateColorPrice(float $basePrice, array $color): float
    {
        if (empty($color['price_modifier']) || $color['price_modifier'] == 0) {
            return $basePrice;
        }

        $modifier = (float)$color['price_modifier'];
        $type = $color['price_modifier_type'] ?? 'fixed';

        if ($type === 'percent') {
            return $basePrice * (1 + $modifier / 100);
        } else {
            return $basePrice + $modifier;
        }
    }
}
