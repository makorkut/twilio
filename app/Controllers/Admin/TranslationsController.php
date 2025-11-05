<?php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Http\Request;

class TranslationsController
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function list(Request $request): void
    {
        $search = $request->get('search', '');
        $group = $request->get('group', '');
        $page = (int) $request->get('page', 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        // Build query
        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(k.key_name LIKE ? OR k.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($group) {
            $where[] = "k.`group` = ?";
            $params[] = $group;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get all translation keys with their TR and EN values
        $sql = "
            SELECT
                k.id,
                k.key_name,
                k.`group`,
                k.description,
                tr.value as tr_value,
                en.value as en_value
            FROM i18n_keys k
            LEFT JOIN i18n_values tr ON k.id = tr.key_id AND tr.lang_id = 2
            LEFT JOIN i18n_values en ON k.id = en.key_id AND en.lang_id = 1
            {$whereClause}
            ORDER BY k.`group`, k.key_name
            LIMIT ? OFFSET ?
        ";
        $params[] = $perPage;
        $params[] = $offset;

        $translations = $this->db->fetchAll($sql, $params);

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM i18n_keys k {$whereClause}";
        $countParams = array_slice($params, 0, count($params) - 2);
        $totalCount = $this->db->fetch($countSql, $countParams)['total'] ?? 0;
        $totalPages = (int) ceil($totalCount / $perPage);

        // Get all groups for filter
        $groups = $this->db->fetchAll("SELECT DISTINCT `group` FROM i18n_keys ORDER BY `group`");

        include __DIR__ . '/../../Views/admin/translations/list.php';
    }

    public function create(Request $request): void
    {
        $groups = $this->db->fetchAll("SELECT DISTINCT `group` FROM i18n_keys ORDER BY `group`");

        if ($request->method() === 'POST') {
            $keyName = trim($request->post('key_name', ''));
            $group = trim($request->post('group', ''));
            $description = trim($request->post('description', ''));
            $trValue = $request->post('tr_value', '');
            $enValue = $request->post('en_value', '');

            // Validate
            if (!$keyName || !$group) {
                $_SESSION['error'] = 'Key name and group are required';
                header('Location: /admin/translations/create');
                exit;
            }

            // Check if key already exists
            $existing = $this->db->fetch("SELECT id FROM i18n_keys WHERE key_name = ?", [$keyName]);
            if ($existing) {
                $_SESSION['error'] = 'Translation key already exists';
                header('Location: /admin/translations/create');
                exit;
            }

            // Insert key
            $this->db->insert('i18n_keys', [
                'key_name' => $keyName,
                'group' => $group,
                'description' => $description
            ]);

            $keyId = $this->db->lastInsertId();

            // Insert TR value if provided
            if ($trValue) {
                $this->db->insert('i18n_values', [
                    'key_id' => $keyId,
                    'lang_id' => 2,
                    'value' => $trValue
                ]);
            }

            // Insert EN value if provided
            if ($enValue) {
                $this->db->insert('i18n_values', [
                    'key_id' => $keyId,
                    'lang_id' => 1,
                    'value' => $enValue
                ]);
            }

            $_SESSION['success'] = trans('admin.translations.save_success');
            header('Location: /admin/translations');
            exit;
        }

        include __DIR__ . '/../../Views/admin/translations/form.php';
    }

    public function edit(Request $request, array $params): void
    {
        $id = (int) $params['id'];

        // Get translation key
        $translation = $this->db->fetch("SELECT * FROM i18n_keys WHERE id = ?", [$id]);

        if (!$translation) {
            $_SESSION['error'] = 'Translation not found';
            header('Location: /admin/translations');
            exit;
        }

        // Get TR and EN values
        $trValue = $this->db->fetch(
            "SELECT value FROM i18n_values WHERE key_id = ? AND lang_id = 2",
            [$id]
        );
        $enValue = $this->db->fetch(
            "SELECT value FROM i18n_values WHERE key_id = ? AND lang_id = 1",
            [$id]
        );

        $translation['tr_value'] = $trValue['value'] ?? '';
        $translation['en_value'] = $enValue['value'] ?? '';

        if ($request->method() === 'POST') {
            $group = trim($request->post('group', ''));
            $description = trim($request->post('description', ''));
            $trValueNew = $request->post('tr_value', '');
            $enValueNew = $request->post('en_value', '');

            // Update key
            $this->db->update('i18n_keys', [
                'group' => $group,
                'description' => $description
            ], 'id = ?', [$id]);

            // Update or insert TR value
            if ($trValue) {
                $this->db->update('i18n_values', [
                    'value' => $trValueNew
                ], 'key_id = ? AND lang_id = 2', [$id]);
            } else if ($trValueNew) {
                $this->db->insert('i18n_values', [
                    'key_id' => $id,
                    'lang_id' => 2,
                    'value' => $trValueNew
                ]);
            }

            // Update or insert EN value
            if ($enValue) {
                $this->db->update('i18n_values', [
                    'value' => $enValueNew
                ], 'key_id = ? AND lang_id = 1', [$id]);
            } else if ($enValueNew) {
                $this->db->insert('i18n_values', [
                    'key_id' => $id,
                    'lang_id' => 1,
                    'value' => $enValueNew
                ]);
            }

            $_SESSION['success'] = trans('admin.translations.save_success');
            header('Location: /admin/translations');
            exit;
        }

        $groups = $this->db->fetchAll("SELECT DISTINCT `group` FROM i18n_keys ORDER BY `group`");

        include __DIR__ . '/../../Views/admin/translations/form.php';
    }

    public function delete(Request $request, array $params): void
    {
        $id = (int) $params['id'];

        // Delete translation values first
        $this->db->delete('i18n_values', 'key_id = ?', [$id]);

        // Delete translation key
        $this->db->delete('i18n_keys', 'id = ?', [$id]);

        $_SESSION['success'] = trans('admin.translations.delete_success');
        header('Location: /admin/translations');
        exit;
    }
}
