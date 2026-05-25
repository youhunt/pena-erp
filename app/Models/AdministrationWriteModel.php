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

    public function assignRole(int $companyId, int $userId, int $roleId, int $actorId): bool
    {
        $roleExists = $this->db->table('roles')
            ->where(['id' => $roleId, 'company_id' => $companyId, 'status' => 'active'])
            ->countAllResults() === 1;

        if (! $roleExists) {
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

        return true;
    }
}
