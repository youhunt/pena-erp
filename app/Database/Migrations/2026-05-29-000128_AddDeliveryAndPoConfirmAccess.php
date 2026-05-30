<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class AddDeliveryAndPoConfirmAccess extends Migration
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

            $deliveryView = $this->ensurePermission($companyId, 'sales.delivery.view', 'View Sales Delivery', 'sales', $now);
            $deliveryManage = $this->ensurePermission($companyId, 'sales.delivery.manage', 'Manage Sales Delivery', 'sales', $now);
            $poConfirm = $this->ensurePermission($companyId, 'purchasing.po.confirm', 'Confirm Purchase Order', 'purchasing', $now);

            $menuId = $this->ensureMenu($companyId, 'sales-delivery', 'Delivery Order', 'sales/deliveries', 'bx bx-package', 640, $now);
            $this->ensureMenuPermission($companyId, $menuId, $deliveryView, $now);

            $this->grantToActiveRoles($companyId, [$deliveryView, $deliveryManage, $poConfirm], $now);
        }
    }

    public function down(): void
    {
        // Data is intentionally kept to avoid removing user-managed RBAC grants.
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
            $this->db->table('menus')->where('id', (int) $row['id'])->update([
                'label'      => $label,
                'route'      => $route,
                'icon'       => $icon,
                'sort_order' => $sortOrder,
                'updated_at' => $now,
            ]);

            return (int) $row['id'];
        }

        $this->db->table('menus')->insert([
            'company_id'  => $companyId,
            'code'        => $code,
            'label'       => $label,
            'route'       => $route,
            'icon'        => $icon,
            'sort_order'  => $sortOrder,
            'created_at'  => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function ensureMenuPermission(int $companyId, int $menuId, int $permissionId, string $now): void
    {
        $exists = $this->db->table('menu_permissions')
            ->where(['company_id' => $companyId, 'menu_id' => $menuId, 'permission_id' => $permissionId])
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
                    ->where(['company_id' => $companyId, 'role_id' => $roleId, 'permission_id' => $permissionId])
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
