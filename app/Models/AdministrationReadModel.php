<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class AdministrationReadModel extends Model
{
    protected $table = 'companies';

    /**
     * @return list<array<string, mixed>>
     */
    public function companies(): array
    {
        return $this->db->table('companies c')
            ->select('c.id, c.code, c.name, c.base_currency, c.timezone, c.status, v.name AS village, d.name AS district, r.name AS regency, p.name AS province')
            ->join('villages v', 'v.id = c.village_id', 'left')
            ->join('districts d', 'd.id = v.district_id', 'left')
            ->join('regencies r', 'r.id = d.regency_id', 'left')
            ->join('provinces p', 'p.id = r.province_id', 'left')
            ->where('c.deleted_at', null)
            ->orderBy('c.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function branches(): array
    {
        return $this->db->table('branches b')
            ->select('b.id, b.company_id, c.code AS company_code, b.code, b.name, b.is_head_office, b.status, v.name AS village, r.name AS regency, p.name AS province')
            ->join('companies c', 'c.id = b.company_id')
            ->join('villages v', 'v.id = b.village_id', 'left')
            ->join('districts d', 'd.id = v.district_id', 'left')
            ->join('regencies r', 'r.id = d.regency_id', 'left')
            ->join('provinces p', 'p.id = r.province_id', 'left')
            ->where('b.deleted_at', null)
            ->orderBy('c.name', 'ASC')
            ->orderBy('b.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function company(int $id): ?array
    {
        return $this->db->table('companies')
            ->where('id', $id)
            ->where('deleted_at', null)
            ->get()
            ->getFirstRow('array');
    }

    public function branch(int $id): ?array
    {
        return $this->db->table('branches')
            ->where('id', $id)
            ->where('deleted_at', null)
            ->get()
            ->getFirstRow('array');
    }

    public function branchCodeExists(int $companyId, string $code, ?int $exceptId = null): bool
    {
        $builder = $this->db->table('branches')
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->where('deleted_at', null);

        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function villages(): array
    {
        return $this->db->table('villages v')
            ->select('p.code AS province_code, p.name AS province, r.name AS regency, d.name AS district, v.code, v.name, v.type, v.postal_code, v.source_version')
            ->join('districts d', 'd.id = v.district_id')
            ->join('regencies r', 'r.id = d.regency_id')
            ->join('provinces p', 'p.id = r.province_id')
            ->where('v.is_active', true)
            ->orderBy('p.code', 'ASC')
            ->orderBy('r.name', 'ASC')
            ->orderBy('d.name', 'ASC')
            ->orderBy('v.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function villageOptions(): array
    {
        return $this->db->table('villages v')
            ->select("v.id, v.name, d.name AS district, r.name AS regency, p.name AS province")
            ->join('districts d', 'd.id = v.district_id')
            ->join('regencies r', 'r.id = d.regency_id')
            ->join('provinces p', 'p.id = r.province_id')
            ->where('v.is_active', true)
            ->orderBy('p.name', 'ASC')
            ->orderBy('v.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function tenantAccessAssignments(): array
    {
        return $this->db->table('user_company_memberships m')
            ->select('m.company_id, c.code AS company_code, c.name AS company_name, u.username, i.secret AS email, r.name AS role_name, m.status')
            ->join('companies c', 'c.id = m.company_id')
            ->join('users u', 'u.id = m.user_id')
            ->join('auth_identities i', "i.user_id = u.id AND i.type = 'email_password'", 'left')
            ->join('user_roles ur', 'ur.company_id = m.company_id AND ur.user_id = m.user_id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->orderBy('c.name', 'ASC')
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function users(): array
    {
        return $this->db->table('users u')
            ->select('u.id, u.username, i.secret AS email')
            ->join('auth_identities i', "i.user_id = u.id AND i.type = 'email_password'", 'left')
            ->where('u.active', true)
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function roles(): array
    {
        return $this->db->table('roles r')
            ->select('r.id, r.company_id, r.name, c.code AS company_code')
            ->join('companies c', 'c.id = r.company_id')
            ->where('r.status', 'active')
            ->orderBy('c.name', 'ASC')
            ->orderBy('r.name', 'ASC')
            ->get()
            ->getResultArray();
    }
}
