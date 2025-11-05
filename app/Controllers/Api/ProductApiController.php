<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Database;
use App\Services\ProductService;
use App\Services\MediaService;
use App\Services\PricingService;
use App\Services\I18nService;
use App\Http\Request;
use App\Http\Response;

class ProductApiController
{
    protected Database $db;
    protected ProductService $productService;
    protected MediaService $mediaService;
    protected PricingService $pricingService;
    protected I18nService $i18n;

    public function __construct(
        Database $db,
        ProductService $productService,
        MediaService $mediaService,
        PricingService $pricingService,
        I18nService $i18n
    ) {
        $this->db = $db;
        $this->productService = $productService;
        $this->mediaService = $mediaService;
        $this->pricingService = $pricingService;
        $this->i18n = $i18n;
    }

    public function index(Request $request): Response
    {
        $page = (int) ($request->get('page') ?? 1);
        $perPage = (int) ($request->get('per_page') ?? 20);
        $offset = ($page - 1) * $perPage;

        $lang = $this->i18n->getCurrentLanguage();
        $currency = $request->get('currency', env('DEFAULT_CURRENCY', 'TRY'));

        // Filters
        $categoryId = $request->get('category_id');
        $brandId = $request->get('brand_id');
        $search = $request->get('search');

        $where = ['p.status = ?', 'p.deleted_at IS NULL'];
        $params = ['active'];

        if ($categoryId) {
            $where[] = 'p.category_id = ?';
            $params[] = $categoryId;
        }

        if ($brandId) {
            $where[] = 'p.brand_id = ?';
            $params[] = $brandId;
        }

        if ($search) {
            $where[] = '(p.sku LIKE ? OR pl.name LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $products = $this->db->fetchAll(
            "SELECT p.*, pl.name, pl.slug, pl.short_description,
                    ppc.base_price, ppc.compare_at_price, ppc.currency_code
             FROM products p
             LEFT JOIN product_lang pl ON pl.product_id = p.id AND pl.lang = ?
             LEFT JOIN product_prices_currency ppc ON ppc.product_id = p.id AND ppc.currency_code = ?
             {$whereClause}
             ORDER BY p.id DESC
             LIMIT ? OFFSET ?",
            array_merge([$lang, $currency], $params, [$perPage, $offset])
        );

        // Get total
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total
             FROM products p
             LEFT JOIN product_lang pl ON pl.product_id = p.id AND pl.lang = ?
             {$whereClause}",
            array_merge([$lang], $params)
        );
        $total = $result['total'];

        // Add media to each product
        foreach ($products as &$product) {
            $media = $this->mediaService->getByEntity('product', (int) $product['id'], 'gallery');
            $product['images'] = array_map(function($m) {
                return [
                    'id' => $m['id'],
                    'url' => $this->mediaService->getUrl($m),
                    'thumbnail' => $this->mediaService->getThumbnailUrl($m),
                    'alt' => $m['alt_text'] ?? '',
                ];
            }, $media);
        }

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

    public function show(Request $request, string $slug): Response
    {
        $lang = $this->i18n->getCurrentLanguage();
        $currency = $request->get('currency', env('DEFAULT_CURRENCY', 'TRY'));

        $product = $this->db->fetch(
            "SELECT p.*, pl.name, pl.slug, pl.short_description, pl.description,
                    pl.technical_specs, pl.application_areas,
                    ppc.base_price, ppc.compare_at_price, ppc.currency_code,
                    ppc.tier_1_min_qty, ppc.tier_1_price,
                    ppc.tier_2_min_qty, ppc.tier_2_price,
                    ppc.tier_3_min_qty, ppc.tier_3_price
             FROM products p
             INNER JOIN product_lang pl ON pl.product_id = p.id AND pl.lang = ?
             LEFT JOIN product_prices_currency ppc ON ppc.product_id = p.id AND ppc.currency_code = ?
             WHERE pl.slug = ? AND p.status = 'active' AND p.deleted_at IS NULL
             LIMIT 1",
            [$lang, $currency, $slug]
        );

        if (!$product) {
            return Response::json(['error' => 'Product not found'], 404);
        }

        // Get media
        $media = $this->mediaService->getByEntity('product', (int) $product['id']);
        $product['images'] = array_map(function($m) {
            return [
                'id' => $m['id'],
                'url' => $this->mediaService->getUrl($m),
                'thumbnail' => $this->mediaService->getThumbnailUrl($m),
                'alt' => $m['alt_text'] ?? '',
                'usage_type' => $m['usage_type'],
            ];
        }, $media);

        // Track view
        $this->db->insert('product_views', [
            'product_id' => $product['id'],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'viewed_at' => date('Y-m-d H:i:s'),
        ]);

        return Response::json(['success' => true, 'data' => $product]);
    }

    public function calculatePrice(Request $request, int $id): Response
    {
        $currency = $request->get('currency', env('DEFAULT_CURRENCY', 'TRY'));
        $quantity = (int) ($request->get('quantity') ?? 1);
        $userId = null; // TODO: Get from auth

        $price = $this->pricingService->calculatePrice($id, $currency, $quantity, $userId);

        if (!$price) {
            return Response::json(['error' => 'Price not found'], 404);
        }

        return Response::json(['success' => true, 'data' => $price]);
    }
}
