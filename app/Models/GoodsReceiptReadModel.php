<?php

namespace App\Models;

use CodeIgniter\Model;

class GoodsReceiptReadModel extends Model
{
    protected $table = 'goods_receipts';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = true;
    protected $returnType = 'object';

    public function listReceipts(int $companyId): array
    {
        return $this->db->table('goods_receipts gr')
            ->select('gr.*, po.po_no, w.name as warehouse_name')
            ->join('purchase_orders po', 'po.id = gr.purchase_order_id', 'left')
            ->join('warehouses w', 'w.id = gr.warehouse_id', 'left')
            ->where('gr.company_id', $companyId)
            ->where('gr.deleted_at', null)
            ->orderBy('gr.id', 'DESC')
            ->get()
            ->getResult();
    }

    public function listPurchaseOrders(int $companyId): array
    {
        return $this->db->table('purchase_orders po')
            ->select('po.id, po.po_no, po.status')
            ->where('po.company_id', $companyId)
            ->where('po.deleted_at', null)
            ->orderBy('po.id', 'DESC')
            ->get()
            ->getResult();
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
            ->select('gri.*, p.name as product_name')
            ->join('products p', 'p.id = gri.product_id', 'left')
            ->where('gri.goods_receipt_id', $row->id)
            ->where('gri.deleted_at', null)
            ->get()
            ->getResult();

        return $row;
    }

    public function listPoItems(int $companyId, int $purchaseOrderId): array
    {
        return $this->db->table('purchase_order_items poi')
            ->select('poi.id, poi.product_id, poi.qty_ordered, poi.qty_remaining, poi.unit_price, p.name as product_name')
            ->join('products p', 'p.id = poi.product_id', 'left')
            ->where('poi.company_id', $companyId)
            ->where('poi.purchase_order_id', $purchaseOrderId)
            ->where('poi.deleted_at', null)
            ->get()
            ->getResult();
    }
}
