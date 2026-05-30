<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class AddMissingRouteMenus extends Migration
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
            $rbacManage = $this->ensurePermission($companyId, 'platform.rbac.manage', 'Manage Roles & Permissions', 'administration', $now);
            $financeView = $this->ensurePermission($companyId, 'finance.invoice.view', 'View Finance Invoices & Payments', 'finance', $now);
            $financeManage = $this->ensurePermission($companyId, 'finance.invoice.manage', 'Manage Finance Invoices & Payments', 'finance', $now);

            $menus = [
                ['administration-regions', 'Regions', 'administration/regions', 'bx bx-map', 42, $companyManage],
                ['administration-menu-debug', 'Menu Debug', 'administration/menu-debug', 'bx bx-bug', 48, $rbacManage],
                ['finance-transactions', 'Finance Transactions', 'finance/invoices', 'bx bx-receipt', 710, $financeView],
            ];

            foreach ($menus as [$code, $label, $route, $icon, $sortOrder, $permissionId]) {
                $menuId = $this->ensureMenu($companyId, $code, $label, $route, $icon, $sortOrder, $now);
                $this->ensureMenuPermission($companyId, $menuId, (int) $permissionId, $now);
            }

            $this->grantToAssignedRoles($companyId, [$companyManage, $rbacManage, $financeView, $financeManage], $now);
        }
    }

    public function down(): void
    {
        // Non-destructive migration. Menus and grants can be managed from RBAC.
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
            'code' => $code,
            'name' => $name,
            'module' => $module,
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
                    'label' => $label,
                    'route' => $route,
                    'icon' => $icon,
                    'sort_order' => $sortOrder,
                    'updated_at' => $now,
                ]);

            return (int) $row['id'];
        }

        $this->db->table('menus')->insert([
            'company_id' => $companyId,
            'code' => $code,
            'label' => $label,
            'route' => $route,
            'icon' => $icon,
            'sort_order' => $sortOrder,
            'created_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function ensureMenuPermission(int $companyId, int $menuId, int $permissionId, string $now): void
    {
        $exists = $this->db->table('menu_permissions')
            ->where([
                'company_id' => $companyId,
                'menu_id' => $menuId,
                'permission_id' => $permissionId,
            ])
            ->countAllResults() > 0;

        if ($exists) {
            return;
        }

        $this->db->table('menu_permissions')->insert([
            'company_id' => $companyId,
            'menu_id' => $menuId,
            'permission_id' => $permissionId,
            'created_at' => $now,
        ]);
    }

    /** @param list<int> $permissionIds */
    private function grantToAssignedRoles(int $companyId, array $permissionIds, string $now): void
    {
        $roles = $this->db->table('user_roles')
            ->distinct()
            ->select('role_id')
            ->where('company_id', $companyId)
            ->get()
            ->getResultArray();

        foreach ($roles as $role) {
            $roleId = (int) $role['role_id'];
            foreach ($permissionIds as $permissionId) {
                $exists = $this->db->table('role_permissions')
                    ->where([
                        'company_id' => $companyId,
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    ])
                    ->countAllResults() > 0;

                if ($exists) {
                    continue;
                }

                $this->db->table('role_permissions')->insert([
                    'company_id' => $companyId,
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                ]);
            }
        }
    }
}
