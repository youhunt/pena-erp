<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class CommercialOrderReadModel extends Model
{
    protected $table = 'sales_orders';

    /** @return list<array<string, mixed>> */
    public function salesOrders(int $companyId): array
    {
        return $this->db->table('sales_orders o')
            ->select('o.*, c.code AS customer_code, c.name AS customer_name, w.code AS warehouse_code, b.code AS branch_code, cur.code AS currency_code')
            ->join('customers c', 'c.id = o.customer_id AND c.company_id = o.company_id')
            ->join('warehouses w', 'w.id = o.warehouse_id AND w.company_id = o.company_id')
            ->join('branches b', 'b.id = o.branch_id AND b.company_id = o.company_id')
            ->join('currencies cur', 'cur.id = o.currency_id AND cur.company_id = o.company_id')
            ->where('o.company_id', $companyId)->where('o.deleted_at', null)
            ->orderBy('o.order_date', 'DESC')->orderBy('o.id', 'DESC')
            ->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function purchaseOrders(int $companyId): array
    {
        return $this->db->table('purchase_orders o')
            ->select('o.*, s.code AS supplier_code, s.name AS supplier_name, w.code AS warehouse_code, b.code AS branch_code, cur.code AS currency_code')
            ->join('suppliers s', 's.id = o.supplier_id AND s.company_id = o.company_id')
            ->join('warehouses w', 'w.id = o.warehouse_id AND w.company_id = o.company_id')
            ->join('branches b', 'b.id = o.branch_id AND b.company_id = o.company_id')
            ->join('currencies cur', 'cur.id = o.currency_id AND cur.company_id = o.company_id')
            ->where('o.company_id', $companyId)->where('o.deleted_at', null)
            ->orderBy('o.order_date', 'DESC')->orderBy('o.id', 'DESC')
            ->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function customers(int $companyId): array
    {
        return $this->db->table('customers')
            ->select('id, code, name, currency_id, default_term_id')
            ->where(['company_id' => $companyId, 'status' => 'active'])->where('deleted_at', null)
            ->orderBy('name', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function suppliers(int $companyId): array
    {
        return $this->db->table('suppliers')
            ->select('id, code, name, currency_id, default_term_id')
            ->where(['company_id' => $companyId, 'status' => 'active'])->where('deleted_at', null)
            ->orderBy('name', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function customerTerms(int $companyId): array
    {
        return $this->terms('customer_terms', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function supplierTerms(int $companyId): array
    {
        return $this->terms('supplier_terms', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function currencies(int $companyId): array
    {
        return $this->db->table('currencies')
            ->select('id, code, name')
            ->where(['company_id' => $companyId, 'status' => 'active'])->where('deleted_at', null)
            ->orderBy('code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function warehouses(int $companyId): array
    {
        return $this->db->table('warehouses w')
            ->select('w.id, w.code, w.name, w.branch_id, b.code AS branch_code')
            ->join('branches b', 'b.id = w.branch_id AND b.company_id = w.company_id')
            ->where(['w.company_id' => $companyId, 'w.is_active' => true])
            ->where('w.deleted_at', null)
            ->orderBy('b.code', 'ASC')->orderBy('w.code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function products(int $companyId, string $priceType): array
    {
        return $this->db->table('products p')
            ->select('p.id, p.sku, p.name, p.base_uom_id, u.code AS uom_code, COALESCE(pr.unit_price, p.standard_cost, 0) AS unit_price')
            ->join('units_of_measure u', 'u.id = p.base_uom_id AND u.company_id = p.company_id')
            ->join('product_prices pr', "pr.product_id = p.id AND pr.company_id = p.company_id AND pr.price_type = '{$priceType}' AND pr.status = 'active' AND pr.deleted_at IS NULL", 'left')
            ->where(['p.company_id' => $companyId, 'p.status' => 'active'])
            ->where('p.deleted_at', null)
            ->groupBy('p.id, p.sku, p.name, p.base_uom_id, u.code, pr.unit_price, p.standard_cost')
            ->orderBy('p.sku', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function transactionCodes(int $companyId, string $module): array
    {
        return $this->db->table('transaction_codes t')
            ->select('t.id, t.code, t.prefix, t.branch_id, b.code AS branch_code')
            ->join('branches b', 'b.id = t.branch_id AND b.company_id = t.company_id', 'left')
            ->where(['t.company_id' => $companyId, 't.module' => $module, 't.status' => 'active'])
            ->where('t.deleted_at', null)
            ->orderBy('b.code', 'ASC')->orderBy('t.code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    private function terms(string $table, int $companyId): array
    {
        return $this->db->table($table)
            ->select('id, code, name, due_days')
            ->where(['company_id' => $companyId, 'status' => 'active'])->where('deleted_at', null)
            ->orderBy('code', 'ASC')->get()->getResultArray();
    }
}
