<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\AuditTrailService;
use CodeIgniter\Model;
use RuntimeException;

final class AdministrationWriteModel extends Model
{
    protected $table = 'companies';

    /**
     * @param array<string, mixed> $data
     */
    public function createCompany(array $data, int $actorId): void
    {
        $this->db->transStart();
        $this->db->table('companies')->insert($data + [
            'created_by' => $actorId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $this->audit()->record('COMPANY_CREATED', 'company', $id, $id, null, $actorId, $data);
        $this->completeTransaction();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateCompany(int $id, array $data, int $actorId): void
    {
        $before = $this->db->table('companies')->where('id', $id)->get()->getFirstRow('array');

        $this->db->transStart();
        $this->db->table('companies')->where('id', $id)->update($data + [
            'updated_by' => $actorId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit()->record('COMPANY_UPDATED', 'company', $id, $id, null, $actorId, $data, $before);
        $this->completeTransaction();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createBranch(array $data, int $actorId): void
    {
        $this->db->transStart();
        $this->db->table('branches')->insert($data + [
            'created_by' => $actorId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $this->audit()->record('BRANCH_CREATED', 'branch', $id, (int) $data['company_id'], $id, $actorId, $data);
        $this->completeTransaction();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateBranch(int $id, array $data, int $actorId): void
    {
        $before = $this->db->table('branches')->where('id', $id)->get()->getFirstRow('array');

        if ($before === null) {
            return;
        }

        // A branch cannot cross tenant ownership through a regular edit operation.
        $data['company_id'] = (int) $before['company_id'];
        $this->db->transStart();
        $this->db->table('branches')->where('id', $id)->update($data + [
            'updated_by' => $actorId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit()->record('BRANCH_UPDATED', 'branch', $id, (int) $before['company_id'], $id, $actorId, $data, $before);
        $this->completeTransaction();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createRole(array $data, int $actorId): void
    {
        $this->db->transStart();
        $this->db->table('roles')->insert($data + [
            'is_system'  => false,
            'created_by' => $actorId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $this->audit()->record('ROLE_CREATED', 'role', $id, (int) $data['company_id'], null, $actorId, $data);
        $this->completeTransaction();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createPermission(array $data, int $actorId): void
    {
        $this->db->transStart();
        $this->db->table('permissions')->insert($data + [
            'created_by' => $actorId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $this->audit()->record('PERMISSION_CREATED', 'permission', $id, (int) $data['company_id'], null, $actorId, $data);
        $this->completeTransaction();
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
            $this->db->transStart();
            $this->db->table('role_permissions')->insert([
                'company_id'    => $companyId,
                'role_id'       => $roleId,
                'permission_id' => $permissionId,
                'created_by'    => $actorId,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
            $id = (int) $this->db->insertID();
            $this->audit()->record('ROLE_PERMISSION_GRANTED', 'role_permission', $id, $companyId, null, $actorId, [
                'role_id'       => $roleId,
                'permission_id' => $permissionId,
            ]);
            $this->completeTransaction();
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
        $this->db->transStart();
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

        $this->audit()->record('USER_ROLE_ASSIGNED', 'user', $userId, $companyId, $branchId, $actorId, [
            'role_id'   => $roleId,
            'branch_id' => $branchId,
        ]);
        $this->completeTransaction();

        return true;
    }

    public function revokeRolePermission(int $companyId, int $grantId, int $actorId): bool
    {
        $grant = $this->db->table('role_permissions')
            ->where(['id' => $grantId, 'company_id' => $companyId])
            ->get()
            ->getFirstRow('array');

        if ($grant === null) {
            return false;
        }

        $this->db->transStart();
        $this->db->table('role_permissions')->where('id', $grantId)->delete();
        $this->audit()->record('ROLE_PERMISSION_REVOKED', 'role_permission', $grantId, $companyId, null, $actorId, [
            'role_id'       => (int) $grant['role_id'],
            'permission_id' => (int) $grant['permission_id'],
        ], $grant);
        $this->completeTransaction();

        return true;
    }

    private function audit(): AuditTrailService
    {
        return new AuditTrailService($this->db);
    }

    private function completeTransaction(): void
    {
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Perubahan administrasi gagal dan transaksi dibatalkan.');
        }
    }
}
