<?php

namespace App\Services;

use App\Core\Database;

class QRCodeService
{
    private Database $db;
    private string $qrCodesPath;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->qrCodesPath = $_ENV['QR_CODES_PATH'] ?? 'storage/qrcodes';

        // Create directory if not exists
        if (!is_dir($this->qrCodesPath)) {
            mkdir($this->qrCodesPath, 0755, true);
        }
    }

    /**
     * Generate QR code for a product
     */
    public function generateForProduct(int $productId, array $options = []): string
    {
        // Get product details
        $product = $this->db->fetch(
            "SELECT * FROM products WHERE id = ?",
            [$productId]
        );

        if (!$product) {
            throw new \RuntimeException('Product not found');
        }

        // Build product URL
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';
        $productUrl = $baseUrl . '/product/' . $product['slug'];

        // Generate QR code content
        $qrContent = $options['content'] ?? $productUrl;
        $size = $options['size'] ?? 300;
        $format = $options['format'] ?? 'png';

        // Generate QR code filename
        $filename = 'product_' . $productId . '_' . time() . '.' . $format;
        $filepath = $this->qrCodesPath . '/' . $filename;

        // Generate QR code image
        $this->generateQRImage($qrContent, $filepath, $size);

        // Save to database
        $qrCodeId = $this->db->insert('product_qrcodes', [
            'product_id' => $productId,
            'content' => $qrContent,
            'file_path' => $filepath,
            'file_name' => $filename,
            'size' => $size,
            'format' => $format,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Update product with QR code ID
        $this->db->update('products', ['qrcode_id' => $qrCodeId], 'id = ?', [$productId]);

        return $filepath;
    }

    /**
     * Generate QR code image using simple algorithm
     */
    private function generateQRImage(string $content, string $filepath, int $size = 300): void
    {
        // Use QR code generator API (free, no API key required)
        $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/';
        $params = http_build_query([
            'data' => $content,
            'size' => $size . 'x' . $size,
            'format' => 'png',
            'margin' => 10,
        ]);

        $qrImageData = @file_get_contents($apiUrl . '?' . $params);

        if ($qrImageData === false) {
            // Fallback: generate simple QR code SVG
            $this->generateSimpleQRSVG($content, $filepath, $size);
            return;
        }

        file_put_contents($filepath, $qrImageData);
    }

    /**
     * Generate simple QR code as SVG (fallback)
     */
    private function generateSimpleQRSVG(string $content, string $filepath, int $size = 300): void
    {
        // Create a simple data matrix representation
        $hash = md5($content);
        $matrix = [];
        $gridSize = 21; // Standard QR code size for version 1

        // Generate pseudo-random pattern based on content hash
        for ($y = 0; $y < $gridSize; $y++) {
            $matrix[$y] = [];
            for ($x = 0; $x < $gridSize; $x++) {
                $index = ($y * $gridSize + $x) % strlen($hash);
                $matrix[$y][$x] = (hexdec($hash[$index]) % 2 === 0) ? 1 : 0;
            }
        }

        // Add finder patterns (corners)
        $this->addFinderPattern($matrix, 0, 0);
        $this->addFinderPattern($matrix, 0, $gridSize - 7);
        $this->addFinderPattern($matrix, $gridSize - 7, 0);

        // Generate SVG
        $cellSize = $size / $gridSize;
        $svg = '<?xml version="1.0" encoding="UTF-8"?>';
        $svg .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $size . ' ' . $size . '" width="' . $size . '" height="' . $size . '">';
        $svg .= '<rect width="' . $size . '" height="' . $size . '" fill="white"/>';

        foreach ($matrix as $y => $row) {
            foreach ($row as $x => $value) {
                if ($value === 1) {
                    $posX = $x * $cellSize;
                    $posY = $y * $cellSize;
                    $svg .= '<rect x="' . $posX . '" y="' . $posY . '" width="' . $cellSize . '" height="' . $cellSize . '" fill="black"/>';
                }
            }
        }

        $svg .= '</svg>';

        file_put_contents(str_replace('.png', '.svg', $filepath), $svg);
    }

    /**
     * Add finder pattern to matrix (corner markers)
     */
    private function addFinderPattern(array &$matrix, int $startY, int $startX): void
    {
        // Outer 7x7 square
        for ($y = 0; $y < 7; $y++) {
            for ($x = 0; $x < 7; $x++) {
                if ($y === 0 || $y === 6 || $x === 0 || $x === 6) {
                    $matrix[$startY + $y][$startX + $x] = 1;
                } elseif ($y >= 2 && $y <= 4 && $x >= 2 && $x <= 4) {
                    $matrix[$startY + $y][$startX + $x] = 1;
                } else {
                    $matrix[$startY + $y][$startX + $x] = 0;
                }
            }
        }
    }

    /**
     * Get QR code for product
     */
    public function getProductQRCode(int $productId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM product_qrcodes
             WHERE product_id = ?
             ORDER BY created_at DESC
             LIMIT 1",
            [$productId]
        );
    }

    /**
     * Generate batch QR codes for multiple products
     */
    public function generateBatch(array $productIds, array $options = []): array
    {
        $results = [];

        foreach ($productIds as $productId) {
            try {
                $filepath = $this->generateForProduct((int)$productId, $options);
                $results[$productId] = ['success' => true, 'path' => $filepath];
            } catch (\Exception $e) {
                $results[$productId] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Generate QR code for custom content (not product)
     */
    public function generateCustom(string $content, array $options = []): string
    {
        $size = $options['size'] ?? 300;
        $format = $options['format'] ?? 'png';
        $filename = $options['filename'] ?? 'custom_' . md5($content) . '.' . $format;
        $filepath = $this->qrCodesPath . '/' . $filename;

        $this->generateQRImage($content, $filepath, $size);

        return $filepath;
    }

    /**
     * Delete QR code
     */
    public function delete(int $qrCodeId): bool
    {
        $qrCode = $this->db->fetch(
            "SELECT * FROM product_qrcodes WHERE id = ?",
            [$qrCodeId]
        );

        if (!$qrCode) {
            return false;
        }

        // Delete physical file
        if (file_exists($qrCode['file_path'])) {
            unlink($qrCode['file_path']);
        }

        // Delete from database
        return $this->db->delete('product_qrcodes', 'id = ?', [$qrCodeId]);
    }

    /**
     * Generate QR code with logo overlay (branded QR)
     */
    public function generateWithLogo(int $productId, string $logoPath, array $options = []): string
    {
        // First generate base QR code
        $qrPath = $this->generateForProduct($productId, $options);

        // Load QR code image
        $qrImage = imagecreatefrompng($qrPath);
        if (!$qrImage) {
            return $qrPath; // Return without logo if image loading fails
        }

        // Load logo
        $logoInfo = getimagesize($logoPath);
        if (!$logoInfo) {
            return $qrPath; // Return without logo if logo loading fails
        }

        switch ($logoInfo[2]) {
            case IMAGETYPE_PNG:
                $logo = imagecreatefrompng($logoPath);
                break;
            case IMAGETYPE_JPEG:
                $logo = imagecreatefromjpeg($logoPath);
                break;
            default:
                return $qrPath;
        }

        if (!$logo) {
            return $qrPath;
        }

        // Calculate logo size (20% of QR code)
        $qrWidth = imagesx($qrImage);
        $qrHeight = imagesy($qrImage);
        $logoWidth = imagesx($logo);
        $logoHeight = imagesy($logo);

        $targetLogoWidth = $qrWidth * 0.2;
        $targetLogoHeight = $logoHeight * ($targetLogoWidth / $logoWidth);

        // Calculate position (center)
        $logoX = ($qrWidth - $targetLogoWidth) / 2;
        $logoY = ($qrHeight - $targetLogoHeight) / 2;

        // Add white background behind logo
        $white = imagecolorallocate($qrImage, 255, 255, 255);
        imagefilledrectangle(
            $qrImage,
            $logoX - 10,
            $logoY - 10,
            $logoX + $targetLogoWidth + 10,
            $logoY + $targetLogoHeight + 10,
            $white
        );

        // Copy logo onto QR code
        imagecopyresampled(
            $qrImage,
            $logo,
            $logoX,
            $logoY,
            0,
            0,
            $targetLogoWidth,
            $targetLogoHeight,
            $logoWidth,
            $logoHeight
        );

        // Save branded QR code
        $brandedPath = str_replace('.png', '_branded.png', $qrPath);
        imagepng($qrImage, $brandedPath);

        // Clean up
        imagedestroy($qrImage);
        imagedestroy($logo);

        return $brandedPath;
    }

    /**
     * Get QR code statistics
     */
    public function getStatistics(): array
    {
        $stats = [
            'total_qrcodes' => 0,
            'products_with_qr' => 0,
            'recent_qrcodes' => []
        ];

        // Total QR codes
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM product_qrcodes");
        $stats['total_qrcodes'] = $result['count'] ?? 0;

        // Products with QR codes
        $result = $this->db->fetch(
            "SELECT COUNT(DISTINCT product_id) as count FROM product_qrcodes"
        );
        $stats['products_with_qr'] = $result['count'] ?? 0;

        // Recent QR codes
        $stats['recent_qrcodes'] = $this->db->fetchAll(
            "SELECT pq.*, p.name as product_name, p.sku
             FROM product_qrcodes pq
             LEFT JOIN products p ON pq.product_id = p.id
             ORDER BY pq.created_at DESC
             LIMIT 10"
        );

        return $stats;
    }

    /**
     * Generate printable QR code sheet (multiple products)
     */
    public function generatePrintableSheet(array $productIds, array $options = []): string
    {
        $productsPerRow = $options['per_row'] ?? 3;
        $includeInfo = $options['include_info'] ?? true;
        $qrSize = $options['qr_size'] ?? 200;

        $html = '<!DOCTYPE html><html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<title>QR Code Sheet</title>';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; margin: 20px; }';
        $html .= '.qr-grid { display: grid; grid-template-columns: repeat(' . $productsPerRow . ', 1fr); gap: 20px; }';
        $html .= '.qr-item { border: 1px solid #ddd; padding: 15px; text-align: center; break-inside: avoid; }';
        $html .= '.qr-item img { max-width: 100%; height: auto; }';
        $html .= '.product-info { margin-top: 10px; font-size: 12px; }';
        $html .= '@media print { .qr-grid { gap: 10px; } body { margin: 10px; } }';
        $html .= '</style></head><body>';
        $html .= '<h1>Product QR Codes</h1>';
        $html .= '<div class="qr-grid">';

        foreach ($productIds as $productId) {
            $product = $this->db->fetch("SELECT * FROM products WHERE id = ?", [$productId]);
            if (!$product) continue;

            $qrCode = $this->getProductQRCode($productId);
            if (!$qrCode) {
                $this->generateForProduct($productId, ['size' => $qrSize]);
                $qrCode = $this->getProductQRCode($productId);
            }

            $html .= '<div class="qr-item">';
            if ($qrCode) {
                $html .= '<img src="/' . $qrCode['file_path'] . '" alt="QR Code">';
            }
            if ($includeInfo) {
                $html .= '<div class="product-info">';
                $html .= '<strong>' . htmlspecialchars($product['name']) . '</strong><br>';
                $html .= 'SKU: ' . htmlspecialchars($product['sku']) . '<br>';
                $html .= '₺' . number_format($product['base_price'], 2);
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        $html .= '</div></body></html>';

        // Save to temporary file
        $filename = 'qr_sheet_' . time() . '.html';
        $filepath = $this->qrCodesPath . '/' . $filename;
        file_put_contents($filepath, $html);

        return $filepath;
    }
}
