<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class FinanceInvoiceSourceModel extends Model
{
    /** @return list<array<string, mixed>> */
    public function purchaseOrderOptions(int $companyId): array
    {
        return $this->db->table('purchase_orders po')
            ->select('po.id, po.po_no, po.supplier_id, po.currency_id, po.total_amount, po.status, s.code AS supplier_code, s.name AS supplier_name, c.code AS currency_code')
            ->join('suppliers s', 's.id = po.supplier_id AND s.company_id = po.company_id', 'left')
            ->join('currencies c', 'c.id = po.currency_id AND c.company_id = po.company_id', 'left')
            ->where('po.company_id', $companyId)
            ->whereIn('po.status', ['confirmed', 'partial_received', 'received'])
            ->where('po.deleted_at IS NULL', null, false)
            ->orderBy('po.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function salesOrderOptions(int $companyId): array
    {
        return $this->db->table('sales_orders so')
            ->select('so.id, so.order_no, so.customer_id, so.currency_id, so.total_amount, so.status, cst.code AS customer_code, cst.name AS customer_name, cur.code AS currency_code')
            ->join('customers cst', 'cst.id = so.customer_id AND cst.company_id = so.company_id', 'left')
            ->join('currencies cur', 'cur.id = so.currency_id AND cur.company_id = so.company_id', 'left')
            ->where('so.company_id', $companyId)
            ->whereIn('so.status', ['confirmed', 'partial_delivered', 'delivered'])
            ->where('so.deleted_at IS NULL', null, false)
            ->orderBy('so.id', 'DESC')
            ->get()
            ->getResultArray();
    }
}
