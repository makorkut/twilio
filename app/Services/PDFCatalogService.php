<?php

namespace App\Services;

use App\Core\Database;

class PDFCatalogService
{
    private Database $db;
    private string $catalogsPath;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->catalogsPath = $_ENV['CATALOGS_PATH'] ?? 'storage/catalogs';

        if (!is_dir($this->catalogsPath)) {
            mkdir($this->catalogsPath, 0755, true);
        }
    }

    /**
     * Generate PDF catalog from products
     */
    public function generateCatalog(array $options = []): string
    {
        $productIds = $options['product_ids'] ?? [];
        $categoryId = $options['category_id'] ?? null;
        $title = $options['title'] ?? 'Ürün Kataloğu';
        $includeImages = $options['include_images'] ?? true;
        $includePrices = $options['include_prices'] ?? true;
        $layout = $options['layout'] ?? 'grid'; // grid, list, detailed

        // Get products
        if (!empty($productIds)) {
            $products = $this->getProductsByIds($productIds);
        } elseif ($categoryId) {
            $products = $this->getProductsByCategory($categoryId);
        } else {
            $products = $this->getAllActiveProducts();
        }

        if (empty($products)) {
            throw new \RuntimeException('No products found for catalog');
        }

        // Generate HTML catalog
        $html = $this->generateCatalogHTML($products, $title, $layout, $includeImages, $includePrices);

        // Save HTML to file
        $filename = 'catalog_' . date('Y-m-d_His') . '.html';
        $filepath = $this->catalogsPath . '/' . $filename;
        file_put_contents($filepath, $html);

        // Save catalog metadata
        $catalogId = $this->db->insert('catalogs', [
            'title' => $title,
            'filename' => $filename,
            'filepath' => $filepath,
            'product_count' => count($products),
            'layout' => $layout,
            'include_images' => (int)$includeImages,
            'include_prices' => (int)$includePrices,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $filepath;
    }

    /**
     * Generate catalog HTML
     */
    private function generateCatalogHTML(array $products, string $title, string $layout, bool $includeImages, bool $includePrices): string
    {
        $companyName = $_ENV['COMPANY_NAME'] ?? 'E-Commerce';
        $companyLogo = $_ENV['COMPANY_LOGO'] ?? '';
        $companyInfo = $_ENV['COMPANY_INFO'] ?? '';

        $html = '<!DOCTYPE html><html lang="tr"><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $html .= '<title>' . htmlspecialchars($title) . '</title>';
        $html .= $this->getCatalogCSS($layout);
        $html .= '</head><body>';

        // Header
        $html .= '<div class="catalog-header">';
        if ($companyLogo) {
            $html .= '<img src="' . htmlspecialchars($companyLogo) . '" alt="' . htmlspecialchars($companyName) . '" class="company-logo">';
        }
        $html .= '<h1>' . htmlspecialchars($title) . '</h1>';
        $html .= '<p class="date">Tarih: ' . date('d.m.Y') . '</p>';
        $html .= '</div>';

        // Products
        $html .= '<div class="products-' . $layout . '">';

        foreach ($products as $product) {
            $html .= $this->generateProductHTML($product, $layout, $includeImages, $includePrices);
        }

        $html .= '</div>';

        // Footer
        $html .= '<div class="catalog-footer">';
        $html .= '<div class="company-info">';
        if ($companyInfo) {
            $html .= '<p>' . nl2br(htmlspecialchars($companyInfo)) . '</p>';
        }
        $html .= '</div>';
        $html .= '<div class="page-info">';
        $html .= '<p>Toplam ' . count($products) . ' ürün</p>';
        $html .= '<p>' . htmlspecialchars($companyName) . ' - ' . date('Y') . '</p>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Generate single product HTML
     */
    private function generateProductHTML(array $product, string $layout, bool $includeImages, bool $includePrices): string
    {
        $html = '<div class="product-item">';

        if ($layout === 'grid') {
            // Grid layout - compact cards
            if ($includeImages && !empty($product['image'])) {
                $html .= '<div class="product-image">';
                $html .= '<img src="' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">';
                $html .= '</div>';
            }
            $html .= '<div class="product-info">';
            $html .= '<h3>' . htmlspecialchars($product['name']) . '</h3>';
            $html .= '<p class="sku">SKU: ' . htmlspecialchars($product['sku']) . '</p>';
            if ($includePrices) {
                $html .= '<p class="price">₺' . number_format($product['base_price'], 2) . '</p>';
            }
            $html .= '</div>';

        } elseif ($layout === 'list') {
            // List layout - horizontal rows
            $html .= '<div class="product-row">';
            if ($includeImages && !empty($product['image'])) {
                $html .= '<div class="product-image-small">';
                $html .= '<img src="' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">';
                $html .= '</div>';
            }
            $html .= '<div class="product-details">';
            $html .= '<h4>' . htmlspecialchars($product['name']) . '</h4>';
            $html .= '<p class="sku">SKU: ' . htmlspecialchars($product['sku']) . '</p>';
            if ($product['short_description']) {
                $html .= '<p class="description">' . htmlspecialchars($product['short_description']) . '</p>';
            }
            $html .= '</div>';
            if ($includePrices) {
                $html .= '<div class="product-price-col">';
                $html .= '<p class="price">₺' . number_format($product['base_price'], 2) . '</p>';
                $html .= '</div>';
            }
            $html .= '</div>';

        } else { // detailed
            // Detailed layout - full product info
            if ($includeImages && !empty($product['image'])) {
                $html .= '<div class="product-image-large">';
                $html .= '<img src="' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">';
                $html .= '</div>';
            }
            $html .= '<div class="product-content">';
            $html .= '<h2>' . htmlspecialchars($product['name']) . '</h2>';
            $html .= '<p class="sku">SKU: ' . htmlspecialchars($product['sku']) . '</p>';
            if ($product['description']) {
                $html .= '<div class="description">' . nl2br(htmlspecialchars($product['description'])) . '</div>';
            }

            // Specifications
            if ($product['length_mm'] || $product['width_mm'] || $product['weight_kg']) {
                $html .= '<div class="specifications">';
                $html .= '<h4>Teknik Özellikler</h4>';
                $html .= '<ul>';
                if ($product['length_mm']) $html .= '<li>Uzunluk: ' . $product['length_mm'] . ' mm</li>';
                if ($product['width_mm']) $html .= '<li>Genişlik: ' . $product['width_mm'] . ' mm</li>';
                if ($product['height_mm']) $html .= '<li>Yükseklik: ' . $product['height_mm'] . ' mm</li>';
                if ($product['weight_kg']) $html .= '<li>Ağırlık: ' . $product['weight_kg'] . ' kg</li>';
                $html .= '</ul>';
                $html .= '</div>';
            }

            if ($includePrices) {
                $html .= '<div class="price-section">';
                $html .= '<p class="price-label">Fiyat:</p>';
                $html .= '<p class="price-large">₺' . number_format($product['base_price'], 2) . '</p>';
                if ($product['compare_price']) {
                    $html .= '<p class="compare-price">₺' . number_format($product['compare_price'], 2) . '</p>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Get catalog CSS based on layout
     */
    private function getCatalogCSS(string $layout): string
    {
        $css = '<style>';
        $css .= '* { margin: 0; padding: 0; box-sizing: border-box; }';
        $css .= 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; line-height: 1.6; color: #333; }';
        $css .= 'img { max-width: 100%; height: auto; display: block; }';

        // Header styles
        $css .= '.catalog-header { text-align: center; padding: 40px 20px; border-bottom: 3px solid #000; margin-bottom: 40px; }';
        $css .= '.company-logo { max-width: 200px; margin: 0 auto 20px; }';
        $css .= '.catalog-header h1 { font-size: 36px; font-weight: 300; margin-bottom: 10px; }';
        $css .= '.date { color: #666; font-size: 14px; }';

        // Footer styles
        $css .= '.catalog-footer { margin-top: 60px; padding: 30px 20px; border-top: 2px solid #ddd; background: #f8f8f8; }';
        $css .= '.company-info { margin-bottom: 20px; font-size: 13px; color: #666; }';
        $css .= '.page-info { text-align: center; font-size: 12px; color: #999; }';

        // Product styles based on layout
        if ($layout === 'grid') {
            $css .= '.products-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; padding: 20px; }';
            $css .= '.product-item { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; break-inside: avoid; }';
            $css .= '.product-image { background: #f5f5f5; height: 200px; display: flex; align-items: center; justify-content: center; }';
            $css .= '.product-image img { max-height: 180px; object-fit: contain; }';
            $css .= '.product-info { padding: 15px; }';
            $css .= '.product-info h3 { font-size: 16px; margin-bottom: 8px; }';
            $css .= '.sku { font-size: 12px; color: #666; margin-bottom: 8px; }';
            $css .= '.price { font-size: 20px; font-weight: bold; color: #2c5aa0; }';

        } elseif ($layout === 'list') {
            $css .= '.products-list { padding: 20px; }';
            $css .= '.product-item { border-bottom: 1px solid #ddd; padding: 20px 0; break-inside: avoid; }';
            $css .= '.product-row { display: flex; gap: 20px; align-items: center; }';
            $css .= '.product-image-small { flex-shrink: 0; width: 80px; height: 80px; background: #f5f5f5; border-radius: 8px; overflow: hidden; }';
            $css .= '.product-image-small img { width: 100%; height: 100%; object-fit: cover; }';
            $css .= '.product-details { flex: 1; }';
            $css .= '.product-details h4 { font-size: 18px; margin-bottom: 4px; }';
            $css .= '.description { font-size: 13px; color: #666; margin-top: 8px; }';
            $css .= '.product-price-col { flex-shrink: 0; text-align: right; }';
            $css .= '.price { font-size: 24px; font-weight: bold; color: #2c5aa0; }';

        } else { // detailed
            $css .= '.products-detailed { padding: 20px; }';
            $css .= '.product-item { border: 1px solid #ddd; border-radius: 12px; padding: 30px; margin-bottom: 40px; break-inside: avoid; }';
            $css .= '.product-image-large { width: 100%; max-width: 400px; margin: 0 auto 20px; }';
            $css .= '.product-content h2 { font-size: 28px; margin-bottom: 10px; }';
            $css .= '.description { margin: 20px 0; font-size: 14px; line-height: 1.8; }';
            $css .= '.specifications { margin: 20px 0; padding: 15px; background: #f8f8f8; border-radius: 8px; }';
            $css .= '.specifications h4 { font-size: 16px; margin-bottom: 10px; }';
            $css .= '.specifications ul { list-style-position: inside; }';
            $css .= '.specifications li { padding: 4px 0; }';
            $css .= '.price-section { margin-top: 20px; padding-top: 20px; border-top: 2px solid #ddd; }';
            $css .= '.price-label { font-size: 14px; color: #666; margin-bottom: 5px; }';
            $css .= '.price-large { font-size: 32px; font-weight: bold; color: #2c5aa0; }';
            $css .= '.compare-price { font-size: 18px; color: #999; text-decoration: line-through; }';
        }

        // Print styles
        $css .= '@media print {';
        $css .= '  body { margin: 0; }';
        $css .= '  .product-item { page-break-inside: avoid; }';
        $css .= '  .catalog-footer { page-break-before: always; }';
        $css .= '}';

        $css .= '</style>';

        return $css;
    }

    /**
     * Get products by IDs
     */
    private function getProductsByIds(array $ids): array
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        return $this->db->fetchAll(
            "SELECT * FROM products WHERE id IN ($placeholders) AND status = 'active' ORDER BY name",
            $ids
        );
    }

    /**
     * Get products by category
     */
    private function getProductsByCategory(int $categoryId): array
    {
        return $this->db->fetchAll(
            "SELECT p.* FROM products p
             INNER JOIN product_categories pc ON p.id = pc.product_id
             WHERE pc.category_id = ? AND p.status = 'active'
             ORDER BY p.name",
            [$categoryId]
        );
    }

    /**
     * Get all active products
     */
    private function getAllActiveProducts(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM products WHERE status = 'active' ORDER BY name"
        );
    }

    /**
     * Get all catalogs
     */
    public function getAllCatalogs(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM catalogs ORDER BY created_at DESC"
        );
    }

    /**
     * Delete catalog
     */
    public function delete(int $catalogId): bool
    {
        $catalog = $this->db->fetchOne(
            "SELECT * FROM catalogs WHERE id = ?",
            [$catalogId]
        );

        if (!$catalog) {
            return false;
        }

        // Delete file
        if (file_exists($catalog['filepath'])) {
            unlink($catalog['filepath']);
        }

        // Delete from database
        return $this->db->delete('catalogs', ['id' => $catalogId]);
    }
}
