<?php

declare(strict_types=1);

namespace App\Authorization;

use Config\Database;

final class TenantAuthorizationService
{
    public function can(int $userId, int $companyId, string $permission): bool
    {
        return Database::connect()->table('user_company_memberships m')
            ->join('users u', 'u.id = m.user_id AND u.active = 1')
            ->join('companies c', "c.id = m.company_id AND c.status = 'active' AND c.deleted_at IS NULL")
            ->join('user_roles ur', 'ur.company_id = m.company_id AND ur.user_id = m.user_id')
            ->join('roles r', "r.id = ur.role_id AND r.status = 'active'")
            ->join('role_permissions rp', 'rp.company_id = m.company_id AND rp.role_id = r.id')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('m.company_id', $companyId)
            ->where('m.user_id', $userId)
            ->where('m.status', 'active')
            ->where('p.code', $permission)
            ->groupStart()
                ->where('ur.effective_from <=', date('Y-m-d'))
                ->orWhere('ur.effective_from', null)
            ->groupEnd()
            ->groupStart()
                ->where('ur.effective_to >=', date('Y-m-d'))
                ->orWhere('ur.effective_to', null)
            ->groupEnd()
            ->countAllResults() > 0;
    }
}
