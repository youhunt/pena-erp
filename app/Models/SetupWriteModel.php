<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\AuditTrailService;
use CodeIgniter\Model;
use RuntimeException;

final class SetupWriteModel extends Model
{
    protected $table = 'departments';

    /**
     * @param array<string, mixed> $data
     */
    public function createDepartment(array $data, int $actorId): void
    {
        $this->create('departments', 'DEPARTMENT_CREATED', 'department', $data, $actorId);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createCurrency(array $data, int $actorId): void
    {
        $this->create('currencies', 'CURRENCY_CREATED', 'currency', $data, $actorId);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createTaxCode(array $data, int $actorId): void
    {
        $this->create('tax_codes', 'TAX_CODE_CREATED', 'tax_code', $data, $actorId);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createTransactionCode(array $data, int $actorId): bool
    {
        if (($data['branch_id'] ?? null) !== null && ! $this->tenantRecord('branches', (int) $data['branch_id'], (int) $data['company_id'])) {
            return false;
        }

        $this->create('transaction_codes', 'TRANSACTION_CODE_CREATED', 'transaction_code', $data, $actorId, $data['branch_id'] ?? null);

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createAddress(array $data, int $actorId): bool
    {
        $countryExists = $this->db->table('countries')
            ->where(['id' => $data['country_id'], 'is_active' => true])
            ->countAllResults() === 1;
        $villageExists = ($data['village_id'] ?? null) === null || $this->db->table('villages')
            ->where(['id' => $data['village_id'], 'is_active' => true])
            ->countAllResults() === 1;

        if (! $countryExists || ! $villageExists) {
            return false;
        }

        $this->create('addresses', 'ADDRESS_CREATED', 'address', $data, $actorId);

        return true;
    }

    private function tenantRecord(string $table, int $id, int $companyId): bool
    {
        return $this->db->table($table)
            ->where(['id' => $id, 'company_id' => $companyId])
            ->where('deleted_at', null)
            ->countAllResults() === 1;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function create(string $table, string $event, string $entity, array $data, int $actorId, ?int $branchId = null): void
    {
        $this->db->transStart();
        $this->db->table($table)->insert($data + [
            'created_by' => $actorId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        (new AuditTrailService($this->db))->record($event, $entity, $id, (int) $data['company_id'], $branchId, $actorId, $data);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Perubahan setup master gagal dan transaksi dibatalkan.');
        }
    }
}
