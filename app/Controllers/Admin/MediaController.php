<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Services\MediaService;
use App\Http\Request;
use App\Http\Response;

class MediaController
{
    protected Database $db;
    protected MediaService $mediaService;

    public function __construct(Database $db, MediaService $mediaService)
    {
        $this->db = $db;
        $this->mediaService = $mediaService;
    }

    /**
     * List all media
     */
    public function index(Request $request): Response
    {
        $page = (int) ($request->get('page') ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        // Filters
        $mediaType = $request->get('type');
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $tagId = $request->get('tag_id');

        // Build query
        $where = ['m.status != ?'];
        $params = ['deleted'];

        if ($mediaType) {
            $where[] = 'm.media_type = ?';
            $params[] = $mediaType;
        }

        if ($search) {
            $where[] = '(m.filename LIKE ? OR ml.title LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($categoryId) {
            $where[] = 'EXISTS (SELECT 1 FROM media_category_relations mcr WHERE mcr.media_id = m.id AND mcr.category_id = ?)';
            $params[] = $categoryId;
        }

        if ($tagId) {
            $where[] = 'EXISTS (SELECT 1 FROM media_tag_relations mtr WHERE mtr.media_id = m.id AND mtr.tag_id = ?)';
            $params[] = $tagId;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // Get media
        $query = "SELECT m.*, ml.title, ml.alt_text
                  FROM media m
                  LEFT JOIN media_lang ml ON ml.media_id = m.id AND ml.lang = ?
                  {$whereClause}
                  ORDER BY m.id DESC
                  LIMIT ? OFFSET ?";

        $langParam = env('DEFAULT_LANG', 'tr');
        $finalParams = array_merge([$langParam], $params, [$perPage, $offset]);

        $media = $this->db->query($query, $finalParams);

        // Add URLs
        foreach ($media as &$item) {
            $item['url'] = $this->mediaService->getUrl($item);
            $item['thumbnail_url'] = $this->mediaService->getThumbnailUrl($item);
        }

        // Get total count
        $countQuery = "SELECT COUNT(DISTINCT m.id) as total
                       FROM media m
                       LEFT JOIN media_lang ml ON ml.media_id = m.id AND ml.lang = ?
                       {$whereClause}";

        $countParams = array_merge([$langParam], $params);
        $countResult = $this->db->fetch($countQuery, $countParams);
        $total = $countResult['total'] ?? 0;

        return Response::json([
            'success' => true,
            'data' => $media,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'pages' => ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Get single media
     */
    public function show(Request $request, int $id): Response
    {
        $media = $this->mediaService->findById($id);

        if (!$media) {
            return Response::json(['error' => 'Media not found'], 404);
        }

        // Get translations
        $translations = $this->db->query(
            "SELECT * FROM media_lang WHERE media_id = ?",
            [$id]
        );

        $media['translations'] = [];
        foreach ($translations as $trans) {
            $media['translations'][$trans['lang']] = $trans;
        }

        // Get tags
        $tags = $this->db->query(
            "SELECT mt.*
             FROM media_tags mt
             INNER JOIN media_tag_relations mtr ON mtr.tag_id = mt.id
             WHERE mtr.media_id = ?",
            [$id]
        );

        $media['tags'] = $tags;

        // Get categories
        $categories = $this->db->query(
            "SELECT mc.*, mcr.level
             FROM media_categories mc
             INNER JOIN media_category_relations mcr ON mcr.category_id = mc.id
             WHERE mcr.media_id = ?",
            [$id]
        );

        $media['categories'] = $categories;

        // Get usage
        $usage = $this->db->query(
            "SELECT * FROM media_usage WHERE media_id = ?",
            [$id]
        );

        $media['usage'] = $usage;

        // Add URLs
        $media['url'] = $this->mediaService->getUrl($media);
        $media['thumbnail_url'] = $this->mediaService->getThumbnailUrl($media);

        return Response::json([
            'success' => true,
            'data' => $media,
        ]);
    }

    /**
     * Upload media
     */
    public function upload(Request $request): Response
    {
        try {
            $file = $request->file('file');

            if (!$file) {
                return Response::json(['error' => 'No file provided'], 400);
            }

            $metadata = [
                'uploaded_by' => 1, // TODO: Get from auth
                'translations' => json_decode($request->get('translations', '[]'), true),
                'tags' => json_decode($request->get('tags', '[]'), true),
                'categories' => json_decode($request->get('categories', '[]'), true),
            ];

            $mediaId = $this->mediaService->upload($file, $metadata);

            return Response::json([
                'success' => true,
                'message' => 'Media uploaded successfully',
                'data' => ['id' => $mediaId],
            ], 201);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Download from URL
     */
    public function downloadFromUrl(Request $request): Response
    {
        $url = $request->get('url');

        if (!$url) {
            return Response::json(['error' => 'URL is required'], 400);
        }

        try {
            $metadata = [
                'uploaded_by' => 1, // TODO: Get from auth
                'translations' => $request->get('translations', []),
                'tags' => $request->get('tags', []),
                'categories' => $request->get('categories', []),
                'external_id' => $request->get('external_id'),
                'external_source' => $request->get('external_source', 'local-crm'),
            ];

            $mediaId = $this->mediaService->downloadFromUrl($url, $metadata);

            return Response::json([
                'success' => true,
                'message' => 'Media downloaded successfully',
                'data' => ['id' => $mediaId],
            ], 201);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update media metadata
     */
    public function update(Request $request, int $id): Response
    {
        try {
            $data = $request->all();

            // Update media record
            $updateData = [];
            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }
            if (isset($data['sort_order'])) {
                $updateData['sort_order'] = $data['sort_order'];
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                $this->db->update('media', $updateData, ['id' => $id]);
            }

            // Update translations
            if (isset($data['translations'])) {
                foreach ($data['translations'] as $lang => $translation) {
                    $this->db->query(
                        "INSERT INTO media_lang (media_id, lang, title, alt_text, caption, description, slug)
                         VALUES (?, ?, ?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE
                            title = VALUES(title),
                            alt_text = VALUES(alt_text),
                            caption = VALUES(caption),
                            description = VALUES(description),
                            slug = VALUES(slug)",
                        [
                            $id,
                            $lang,
                            $translation['title'] ?? '',
                            $translation['alt_text'] ?? null,
                            $translation['caption'] ?? null,
                            $translation['description'] ?? null,
                            $translation['slug'] ?? '',
                        ]
                    );
                }
            }

            return Response::json([
                'success' => true,
                'message' => 'Media updated successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete media
     */
    public function delete(Request $request, int $id): Response
    {
        try {
            $this->mediaService->delete($id);

            return Response::json([
                'success' => true,
                'message' => 'Media deleted successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Attach tag to media
     */
    public function attachTag(Request $request, int $id): Response
    {
        $tagName = $request->get('tag_name');
        $tagType = $request->get('tag_type', 'general');

        if (!$tagName) {
            return Response::json(['error' => 'Tag name is required'], 400);
        }

        try {
            $this->mediaService->attachTag($id, [
                'name' => $tagName,
                'type' => $tagType,
            ]);

            return Response::json([
                'success' => true,
                'message' => 'Tag attached successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Detach tag from media
     */
    public function detachTag(Request $request, int $id): Response
    {
        $tagId = (int) $request->get('tag_id');

        try {
            $this->mediaService->detachTag($id, $tagId);

            return Response::json([
                'success' => true,
                'message' => 'Tag detached successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Attach category to media
     */
    public function attachCategory(Request $request, int $id): Response
    {
        $categoryId = (int) $request->get('category_id');
        $level = $request->get('level', 'main');

        try {
            $this->mediaService->attachCategory($id, $categoryId, $level);

            return Response::json([
                'success' => true,
                'message' => 'Category attached successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get media by SKU tag
     */
    public function getBySku(Request $request): Response
    {
        $sku = $request->get('sku');

        if (!$sku) {
            return Response::json(['error' => 'SKU is required'], 400);
        }

        $media = $this->mediaService->getBySku($sku);

        // Add URLs
        foreach ($media as &$item) {
            $item['url'] = $this->mediaService->getUrl($item);
            $item['thumbnail_url'] = $this->mediaService->getThumbnailUrl($item);
        }

        return Response::json([
            'success' => true,
            'data' => $media,
            'count' => count($media),
        ]);
    }

    /**
     * Get media statistics
     */
    public function statistics(Request $request): Response
    {
        $stats = $this->db->query(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN media_type = 'image' THEN 1 ELSE 0 END) as images,
                SUM(CASE WHEN media_type = 'video' THEN 1 ELSE 0 END) as videos,
                SUM(CASE WHEN media_type = 'document' THEN 1 ELSE 0 END) as documents,
                SUM(file_size) as total_size
             FROM media
             WHERE status != 'deleted'"
        );

        return Response::json([
            'success' => true,
            'data' => $stats[0],
        ]);
    }

    /**
     * Bulk attach media to products by SKU
     */
    public function bulkAttachBySku(Request $request): Response
    {
        $sku = $request->get('sku');
        $mediaIds = $request->get('media_ids', []);
        $usageType = $request->get('usage_type', 'gallery');

        if (!$sku || empty($mediaIds)) {
            return Response::json(['error' => 'SKU and media IDs are required'], 400);
        }

        try {
            $attachedCount = 0;

            foreach ($mediaIds as $mediaId) {
                $count = $this->mediaService->attachToProductsBySku($sku, (int) $mediaId, $usageType);
                $attachedCount += $count;
            }

            return Response::json([
                'success' => true,
                'message' => "Media attached to {$attachedCount} products",
                'count' => $attachedCount,
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get all tags
     */
    public function getTags(Request $request): Response
    {
        $type = $request->get('type');

        $query = "SELECT mt.*, COUNT(mtr.media_id) as usage_count
                  FROM media_tags mt
                  LEFT JOIN media_tag_relations mtr ON mtr.tag_id = mt.id";

        $params = [];

        if ($type) {
            $query .= " WHERE mt.tag_type = ?";
            $params[] = $type;
        }

        $query .= " GROUP BY mt.id ORDER BY mt.name ASC";

        $tags = $this->db->query($query, $params);

        return Response::json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    /**
     * Get media categories
     */
    public function getCategories(Request $request): Response
    {
        $categories = $this->db->query(
            "SELECT mc.*, mcl.name, COUNT(mcr.media_id) as media_count
             FROM media_categories mc
             LEFT JOIN media_categories_lang mcl ON mcl.category_id = mc.id AND mcl.lang = ?
             LEFT JOIN media_category_relations mcr ON mcr.category_id = mc.id
             GROUP BY mc.id
             ORDER BY mc.parent_id ASC, mc.sort_order ASC",
            [env('DEFAULT_LANG', 'tr')]
        );

        return Response::json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
