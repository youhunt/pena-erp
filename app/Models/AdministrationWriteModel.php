<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class AdministrationWriteModel extends Model
{
    protected $table = 'companies';

    /**
     * @param array<string, mixed> $data
     */
    public function createCompany(array $data, int $actorId): void
    {
        $this->db->table('companies')->insert($data + [
            'created_by' => $actorId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateCompany(int $id, array $data, int $actorId): void
    {
        $this->db->table('companies')->where('id', $id)->update($data + [
            'updated_by' => $actorId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createBranch(array $data, int $actorId): void
    {
        $this->db->table('branches')->insert($data + [
            'created_by' => $actorId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateBranch(int $id, array $data, int $actorId): void
    {
        $this->db->table('branches')->where('id', $id)->update($data + [
            'updated_by' => $actorId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createRole(array $data, int $actorId): void
    {
        $this->db->table('roles')->insert($data + [
            'is_system'  => false,
            'created_by' => $actorId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createPermission(array $data, int $actorId): void
    {
        $this->db->table('permissions')->insert($data + [
            'created_by' => $actorId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function grantRolePermission(int $companyId, int $roleId, int $permissionId, int $actorId): bool
    {
        $roleExists = $this->db->table('roles')
            ->where(['id' => $roleId, 'company_id' => $companyId, 'status' => 'active'])
            ->countAllResults() === 1;
        $permissionExists = $this->db->table('permissions')
            ->where(['id' => $permissionId, 'company_id' => $companyId])
            ->countAllResults() === 1;

        if (! $roleExists || ! $permissionExists) {
            return false;
        }

        $grantExists = $this->db->table('role_permissions')
            ->where(['company_id' => $companyId, 'role_id' => $roleId, 'permission_id' => $permissionId])
            ->countAllResults() > 0;

        if (! $grantExists) {
            $this->db->table('role_permissions')->insert([
                'company_id'    => $companyId,
                'role_id'       => $roleId,
                'permission_id' => $permissionId,
                'created_by'    => $actorId,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        return true;
    }

    public function assignRole(int $companyId, int $userId, int $roleId, ?int $branchId, int $actorId): bool
    {
        $roleExists = $this->db->table('roles')
            ->where(['id' => $roleId, 'company_id' => $companyId, 'status' => 'active'])
            ->countAllResults() === 1;

        if (! $roleExists) {
            return false;
        }

        if ($branchId !== null && $this->db->table('branches')
            ->where(['id' => $branchId, 'company_id' => $companyId, 'status' => 'active'])
            ->where('deleted_at', null)
            ->countAllResults() !== 1) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $membership = $this->db->table('user_company_memberships')
            ->where(['company_id' => $companyId, 'user_id' => $userId])
            ->get()
            ->getFirstRow('array');

        if ($membership === null) {
            $this->db->table('user_company_memberships')->insert([
                'company_id' => $companyId,
                'user_id'    => $userId,
                'status'     => 'active',
                'created_by' => $actorId,
                'created_at' => $now,
            ]);
        } else {
            $this->db->table('user_company_memberships')->where('id', $membership['id'])->update([
                'status'     => 'active',
                'updated_by' => $actorId,
                'updated_at' => $now,
            ]);
        }

        $assignment = $this->db->table('user_roles')
            ->where(['company_id' => $companyId, 'user_id' => $userId, 'role_id' => $roleId])
            ->countAllResults();

        if ($assignment === 0) {
            $this->db->table('user_roles')->insert([
                'company_id'     => $companyId,
                'user_id'        => $userId,
                'role_id'        => $roleId,
                'effective_from' => date('Y-m-d'),
                'created_by'     => $actorId,
                'created_at'     => $now,
            ]);
        }

        if ($branchId !== null) {
            $branchMembership = $this->db->table('user_branch_memberships')
                ->where(['company_id' => $companyId, 'user_id' => $userId, 'branch_id' => $branchId])
                ->get()
                ->getFirstRow('array');

            if ($branchMembership === null) {
                $this->db->table('user_branch_memberships')->insert([
                    'company_id' => $companyId,
                    'user_id'    => $userId,
                    'branch_id'  => $branchId,
                    'can_switch' => true,
                    'status'     => 'active',
                    'created_by' => $actorId,
                    'created_at' => $now,
                ]);
            } else {
                $this->db->table('user_branch_memberships')->where('id', $branchMembership['id'])->update([
                    'can_switch' => true,
                    'status'     => 'active',
                    'updated_by' => $actorId,
                    'updated_at' => $now,
                ]);
            }
        }

        return true;
    }
}
