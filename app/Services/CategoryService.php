<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class CategoryService
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Get all categories (hierarchical)
     */
    public function getAll(bool $activeOnly = false): array
    {
        $where = $activeOnly ? 'WHERE is_active = 1' : '';

        $categories = $this->db->fetchAll(
            "SELECT * FROM categories {$where} ORDER BY sort_order, name"
        );

        return $this->buildTree($categories);
    }

    /**
     * Get category by ID
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM categories WHERE id = ?",
            [$id]
        );
    }

    /**
     * Get category by slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM categories WHERE slug = ?",
            [$slug]
        );
    }

    /**
     * Create category
     */
    public function create(array $data): int
    {
        $this->db->beginTransaction();

        try {
            $categoryData = [
                'parent_id' => $data['parent_id'] ?? null,
                'name' => $data['name'],
                'slug' => $data['slug'] ?? $this->generateSlug($data['name']),
                'description' => $data['description'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'icon' => $data['icon'] ?? null,
                'image_id' => $data['image_id'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => $data['is_active'] ?? 1,
                'is_featured' => $data['is_featured'] ?? 0,
            ];

            $categoryId = $this->db->insert('categories', $categoryData);

            // Insert translations if provided
            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $langCode => $translation) {
                    $this->db->insert('category_translations', [
                        'category_id' => $categoryId,
                        'lang_code' => $langCode,
                        'name' => $translation['name'],
                        'description' => $translation['description'] ?? null,
                        'meta_title' => $translation['meta_title'] ?? null,
                        'meta_description' => $translation['meta_description'] ?? null,
                    ]);
                }
            }

            $this->db->commit();

            return $categoryId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update category
     */
    public function update(int $id, array $data): bool
    {
        $this->db->beginTransaction();

        try {
            $updateData = [
                'parent_id' => $data['parent_id'] ?? null,
                'name' => $data['name'],
                'slug' => $data['slug'] ?? $this->generateSlug($data['name']),
                'description' => $data['description'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'icon' => $data['icon'] ?? null,
                'image_id' => $data['image_id'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => $data['is_active'] ?? 1,
                'is_featured' => $data['is_featured'] ?? 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->db->update('categories', $updateData, 'id = ?', [$id]);

            // Update translations
            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $langCode => $translation) {
                    $existing = $this->db->fetch(
                        "SELECT id FROM category_translations WHERE category_id = ? AND lang_code = ?",
                        [$id, $langCode]
                    );

                    $transData = [
                        'name' => $translation['name'],
                        'description' => $translation['description'] ?? null,
                        'meta_title' => $translation['meta_title'] ?? null,
                        'meta_description' => $translation['meta_description'] ?? null,
                    ];

                    if ($existing) {
                        $this->db->update('category_translations', $transData, 'category_id = ? AND lang_code = ?', [$id, $langCode]);
                    } else {
                        $transData['category_id'] = $id;
                        $transData['lang_code'] = $langCode;
                        $this->db->insert('category_translations', $transData);
                    }
                }
            }

            $this->db->commit();

            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete category
     */
    public function delete(int $id): bool
    {
        // Check if category has children
        $children = $this->db->fetch(
            "SELECT COUNT(*) as count FROM categories WHERE parent_id = ?",
            [$id]
        );

        if ($children['count'] > 0) {
            throw new \Exception('Cannot delete category with children. Delete children first.');
        }

        // Check if category has products
        $products = $this->db->fetch(
            "SELECT COUNT(*) as count FROM product_categories WHERE category_id = ?",
            [$id]
        );

        if ($products['count'] > 0) {
            throw new \Exception('Cannot delete category with products. Remove products first.');
        }

        return $this->db->delete('categories', 'id = ?', [$id]);
    }

    /**
     * Get category with translations
     */
    public function getWithTranslations(int $id): ?array
    {
        $category = $this->findById($id);
        if (!$category) {
            return null;
        }

        $translations = $this->db->fetchAll(
            "SELECT * FROM category_translations WHERE category_id = ?",
            [$id]
        );

        $category['translations'] = [];
        foreach ($translations as $trans) {
            $category['translations'][$trans['lang_code']] = $trans;
        }

        return $category;
    }

    /**
     * Get category tree (hierarchical)
     */
    public function getTree(?int $parentId = null, bool $activeOnly = false): array
    {
        $where = ['parent_id' => $parentId];
        if ($activeOnly) {
            $where['is_active'] = 1;
        }

        $sql = "SELECT * FROM categories WHERE parent_id " .
               ($parentId === null ? 'IS NULL' : '= ?') .
               ($activeOnly ? ' AND is_active = 1' : '') .
               " ORDER BY sort_order, name";

        $params = $parentId !== null ? [$parentId] : [];
        $categories = $this->db->fetchAll($sql, $params);

        foreach ($categories as &$category) {
            $category['children'] = $this->getTree((int) $category['id'], $activeOnly);
        }

        return $categories;
    }

    /**
     * Build tree from flat array
     */
    protected function buildTree(array $elements, ?int $parentId = null): array
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, (int) $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    /**
     * Update product count for category
     */
    public function updateProductCount(int $categoryId): void
    {
        $count = $this->db->fetch(
            "SELECT COUNT(*) as count FROM product_categories WHERE category_id = ?",
            [$categoryId]
        );

        $this->db->update('categories',
            ['product_count' => $count['count']],
            'id = ?',
            [$categoryId]
        );
    }

    /**
     * Generate unique slug
     */
    protected function generateSlug(string $text, int $categoryId = 0): string
    {
        $slug = generateSlug($text);

        // Check uniqueness
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $existing = $this->db->fetch(
                "SELECT id FROM categories WHERE slug = ? AND id != ? LIMIT 1",
                [$slug, $categoryId]
            );

            if (!$existing) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get breadcrumbs for category
     */
    public function getBreadcrumbs(int $categoryId): array
    {
        $breadcrumbs = [];
        $currentId = $categoryId;

        while ($currentId !== null) {
            $category = $this->findById($currentId);
            if (!$category) {
                break;
            }

            array_unshift($breadcrumbs, $category);
            $currentId = $category['parent_id'];
        }

        return $breadcrumbs;
    }
}
