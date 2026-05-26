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
    public function villages(string $search = '', int $limit = 100): array
    {
        $builder = $this->db->table('villages v')
            ->select('p.code AS province_code, p.name AS province, r.name AS regency, d.name AS district, v.code, v.name, v.type, v.postal_code, v.source_version')
            ->join('districts d', 'd.id = v.district_id')
            ->join('regencies r', 'r.id = d.regency_id')
            ->join('provinces p', 'p.id = r.province_id')
            ->where('v.is_active', true);

        $this->applyVillageSearch($builder, $search);

        return $builder
            ->orderBy('p.code', 'ASC')
            ->orderBy('r.name', 'ASC')
            ->orderBy('d.name', 'ASC')
            ->orderBy('v.name', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * @return array{provinces: int, regencies: int, districts: int, villages: int}
     */
    public function regionCounts(): array
    {
        return [
            'provinces' => $this->db->table('provinces')->where('is_active', true)->countAllResults(),
            'regencies' => $this->db->table('regencies')->where('is_active', true)->countAllResults(),
            'districts' => $this->db->table('districts')->where('is_active', true)->countAllResults(),
            'villages'  => $this->db->table('villages')->where('is_active', true)->countAllResults(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function villageOptions(string $search = '', ?int $selectedId = null, int $limit = 100): array
    {
        $builder = $this->db->table('villages v')
            ->select("v.id, v.name, d.name AS district, r.name AS regency, p.name AS province")
            ->join('districts d', 'd.id = v.district_id')
            ->join('regencies r', 'r.id = d.regency_id')
            ->join('provinces p', 'p.id = r.province_id')
            ->where('v.is_active', true);

        $this->applyVillageSearch($builder, $search);

        $options = $builder
            ->orderBy('p.name', 'ASC')
            ->orderBy('v.name', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        if ($selectedId === null || array_filter($options, static fn (array $option): bool => (int) $option['id'] === $selectedId) !== []) {
            return $options;
        }

        $selected = $this->db->table('villages v')
            ->select("v.id, v.name, d.name AS district, r.name AS regency, p.name AS province")
            ->join('districts d', 'd.id = v.district_id')
            ->join('regencies r', 'r.id = d.regency_id')
            ->join('provinces p', 'p.id = r.province_id')
            ->where('v.id', $selectedId)
            ->get()
            ->getFirstRow('array');

        return $selected === null ? $options : [$selected, ...$options];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function tenantAccessAssignments(): array
    {
        return $this->db->table('user_company_memberships m')
            ->select('m.id AS membership_id, m.company_id, m.user_id, ur.id AS assignment_id, c.code AS company_code, c.name AS company_name, u.username, u.active AS user_active, i.secret AS email, r.name AS role_name, GROUP_CONCAT(DISTINCT b.code) AS branch_codes, m.status')
            ->join('companies c', 'c.id = m.company_id')
            ->join('users u', 'u.id = m.user_id')
            ->join('auth_identities i', "i.user_id = u.id AND i.type = 'email_password'", 'left')
            ->join('user_roles ur', 'ur.company_id = m.company_id AND ur.user_id = m.user_id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->join('user_branch_memberships bm', "bm.company_id = m.company_id AND bm.user_id = m.user_id AND bm.status = 'active'", 'left')
            ->join('branches b', 'b.id = bm.branch_id', 'left')
            ->groupBy('m.id, m.company_id, m.user_id, ur.id, c.code, c.name, u.username, u.active, i.secret, r.name, m.status')
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
            ->select('u.id, u.username, u.active, i.secret AS email, i.force_reset')
            ->join('auth_identities i', "i.user_id = u.id AND i.type = 'email_password'", 'left')
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function branchMemberships(): array
    {
        return $this->db->table('user_branch_memberships bm')
            ->select('bm.id, bm.company_id, bm.user_id, bm.branch_id, bm.can_switch, bm.status, c.code AS company_code, u.username, b.code AS branch_code, b.name AS branch_name')
            ->join('companies c', 'c.id = bm.company_id')
            ->join('users u', 'u.id = bm.user_id')
            ->join('branches b', 'b.id = bm.branch_id')
            ->orderBy('c.name', 'ASC')
            ->orderBy('u.username', 'ASC')
            ->orderBy('b.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function roles(): array
    {
        return $this->db->table('roles r')
            ->select('r.id, r.company_id, r.code, r.name, r.is_system, r.status, c.code AS company_code, c.name AS company_name')
            ->join('companies c', 'c.id = r.company_id')
            ->orderBy('c.name', 'ASC')
            ->orderBy('r.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function branchOptions(): array
    {
        return $this->db->table('branches b')
            ->select('b.id, b.company_id, b.code, b.name, c.code AS company_code')
            ->join('companies c', 'c.id = b.company_id')
            ->where('b.deleted_at', null)
            ->where('b.status', 'active')
            ->orderBy('c.name', 'ASC')
            ->orderBy('b.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function permissions(): array
    {
        return $this->db->table('permissions p')
            ->select('p.id, p.company_id, p.code, p.name, p.module, c.code AS company_code, c.name AS company_name')
            ->join('companies c', 'c.id = p.company_id')
            ->orderBy('c.name', 'ASC')
            ->orderBy('p.module', 'ASC')
            ->orderBy('p.code', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function rolePermissionGrants(): array
    {
        return $this->db->table('role_permissions rp')
            ->select('rp.id, rp.company_id, c.code AS company_code, r.name AS role_name, p.code AS permission_code, p.name AS permission_name')
            ->join('companies c', 'c.id = rp.company_id')
            ->join('roles r', 'r.id = rp.role_id')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->orderBy('c.name', 'ASC')
            ->orderBy('r.name', 'ASC')
            ->orderBy('p.code', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function usernameExists(string $username): bool
    {
        return $this->db->table('users')
            ->where('username', $username)
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }

    public function userEmailExists(string $email): bool
    {
        return $this->db->table('auth_identities')
            ->where(['type' => 'email_password', 'secret' => $email])
            ->countAllResults() > 0;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function menuPermissionMatrix(): array
    {
        return $this->db->table('menu_permissions mp')
            ->select('mp.id, mp.company_id, c.code AS company_code, m.label AS menu_label, m.route, p.code AS permission_code')
            ->join('companies c', 'c.id = mp.company_id')
            ->join('menus m', 'm.id = mp.menu_id')
            ->join('permissions p', 'p.id = mp.permission_id')
            ->where('m.deleted_at', null)
            ->orderBy('c.name', 'ASC')
            ->orderBy('m.sort_order', 'ASC')
            ->orderBy('p.code', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function menus(): array
    {
        return $this->db->table('menus m')
            ->select('m.id, m.company_id, m.code, m.label, m.route, c.code AS company_code')
            ->join('companies c', 'c.id = m.company_id')
            ->where('m.deleted_at', null)
            ->where('m.route IS NOT NULL', null, false)
            ->where('m.route !=', '')
            ->orderBy('c.name', 'ASC')
            ->orderBy('m.sort_order', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function auditLogs(?int $companyId = null, string $eventType = '', string $search = '', int $limit = 100): array
    {
        $builder = $this->db->table('audit_logs a')
            ->select('a.id, a.event_type, a.entity_type, a.entity_id, a.after_json, a.occurred_at, c.code AS company_code, b.code AS branch_code, u.username')
            ->join('companies c', 'c.id = a.company_id', 'left')
            ->join('branches b', 'b.id = a.branch_id', 'left')
            ->join('users u', 'u.id = a.user_id', 'left');

        if ($companyId !== null) {
            $builder->where('a.company_id', $companyId);
        }

        if ($eventType !== '') {
            $builder->where('a.event_type', $eventType);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('a.entity_type', $search)
                ->orLike('a.after_json', $search)
                ->orLike('u.username', $search)
                ->groupEnd();
        }

        return $builder
            ->orderBy('a.occurred_at', 'DESC')
            ->orderBy('a.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<string>
     */
    public function auditEventTypes(): array
    {
        return array_column(
            $this->db->table('audit_logs')
                ->distinct()
                ->select('event_type')
                ->orderBy('event_type', 'ASC')
                ->get()
                ->getResultArray(),
            'event_type',
        );
    }

    public function roleCodeExists(int $companyId, string $code): bool
    {
        return $this->db->table('roles')
            ->where(['company_id' => $companyId, 'code' => $code])
            ->countAllResults() > 0;
    }

    public function permissionCodeExists(int $companyId, string $code): bool
    {
        return $this->db->table('permissions')
            ->where(['company_id' => $companyId, 'code' => $code])
            ->countAllResults() > 0;
    }

    private function applyVillageSearch(\CodeIgniter\Database\BaseBuilder $builder, string $search): void
    {
        $search = trim($search);

        if ($search === '') {
            return;
        }

        $builder->groupStart()
            ->like('v.name', $search)
            ->orLike('d.name', $search)
            ->orLike('r.name', $search)
            ->orLike('p.name', $search)
            ->orLike('v.code', $search)
            ->groupEnd();
    }
}
