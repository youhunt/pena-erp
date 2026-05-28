<?php

namespace App\Models;

use CodeIgniter\Model;

class GoodsReceiptWriteModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function createDraftReceipt(array $data): array
    {
        $companyId = (int) session('tenant.company_id');
        $userId = (int) user_id();

        $po = $this->db->table('purchase_orders')
            ->where('id', (int) $data['purchase_order_id'])
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->get()
            ->getRow();

        if (! $po) {
            throw new \RuntimeException('Purchase order not found in current tenant.');
        }

        $item = $this->db->table('purchase_order_items')
            ->where('id', (int) $data['purchase_order_item_id'])
            ->where('purchase_order_id', $po->id)
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->get()
            ->getRow();

        if (! $item) {
            throw new \RuntimeException('Purchase order item not found.');
        }

        if ((float) $data['qty_received'] <= 0) {
            throw new \RuntimeException('Receipt quantity must be greater than zero.');
        }

        if ((float) $item->qty_remaining < (float) $data['qty_received']) {
            throw new \RuntimeException('Receipt quantity exceeds remaining PO quantity.');
        }

        $warehouse = $this->db->table('warehouses')
            ->where('id', (int) $data['warehouse_id'])
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->get()
            ->getRow();

        if (! $warehouse) {
            throw new \RuntimeException('Warehouse not found in current tenant.');
        }

        $product = $this->db->table('products')
            ->where('id', (int) $item->product_id)
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->get()
            ->getRow();

        if (! $product) {
            throw new \RuntimeException('Product not found in current tenant.');
        }

        $this->db->transStart();

        $receiptId = $this->db->table('goods_receipts')->insert([
            'company_id' => $companyId,
            'branch_id' => $po->branch_id,
            'warehouse_id' => $warehouse->id,
            'purchase_order_id' => $po->id,
            'receipt_number' => $this->nextReceiptNumber($companyId),
            'receipt_date' => date('Y-m-d'),
            'status' => 'draft',
            'total_qty' => (float) $data['qty_received'],
            'total_amount' => (float) $data['qty_received'] * (float) $item->unit_price,
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], true);

        $this->db->table('goods_receipt_items')->insert([
            'goods_receipt_id' => $receiptId,
            'purchase_order_item_id' => $item->id,
            'product_id' => $item->product_id,
            'warehouse_id' => $warehouse->id,
            'qty_received' => (float) $data['qty_received'],
            'unit_cost' => (float) $item->unit_price,
            'line_total' => (float) $data['qty_received'] * (float) $item->unit_price,
            'status' => 'draft',
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->audit($companyId, $userId, 'GOODS_RECEIPT_CREATED', 'Goods receipt draft created', $receiptId);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Failed to create goods receipt draft.');
        }

        return ['id' => $receiptId, 'status' => 'draft'];
    }

    public function postReceipt(int $receiptId): array
    {
        $companyId = (int) session('tenant.company_id');
        $userId = (int) user_id();

        $receipt = $this->db->table('goods_receipts')
            ->where('id', $receiptId)
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->get()
            ->getRow();

        if (! $receipt) {
            throw new \RuntimeException('Goods receipt not found.');
        }

        if ($receipt->status === 'posted') {
            throw new \RuntimeException('Goods receipt already posted.');
        }

        $item = $this->db->table('goods_receipt_items')
            ->where('goods_receipt_id', $receipt->id)
            ->where('deleted_at', null)
            ->get()
            ->getRow();

        if (! $item) {
            throw new \RuntimeException('Goods receipt has no item.');
        }

        $poItem = $this->db->table('purchase_order_items')
            ->where('id', $item->purchase_order_item_id)
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->get()
            ->getRow();

        if (! $poItem) {
            throw new \RuntimeException('PO item not found.');
        }

        if ((float) $poItem->qty_remaining < (float) $item->qty_received) {
            throw new \RuntimeException('Remaining quantity is not enough to post receipt.');
        }

        $this->db->transStart();

        $this->db->table('purchase_order_items')
            ->where('id', $poItem->id)
            ->update([
                'qty_remaining' => (float) $poItem->qty_remaining - (float) $item->qty_received,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $balance = $this->db->table('stock_balances')
            ->where('company_id', $companyId)
            ->where('warehouse_id', $receipt->warehouse_id)
            ->where('product_id', $item->product_id)
            ->get()
            ->getRow();

        if ($balance) {
            $this->db->table('stock_balances')
                ->where('id', $balance->id)
                ->update([
                    'qty_on_hand' => (float) $balance->qty_on_hand + (float) $item->qty_received,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        } else {
            $this->db->table('stock_balances')->insert([
                'company_id' => $companyId,
                'warehouse_id' => $receipt->warehouse_id,
                'product_id' => $item->product_id,
                'qty_on_hand' => (float) $item->qty_received,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->db->table('stock_movements')->insert([
            'company_id' => $companyId,
            'warehouse_id' => $receipt->warehouse_id,
            'product_id' => $item->product_id,
            'movement_type' => 'receipt_in',
            'qty' => (float) $item->qty_received,
            'reference_type' => 'goods_receipt',
            'reference_id' => $receipt->id,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->table('goods_receipts')
            ->where('id', $receipt->id)
            ->update([
                'status' => 'posted',
                'updated_by' => $userId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $this->db->table('goods_receipt_items')
            ->where('id', $item->id)
            ->update([
                'status' => 'posted',
                'updated_by' => $userId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $this->audit($companyId, $userId, 'GOODS_RECEIPT_POSTED', 'Goods receipt posted to stock ledger', $receipt->id);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Failed to post goods receipt.');
        }

        return ['id' => $receipt->id, 'status' => 'posted'];
    }

    protected function nextReceiptNumber(int $companyId): string
    {
        $prefix = 'GR';
        $year = date('Y');
        $month = date('m');

        $last = $this->db->table('goods_receipts')
            ->select('MAX(id) as max_id')
            ->where('company_id', $companyId)
            ->get()
            ->getRow();

        $next = ((int) ($last->max_id ?? 0)) + 1;

        return $prefix . $year . $month . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    protected function audit(int $companyId, int $userId, string $event, string $description, int $referenceId): void
    {
        $this->db->table('audit_logs')->insert([
            'company_id' => $companyId,
            'user_id' => $userId,
            'event_type' => $event,
            'description' => $description,
            'reference_type' => 'goods_receipt',
            'reference_id' => $referenceId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
