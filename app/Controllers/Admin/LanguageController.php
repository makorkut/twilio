<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Http\Request;
use App\Http\Response;

class LanguageController
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * List all languages
     */
    public function index(Request $request): Response
    {
        $languages = $this->db->query(
            "SELECT * FROM languages ORDER BY is_default DESC, name ASC"
        );

        return Response::json([
            'success' => true,
            'data' => $languages,
        ]);
    }

    /**
     * Get single language
     */
    public function show(Request $request, int $id): Response
    {
        $language = $this->db->fetch(
            "SELECT * FROM languages WHERE id = ?",
            [$id]
        );

        if (!$language) {
            return Response::json(['error' => 'Language not found'], 404);
        }

        return Response::json([
            'success' => true,
            'data' => $language,
        ]);
    }

    /**
     * Create new language
     */
    public function store(Request $request): Response
    {
        $data = $request->all();

        try {
            // If this is set as default, unset other defaults
            if (!empty($data['is_default'])) {
                $this->db->query("UPDATE languages SET is_default = 0");
            }

            $languageId = $this->db->insert('languages', [
                'name' => $data['name'] ?? '',
                'language_code' => $data['language_code'] ?? '',
                'locale_code' => $data['locale_code'] ?? null,
                'text_editor_lang' => $data['text_editor_lang'] ?? null,
                'status' => $data['status'] ?? 1,
                'is_default' => $data['is_default'] ?? 0,
                'date_format' => $data['date_format'] ?? 'd/m/Y',
                'time_format' => $data['time_format'] ?? 'H:i',
                'decimal_separator' => $data['decimal_separator'] ?? ',',
                'thousands_separator' => $data['thousands_separator'] ?? '.',
            ]);

            return Response::json([
                'success' => true,
                'message' => 'Language created successfully',
                'data' => ['id' => $languageId],
            ], 201);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update language
     */
    public function update(Request $request, int $id): Response
    {
        $data = $request->all();

        try {
            // If this is set as default, unset other defaults
            if (!empty($data['is_default'])) {
                $this->db->query("UPDATE languages SET is_default = 0 WHERE id != ?", [$id]);
            }

            $this->db->update('languages', $id, [
                'name' => $data['name'] ?? '',
                'language_code' => $data['language_code'] ?? '',
                'locale_code' => $data['locale_code'] ?? null,
                'text_editor_lang' => $data['text_editor_lang'] ?? null,
                'status' => $data['status'] ?? 1,
                'is_default' => $data['is_default'] ?? 0,
                'date_format' => $data['date_format'] ?? 'd/m/Y',
                'time_format' => $data['time_format'] ?? 'H:i',
                'decimal_separator' => $data['decimal_separator'] ?? ',',
                'thousands_separator' => $data['thousands_separator'] ?? '.',
            ]);

            return Response::json([
                'success' => true,
                'message' => 'Language updated successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete language
     */
    public function delete(Request $request, int $id): Response
    {
        try {
            // Check if it's the default language
            $language = $this->db->fetch(
                "SELECT * FROM languages WHERE id = ?",
                [$id]
            );

            if (!$language) {
                return Response::json(['error' => 'Language not found'], 404);
            }

            if ($language['is_default']) {
                return Response::json([
                    'success' => false,
                    'error' => 'Cannot delete default language',
                ], 400);
            }

            $this->db->delete('languages', $id);

            return Response::json([
                'success' => true,
                'message' => 'Language deleted successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Set language as default
     */
    public function setDefault(Request $request, int $id): Response
    {
        try {
            // Unset all defaults
            $this->db->query("UPDATE languages SET is_default = 0");

            // Set this as default
            $this->db->update('languages', $id, ['is_default' => 1]);

            return Response::json([
                'success' => true,
                'message' => 'Default language updated successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
