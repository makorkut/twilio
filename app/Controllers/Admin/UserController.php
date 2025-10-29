<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Http\Request;
use App\Http\Response;

class UserController
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function index(Request $request): Response
    {
        $page = (int) ($request->get('page') ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $role = $request->get('role');
        $status = $request->get('status');
        $search = $request->get('search');

        $where = ['deleted_at IS NULL'];
        $params = [];

        if ($role) {
            $where[] = 'role = ?';
            $params[] = $role;
        }

        if ($status) {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        if ($search) {
            $where[] = '(name LIKE ? OR email LIKE ? OR company_name LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $users = $this->db->query(
            "SELECT u.*, cg.name as customer_group_name
             FROM users u
             LEFT JOIN customer_groups cg ON cg.id = u.customer_group_id
             {$whereClause}
             ORDER BY u.id DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = $this->db->query(
            "SELECT COUNT(*) as total FROM users {$whereClause}",
            $params
        )[0]['total'];

        return Response::json([
            'success' => true,
            'data' => $users,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'pages' => ceil($total / $perPage),
            ],
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $user = $this->db->query(
            "SELECT u.*, cg.name as customer_group_name
             FROM users u
             LEFT JOIN customer_groups cg ON cg.id = u.customer_group_id
             WHERE u.id = ?
             LIMIT 1",
            [$id]
        )[0] ?? null;

        if (!$user) {
            return Response::json(['error' => 'User not found'], 404);
        }

        // Remove sensitive data
        unset($user['password']);

        return Response::json(['success' => true, 'data' => $user]);
    }

    public function store(Request $request): Response
    {
        try {
            $data = $request->all();

            if (empty($data['email']) || empty($data['password'])) {
                return Response::json(['error' => 'Email and password are required'], 400);
            }

            $userData = [
                'name' => $data['name'] ?? '',
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => $data['role'] ?? 'customer',
                'status' => $data['status'] ?? 'active',
                'customer_group_id' => $data['customer_group_id'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'tax_number' => $data['tax_number'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $userId = $this->db->insert('users', $userData);

            return Response::json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => ['id' => $userId],
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
            if (isset($data['name'])) $updateData['name'] = $data['name'];
            if (isset($data['role'])) $updateData['role'] = $data['role'];
            if (isset($data['status'])) $updateData['status'] = $data['status'];
            if (isset($data['customer_group_id'])) $updateData['customer_group_id'] = $data['customer_group_id'];
            if (isset($data['company_name'])) $updateData['company_name'] = $data['company_name'];
            if (isset($data['tax_number'])) $updateData['tax_number'] = $data['tax_number'];

            if (isset($data['password'])) {
                $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                $this->db->update('users', $updateData, ['id' => $id]);
            }

            return Response::json(['success' => true, 'message' => 'User updated successfully']);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function delete(Request $request, int $id): Response
    {
        try {
            $this->db->update('users', [
                'deleted_at' => date('Y-m-d H:i:s'),
            ], ['id' => $id]);

            return Response::json(['success' => true, 'message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
}
