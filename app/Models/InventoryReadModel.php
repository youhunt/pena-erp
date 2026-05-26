<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class InventoryReadModel extends Model
{
    protected $table = 'products';

    /**
     * @return list<array<string, mixed>>
     */
    public function products(int $companyId): array
    {
        return $this->db->table('products p')
            ->select('p.id, p.sku, p.barcode, p.name, p.product_type, p.track_lot, p.standard_cost, p.status, c.name AS category_name, u.code AS uom_code')
            ->join('product_categories c', 'c.id = p.category_id AND c.company_id = p.company_id', 'left')
            ->join('units_of_measure u', 'u.id = p.base_uom_id AND u.company_id = p.company_id')
            ->where('p.company_id', $companyId)
            ->where('p.deleted_at', null)
            ->orderBy('p.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function warehouses(int $companyId): array
    {
        return $this->db->table('warehouses w')
            ->select('w.id, w.code, w.name, w.address, w.postal_code, w.is_active, b.code AS branch_code, b.name AS branch_name')
            ->join('branches b', 'b.id = w.branch_id AND b.company_id = w.company_id')
            ->where('w.company_id', $companyId)
            ->where('w.deleted_at', null)
            ->orderBy('b.name', 'ASC')
            ->orderBy('w.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function unitsOfMeasure(int $companyId): array
    {
        return $this->db->table('units_of_measure')
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->orderBy('code', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function productCategories(int $companyId): array
    {
        return $this->db->table('product_categories')
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
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
            ->where('deleted_at', null)
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function productCodeExists(int $companyId, string $sku): bool
    {
        return $this->db->table('products')
            ->where(['company_id' => $companyId, 'sku' => $sku])
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }

    public function uomCodeExists(int $companyId, string $code): bool
    {
        return $this->db->table('units_of_measure')
            ->where(['company_id' => $companyId, 'code' => $code])
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }

    public function categoryCodeExists(int $companyId, string $code): bool
    {
        return $this->db->table('product_categories')
            ->where(['company_id' => $companyId, 'code' => $code])
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }

    public function warehouseCodeExists(int $companyId, int $branchId, string $code): bool
    {
        return $this->db->table('warehouses')
            ->where(['company_id' => $companyId, 'branch_id' => $branchId, 'code' => $code])
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }
}
