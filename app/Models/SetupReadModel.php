<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class SetupReadModel extends Model
{
    protected $table = 'departments';

    /**
     * @return list<array<string, mixed>>
     */
    public function departments(int $companyId): array
    {
        return $this->tenantRows('departments', $companyId, 'name');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function currencies(int $companyId): array
    {
        return $this->tenantRows('currencies', $companyId, 'code');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function taxCodes(int $companyId): array
    {
        return $this->tenantRows('tax_codes', $companyId, 'code');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function transactionCodes(int $companyId): array
    {
        return $this->db->table('transaction_codes t')
            ->select('t.*, b.code AS branch_code')
            ->join('branches b', 'b.id = t.branch_id AND b.company_id = t.company_id', 'left')
            ->where('t.company_id', $companyId)
            ->where('t.deleted_at', null)
            ->orderBy('t.module', 'ASC')
            ->orderBy('t.code', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function addresses(int $companyId): array
    {
        return $this->db->table('addresses a')
            ->select('a.*, c.name AS country_name, v.name AS village_name, r.name AS city_name')
            ->join('countries c', 'c.id = a.country_id')
            ->join('villages v', 'v.id = a.village_id', 'left')
            ->join('districts d', 'd.id = v.district_id', 'left')
            ->join('regencies r', 'r.id = d.regency_id', 'left')
            ->where('a.company_id', $companyId)
            ->where('a.deleted_at', null)
            ->orderBy('a.label', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function countries(): array
    {
        return $this->db->table('countries')
            ->where('is_active', true)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function branchOptions(int $companyId): array
    {
        return $this->db->table('branches')
            ->select('id, code, name')
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function codeExists(string $table, int $companyId, string $code, ?int $branchId = null): bool
    {
        $builder = $this->db->table($table)
            ->where(['company_id' => $companyId, 'code' => $code])
            ->where('deleted_at', null);

        if ($table === 'transaction_codes') {
            $branchId === null ? $builder->where('branch_id', null) : $builder->where('branch_id', $branchId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function tenantRows(string $table, int $companyId, string $orderBy): array
    {
        return $this->db->table($table)
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->orderBy($orderBy, 'ASC')
            ->get()
            ->getResultArray();
    }
}
