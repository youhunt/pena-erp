<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class AddAdministrationMenus extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $companies = $this->db->table('companies')
            ->select('id')
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getResultArray();

        foreach ($companies as $company) {
            $companyId = (int) $company['id'];

            $companyManage = $this->ensurePermission($companyId, 'platform.company.manage', 'Manage Company & Branch', 'administration', $now);
            $userManage    = $this->ensurePermission($companyId, 'platform.user.manage', 'Manage Users & Access', 'administration', $now);
            $rbacManage    = $this->ensurePermission($companyId, 'platform.rbac.manage', 'Manage Roles & Permissions', 'administration', $now);
            $auditView     = $this->ensurePermission($companyId, 'platform.audit.view', 'View Audit Trail', 'administration', $now);

            $menus = [
                ['administration-companies', 'Companies', 'administration/companies', 'bx bx-buildings', 40, $companyManage],
                ['administration-branches', 'Branches', 'administration/branches', 'bx bx-git-branch', 41, $companyManage],
                ['administration-access', 'User Access', 'administration/access', 'bx bx-user-check', 42, $userManage],
                ['administration-rbac', 'RBAC', 'administration/rbac', 'bx bx-shield-quarter', 43, $rbacManage],
                ['administration-audit', 'Audit Trail', 'administration/audit', 'bx bx-history', 44, $auditView],
            ];

            foreach ($menus as [$code, $label, $route, $icon, $sortOrder, $permissionId]) {
                $menuId = $this->ensureMenu($companyId, $code, $label, $route, $icon, $sortOrder, $now);
                $this->ensureMenuPermission($companyId, $menuId, (int) $permissionId, $now);
            }

            $this->grantToActiveRoles($companyId, [$companyManage, $userManage, $rbacManage, $auditView], $now);
        }
    }

    public function down(): void
    {
        // Keep non-destructive. Admin may have modified menu/permission grants manually.
    }

    private function ensurePermission(int $companyId, string $code, string $name, string $module, string $now): int
    {
        $row = $this->db->table('permissions')
            ->select('id')
            ->where(['company_id' => $companyId, 'code' => $code])
            ->get()
            ->getFirstRow('array');

        if ($row !== null) {
            return (int) $row['id'];
        }

        $this->db->table('permissions')->insert([
            'company_id' => $companyId,
            'code'       => $code,
            'name'       => $name,
            'module'     => $module,
            'created_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function ensureMenu(int $companyId, string $code, string $label, string $route, string $icon, int $sortOrder, string $now): int
    {
        $row = $this->db->table('menus')
            ->select('id')
            ->where(['company_id' => $companyId, 'code' => $code])
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getFirstRow('array');

        if ($row !== null) {
            $this->db->table('menus')
                ->where('id', (int) $row['id'])
                ->update([
                    'label'      => $label,
                    'route'      => $route,
                    'icon'       => $icon,
                    'sort_order' => $sortOrder,
                    'updated_at' => $now,
                ]);

            return (int) $row['id'];
        }

        $this->db->table('menus')->insert([
            'company_id' => $companyId,
            'code'       => $code,
            'label'      => $label,
            'route'      => $route,
            'icon'       => $icon,
            'sort_order' => $sortOrder,
            'created_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function ensureMenuPermission(int $companyId, int $menuId, int $permissionId, string $now): void
    {
        $exists = $this->db->table('menu_permissions')
            ->where([
                'company_id'    => $companyId,
                'menu_id'       => $menuId,
                'permission_id' => $permissionId,
            ])
            ->countAllResults() > 0;

        if ($exists) {
            return;
        }

        $this->db->table('menu_permissions')->insert([
            'company_id'    => $companyId,
            'menu_id'       => $menuId,
            'permission_id' => $permissionId,
            'created_at'    => $now,
        ]);
    }

    /** @param list<int> $permissionIds */
    private function grantToActiveRoles(int $companyId, array $permissionIds, string $now): void
    {
        $roles = $this->db->table('roles')
            ->select('id')
            ->where(['company_id' => $companyId, 'status' => 'active'])
            ->get()
            ->getResultArray();

        foreach ($roles as $role) {
            $roleId = (int) $role['id'];

            foreach ($permissionIds as $permissionId) {
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
