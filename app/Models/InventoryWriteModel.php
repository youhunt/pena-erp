<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\AuditTrailService;
use CodeIgniter\Model;
use RuntimeException;

final class InventoryWriteModel extends Model
{
    protected $table = 'products';

    /**
     * @param array<string, mixed> $data
     */
    public function createUnitOfMeasure(array $data, int $actorId): void
    {
        $id = $this->insertAudited('units_of_measure', $data, $actorId);
        $this->audit()->record('UOM_CREATED', 'unit_of_measure', $id, (int) $data['company_id'], null, $actorId, $data);
        $this->completeTransaction();
    }

    /**
     * @param array<string, mixed> $data
     */
    /** @param array<string, mixed> $data */
    public function updateUnitOfMeasure(int $companyId, int $id, array $data, int $actorId): bool
    {
        return $this->updateTenantStatus('units_of_measure', 'unit_of_measure', 'UOM_UPDATED', $companyId, $id, $data, null, $actorId);
    }

    public function createProductCategory(array $data, int $actorId): bool
    {
        if (($data['parent_id'] ?? null) !== null && ! $this->sameTenantRecord('product_categories', (int) $data['parent_id'], (int) $data['company_id'])) {
            return false;
        }

        $id = $this->insertAudited('product_categories', $data, $actorId);
        $this->audit()->record('PRODUCT_CATEGORY_CREATED', 'product_category', $id, (int) $data['company_id'], null, $actorId, $data);
        $this->completeTransaction();

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    /** @param array<string, mixed> $data */
    public function updateProductCategory(int $companyId, int $id, array $data, int $actorId): bool
    {
        return $this->updateTenantStatus('product_categories', 'product_category', 'PRODUCT_CATEGORY_UPDATED', $companyId, $id, $data, null, $actorId);
    }

    public function createProduct(array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];

        if (! $this->sameTenantRecord('units_of_measure', (int) $data['base_uom_id'], $companyId)) {
            return false;
        }

        if (($data['category_id'] ?? null) !== null && ! $this->sameTenantRecord('product_categories', (int) $data['category_id'], $companyId)) {
            return false;
        }

        $id = $this->insertAudited('products', $data, $actorId);
        $this->audit()->record('PRODUCT_CREATED', 'product', $id, $companyId, null, $actorId, $data);
        $this->completeTransaction();

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    /** @param array<string, mixed> $data */
    public function updateProduct(int $companyId, int $id, array $data, int $actorId): bool
    {
        if (! $this->activeTenantRecord('units_of_measure', (int) $data['base_uom_id'], $companyId, 'status', 'active')
            || (($data['category_id'] ?? null) !== null && ! $this->activeTenantRecord('product_categories', (int) $data['category_id'], $companyId, 'status', 'active'))) {
            return false;
        }

        return $this->updateTenantStatus('products', 'product', 'PRODUCT_UPDATED', $companyId, $id, $data, null, $actorId);
    }

    public function createWarehouse(array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];

        if (! $this->activeTenantRecord('branches', (int) $data['branch_id'], $companyId, 'status', 'active')) {
            return false;
        }

        $department = $this->db->table('departments')
            ->where([
                'id'         => $data['department_id'],
                'company_id' => $companyId,
                'branch_id'  => $data['branch_id'],
                'status'     => 'active',
            ])
            ->where('deleted_at', null)
            ->get()
            ->getFirstRow('array');

        if ($department === null) {
            return false;
        }

        $id = $this->insertAudited('warehouses', $data, $actorId);
        $this->audit()->record('WAREHOUSE_CREATED', 'warehouse', $id, $companyId, (int) $data['branch_id'], $actorId, $data);
        $this->completeTransaction();

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    /** @param array<string, mixed> $data */
    public function updateWarehouse(int $companyId, int $id, array $data, int $actorId): bool
    {
        return $this->updateTenantStatus('warehouses', 'warehouse', 'WAREHOUSE_UPDATED', $companyId, $id, $data, null, $actorId);
    }

    public function createLocation(array $data, int $actorId): bool
    {
        $warehouse = $this->db->table('warehouses')
            ->where(['id' => $data['warehouse_id'], 'company_id' => $data['company_id'], 'is_active' => true])
            ->where('deleted_at', null)
            ->get()
            ->getFirstRow('array');

        if ($warehouse === null || $warehouse['department_id'] === null || (int) $warehouse['branch_id'] !== (int) $data['branch_id']) {
            return false;
        }

        $id = $this->insertAudited('warehouse_bins', $data, $actorId);
        $this->audit()->record('WAREHOUSE_LOCATION_CREATED', 'warehouse_location', $id, (int) $data['company_id'], (int) $data['branch_id'], $actorId, $data);
        $this->completeTransaction();

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    /** @param array<string, mixed> $data */
    public function updateLocation(int $companyId, int $id, array $data, int $actorId): bool
    {
        return $this->updateTenantStatus('warehouse_bins', 'warehouse_location', 'WAREHOUSE_LOCATION_UPDATED', $companyId, $id, $data, null, $actorId);
    }

    public function createUomConversion(array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];

        if (! $this->sameTenantRecord('products', (int) $data['product_id'], $companyId)
            || ! $this->sameTenantRecord('units_of_measure', (int) $data['from_uom_id'], $companyId)
            || ! $this->sameTenantRecord('units_of_measure', (int) $data['to_uom_id'], $companyId)) {
            return false;
        }

        $id = $this->insertAudited('product_uom_conversions', $data, $actorId);
        $this->audit()->record('PRODUCT_UOM_CONVERSION_CREATED', 'product_uom_conversion', $id, $companyId, null, $actorId, $data);
        $this->completeTransaction();

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createItemTax(array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];

        if (! $this->sameTenantRecord('products', (int) $data['product_id'], $companyId)
            || ! $this->sameTenantRecord('tax_codes', (int) $data['tax_code_id'], $companyId)) {
            return false;
        }

        $id = $this->insertAudited('product_tax_codes', $data, $actorId);
        $this->audit()->record('PRODUCT_TAX_CODE_CREATED', 'product_tax_code', $id, $companyId, null, $actorId, $data);
        $this->completeTransaction();

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createBatch(array $data, int $actorId): bool
    {
        if (! $this->sameTenantRecord('products', (int) $data['product_id'], (int) $data['company_id'])) {
            return false;
        }

        $id = $this->insertAudited('stock_lots', $data, $actorId);
        $this->audit()->record('STOCK_LOT_CREATED', 'stock_lot', $id, (int) $data['company_id'], null, $actorId, $data);
        $this->completeTransaction();

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    /** @param array<string, mixed> $data */
    public function updateStockLot(int $companyId, int $id, array $data, int $actorId): bool
    {
        return $this->updateTenantStatus('stock_lots', 'stock_lot', 'STOCK_LOT_UPDATED', $companyId, $id, $data, null, $actorId);
    }

    public function saveProductProfile(array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];

        if (! $this->activeTenantRecord('products', (int) $data['product_id'], $companyId, 'status', 'active')
            || (($data['default_warehouse_id'] ?? null) !== null && ! $this->activeTenantRecord('warehouses', (int) $data['default_warehouse_id'], $companyId, 'is_active', true))
            || (($data['package_uom_id'] ?? null) !== null && ! $this->activeTenantRecord('units_of_measure', (int) $data['package_uom_id'], $companyId, 'status', 'active'))) {
            return false;
        }

        $before = $this->db->table('product_profiles')
            ->where(['company_id' => $companyId, 'product_id' => (int) $data['product_id']])
            ->where('deleted_at', null)
            ->get()
            ->getFirstRow('array');

        $this->db->transStart();

        if ($before === null) {
            $this->db->table('product_profiles')->insert($data + [
                'created_by' => $actorId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $id = (int) $this->db->insertID();
            $this->audit()->record('PRODUCT_PROFILE_CREATED', 'product_profile', $id, $companyId, null, $actorId, $data);
        } else {
            $id = (int) $before['id'];
            $update = $data + ['updated_by' => $actorId, 'updated_at' => date('Y-m-d H:i:s')];
            unset($update['company_id'], $update['product_id']);
            $this->db->table('product_profiles')->where('id', $id)->update($update);
            $this->audit()->record('PRODUCT_PROFILE_UPDATED', 'product_profile', $id, $companyId, null, $actorId, $data, $before);
        }

        $this->completeTransaction();

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createProductPrice(array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];

        if (! $this->activeTenantRecord('products', (int) $data['product_id'], $companyId, 'status', 'active')
            || ! $this->activeTenantRecord('currencies', (int) $data['currency_id'], $companyId, 'status', 'active')
            || ! $this->activeTenantRecord('units_of_measure', (int) $data['uom_id'], $companyId, 'status', 'active')) {
            return false;
        }

        $id = $this->insertAudited('product_prices', $data, $actorId);
        $this->audit()->record('PRODUCT_PRICE_CREATED', 'product_price', $id, $companyId, null, $actorId, $data);
        $this->completeTransaction();

        return true;
    }

    public function updateProductStatus(int $companyId, int $productId, string $status, int $actorId): bool
    {
        return $this->updateTenantStatus('products', 'product', 'PRODUCT_STATUS_UPDATED', $companyId, $productId, ['status' => $status], null, $actorId);
    }

    public function updateWarehouseStatus(int $companyId, int $warehouseId, bool $isActive, int $actorId): bool
    {
        $warehouse = $this->db->table('warehouses')
            ->where(['id' => $warehouseId, 'company_id' => $companyId])
            ->where('deleted_at', null)
            ->get()
            ->getFirstRow('array');

        return $warehouse !== null && $this->updateTenantStatus(
            'warehouses',
            'warehouse',
            'WAREHOUSE_STATUS_UPDATED',
            $companyId,
            $warehouseId,
            ['is_active' => $isActive],
            (int) $warehouse['branch_id'],
            $actorId,
            $warehouse,
        );
    }

    public function updateMasterStatus(string $master, int $companyId, int $id, string $status, int $actorId): bool
    {
        $map = [
            'uom'        => ['units_of_measure', 'unit_of_measure', 'UOM_STATUS_UPDATED'],
            'category'   => ['product_categories', 'product_category', 'PRODUCT_CATEGORY_STATUS_UPDATED'],
            'location'   => ['warehouse_bins', 'warehouse_location', 'WAREHOUSE_LOCATION_STATUS_UPDATED'],
            'conversion' => ['product_uom_conversions', 'product_uom_conversion', 'PRODUCT_UOM_CONVERSION_STATUS_UPDATED'],
            'item-tax'   => ['product_tax_codes', 'product_tax_code', 'PRODUCT_TAX_CODE_STATUS_UPDATED'],
            'batch'      => ['stock_lots', 'stock_lot', 'STOCK_LOT_STATUS_UPDATED'],
            'profile'    => ['product_profiles', 'product_profile', 'PRODUCT_PROFILE_STATUS_UPDATED'],
            'price'      => ['product_prices', 'product_price', 'PRODUCT_PRICE_STATUS_UPDATED'],
        ];

        if (! isset($map[$master]) || ! in_array($status, ['active', 'inactive'], true)) {
            return false;
        }

        [$table, $entity, $event] = $map[$master];

        return $this->updateTenantStatus($table, $entity, $event, $companyId, $id, ['status' => $status], null, $actorId);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function insertAudited(string $table, array $data, int $actorId): int
    {
        $this->db->transStart();
        $this->db->table($table)->insert($data + [
            'created_by' => $actorId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    private function sameTenantRecord(string $table, int $id, int $companyId): bool
    {
        return $this->db->table($table)
            ->where(['id' => $id, 'company_id' => $companyId])
            ->where('deleted_at', null)
            ->countAllResults() === 1;
    }

    private function activeTenantRecord(string $table, int $id, int $companyId, string $activeColumn, mixed $activeValue): bool
    {
        return $this->db->table($table)
            ->where(['id' => $id, 'company_id' => $companyId, $activeColumn => $activeValue])
            ->where('deleted_at', null)
            ->countAllResults() === 1;
    }

    /**
     * @param array<string, mixed>      $data
     * @param array<string, mixed>|null $before
     */
    private function updateTenantStatus(
        string $table,
        string $entityType,
        string $event,
        int $companyId,
        int $entityId,
        array $data,
        ?int $branchId,
        int $actorId,
        ?array $before = null,
    ): bool {
        $before ??= $this->db->table($table)
            ->where(['id' => $entityId, 'company_id' => $companyId])
            ->where('deleted_at', null)
            ->get()
            ->getFirstRow('array');

        if ($before === null) {
            return false;
        }

        $this->db->transStart();
        $this->db->table($table)->where(['id' => $entityId, 'company_id' => $companyId])->update($data + [
            'updated_by' => $actorId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit()->record($event, $entityType, $entityId, $companyId, $branchId, $actorId, $data, $before);
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
            throw new RuntimeException('Perubahan master inventory gagal dan transaksi dibatalkan.');
        }
    }
}
