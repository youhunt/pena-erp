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

    /** Hanya PO confirmed yang masih punya item tersisa yang bisa dijadikan GR */
    public function listPurchaseOrders(int $companyId): array
    {
        $qtyRemainExpr = $this->poRemainingQuantityExpression();

        return $this->db->table('purchase_orders po')
            ->select("po.id, po.po_no, po.status, po.warehouse_id, s.name as supplier_name, s.code as supplier_code, SUM({$qtyRemainExpr}) as total_qty_remaining", false)
            ->join('suppliers s', 's.id = po.supplier_id AND s.company_id = po.company_id', 'left')
            ->join('purchase_order_items poi', 'poi.purchase_order_id = po.id AND poi.company_id = po.company_id AND poi.deleted_at IS NULL', 'inner')
            ->where('po.company_id', $companyId)
            ->where('po.status', 'confirmed')
            ->where('po.deleted_at', null)
            ->groupBy('po.id, po.po_no, po.status, po.warehouse_id, s.name, s.code')
            ->having('total_qty_remaining >', 0)
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
        $qtyOrderedExpr = $this->poOrderedQuantityExpression();
        $qtyRemainExpr  = $this->poRemainingQuantityExpression();

        return $this->db->table('purchase_order_items poi')
            ->select("poi.id, poi.product_id, {$qtyOrderedExpr} AS qty_ordered, {$qtyRemainExpr} AS qty_remaining, poi.unit_price, p.name as product_name, p.sku as product_sku, u.code as uom_code", false)
            ->join('products p', 'p.id = poi.product_id AND p.company_id = poi.company_id', 'left')
            ->join('units_of_measure u', 'u.id = p.base_uom_id AND u.company_id = p.company_id', 'left')
            ->where('poi.company_id', $companyId)
            ->where('poi.purchase_order_id', $purchaseOrderId)
            ->where("{$qtyRemainExpr} >", 0, false)
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

    private function poOrderedQuantityExpression(): string
    {
        $fields = $this->db->getFieldNames('purchase_order_items');

        return in_array('qty_ordered', $fields, true) ? 'poi.qty_ordered' : 'poi.qty';
    }

    private function poRemainingQuantityExpression(): string
    {
        $fields = $this->db->getFieldNames('purchase_order_items');

        if (in_array('qty_remaining', $fields, true)) {
            return 'poi.qty_remaining';
        }

        return 'poi.qty';
    }
}
