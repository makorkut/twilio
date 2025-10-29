<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Database;
use App\Services\ProductService;
use App\Services\MediaService;
use App\Services\PricingService;
use App\Services\I18nService;
use App\Http\Request;
use App\Http\Response;

class ProductFrontendController
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

    public function category(Request $request, string $categorySlug): Response
    {
        $lang = $this->i18n->getCurrentLanguage();

        // Get category
        $category = $this->db->query(
            "SELECT c.*, cl.name, cl.description
             FROM categories c
             INNER JOIN category_lang cl ON cl.category_id = c.id AND cl.lang = ?
             WHERE cl.slug = ? AND c.status = 'active'
             LIMIT 1",
            [$lang, $categorySlug]
        )[0] ?? null;

        if (!$category) {
            return Response::html('404', ['message' => 'Category not found'], 404);
        }

        // Get products
        $page = (int) ($request->get('page') ?? 1);
        $perPage = 24;
        $offset = ($page - 1) * $perPage;

        $products = $this->db->query(
            "SELECT p.*, pl.name, pl.slug
             FROM products p
             INNER JOIN product_lang pl ON pl.product_id = p.id AND pl.lang = ?
             WHERE p.category_id = ? AND p.status = 'active'
             ORDER BY p.sort_order ASC, p.id DESC
             LIMIT ? OFFSET ?",
            [$lang, $category['id'], $perPage, $offset]
        );

        // Add images
        foreach ($products as &$product) {
            $media = $this->mediaService->getByEntity('product', (int) $product['id'], 'gallery');
            $product['image'] = !empty($media) ? $this->mediaService->getThumbnailUrl($media[0]) : null;
        }

        return Response::html('frontend/category', [
            'category' => $category,
            'products' => $products,
            'page' => $page,
        ]);
    }

    public function show(Request $request, string $categorySlug, string $productSlug): Response
    {
        $lang = $this->i18n->getCurrentLanguage();

        // Get product
        $product = $this->db->query(
            "SELECT p.*, pl.name, pl.slug, pl.description, pl.short_description,
                    pl.technical_specs, pl.application_areas
             FROM products p
             INNER JOIN product_lang pl ON pl.product_id = p.id AND pl.lang = ?
             WHERE pl.slug = ? AND p.status = 'active'
             LIMIT 1",
            [$lang, $productSlug]
        )[0] ?? null;

        if (!$product) {
            return Response::html('404', ['message' => 'Product not found'], 404);
        }

        // Get images
        $media = $this->mediaService->getByEntity('product', (int) $product['id']);
        $product['images'] = array_map(function($m) {
            return [
                'url' => $this->mediaService->getUrl($m),
                'thumbnail' => $this->mediaService->getThumbnailUrl($m),
            ];
        }, $media);

        // Track view
        $this->db->insert('product_views', [
            'product_id' => $product['id'],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'viewed_at' => date('Y-m-d H:i:s'),
        ]);

        return Response::html('frontend/product', ['product' => $product]);
    }
}
