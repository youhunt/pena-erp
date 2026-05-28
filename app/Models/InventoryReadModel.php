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
            ->select('p.id, p.category_id, p.base_uom_id, p.sku, p.barcode, p.name, p.product_type, p.track_lot, p.standard_cost, p.status, c.name AS category_name, u.code AS uom_code')
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
            ->select('w.id, w.branch_id, w.department_id, w.code, w.name, w.address, w.postal_code, w.is_active, b.code AS branch_code, b.name AS branch_name, d.code AS department_code, d.name AS department_name')
            ->join('branches b', 'b.id = w.branch_id AND b.company_id = w.company_id')
            ->join('departments d', 'd.id = w.department_id AND d.company_id = w.company_id AND d.branch_id = w.branch_id', 'left')
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

    /**
     * @return list<array<string, mixed>>
     */
    public function departmentOptions(int $companyId): array
    {
        return $this->db->table('departments d')
            ->select('d.id, d.branch_id, d.code, d.name, b.code AS branch_code')
            ->join('branches b', 'b.id = d.branch_id AND b.company_id = d.company_id')
            ->where(['d.company_id' => $companyId, 'd.status' => 'active'])
            ->where('d.deleted_at', null)
            ->orderBy('b.code', 'ASC')
            ->orderBy('d.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function locations(int $companyId): array
    {
        return $this->db->table('warehouse_bins l')
            ->select('l.id, l.warehouse_id, l.code, l.name, l.status, w.code AS warehouse_code, b.code AS branch_code, d.code AS department_code')
            ->join('warehouses w', 'w.id = l.warehouse_id AND w.company_id = l.company_id')
            ->join('branches b', 'b.id = l.branch_id AND b.company_id = l.company_id')
            ->join('departments d', 'd.id = w.department_id AND d.company_id = w.company_id AND d.branch_id = w.branch_id', 'left')
            ->where('l.company_id', $companyId)
            ->where('l.deleted_at', null)
            ->orderBy('b.code', 'ASC')
            ->orderBy('w.code', 'ASC')
            ->orderBy('l.code', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function uomConversions(int $companyId): array
    {
        return $this->db->table('product_uom_conversions x')
            ->select('x.id, x.factor, x.status, p.sku, p.name AS product_name, f.code AS from_uom, t.code AS to_uom')
            ->join('products p', 'p.id = x.product_id AND p.company_id = x.company_id')
            ->join('units_of_measure f', 'f.id = x.from_uom_id AND f.company_id = x.company_id')
            ->join('units_of_measure t', 't.id = x.to_uom_id AND t.company_id = x.company_id')
            ->where('x.company_id', $companyId)
            ->where('x.deleted_at', null)
            ->orderBy('p.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function itemTaxes(int $companyId): array
    {
        return $this->db->table('product_tax_codes x')
            ->select('x.id, x.usage_type, x.status, p.sku, p.name AS product_name, t.code AS tax_code, t.rate')
            ->join('products p', 'p.id = x.product_id AND p.company_id = x.company_id')
            ->join('tax_codes t', 't.id = x.tax_code_id AND t.company_id = x.company_id')
            ->where('x.company_id', $companyId)
            ->where('x.deleted_at', null)
            ->orderBy('p.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function batches(int $companyId): array
    {
        return $this->db->table('stock_lots l')
            ->select('l.id, l.product_id, l.lot_no, l.expiry_date, l.status, p.sku, p.name AS product_name')
            ->join('products p', 'p.id = l.product_id AND p.company_id = l.company_id')
            ->where('l.company_id', $companyId)
            ->where('l.deleted_at', null)
            ->orderBy('p.name', 'ASC')
            ->orderBy('l.expiry_date', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function productProfiles(int $companyId): array
    {
        return $this->db->table('product_profiles x')
            ->select('x.*, p.sku, p.name AS product_name, w.code AS warehouse_code, u.code AS package_uom_code')
            ->join('products p', 'p.id = x.product_id AND p.company_id = x.company_id')
            ->join('warehouses w', 'w.id = x.default_warehouse_id AND w.company_id = x.company_id', 'left')
            ->join('units_of_measure u', 'u.id = x.package_uom_id AND u.company_id = x.company_id', 'left')
            ->where('x.company_id', $companyId)
            ->where('x.deleted_at', null)
            ->orderBy('p.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function productPrices(int $companyId): array
    {
        return $this->db->table('product_prices x')
            ->select('x.*, p.sku, p.name AS product_name, c.code AS currency_code, u.code AS uom_code')
            ->join('products p', 'p.id = x.product_id AND p.company_id = x.company_id')
            ->join('currencies c', 'c.id = x.currency_id AND c.company_id = x.company_id')
            ->join('units_of_measure u', 'u.id = x.uom_id AND u.company_id = x.company_id')
            ->where('x.company_id', $companyId)
            ->where('x.deleted_at', null)
            ->orderBy('p.name', 'ASC')
            ->orderBy('x.effective_from', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function stockBalances(int $companyId): array
    {
        return $this->db->table('stock_balances b')
            ->select('b.*, p.sku, p.name AS product_name, u.code AS uom_code, w.code AS warehouse_code, br.code AS branch_code, l.code AS location_code')
            ->join('products p', 'p.id = b.product_id AND p.company_id = b.company_id')
            ->join('units_of_measure u', 'u.id = p.base_uom_id AND u.company_id = p.company_id')
            ->join('warehouses w', 'w.id = b.warehouse_id AND w.company_id = b.company_id')
            ->join('branches br', 'br.id = w.branch_id AND br.company_id = w.company_id')
            ->join('warehouse_bins l', 'l.id = b.bin_id AND l.company_id = b.company_id', 'left')
            ->where('b.company_id', $companyId)
            ->where('b.deleted_at', null)
            ->orderBy('br.code', 'ASC')
            ->orderBy('w.code', 'ASC')
            ->orderBy('p.sku', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function stockMovements(int $companyId, int $limit = 25): array
    {
        return $this->db->table('stock_movements m')
            ->select('m.*, p.sku, p.name AS product_name, u.code AS uom_code, w.code AS warehouse_code, br.code AS branch_code, l.code AS location_code')
            ->join('products p', 'p.id = m.product_id AND p.company_id = m.company_id')
            ->join('units_of_measure u', 'u.id = p.base_uom_id AND u.company_id = p.company_id')
            ->join('warehouses w', 'w.id = m.warehouse_id AND w.company_id = m.company_id')
            ->join('branches br', 'br.id = w.branch_id AND br.company_id = w.company_id')
            ->join('warehouse_bins l', 'l.id = m.bin_id AND l.company_id = m.company_id', 'left')
            ->where('m.company_id', $companyId)
            ->where('m.deleted_at', null)
            ->orderBy('m.posted_at', 'DESC')
            ->orderBy('m.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function stockAdjustments(int $companyId): array
    {
        return $this->db->table('inventory_adjustments a')
            ->select('a.*, i.product_id, i.system_qty, i.counted_qty, i.variance_qty, p.sku, p.name AS product_name, u.code AS uom_code, w.code AS warehouse_code, br.code AS branch_code')
            ->join('inventory_adjustment_items i', 'i.inventory_adjustment_id = a.id AND i.company_id = a.company_id')
            ->join('products p', 'p.id = i.product_id AND p.company_id = i.company_id')
            ->join('units_of_measure u', 'u.id = i.uom_id AND u.company_id = i.company_id')
            ->join('warehouses w', 'w.id = a.warehouse_id AND w.company_id = a.company_id')
            ->join('branches br', 'br.id = w.branch_id AND br.company_id = w.company_id')
            ->where('a.company_id', $companyId)
            ->where('a.deleted_at', null)
            ->where('i.deleted_at', null)
            ->orderBy('a.created_at', 'DESC')
            ->orderBy('a.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function stockTransfers(int $companyId): array
    {
        return $this->db->table('stock_transfers t')
            ->select('t.*, i.product_id, i.qty, p.sku, p.name AS product_name, u.code AS uom_code, fw.code AS from_warehouse_code, fb.code AS from_branch_code, tw.code AS to_warehouse_code, tb.code AS to_branch_code')
            ->join('stock_transfer_items i', 'i.stock_transfer_id = t.id AND i.company_id = t.company_id')
            ->join('products p', 'p.id = i.product_id AND p.company_id = i.company_id')
            ->join('units_of_measure u', 'u.id = i.uom_id AND u.company_id = i.company_id')
            ->join('warehouses fw', 'fw.id = t.from_warehouse_id AND fw.company_id = t.company_id')
            ->join('branches fb', 'fb.id = fw.branch_id AND fb.company_id = fw.company_id')
            ->join('warehouses tw', 'tw.id = t.to_warehouse_id AND tw.company_id = t.company_id')
            ->join('branches tb', 'tb.id = tw.branch_id AND tb.company_id = tw.company_id')
            ->where('t.company_id', $companyId)
            ->where('t.deleted_at', null)
            ->where('i.deleted_at', null)
            ->orderBy('t.created_at', 'DESC')
            ->orderBy('t.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function taxOptions(int $companyId): array
    {
        return $this->db->table('tax_codes')
            ->select('id, code, name, rate')
            ->where(['company_id' => $companyId, 'status' => 'active'])
            ->where('deleted_at', null)
            ->orderBy('code', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function currencyOptions(int $companyId): array
    {
        return $this->db->table('currencies')
            ->select('id, code, name')
            ->where(['company_id' => $companyId, 'status' => 'active'])
            ->where('deleted_at', null)
            ->orderBy('code', 'ASC')
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

    public function locationCodeExists(int $companyId, int $warehouseId, string $code): bool
    {
        return $this->db->table('warehouse_bins')
            ->where(['company_id' => $companyId, 'warehouse_id' => $warehouseId, 'code' => $code])
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }

    public function uomConversionExists(int $companyId, int $productId, int $fromUomId, int $toUomId): bool
    {
        return $this->db->table('product_uom_conversions')
            ->where([
                'company_id'  => $companyId,
                'product_id'  => $productId,
                'from_uom_id' => $fromUomId,
                'to_uom_id'   => $toUomId,
            ])
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }

    public function itemTaxExists(int $companyId, int $productId, int $taxCodeId, string $usageType): bool
    {
        return $this->db->table('product_tax_codes')
            ->where([
                'company_id'  => $companyId,
                'product_id'  => $productId,
                'tax_code_id' => $taxCodeId,
                'usage_type'  => $usageType,
            ])
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }

    public function batchExists(int $companyId, int $productId, string $lotNo): bool
    {
        return $this->db->table('stock_lots')
            ->where(['company_id' => $companyId, 'product_id' => $productId, 'lot_no' => $lotNo])
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }

    public function productPriceExists(int $companyId, int $productId, string $priceType, int $currencyId, int $uomId, string $effectiveFrom): bool
    {
        return $this->db->table('product_prices')
            ->where([
                'company_id'    => $companyId,
                'product_id'    => $productId,
                'price_type'    => $priceType,
                'currency_id'   => $currencyId,
                'uom_id'        => $uomId,
                'effective_from' => $effectiveFrom,
            ])
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }
}
