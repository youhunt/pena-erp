<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class GoodsReceiptReadModel extends Model
{
    protected $table      = 'goods_receipts';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = true;
    protected $returnType = 'object';

    public function listReceipts(int $companyId): array
    {
        return $this->db->table('goods_receipts gr')
            ->select('gr.*, po.po_no, w.name as warehouse_name, s.name as supplier_name')
            ->join('purchase_orders po', 'po.id = gr.purchase_order_id', 'left')
            ->join('suppliers s', 's.id = po.supplier_id', 'left')
            ->join('warehouses w', 'w.id = gr.warehouse_id', 'left')
            ->where('gr.company_id', $companyId)
            ->where('gr.deleted_at', null)
            ->orderBy('gr.id', 'DESC')
            ->get()
            ->getResult();
    }

    /** Hanya PO berstatus draft yang bisa dijadikan GR */
    public function listPurchaseOrders(int $companyId): array
    {
        return $this->db->table('purchase_orders po')
            ->select('po.id, po.po_no, po.status, po.warehouse_id, s.name as supplier_name, s.code as supplier_code')
            ->join('suppliers s', 's.id = po.supplier_id AND s.company_id = po.company_id', 'left')
            ->where('po.company_id', $companyId)
            ->where('po.status', 'draft')
            ->where('po.deleted_at', null)
            ->orderBy('po.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function listActiveWarehouses(int $companyId): array
    {
        return $this->db->table('warehouses w')
            ->select('w.id, w.code, w.name, b.code as branch_code')
            ->join('branches b', 'b.id = w.branch_id AND b.company_id = w.company_id', 'left')
            ->where('w.company_id', $companyId)
            ->where('w.is_active', true)
            ->where('w.deleted_at', null)
            ->orderBy('b.code', 'ASC')
            ->orderBy('w.code', 'ASC')
            ->get()
            ->getResultArray();
    }

    /** JSON payload untuk AJAX: items dari PO yang dipilih */
    public function poItemsForAjax(int $companyId, int $purchaseOrderId): array
    {
        return $this->db->table('purchase_order_items poi')
            ->select('poi.id, poi.product_id, poi.qty_ordered, poi.qty_remaining, poi.unit_price, p.name as product_name, p.sku as product_sku, u.code as uom_code')
            ->join('products p', 'p.id = poi.product_id', 'left')
            ->join('units_of_measure u', 'u.id = p.base_uom_id', 'left')
            ->where('poi.company_id', $companyId)
            ->where('poi.purchase_order_id', $purchaseOrderId)
            ->where('poi.qty_remaining >', 0)
            ->where('poi.deleted_at', null)
            ->get()
            ->getResultArray();
    }

    public function getReceiptById(int $companyId, int $id): ?object
    {
        $row = $this->db->table('goods_receipts gr')
            ->select('gr.*, po.po_no, w.name as warehouse_name')
            ->join('purchase_orders po', 'po.id = gr.purchase_order_id', 'left')
            ->join('warehouses w', 'w.id = gr.warehouse_id', 'left')
            ->where('gr.company_id', $companyId)
            ->where('gr.id', $id)
            ->where('gr.deleted_at', null)
            ->get()
            ->getRow();

        if (! $row) {
            return null;
        }

        $row->items = $this->db->table('goods_receipt_items gri')
            ->select('gri.*, p.name as product_name, p.sku as product_sku')
            ->join('products p', 'p.id = gri.product_id', 'left')
            ->where('gri.goods_receipt_id', $row->id)
            ->where('gri.deleted_at', null)
            ->get()
            ->getResult();

        return $row;
    }
}
