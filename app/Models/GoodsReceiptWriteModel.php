<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\AuditTrailService;
use CodeIgniter\Model;
use RuntimeException;

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
        $companyId = (int) ($data['company_id'] ?? 0);
        $branchId  = isset($data['branch_id']) ? (int) $data['branch_id'] : null;
        $userId    = (int) ($data['actor_id'] ?? auth()->id());

        if ($companyId <= 0) {
            throw new RuntimeException('Company context Goods Receipt tidak valid.');
        }

        $po = $this->db->table('purchase_orders')
            ->where('id', (int) ($data['purchase_order_id'] ?? 0))
            ->where('company_id', $companyId)
            ->where('status', 'draft')
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getRow();

        if (! $po) {
            throw new RuntimeException('Purchase Order tidak ditemukan atau bukan berstatus draft pada company aktif.');
        }

        $warehouse = $this->db->table('warehouses')
            ->where('id', (int) ($data['warehouse_id'] ?? 0))
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getRow();

        if (! $warehouse) {
            throw new RuntimeException('Warehouse tidak ditemukan atau tidak aktif pada company aktif.');
        }

        $lines = $this->normalizeReceiptLines($data);

        if ($lines === []) {
            throw new RuntimeException('Minimal satu item Goods Receipt wajib diisi.');
        }

        $preparedLines = [];
        $totalQty      = 0.0;
        $totalAmount   = 0.0;

        foreach ($lines as $line) {
            $item = $this->db->table('purchase_order_items')
                ->where('id', (int) $line['purchase_order_item_id'])
                ->where('purchase_order_id', (int) $po->id)
                ->where('company_id', $companyId)
                ->where('deleted_at IS NULL', null, false)
                ->get()
                ->getRow();

            if (! $item) {
                throw new RuntimeException('Item Purchase Order tidak ditemukan.');
            }

            $qtyReceived = $this->decimal($line['qty_received']);

            if ($qtyReceived <= 0) {
                throw new RuntimeException('Qty diterima harus lebih dari nol.');
            }

            if ((float) $item->qty_remaining < $qtyReceived) {
                throw new RuntimeException(sprintf('Qty diterima (%.4f) melebihi sisa PO (%.4f).', $qtyReceived, (float) $item->qty_remaining));
            }

            $product = $this->db->table('products')
                ->where('id', (int) $item->product_id)
                ->where('company_id', $companyId)
                ->where('deleted_at IS NULL', null, false)
                ->get()
                ->getRow();

            if (! $product) {
                throw new RuntimeException('Produk pada PO item tidak ditemukan.');
            }

            $lineTotal = $qtyReceived * (float) $item->unit_price;

            $preparedLines[] = [
                'po_item'      => $item,
                'qty_received' => $qtyReceived,
                'unit_cost'    => (float) $item->unit_price,
                'line_total'   => $lineTotal,
            ];

            $totalQty    += $qtyReceived;
            $totalAmount += $lineTotal;
        }

        $receiptNumber = $this->nextReceiptNumber($companyId);
        $receiptBranch = isset($po->branch_id) ? (int) $po->branch_id : $branchId;
        $now           = date('Y-m-d H:i:s');

        $this->db->transStart();

        $this->db->table('goods_receipts')->insert([
            'company_id'        => $companyId,
            'branch_id'         => $receiptBranch,
            'warehouse_id'      => (int) $warehouse->id,
            'purchase_order_id' => (int) $po->id,
            'receipt_number'    => $receiptNumber,
            'receipt_date'      => date('Y-m-d'),
            'status'            => 'draft',
            'total_qty'         => $this->money($totalQty),
            'total_amount'      => $this->money($totalAmount),
            'created_by'        => $userId,
            'updated_by'        => $userId,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $receiptId = (int) $this->db->insertID();

        if ($receiptId <= 0) {
            throw new RuntimeException('Header Goods Receipt gagal dibuat.');
        }

        foreach ($preparedLines as $line) {
            $poItem = $line['po_item'];

            $this->db->table('goods_receipt_items')->insert([
                'goods_receipt_id'       => $receiptId,
                'purchase_order_item_id' => (int) $poItem->id,
                'product_id'             => (int) $poItem->product_id,
                'warehouse_id'           => (int) $warehouse->id,
                'qty_received'           => $this->money((float) $line['qty_received']),
                'unit_cost'              => $this->money((float) $line['unit_cost']),
                'line_total'             => $this->money((float) $line['line_total']),
                'status'                 => 'draft',
                'created_by'             => $userId,
                'updated_by'             => $userId,
                'created_at'             => $now,
                'updated_at'             => $now,
            ]);
        }

        $this->audit($companyId, $receiptBranch, $userId, 'GOODS_RECEIPT_CREATED', $receiptId, [
            'receipt_number'    => $receiptNumber,
            'purchase_order_id' => (int) $po->id,
            'po_no'             => (string) $po->po_no,
            'warehouse_id'      => (int) $warehouse->id,
            'total_qty'         => $totalQty,
            'total_amount'      => $totalAmount,
            'line_count'        => count($preparedLines),
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Transaksi gagal disimpan. Silakan coba lagi.');
        }

        return ['id' => $receiptId, 'receipt_number' => $receiptNumber, 'status' => 'draft'];
    }

    public function postReceipt(int $receiptId, int $companyId, int $userId): array
    {
        if ($companyId <= 0) {
            throw new RuntimeException('Company context Goods Receipt tidak valid.');
        }

        $receipt = $this->db->table('goods_receipts')
            ->where('id', $receiptId)
            ->where('company_id', $companyId)
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getRow();

        if (! $receipt) {
            throw new RuntimeException('Goods Receipt tidak ditemukan.');
        }

        if ($receipt->status === 'posted') {
            throw new RuntimeException('Goods Receipt sudah pernah diposting.');
        }

        $items = $this->db->table('goods_receipt_items')
            ->where('goods_receipt_id', (int) $receipt->id)
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getResult();

        if ($items === []) {
            throw new RuntimeException('Goods Receipt tidak memiliki item.');
        }

        $poItems = [];

        foreach ($items as $item) {
            $poItem = $this->db->table('purchase_order_items')
                ->where('id', (int) $item->purchase_order_item_id)
                ->where('company_id', $companyId)
                ->where('deleted_at IS NULL', null, false)
                ->get()
                ->getRow();

            if (! $poItem) {
                throw new RuntimeException('PO item tidak ditemukan.');
            }

            if ((float) $poItem->qty_remaining < (float) $item->qty_received) {
                throw new RuntimeException('Sisa qty PO tidak mencukupi untuk posting receipt ini.');
            }

            $poItems[(int) $item->id] = $poItem;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        foreach ($items as $item) {
            $poItem = $poItems[(int) $item->id];
            $qty    = (float) $item->qty_received;

            $this->db->table('purchase_order_items')
                ->where('id', (int) $poItem->id)
                ->update([
                    'qty_remaining' => $this->money((float) $poItem->qty_remaining - $qty),
                    'received_qty'  => $this->money((float) ($poItem->received_qty ?? 0) + $qty),
                    'updated_by'    => $userId,
                    'updated_at'    => $now,
                ]);

            $balance = $this->db->table('stock_balances')
                ->where('company_id', $companyId)
                ->where('warehouse_id', (int) $item->warehouse_id)
                ->where('product_id', (int) $item->product_id)
                ->get()
                ->getRow();

            if ($balance) {
                $this->db->table('stock_balances')
                    ->where('id', (int) $balance->id)
                    ->update([
                        'qty_on_hand' => $this->money((float) $balance->qty_on_hand + $qty),
                        'updated_at'  => $now,
                    ]);
            } else {
                $this->db->table('stock_balances')->insert([
                    'company_id'  => $companyId,
                    'warehouse_id' => (int) $item->warehouse_id,
                    'product_id'  => (int) $item->product_id,
                    'qty_on_hand' => $this->money($qty),
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }

            $this->db->table('stock_movements')->insert([
                'company_id'     => $companyId,
                'warehouse_id'   => (int) $item->warehouse_id,
                'product_id'     => (int) $item->product_id,
                'movement_type'  => 'receipt_in',
                'qty'            => $this->money($qty),
                'reference_type' => 'goods_receipt',
                'reference_id'   => (int) $receipt->id,
                'created_by'     => $userId,
                'created_at'     => $now,
            ]);

            $this->db->table('goods_receipt_items')
                ->where('id', (int) $item->id)
                ->update([
                    'status'     => 'posted',
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);
        }

        $this->db->table('goods_receipts')
            ->where('id', (int) $receipt->id)
            ->update([
                'status'     => 'posted',
                'updated_by' => $userId,
                'updated_at' => $now,
            ]);

        $this->audit($companyId, isset($receipt->branch_id) ? (int) $receipt->branch_id : null, $userId, 'GOODS_RECEIPT_POSTED', (int) $receipt->id, [
            'receipt_number' => (string) $receipt->receipt_number,
            'total_qty'      => (float) $receipt->total_qty,
            'total_amount'   => (float) $receipt->total_amount,
            'line_count'     => count($items),
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Posting gagal. Silakan coba lagi.');
        }

        return ['id' => (int) $receipt->id, 'receipt_number' => (string) $receipt->receipt_number, 'status' => 'posted'];
    }

    /**
     * @param array<string, mixed> $data
     * @return list<array{purchase_order_item_id:int, qty_received:float}>
     */
    private function normalizeReceiptLines(array $data): array
    {
        if (isset($data['items']) && is_array($data['items'])) {
            $lines = [];

            foreach ($data['items'] as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $poItemId = (int) ($item['purchase_order_item_id'] ?? 0);
                $qty      = $this->decimal($item['qty_received'] ?? 0);

                if ($poItemId <= 0 && $qty <= 0) {
                    continue;
                }

                $lines[] = [
                    'purchase_order_item_id' => $poItemId,
                    'qty_received'           => $qty,
                ];
            }

            return $lines;
        }

        return [[
            'purchase_order_item_id' => (int) ($data['purchase_order_item_id'] ?? 0),
            'qty_received'           => $this->decimal($data['qty_received'] ?? 0),
        ]];
    }

    protected function nextReceiptNumber(int $companyId): string
    {
        $year  = date('Y');
        $month = date('m');

        $last = $this->db->table('goods_receipts')
            ->selectMax('id', 'max_id')
            ->where('company_id', $companyId)
            ->get()
            ->getRow();

        $next = ((int) ($last->max_id ?? 0)) + 1;

        return 'GR' . $year . $month . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /** @param array<string, mixed> $after */
    protected function audit(int $companyId, ?int $branchId, int $userId, string $event, int $entityId, array $after): void
    {
        (new AuditTrailService($this->db))->record(
            $event,
            'goods_receipt',
            $entityId,
            $companyId,
            $branchId,
            $userId,
            $after
        );
    }

    private function decimal(mixed $value): float
    {
        return (float) str_replace(',', '.', trim((string) $value));
    }

    private function money(float $value): string
    {
        return number_format($value, 4, '.', '');
    }
}
