<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Services\ProductService;
use App\Services\MediaService;
use App\Services\PricingService;
use App\Http\Request;
use App\Http\Response;

class ProductController
{
    protected Database $db;
    protected ProductService $productService;
    protected MediaService $mediaService;
    protected PricingService $pricingService;

    public function __construct(
        Database $db,
        ProductService $productService,
        MediaService $mediaService,
        PricingService $pricingService
    ) {
        $this->db = $db;
        $this->productService = $productService;
        $this->mediaService = $mediaService;
        $this->pricingService = $pricingService;
    }

    /**
     * List all products
     */
    public function index(Request $request): Response
    {
        $page = (int) ($request->get('page') ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        // Filters
        $status = $request->get('status');
        $search = $request->get('search');
        $categoryId = $request->get('category_id');

        // Build query
        $where = [];
        $params = [];

        if ($status) {
            $where[] = 'p.status = ?';
            $params[] = $status;
        }

        if ($search) {
            $where[] = '(p.sku LIKE ? OR pl.name LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($categoryId) {
            $where[] = 'p.category_id = ?';
            $params[] = $categoryId;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get products
        $query = "SELECT p.*, pl.name, pl.slug, c.name as category_name
                  FROM products p
                  LEFT JOIN product_lang pl ON pl.product_id = p.id AND pl.lang = ?
                  LEFT JOIN categories c ON c.id = p.category_id
                  {$whereClause}
                  ORDER BY p.id DESC
                  LIMIT ? OFFSET ?";

        $langParams = [env('DEFAULT_LANG', 'tr')];
        $finalParams = array_merge($langParams, $params, [$perPage, $offset]);

        $products = $this->db->fetchAll($query, $finalParams);

        // Get total count
        $countQuery = "SELECT COUNT(DISTINCT p.id) as total
                       FROM products p
                       LEFT JOIN product_lang pl ON pl.product_id = p.id AND pl.lang = ?
                       {$whereClause}";

        $countParams = array_merge($langParams, $params);
        $countResult = $this->db->fetch($countQuery, $countParams);
        $total = $countResult['total'] ?? 0;

        return Response::json([
            'success' => true,
            'data' => $products,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'pages' => ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Get single product
     */
    public function show(Request $request, int $id): Response
    {
        $product = $this->productService->getWithTranslations($id);

        if (!$product) {
            return Response::json(['error' => 'Product not found'], 404);
        }

        // Get prices
        $product = $this->productService->getWithPrices($id);

        // Get media
        $product['media'] = $this->mediaService->getByEntity('product', $id);

        return Response::json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Create new product
     */
    public function store(Request $request): Response
    {
        $data = $request->all();

        try {
            $productId = $this->productService->create($data);

            return Response::json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => ['id' => $productId],
            ], 201);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update product
     */
    public function update(Request $request, int $id): Response
    {
        $data = $request->all();

        try {
            $this->productService->update($id, $data);

            return Response::json([
                'success' => true,
                'message' => 'Product updated successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete product
     */
    public function delete(Request $request, int $id): Response
    {
        try {
            $this->productService->delete($id);

            return Response::json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Bulk import products
     */
    public function bulkImport(Request $request): Response
    {
        $products = $request->get('products', []);
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($products as $productData) {
            try {
                $this->productService->create($productData);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'sku' => $productData['sku'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return Response::json([
            'success' => true,
            'message' => "Import completed: {$results['success']} successful, {$results['failed']} failed",
            'data' => $results,
        ]);
    }

    /**
     * Attach media to product
     */
    public function attachMedia(Request $request, int $id): Response
    {
        $mediaId = (int) $request->get('media_id');
        $usageType = $request->get('usage_type', 'gallery');
        $sortOrder = (int) ($request->get('sort_order') ?? 0);

        try {
            $this->mediaService->attachToEntity($mediaId, 'product', $id, $usageType, $sortOrder);

            return Response::json([
                'success' => true,
                'message' => 'Media attached successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Detach media from product
     */
    public function detachMedia(Request $request, int $id): Response
    {
        $mediaId = (int) $request->get('media_id');
        $usageType = $request->get('usage_type');

        try {
            $this->mediaService->detachFromEntity($mediaId, 'product', $id, $usageType);

            return Response::json([
                'success' => true,
                'message' => 'Media detached successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get product statistics
     */
    public function statistics(Request $request): Response
    {
        $stats = $this->db->fetch(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN stock_status = 'out_of_stock' THEN 1 ELSE 0 END) as out_of_stock,
                SUM(CASE WHEN sync_status = 'pending' THEN 1 ELSE 0 END) as pending_sync
             FROM products
             WHERE deleted_at IS NULL"
        );

        return Response::json([
            'success' => true,
            'data' => $stats ?? [],
        ]);
    }

    /**
     * Update product prices (multi-currency)
     */
    public function updatePrices(Request $request, int $id): Response
    {
        $prices = $request->get('prices', []);

        try {
            foreach ($prices as $currencyCode => $priceData) {
                $this->db->query(
                    "INSERT INTO product_prices_currency
                        (product_id, currency_code, base_price, compare_at_price, cost_price,
                         tier_1_min_qty, tier_1_price, tier_2_min_qty, tier_2_price, tier_3_min_qty, tier_3_price)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        base_price = VALUES(base_price),
                        compare_at_price = VALUES(compare_at_price),
                        cost_price = VALUES(cost_price),
                        tier_1_min_qty = VALUES(tier_1_min_qty),
                        tier_1_price = VALUES(tier_1_price),
                        tier_2_min_qty = VALUES(tier_2_min_qty),
                        tier_2_price = VALUES(tier_2_price),
                        tier_3_min_qty = VALUES(tier_3_min_qty),
                        tier_3_price = VALUES(tier_3_price)",
                    [
                        $id,
                        $currencyCode,
                        $priceData['base_price'],
                        $priceData['compare_at_price'] ?? null,
                        $priceData['cost_price'] ?? null,
                        $priceData['tier_1_min_qty'] ?? null,
                        $priceData['tier_1_price'] ?? null,
                        $priceData['tier_2_min_qty'] ?? null,
                        $priceData['tier_2_price'] ?? null,
                        $priceData['tier_3_min_qty'] ?? null,
                        $priceData['tier_3_price'] ?? null,
                    ]
                );
            }

            return Response::json([
                'success' => true,
                'message' => 'Prices updated successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get pending sync products
     */
    public function pendingSync(Request $request): Response
    {
        $products = $this->productService->getPendingSync(100);

        return Response::json([
            'success' => true,
            'data' => $products,
            'count' => count($products),
        ]);
    }

    /**
     * Duplicate product
     */
    public function duplicate(Request $request, int $id): Response
    {
        try {
            $product = $this->productService->getWithTranslations($id);

            if (!$product) {
                return Response::json(['error' => 'Product not found'], 404);
            }

            // Remove ID and update SKU
            unset($product['id']);
            $product['sku'] = $product['sku'] . '-copy';
            $product['status'] = 'draft';

            // Get prices
            $originalPrices = $this->productService->getWithPrices($id);
            $product['prices'] = $originalPrices['prices'] ?? [];

            // Create duplicate
            $newProductId = $this->productService->create($product);

            // Copy media relations
            $media = $this->mediaService->getByEntity('product', $id);
            foreach ($media as $m) {
                $this->mediaService->attachToEntity(
                    (int) $m['id'],
                    'product',
                    $newProductId,
                    $m['usage_type'],
                    (int) $m['sort_order']
                );
            }

            return Response::json([
                'success' => true,
                'message' => 'Product duplicated successfully',
                'data' => ['id' => $newProductId],
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
