<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class SyncAdministrationAccessToAssignedRoles extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $permissionCodes = [
            'platform.company.manage',
            'platform.user.manage',
            'platform.rbac.manage',
            'platform.audit.view',
        ];

        $companies = $this->db->table('companies')
            ->select('id')
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getResultArray();

        foreach ($companies as $company) {
            $companyId = (int) $company['id'];

            $permissions = $this->db->table('permissions')
                ->select('id, code')
                ->where('company_id', $companyId)
                ->whereIn('code', $permissionCodes)
                ->get()
                ->getResultArray();

            if ($permissions === []) {
                continue;
            }

            $assignedRoles = $this->db->table('user_roles ur')
                ->distinct()
                ->select('ur.role_id')
                ->join('user_company_memberships cm', 'cm.company_id = ur.company_id AND cm.user_id = ur.user_id')
                ->join('users u', 'u.id = ur.user_id')
                ->where('ur.company_id', $companyId)
                ->where('cm.status', 'active')
                ->where('u.active', 1)
                ->groupStart()
                    ->where('ur.effective_from <=', date('Y-m-d'))
                    ->orWhere('ur.effective_from', null)
                ->groupEnd()
                ->groupStart()
                    ->where('ur.effective_to >=', date('Y-m-d'))
                    ->orWhere('ur.effective_to', null)
                ->groupEnd()
                ->get()
                ->getResultArray();

            foreach ($assignedRoles as $role) {
                $roleId = (int) $role['role_id'];

                foreach ($permissions as $permission) {
                    $permissionId = (int) $permission['id'];

                    $exists = $this->db->table('role_permissions')
                        ->where([
                            'company_id'    => $companyId,
                            'role_id'       => $roleId,
                            'permission_id' => $permissionId,
                        ])
                        ->countAllResults() > 0;

                    if ($exists) {
                        continue;
                    }

                    $this->db->table('role_permissions')->insert([
                        'company_id'    => $companyId,
                        'role_id'       => $roleId,
                        'permission_id' => $permissionId,
                        'created_at'    => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Keep grants intact. Admin can revoke manually from RBAC.
    }
}
