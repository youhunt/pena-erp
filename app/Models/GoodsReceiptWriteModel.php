<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class GoodsReceiptWriteModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /** @param array<string, mixed> $data */
    public function createDraftReceipt(array $data): array
    {
        $companyId = (int) session('tenant.company_id');
        $userId    = (int) auth()->id();

        // Validasi PO milik company aktif
        $po = $this->db->table('purchase_orders')
            ->where('id', (int) $data['purchase_order_id'])
            ->where('company_id', $companyId)
            ->where('status', 'draft')
            ->where('deleted_at', null)
            ->get()->getRow();

        if (! $po) {
            throw new \RuntimeException('Purchase Order tidak ditemukan atau bukan berstatus draft pada company aktif.');
        }

        // Validasi PO item
        $item = $this->db->table('purchase_order_items')
            ->where('id', (int) $data['purchase_order_item_id'])
            ->where('purchase_order_id', $po->id)
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->get()->getRow();

        if (! $item) {
            throw new \RuntimeException('Item Purchase Order tidak ditemukan.');
        }

        $qtyReceived = (float) $data['qty_received'];

        if ($qtyReceived <= 0) {
            throw new \RuntimeException('Qty diterima harus lebih dari nol.');
        }

        if ((float) $item->qty_remaining < $qtyReceived) {
            throw new \RuntimeException(
                sprintf('Qty diterima (%.4f) melebihi sisa PO (%.4f).', $qtyReceived, (float) $item->qty_remaining)
            );
        }

        // Validasi warehouse milik company aktif
        $warehouse = $this->db->table('warehouses')
            ->where('id', (int) $data['warehouse_id'])
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('deleted_at', null)
            ->get()->getRow();

        if (! $warehouse) {
            throw new \RuntimeException('Warehouse tidak ditemukan atau tidak aktif pada company aktif.');
        }

        $product = $this->db->table('products')
            ->where('id', (int) $item->product_id)
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->get()->getRow();

        if (! $product) {
            throw new \RuntimeException('Produk pada PO item tidak ditemukan.');
        }

        $receiptNumber = $this->nextReceiptNumber($companyId);
        $lineTotal     = $qtyReceived * (float) $item->unit_price;
        $now           = date('Y-m-d H:i:s');

        $this->db->transStart();

        $receiptId = $this->db->table('goods_receipts')->insert([
            'company_id'        => $companyId,
            'branch_id'         => $po->branch_id ?? null,
            'warehouse_id'      => $warehouse->id,
            'purchase_order_id' => $po->id,
            'receipt_number'    => $receiptNumber,
            'receipt_date'      => date('Y-m-d'),
            'status'            => 'draft',
            'total_qty'         => $qtyReceived,
            'total_amount'      => $lineTotal,
            'created_by'        => $userId,
            'updated_by'        => $userId,
            'created_at'        => $now,
            'updated_at'        => $now,
        ], true);

        $this->db->table('goods_receipt_items')->insert([
            'goods_receipt_id'       => $receiptId,
            'purchase_order_item_id' => $item->id,
            'product_id'             => $item->product_id,
            'warehouse_id'           => $warehouse->id,
            'qty_received'           => $qtyReceived,
            'unit_cost'              => (float) $item->unit_price,
            'line_total'             => $lineTotal,
            'status'                 => 'draft',
            'created_by'             => $userId,
            'updated_by'             => $userId,
            'created_at'             => $now,
            'updated_at'             => $now,
        ]);

        $this->audit($companyId, $userId, 'GOODS_RECEIPT_CREATED', "GR draft {$receiptNumber} dibuat dari PO {$po->po_no}", $receiptId);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Transaksi gagal disimpan. Silakan coba lagi.');
        }

        return ['id' => $receiptId, 'receipt_number' => $receiptNumber, 'status' => 'draft'];
    }

    public function postReceipt(int $receiptId): array
    {
        $companyId = (int) session('tenant.company_id');
        $userId    = (int) auth()->id();

        $receipt = $this->db->table('goods_receipts')
            ->where('id', $receiptId)
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->get()->getRow();

        if (! $receipt) {
            throw new \RuntimeException('Goods Receipt tidak ditemukan.');
        }

        if ($receipt->status === 'posted') {
            throw new \RuntimeException('Goods Receipt sudah pernah diposting.');
        }

        $item = $this->db->table('goods_receipt_items')
            ->where('goods_receipt_id', $receipt->id)
            ->where('deleted_at', null)
            ->get()->getRow();

        if (! $item) {
            throw new \RuntimeException('Goods Receipt tidak memiliki item.');
        }

        $poItem = $this->db->table('purchase_order_items')
            ->where('id', $item->purchase_order_item_id)
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->get()->getRow();

        if (! $poItem) {
            throw new \RuntimeException('PO item tidak ditemukan.');
        }

        if ((float) $poItem->qty_remaining < (float) $item->qty_received) {
            throw new \RuntimeException('Sisa qty PO tidak mencukupi untuk posting receipt ini.');
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        // Kurangi qty_remaining PO item
        $this->db->table('purchase_order_items')
            ->where('id', $poItem->id)
            ->update([
                'qty_remaining' => (float) $poItem->qty_remaining - (float) $item->qty_received,
                'updated_at'    => $now,
            ]);

        // Update stock balance
        $balance = $this->db->table('stock_balances')
            ->where('company_id', $companyId)
            ->where('warehouse_id', $receipt->warehouse_id)
            ->where('product_id', $item->product_id)
            ->get()->getRow();

        if ($balance) {
            $this->db->table('stock_balances')
                ->where('id', $balance->id)
                ->update([
                    'qty_on_hand' => (float) $balance->qty_on_hand + (float) $item->qty_received,
                    'updated_at'  => $now,
                ]);
        } else {
            $this->db->table('stock_balances')->insert([
                'company_id'  => $companyId,
                'warehouse_id' => $receipt->warehouse_id,
                'product_id'  => $item->product_id,
                'qty_on_hand' => (float) $item->qty_received,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        // Catat stock movement immutable
        $this->db->table('stock_movements')->insert([
            'company_id'     => $companyId,
            'warehouse_id'   => $receipt->warehouse_id,
            'product_id'     => $item->product_id,
            'movement_type'  => 'receipt_in',
            'qty'            => (float) $item->qty_received,
            'reference_type' => 'goods_receipt',
            'reference_id'   => $receipt->id,
            'created_by'     => $userId,
            'created_at'     => $now,
        ]);

        // Update status GR header dan item
        $this->db->table('goods_receipts')
            ->where('id', $receipt->id)
            ->update(['status' => 'posted', 'updated_by' => $userId, 'updated_at' => $now]);

        $this->db->table('goods_receipt_items')
            ->where('id', $item->id)
            ->update(['status' => 'posted', 'updated_by' => $userId, 'updated_at' => $now]);

        $this->audit($companyId, $userId, 'GOODS_RECEIPT_POSTED', "GR {$receipt->receipt_number} diposting ke stock ledger", $receipt->id);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Posting gagal. Silakan coba lagi.');
        }

        return ['id' => $receipt->id, 'receipt_number' => $receipt->receipt_number, 'status' => 'posted'];
    }

    protected function nextReceiptNumber(int $companyId): string
    {
        $year  = date('Y');
        $month = date('m');

        $last = $this->db->table('goods_receipts')
            ->selectMax('id', 'max_id')
            ->where('company_id', $companyId)
            ->get()->getRow();

        $next = ((int) ($last->max_id ?? 0)) + 1;

        return 'GR' . $year . $month . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    protected function audit(int $companyId, int $userId, string $event, string $description, int $referenceId): void
    {
        $this->db->table('audit_logs')->insert([
            'company_id'     => $companyId,
            'user_id'        => $userId,
            'event_type'     => $event,
            'description'    => $description,
            'reference_type' => 'goods_receipt',
            'reference_id'   => $referenceId,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }
}
