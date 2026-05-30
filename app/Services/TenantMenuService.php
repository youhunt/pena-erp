<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

final class TenantMenuService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= Database::connect();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function accessibleMenus(int $userId, int $companyId): array
    {
        return $this->menuQuery($userId, $companyId)
            ->orderBy('m.sort_order', 'ASC')
            ->orderBy('m.label', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function accessibleMenu(int $userId, int $companyId, string $code): ?array
    {
        return $this->menuQuery($userId, $companyId)
            ->where('m.code', $code)
            ->get()
            ->getFirstRow('array');
    }

    /**
     * Lightweight diagnostic helper for troubleshooting sidebar/RBAC issues.
     *
     * @return list<array<string, mixed>>
     */
    public function debugMenuAccess(int $userId, int $companyId): array
    {
        return $this->db->table('menus m')
            ->select('m.id, m.code, m.label, m.route, m.sort_order, p.code AS permission_code, r.id AS role_id, r.code AS role_code, r.name AS role_name, r.status AS role_status, ur.user_id, cm.status AS membership_status, u.active AS user_active')
            ->join('menu_permissions mp', 'mp.company_id = m.company_id AND mp.menu_id = m.id', 'left')
            ->join('permissions p', 'p.id = mp.permission_id AND p.company_id = m.company_id', 'left')
            ->join('role_permissions rp', 'rp.permission_id = p.id AND rp.company_id = m.company_id', 'left')
            ->join('roles r', 'r.id = rp.role_id AND r.company_id = m.company_id', 'left')
            ->join('user_roles ur', 'ur.company_id = m.company_id AND ur.role_id = r.id AND ur.user_id = ' . (int) $userId, 'left')
            ->join('user_company_memberships cm', 'cm.company_id = m.company_id AND cm.user_id = ' . (int) $userId, 'left')
            ->join('users u', 'u.id = ' . (int) $userId, 'left')
            ->where('m.company_id', $companyId)
            ->where('m.deleted_at', null)
            ->where('m.route IS NOT NULL', null, false)
            ->where('m.route !=', '')
            ->orderBy('m.sort_order', 'ASC')
            ->orderBy('m.label', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function menuQuery(int $userId, int $companyId): BaseBuilder
    {
        return $this->db->table('menus m')
            ->distinct()
            ->select('m.id, m.code, m.label, m.route, m.icon, m.sort_order')
            ->join('companies c', "c.id = m.company_id AND c.status = 'active' AND c.deleted_at IS NULL")
            ->join('menu_permissions mp', 'mp.company_id = m.company_id AND mp.menu_id = m.id')
            ->join('permissions p', 'p.id = mp.permission_id AND p.company_id = m.company_id')
            ->join('role_permissions rp', 'rp.permission_id = p.id AND rp.company_id = m.company_id')
            ->join('roles r', "r.id = rp.role_id AND r.company_id = m.company_id AND r.status = 'active'")
            ->join('user_roles ur', 'ur.company_id = m.company_id AND ur.role_id = r.id')
            ->join('user_company_memberships cm', "cm.company_id = m.company_id AND cm.user_id = ur.user_id AND cm.status = 'active'")
            ->join('users u', 'u.id = ur.user_id AND u.active = 1')
            ->where('m.company_id', $companyId)
            ->where('m.deleted_at', null)
            ->where('m.route IS NOT NULL', null, false)
            ->where('m.route !=', '')
            ->where('ur.user_id', $userId)
            ->groupStart()
                ->where('ur.effective_from <=', date('Y-m-d'))
                ->orWhere('ur.effective_from', null)
            ->groupEnd()
            ->groupStart()
                ->where('ur.effective_to >=', date('Y-m-d'))
                ->orWhere('ur.effective_to', null)
            ->groupEnd();
    }
}
