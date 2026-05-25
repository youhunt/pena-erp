<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

final class DevelopmentFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $now     = date('Y-m-d H:i:s');
        $version = 'development-bootstrap-2026-05';

        $provinceId = $this->findOrCreate('provinces', 'code', '31', [
            'name'           => 'DKI Jakarta',
            'source_version' => $version,
            'is_active'      => true,
            'created_at'     => $now,
        ]);

        $regencyId = $this->findOrCreate('regencies', 'code', '31.73', [
            'province_id'    => $provinceId,
            'name'           => 'Kota Jakarta Barat',
            'type'           => 'kota',
            'source_version' => $version,
            'is_active'      => true,
            'created_at'     => $now,
        ]);

        $districtId = $this->findOrCreate('districts', 'code', '31.73.01', [
            'regency_id'     => $regencyId,
            'name'           => 'Cengkareng',
            'source_version' => $version,
            'is_active'      => true,
            'created_at'     => $now,
        ]);

        $villageId = $this->findOrCreate('villages', 'code', '31.73.01.1001', [
            'district_id'    => $districtId,
            'name'           => 'Cengkareng Barat',
            'type'           => 'kelurahan',
            'postal_code'    => '11730',
            'source_version' => $version,
            'is_active'      => true,
            'created_at'     => $now,
        ]);

        $companyId = $this->findOrCreate('companies', 'code', 'PENA', [
            'name'          => 'PT Pena Inovasi Sistem',
            'address'       => 'Alamat development',
            'village_id'    => $villageId,
            'postal_code'   => '11730',
            'base_currency' => 'IDR',
            'timezone'      => 'Asia/Jakarta',
            'status'        => 'active',
            'created_at'    => $now,
        ]);

        $existingBranch = $this->db->table('branches')
            ->where('company_id', $companyId)
            ->where('code', 'JKT')
            ->get()
            ->getFirstRow('array');

        if ($existingBranch === null) {
            $this->db->table('branches')->insert([
                'company_id'     => $companyId,
                'code'           => 'JKT',
                'name'           => 'Jakarta Head Office',
                'address'        => 'Alamat development',
                'village_id'     => $villageId,
                'postal_code'    => '11730',
                'is_head_office' => true,
                'status'         => 'active',
                'created_at'     => $now,
            ]);
        }

        $ownerRoleId = $this->findTenantRecord('roles', $companyId, 'code', 'owner', [
            'name'       => 'Owner',
            'is_system'  => true,
            'status'     => 'active',
            'created_at' => $now,
        ]);

        $viewPermissionId = $this->findTenantRecord('permissions', $companyId, 'code', 'company.dashboard.view', [
            'name'       => 'View company dashboard',
            'module'     => 'company',
            'created_at' => $now,
        ]);

        $managePermissionId = $this->findTenantRecord('permissions', $companyId, 'code', 'company.master.manage', [
            'name'       => 'Manage company master data',
            'module'     => 'company',
            'created_at' => $now,
        ]);

        $this->grantPermission($companyId, $ownerRoleId, $viewPermissionId, $now);
        $this->grantPermission($companyId, $ownerRoleId, $managePermissionId, $now);
        $this->assignDevelopmentAdmin($companyId, $ownerRoleId, $now);
    }

    /**
     * @param array<string, bool|int|string|null> $data
     */
    private function findOrCreate(string $table, string $key, string $value, array $data): int
    {
        $existing = $this->db->table($table)->where($key, $value)->get()->getFirstRow('array');

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $this->db->table($table)->insert([$key => $value] + $data);

        return (int) $this->db->insertID();
    }

    /**
     * @param array<string, bool|int|string|null> $data
     */
    private function findTenantRecord(string $table, int $companyId, string $key, string $value, array $data): int
    {
        $existing = $this->db->table($table)
            ->where('company_id', $companyId)
            ->where($key, $value)
            ->get()
            ->getFirstRow('array');

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $this->db->table($table)->insert(['company_id' => $companyId, $key => $value] + $data);

        return (int) $this->db->insertID();
    }

    private function grantPermission(int $companyId, int $roleId, int $permissionId, string $now): void
    {
        $exists = $this->db->table('role_permissions')
            ->where('company_id', $companyId)
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->countAllResults() > 0;

        if (! $exists) {
            $this->db->table('role_permissions')->insert([
                'company_id'    => $companyId,
                'role_id'       => $roleId,
                'permission_id' => $permissionId,
                'created_at'    => $now,
            ]);
        }
    }

    private function assignDevelopmentAdmin(int $companyId, int $roleId, string $now): void
    {
        $identity = $this->db->table('auth_identities')
            ->select('user_id')
            ->where('type', 'email_password')
            ->where('secret', 'admin@belajardisiniaja.com')
            ->get()
            ->getFirstRow('array');

        if ($identity === null) {
            return;
        }

        $userId = (int) $identity['user_id'];

        if ($this->db->table('user_company_memberships')->where(['company_id' => $companyId, 'user_id' => $userId])->countAllResults() === 0) {
            $this->db->table('user_company_memberships')->insert([
                'company_id' => $companyId,
                'user_id'    => $userId,
                'is_default' => true,
                'status'     => 'active',
                'created_at' => $now,
            ]);
        }

        if ($this->db->table('user_roles')->where(['company_id' => $companyId, 'user_id' => $userId, 'role_id' => $roleId])->countAllResults() === 0) {
            $this->db->table('user_roles')->insert([
                'company_id'     => $companyId,
                'user_id'        => $userId,
                'role_id'        => $roleId,
                'effective_from' => date('Y-m-d'),
                'created_at'     => $now,
            ]);
        }
    }
}
