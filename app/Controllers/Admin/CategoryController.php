<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Http\Request;
use App\Http\Response;

class CategoryController
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function index(Request $request): Response
    {
        $type = $request->get('type', 'product');

        $categories = $this->db->query(
            "SELECT c.*, cl.name, cl.slug, cl.description,
                    COUNT(DISTINCT p.id) as product_count
             FROM categories c
             LEFT JOIN category_lang cl ON cl.category_id = c.id AND cl.lang = ?
             LEFT JOIN products p ON p.category_id = c.id AND p.deleted_at IS NULL
             WHERE c.type = ? AND c.deleted_at IS NULL
             GROUP BY c.id
             ORDER BY c.parent_id ASC, c.sort_order ASC",
            [env('DEFAULT_LANG', 'tr'), $type]
        );

        return Response::json(['success' => true, 'data' => $categories]);
    }

    public function show(Request $request, int $id): Response
    {
        $category = $this->db->query(
            "SELECT * FROM categories WHERE id = ? LIMIT 1",
            [$id]
        )[0] ?? null;

        if (!$category) {
            return Response::json(['error' => 'Category not found'], 404);
        }

        $translations = $this->db->query(
            "SELECT * FROM category_lang WHERE category_id = ?",
            [$id]
        );

        $category['translations'] = [];
        foreach ($translations as $trans) {
            $category['translations'][$trans['lang']] = $trans;
        }

        return Response::json(['success' => true, 'data' => $category]);
    }

    public function store(Request $request): Response
    {
        try {
            $data = $request->all();

            $categoryData = [
                'parent_id' => $data['parent_id'] ?? null,
                'type' => $data['type'] ?? 'product',
                'status' => $data['status'] ?? 'active',
                'sort_order' => $data['sort_order'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $categoryId = $this->db->insert('categories', $categoryData);

            // Insert translations
            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $lang => $translation) {
                    $this->db->insert('category_lang', [
                        'category_id' => $categoryId,
                        'lang' => $lang,
                        'name' => $translation['name'] ?? '',
                        'slug' => $translation['slug'] ?? '',
                        'description' => $translation['description'] ?? null,
                    ]);
                }
            }

            return Response::json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => ['id' => $categoryId],
            ], 201);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, int $id): Response
    {
        try {
            $data = $request->all();

            $updateData = [];
            if (isset($data['parent_id'])) $updateData['parent_id'] = $data['parent_id'];
            if (isset($data['status'])) $updateData['status'] = $data['status'];
            if (isset($data['sort_order'])) $updateData['sort_order'] = $data['sort_order'];

            if (!empty($updateData)) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                $this->db->update('categories', $updateData, ['id' => $id]);
            }

            // Update translations
            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $lang => $translation) {
                    $this->db->query(
                        "INSERT INTO category_lang (category_id, lang, name, slug, description)
                         VALUES (?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE
                            name = VALUES(name),
                            slug = VALUES(slug),
                            description = VALUES(description)",
                        [
                            $id,
                            $lang,
                            $translation['name'] ?? '',
                            $translation['slug'] ?? '',
                            $translation['description'] ?? null,
                        ]
                    );
                }
            }

            return Response::json(['success' => true, 'message' => 'Category updated successfully']);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function delete(Request $request, int $id): Response
    {
        try {
            $this->db->update('categories', [
                'deleted_at' => date('Y-m-d H:i:s'),
            ], ['id' => $id]);

            return Response::json(['success' => true, 'message' => 'Category deleted successfully']);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function tree(Request $request): Response
    {
        $type = $request->get('type', 'product');

        $categories = $this->db->query(
            "SELECT c.*, cl.name
             FROM categories c
             LEFT JOIN category_lang cl ON cl.category_id = c.id AND cl.lang = ?
             WHERE c.type = ? AND c.deleted_at IS NULL
             ORDER BY c.parent_id ASC, c.sort_order ASC",
            [env('DEFAULT_LANG', 'tr'), $type]
        );

        // Build tree
        $tree = $this->buildTree($categories);

        return Response::json(['success' => true, 'data' => $tree]);
    }

    protected function buildTree(array $categories, ?int $parentId = null): array
    {
        $branch = [];

        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = $this->buildTree($categories, (int) $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $branch[] = $category;
            }
        }

        return $branch;
    }
}
