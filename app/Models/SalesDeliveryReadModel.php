<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class SalesDeliveryReadModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /** @return list<object> */
    public function listDeliveries(int $companyId): array
    {
        return $this->db->table('delivery_orders do')
            ->select('do.*, so.order_no, c.name AS customer_name, c.code AS customer_code, w.name AS warehouse_name, w.code AS warehouse_code')
            ->join('sales_orders so', 'so.id = do.sales_order_id AND so.company_id = do.company_id', 'left')
            ->join('customers c', 'c.id = so.customer_id AND c.company_id = do.company_id', 'left')
            ->join('warehouses w', 'w.id = do.warehouse_id AND w.company_id = do.company_id', 'left')
            ->where('do.company_id', $companyId)
            ->where('do.deleted_at IS NULL', null, false)
            ->orderBy('do.id', 'DESC')
            ->get()
            ->getResult();
    }

    /** @return list<array<string, mixed>> */
    public function listSalesOrders(int $companyId): array
    {
        return $this->db->table('sales_orders so')
            ->select('so.id, so.order_no, so.warehouse_id, c.name AS customer_name, SUM(soi.qty_remaining) AS total_qty_remaining')
            ->join('sales_order_items soi', 'soi.sales_order_id = so.id AND soi.company_id = so.company_id AND soi.deleted_at IS NULL')
            ->join('customers c', 'c.id = so.customer_id AND c.company_id = so.company_id', 'left')
            ->where('so.company_id', $companyId)
            ->where('so.status', 'confirmed')
            ->where('so.deleted_at IS NULL', null, false)
            ->groupBy('so.id, so.order_no, so.warehouse_id, c.name')
            ->having('total_qty_remaining >', 0)
            ->orderBy('so.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function listActiveWarehouses(int $companyId): array
    {
        return $this->db->table('warehouses w')
            ->select('w.id, w.code, w.name, b.code AS branch_code')
            ->join('branches b', 'b.id = w.branch_id AND b.company_id = w.company_id', 'left')
            ->where('w.company_id', $companyId)
            ->where('w.is_active', true)
            ->where('w.deleted_at IS NULL', null, false)
            ->orderBy('w.code', 'ASC')
            ->get()
            ->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function salesOrderItemsForAjax(int $companyId, int $salesOrderId): array
    {
        return $this->db->table('sales_order_items soi')
            ->select('soi.id, soi.product_id, soi.qty, soi.qty_delivered, soi.qty_remaining, soi.unit_price, p.name AS product_name, p.sku AS product_sku, u.code AS uom_code')
            ->join('products p', 'p.id = soi.product_id AND p.company_id = soi.company_id', 'left')
            ->join('units_of_measure u', 'u.id = p.base_uom_id AND u.company_id = p.company_id', 'left')
            ->where('soi.company_id', $companyId)
            ->where('soi.sales_order_id', $salesOrderId)
            ->where('soi.qty_remaining >', 0)
            ->where('soi.deleted_at IS NULL', null, false)
            ->orderBy('soi.id', 'ASC')
            ->get()
            ->getResultArray();
    }
}
