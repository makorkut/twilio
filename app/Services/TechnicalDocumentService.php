<?php

namespace App\Services;

use App\Core\Database;

class TechnicalDocumentService
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Get all documents for a product
     */
    public function getProductDocuments(int $productId, ?string $type = null): array
    {
        $sql = "SELECT td.*, u.name as uploaded_by_name
                FROM technical_documents td
                LEFT JOIN users u ON td.uploaded_by = u.id
                WHERE td.product_id = ?";

        $params = [$productId];

        if ($type) {
            $sql .= " AND td.document_type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY td.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Upload a new technical document
     */
    public function upload(int $productId, array $fileData, string $type, array $metadata = []): int
    {
        // Validate document type
        $validTypes = ['cad', 'certificate', 'msds', 'tds', 'manual', 'specification', 'drawing', 'other'];
        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException("Invalid document type: {$type}");
        }

        // Validate file
        if (!isset($fileData['tmp_name']) || !is_uploaded_file($fileData['tmp_name'])) {
            throw new \RuntimeException('No valid file uploaded');
        }

        // Get file info
        $originalName = $fileData['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $mimeType = $fileData['type'];
        $fileSize = $fileData['size'];

        // Validate file extension based on type
        $allowedExtensions = $this->getAllowedExtensions($type);
        if (!in_array($extension, $allowedExtensions)) {
            throw new \RuntimeException("Invalid file extension for {$type}: {$extension}");
        }

        // Generate unique filename
        $fileName = uniqid() . '_' . time() . '.' . $extension;
        $uploadDir = $_ENV['UPLOAD_DIR'] ?? 'storage/documents';
        $uploadPath = $uploadDir . '/' . $fileName;

        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($fileData['tmp_name'], $uploadPath)) {
            throw new \RuntimeException('Failed to move uploaded file');
        }

        // Insert into database
        $documentId = $this->db->insert('technical_documents', [
            'product_id' => $productId,
            'document_type' => $type,
            'title' => $metadata['title'] ?? $originalName,
            'description' => $metadata['description'] ?? null,
            'file_name' => $fileName,
            'original_name' => $originalName,
            'file_path' => $uploadPath,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'version' => $metadata['version'] ?? '1.0',
            'language' => $metadata['language'] ?? 'tr',
            'uploaded_by' => $_SESSION['user_id'] ?? null,
            'is_public' => (int)($metadata['is_public'] ?? 1),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $documentId;
    }

    /**
     * Get allowed file extensions for document type
     */
    private function getAllowedExtensions(string $type): array
    {
        return match($type) {
            'cad' => ['dwg', 'dxf', 'step', 'stp', 'iges', 'igs', 'stl', 'obj'],
            'certificate' => ['pdf', 'jpg', 'jpeg', 'png'],
            'msds', 'tds' => ['pdf', 'doc', 'docx'],
            'manual', 'specification' => ['pdf', 'doc', 'docx'],
            'drawing' => ['pdf', 'dwg', 'dxf', 'jpg', 'jpeg', 'png'],
            default => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']
        };
    }

    /**
     * Update document metadata
     */
    public function update(int $documentId, array $data): bool
    {
        $allowedFields = ['title', 'description', 'version', 'language', 'is_public'];
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

        return $this->db->update('technical_documents', $updateData, ['id' => $documentId]);
    }

    /**
     * Delete a document
     */
    public function delete(int $documentId): bool
    {
        // Get document info
        $document = $this->db->fetchOne(
            "SELECT * FROM technical_documents WHERE id = ?",
            [$documentId]
        );

        if (!$document) {
            return false;
        }

        // Delete physical file
        if (file_exists($document['file_path'])) {
            unlink($document['file_path']);
        }

        // Delete from database
        return $this->db->delete('technical_documents', ['id' => $documentId]);
    }

    /**
     * Get document by ID
     */
    public function getById(int $documentId): ?array
    {
        return $this->db->fetchOne(
            "SELECT td.*, u.name as uploaded_by_name, p.name as product_name
             FROM technical_documents td
             LEFT JOIN users u ON td.uploaded_by = u.id
             LEFT JOIN products p ON td.product_id = p.id
             WHERE td.id = ?",
            [$documentId]
        );
    }

    /**
     * Search documents
     */
    public function search(array $filters, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT td.*, p.name as product_name, u.name as uploaded_by_name
                FROM technical_documents td
                LEFT JOIN products p ON td.product_id = p.id
                LEFT JOIN users u ON td.uploaded_by = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['product_id'])) {
            $sql .= " AND td.product_id = ?";
            $params[] = $filters['product_id'];
        }

        if (!empty($filters['document_type'])) {
            $sql .= " AND td.document_type = ?";
            $params[] = $filters['document_type'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (td.title LIKE ? OR td.description LIKE ? OR td.original_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (isset($filters['is_public'])) {
            $sql .= " AND td.is_public = ?";
            $params[] = (int)$filters['is_public'];
        }

        $sql .= " ORDER BY td.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get document statistics
     */
    public function getStatistics(): array
    {
        $stats = [
            'total' => 0,
            'by_type' => [],
            'total_size' => 0,
            'recent_uploads' => []
        ];

        // Total count
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM technical_documents");
        $stats['total'] = $result['count'] ?? 0;

        // Count by type
        $byType = $this->db->fetchAll(
            "SELECT document_type, COUNT(*) as count
             FROM technical_documents
             GROUP BY document_type"
        );
        foreach ($byType as $row) {
            $stats['by_type'][$row['document_type']] = $row['count'];
        }

        // Total file size
        $result = $this->db->fetchOne("SELECT SUM(file_size) as total_size FROM technical_documents");
        $stats['total_size'] = $result['total_size'] ?? 0;

        // Recent uploads
        $stats['recent_uploads'] = $this->db->fetchAll(
            "SELECT td.*, p.name as product_name
             FROM technical_documents td
             LEFT JOIN products p ON td.product_id = p.id
             ORDER BY td.created_at DESC
             LIMIT 10"
        );

        return $stats;
    }

    /**
     * Get document type label
     */
    public static function getTypeLabel(string $type): string
    {
        return match($type) {
            'cad' => 'CAD Dosyası',
            'certificate' => 'Sertifika',
            'msds' => 'MSDS (Malzeme Güvenlik Bilgi Formu)',
            'tds' => 'TDS (Teknik Veri Sayfası)',
            'manual' => 'Kullanım Kılavuzu',
            'specification' => 'Teknik Şartname',
            'drawing' => 'Teknik Çizim',
            default => 'Diğer'
        };
    }

    /**
     * Get icon for document type
     */
    public static function getTypeIcon(string $type): string
    {
        return match($type) {
            'cad' => '📐',
            'certificate' => '🏆',
            'msds' => '⚠️',
            'tds' => '📊',
            'manual' => '📖',
            'specification' => '📋',
            'drawing' => '📏',
            default => '📄'
        };
    }
}
