<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

final class MasterImportMenuSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $companies = $this->db->table('companies')
            ->select('id')
            ->where('deleted_at', null)
            ->get()
            ->getResultArray();

        foreach ($companies as $company) {
            $companyId = (int) $company['id'];
            $permissionId = $this->permission($companyId, $now);
            $menuId = $this->menu($companyId, $now);
            $this->menuPermission($companyId, $menuId, $permissionId, $now);
            $this->grantToRoles($companyId, $permissionId, $now);
        }
    }

    private function permission(int $companyId, string $now): int
    {
        $existing = $this->db->table('permissions')
            ->where(['company_id' => $companyId, 'code' => 'master.import.manage'])
            ->get()
            ->getFirstRow('array');

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $this->db->table('permissions')->insert([
            'company_id' => $companyId,
            'code' => 'master.import.manage',
            'name' => 'Kelola Import Master Data',
            'module' => 'master-import',
            'created_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function menu(int $companyId, string $now): int
    {
        $existing = $this->db->table('menus')
            ->where(['company_id' => $companyId, 'code' => 'master-import'])
            ->get()
            ->getFirstRow('array');

        if ($existing !== null) {
            $this->db->table('menus')->where('id', $existing['id'])->update([
                'label' => 'Master Data Import',
                'route' => 'workspace/modules/master-import',
                'icon' => 'bx bx-upload',
                'sort_order' => 16,
                'updated_at' => $now,
            ]);

            return (int) $existing['id'];
        }

        $this->db->table('menus')->insert([
            'company_id' => $companyId,
            'code' => 'master-import',
            'label' => 'Master Data Import',
            'route' => 'workspace/modules/master-import',
            'icon' => 'bx bx-upload',
            'sort_order' => 16,
            'created_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function menuPermission(int $companyId, int $menuId, int $permissionId, string $now): void
    {
        $exists = $this->db->table('menu_permissions')
            ->where(['company_id' => $companyId, 'menu_id' => $menuId, 'permission_id' => $permissionId])
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

    private function grantToRoles(int $companyId, int $permissionId, string $now): void
    {
        $roleCodes = ['owner', 'manager', 'finance', 'purchasing', 'warehouse', 'sales'];
        $roles = $this->db->table('roles')
            ->select('id, code')
            ->where('company_id', $companyId)
            ->whereIn('code', $roleCodes)
            ->get()
            ->getResultArray();

        foreach ($roles as $role) {
            $exists = $this->db->table('role_permissions')
                ->where([
                    'company_id' => $companyId,
                    'role_id' => (int) $role['id'],
                    'permission_id' => $permissionId,
                ])
                ->countAllResults() > 0;

            if ($exists) {
                continue;
            }

            $this->db->table('role_permissions')->insert([
                'company_id' => $companyId,
                'role_id' => (int) $role['id'],
                'permission_id' => $permissionId,
                'created_at' => $now,
            ]);
        }
    }
}
