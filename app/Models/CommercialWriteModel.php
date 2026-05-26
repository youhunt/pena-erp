<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\AuditTrailService;
use CodeIgniter\Model;
use RuntimeException;

final class CommercialWriteModel extends Model
{
    protected $table = 'customers';

    /** @param array<string, mixed> $data */
    public function createCustomerTerm(array $data, int $actorId): void
    {
        $this->create('customer_terms', 'CUSTOMER_TERM_CREATED', 'customer_term', $data, $actorId);
    }

    /** @param array<string, mixed> $data */
    public function createSupplierTerm(array $data, int $actorId): void
    {
        $this->create('supplier_terms', 'SUPPLIER_TERM_CREATED', 'supplier_term', $data, $actorId);
    }

    /** @param array<string, mixed> $data */
    public function createCustomer(array $data, int $actorId): bool
    {
        return $this->createPartner('customers', 'customer_terms', 'CUSTOMER_CREATED', 'customer', $data, $actorId);
    }

    /** @param array<string, mixed> $data */
    public function createSupplier(array $data, int $actorId): bool
    {
        return $this->createPartner('suppliers', 'supplier_terms', 'SUPPLIER_CREATED', 'supplier', $data, $actorId);
    }

    /** @param array<string, mixed> $data */
    public function linkCustomerAddress(array $data, int $actorId): bool
    {
        return $this->linkAddress('customer_addresses', 'customer_id', 'customers', 'CUSTOMER_ADDRESS_LINKED', 'customer_address', $data, $actorId);
    }

    /** @param array<string, mixed> $data */
    public function linkSupplierAddress(array $data, int $actorId): bool
    {
        return $this->linkAddress('supplier_addresses', 'supplier_id', 'suppliers', 'SUPPLIER_ADDRESS_LINKED', 'supplier_address', $data, $actorId);
    }

    /** @param array<string, mixed> $data */
    public function createCustomerPromotion(array $data, int $actorId): bool
    {
        return $this->createPromotion('customer_promotions', 'customer_id', 'customers', 'CUSTOMER_PROMOTION_CREATED', 'customer_promotion', $data, $actorId);
    }

    /** @param array<string, mixed> $data */
    public function createSupplierPromotion(array $data, int $actorId): bool
    {
        return $this->createPromotion('supplier_promotions', 'supplier_id', 'suppliers', 'SUPPLIER_PROMOTION_CREATED', 'supplier_promotion', $data, $actorId);
    }

    /** @param array<string, mixed> $data */
    public function saveCustomerProfile(array $data, int $actorId): bool
    {
        return $this->saveProfile('customer_profiles', 'customer_id', 'customers', 'CUSTOMER_PROFILE', 'customer_profile', $data, $actorId);
    }

    /** @param array<string, mixed> $data */
    public function saveSupplierProfile(array $data, int $actorId): bool
    {
        return $this->saveProfile('supplier_profiles', 'supplier_id', 'suppliers', 'SUPPLIER_PROFILE', 'supplier_profile', $data, $actorId);
    }

    /** @param array<string, mixed> $data */
    private function createPartner(string $table, string $termsTable, string $event, string $entity, array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];

        if (! $this->tenantRecord('currencies', (int) $data['currency_id'], $companyId)
            || (($data['default_term_id'] ?? null) !== null && ! $this->tenantRecord($termsTable, (int) $data['default_term_id'], $companyId))) {
            return false;
        }

        $this->create($table, $event, $entity, $data, $actorId);

        return true;
    }

    /** @param array<string, mixed> $data */
    private function linkAddress(string $table, string $partnerId, string $partnerTable, string $event, string $entity, array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];

        if (! $this->tenantRecord($partnerTable, (int) $data[$partnerId], $companyId)
            || ! $this->tenantRecord('addresses', (int) $data['address_id'], $companyId)) {
            return false;
        }

        $this->create($table, $event, $entity, $data, $actorId);

        return true;
    }

    /** @param array<string, mixed> $data */
    private function createPromotion(string $table, string $partnerId, string $partnerTable, string $event, string $entity, array $data, int $actorId): bool
    {
        if (($data[$partnerId] ?? null) !== null && ! $this->tenantRecord($partnerTable, (int) $data[$partnerId], (int) $data['company_id'])) {
            return false;
        }

        $this->create($table, $event, $entity, $data, $actorId);

        return true;
    }

    /** @param array<string, mixed> $data */
    private function saveProfile(string $table, string $partnerId, string $partnerTable, string $eventPrefix, string $entity, array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];

        if (! $this->tenantRecord($partnerTable, (int) $data[$partnerId], $companyId)
            || (($data['default_tax_code_id'] ?? null) !== null && ! $this->tenantRecord('tax_codes', (int) $data['default_tax_code_id'], $companyId))
            || (($data['default_warehouse_id'] ?? null) !== null && ! $this->tenantRecord('warehouses', (int) $data['default_warehouse_id'], $companyId))) {
            return false;
        }

        $before = $this->db->table($table)
            ->where(['company_id' => $companyId, $partnerId => (int) $data[$partnerId]])
            ->where('deleted_at', null)
            ->get()
            ->getFirstRow('array');

        $this->db->transStart();

        if ($before === null) {
            $this->db->table($table)->insert($data + ['created_by' => $actorId, 'created_at' => date('Y-m-d H:i:s')]);
            $id = (int) $this->db->insertID();
            (new AuditTrailService($this->db))->record($eventPrefix . '_CREATED', $entity, $id, $companyId, null, $actorId, $data);
        } else {
            $id = (int) $before['id'];
            $update = $data + ['updated_by' => $actorId, 'updated_at' => date('Y-m-d H:i:s')];
            unset($update['company_id'], $update[$partnerId]);
            $this->db->table($table)->where('id', $id)->update($update);
            (new AuditTrailService($this->db))->record($eventPrefix . '_UPDATED', $entity, $id, $companyId, null, $actorId, $data, $before);
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Perubahan profile commercial gagal dan transaksi dibatalkan.');
        }

        return true;
    }

    private function tenantRecord(string $table, int $id, int $companyId): bool
    {
        return $this->db->table($table)->where(['id' => $id, 'company_id' => $companyId])->where('deleted_at', null)->countAllResults() === 1;
    }

    /** @param array<string, mixed> $data */
    private function create(string $table, string $event, string $entity, array $data, int $actorId): void
    {
        $this->db->transStart();
        $this->db->table($table)->insert($data + ['created_by' => $actorId, 'created_at' => date('Y-m-d H:i:s')]);
        $id = (int) $this->db->insertID();
        (new AuditTrailService($this->db))->record($event, $entity, $id, (int) $data['company_id'], null, $actorId, $data);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Perubahan commercial master gagal dan transaksi dibatalkan.');
        }
    }
}
