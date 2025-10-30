<?php

namespace App\Services;

use App\Core\Database;

class DealerApplicationService
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Submit a new dealer application
     */
    public function submit(array $data): int
    {
        // Validate required fields
        $required = ['company_name', 'contact_name', 'email', 'phone'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Check for duplicate application (same email or company)
        $existing = $this->db->fetchOne(
            "SELECT id FROM dealer_applications
             WHERE email = ? OR company_name = ?
             AND status IN ('pending', 'under_review', 'approved')
             LIMIT 1",
            [$data['email'], $data['company_name']]
        );

        if ($existing) {
            throw new \RuntimeException('Bu firma veya e-posta adresi ile başvuru mevcut');
        }

        // Generate application number
        $applicationNumber = $this->generateApplicationNumber();

        // Insert application
        $applicationId = $this->db->insert('dealer_applications', [
            'application_number' => $applicationNumber,
            'status' => 'pending',
            'company_name' => $data['company_name'],
            'company_type' => $data['company_type'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'tax_office' => $data['tax_office'] ?? null,
            'contact_name' => $data['contact_name'],
            'contact_title' => $data['contact_title'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'],
            'website' => $data['website'] ?? null,
            'address' => json_encode($data['address'] ?? []),
            'business_experience_years' => (int)($data['business_experience_years'] ?? 0),
            'current_brands' => $data['current_brands'] ?? null,
            'sales_volume_annual' => !empty($data['sales_volume_annual']) ? (float)$data['sales_volume_annual'] : null,
            'number_of_employees' => (int)($data['number_of_employees'] ?? 0),
            'warehouse_area_sqm' => !empty($data['warehouse_area_sqm']) ? (float)$data['warehouse_area_sqm'] : null,
            'sales_channels' => json_encode($data['sales_channels'] ?? []),
            'target_regions' => json_encode($data['target_regions'] ?? []),
            'why_partner' => $data['why_partner'] ?? null,
            'additional_info' => $data['additional_info'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Add status history
        $this->addStatusHistory($applicationId, 'pending', 'Başvuru alındı');

        return $applicationId;
    }

    /**
     * Generate application number
     */
    private function generateApplicationNumber(): string
    {
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        return "DEALER-{$date}-{$random}";
    }

    /**
     * Update application status
     */
    public function updateStatus(int $applicationId, string $status, ?string $note = null): bool
    {
        $validStatuses = ['pending', 'under_review', 'approved', 'rejected', 'on_hold'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        $updated = $this->db->update(
            'dealer_applications',
            [
                'status' => $status,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'reviewed_by' => $_SESSION['user_id'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            ['id' => $applicationId]
        );

        if ($updated) {
            $this->addStatusHistory($applicationId, $status, $note);

            // If approved, create dealer account
            if ($status === 'approved') {
                $this->createDealerAccount($applicationId);
            }
        }

        return $updated;
    }

    /**
     * Add status history entry
     */
    private function addStatusHistory(int $applicationId, string $status, ?string $note = null): void
    {
        $this->db->insert('dealer_application_history', [
            'application_id' => $applicationId,
            'status' => $status,
            'note' => $note,
            'created_by' => $_SESSION['user_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Create dealer account after approval
     */
    private function createDealerAccount(int $applicationId): void
    {
        $application = $this->getById($applicationId);
        if (!$application) {
            return;
        }

        // Check if dealer account already exists
        $existingDealer = $this->db->fetchOne(
            "SELECT id FROM dealers WHERE email = ?",
            [$application['email']]
        );

        if ($existingDealer) {
            return; // Already exists
        }

        // Generate dealer code
        $dealerCode = $this->generateDealerCode($application['company_name']);

        // Create dealer record
        $this->db->insert('dealers', [
            'dealer_code' => $dealerCode,
            'company_name' => $application['company_name'],
            'contact_name' => $application['contact_name'],
            'email' => $application['email'],
            'phone' => $application['phone'],
            'tax_number' => $application['tax_number'],
            'address' => $application['address'],
            'status' => 'active',
            'application_id' => $applicationId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Generate dealer code
     */
    private function generateDealerCode(string $companyName): string
    {
        // Take first 3 letters of company name
        $words = explode(' ', strtoupper($companyName));
        $prefix = substr($words[0] ?? 'DEA', 0, 3);

        // Add sequential number
        $lastDealer = $this->db->fetchOne(
            "SELECT dealer_code FROM dealers WHERE dealer_code LIKE ? ORDER BY id DESC LIMIT 1",
            [$prefix . '%']
        );

        if ($lastDealer && preg_match('/(\d+)$/', $lastDealer['dealer_code'], $matches)) {
            $number = (int)$matches[1] + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get application by ID with details
     */
    public function getById(int $applicationId): ?array
    {
        $application = $this->db->fetchOne(
            "SELECT da.*, u.name as reviewed_by_name
             FROM dealer_applications da
             LEFT JOIN users u ON da.reviewed_by = u.id
             WHERE da.id = ?",
            [$applicationId]
        );

        if (!$application) {
            return null;
        }

        // Decode JSON fields
        $application['address'] = json_decode($application['address'], true);
        $application['sales_channels'] = json_decode($application['sales_channels'], true);
        $application['target_regions'] = json_decode($application['target_regions'], true);

        // Get status history
        $application['status_history'] = $this->db->fetchAll(
            "SELECT dah.*, u.name as created_by_name
             FROM dealer_application_history dah
             LEFT JOIN users u ON dah.created_by = u.id
             WHERE dah.application_id = ?
             ORDER BY dah.created_at DESC",
            [$applicationId]
        );

        return $application;
    }

    /**
     * Search applications
     */
    public function search(array $filters, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT da.*
                FROM dealer_applications da
                WHERE 1=1";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (da.company_name LIKE ? OR da.contact_name LIKE ? OR da.email LIKE ? OR da.application_number LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND da.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['company_type'])) {
            $sql .= " AND da.company_type = ?";
            $params[] = $filters['company_type'];
        }

        $sql .= " ORDER BY da.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get application statistics
     */
    public function getStatistics(): array
    {
        $stats = [
            'total' => 0,
            'by_status' => [],
            'pending_review' => 0,
            'approved_this_month' => 0,
            'recent_applications' => []
        ];

        // Total count
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM dealer_applications");
        $stats['total'] = $result['count'] ?? 0;

        // Count by status
        $byStatus = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count
             FROM dealer_applications
             GROUP BY status"
        );
        foreach ($byStatus as $row) {
            $stats['by_status'][$row['status']] = $row['count'];
        }

        // Pending review count
        $stats['pending_review'] = $stats['by_status']['pending'] ?? 0;

        // Approved this month
        $startOfMonth = date('Y-m-01 00:00:00');
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count
             FROM dealer_applications
             WHERE status = 'approved' AND updated_at >= ?",
            [$startOfMonth]
        );
        $stats['approved_this_month'] = $result['count'] ?? 0;

        // Recent applications
        $stats['recent_applications'] = $this->search([], 10, 0);

        return $stats;
    }

    /**
     * Get status label
     */
    public static function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'Beklemede',
            'under_review' => 'İnceleniyor',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
            'on_hold' => 'Askıda',
            default => ucfirst($status)
        };
    }

    /**
     * Get status icon
     */
    public static function getStatusIcon(string $status): string
    {
        return match($status) {
            'pending' => '⏳',
            'under_review' => '🔍',
            'approved' => '✅',
            'rejected' => '❌',
            'on_hold' => '⏸️',
            default => '📋'
        };
    }

    /**
     * Get company types
     */
    public static function getCompanyTypes(): array
    {
        return [
            'limited' => 'Limited Şirket',
            'joint_stock' => 'Anonim Şirket',
            'sole_proprietorship' => 'Şahıs Firması',
            'cooperative' => 'Kooperatif',
            'other' => 'Diğer'
        ];
    }

    /**
     * Get sales channels
     */
    public static function getSalesChannels(): array
    {
        return [
            'retail' => 'Perakende Satış',
            'wholesale' => 'Toptan Satış',
            'online' => 'Online Satış',
            'project' => 'Proje Satışları',
            'export' => 'İhracat'
        ];
    }
}
