<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use App\Services\AuditTrailService;

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
        $debugId = 'GRDBG-' . date('His') . '-' . random_int(1000, 9999);

        log_message('error', "{$debugId} START createDraftReceipt");
        log_message('error', "{$debugId} RAW DATA: " . json_encode($data));

        $companyId = (int) ($data['company_id'] ?? 0);
        $branchId  = isset($data['branch_id']) ? (int) $data['branch_id'] : null;
        $userId    = (int) ($data['actor_id'] ?? auth()->id());

        log_message('error', "{$debugId} CONTEXT: " . json_encode([
            'company_id' => $companyId,
            'branch_id'  => $branchId,
            'user_id'    => $userId,
            'auth_id'    => auth()->id(),
        ]));

        if ($companyId <= 0) {
            log_message('error', "{$debugId} FAIL company_id invalid");
            throw new \RuntimeException('Company context Goods Receipt tidak valid.');
        }

        // 1. DEBUG PO LOOKUP
        $poBuilder = $this->db->table('purchase_orders')
            ->where('id', (int) ($data['purchase_order_id'] ?? 0))
            ->where('company_id', $companyId)
            ->where('status', 'draft')
            ->where('deleted_at IS NULL', null, false);

        log_message('error', "{$debugId} PO SQL: " . $poBuilder->getCompiledSelect(false));

        $po = $poBuilder->get()->getRow();

        log_message('error', "{$debugId} PO RESULT: " . json_encode($po));

        if (! $po) {
            $debugPo = $this->db->table('purchase_orders')
                ->where('id', (int) ($data['purchase_order_id'] ?? 0))
                ->get()
                ->getRowArray();

            log_message('error', "{$debugId} PO NOT FOUND DETAIL WITHOUT FILTER: " . json_encode($debugPo));

            throw new \RuntimeException('Purchase Order tidak ditemukan atau bukan berstatus draft pada company aktif.');
        }

        // 2. DEBUG WAREHOUSE LOOKUP
        $warehouseBuilder = $this->db->table('warehouses')
            ->where('id', (int) ($data['warehouse_id'] ?? 0))
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('deleted_at IS NULL', null, false);

        log_message('error', "{$debugId} WAREHOUSE SQL: " . $warehouseBuilder->getCompiledSelect(false));

        $warehouse = $warehouseBuilder->get()->getRow();

        log_message('error', "{$debugId} WAREHOUSE RESULT: " . json_encode($warehouse));

        if (! $warehouse) {
            $debugWarehouse = $this->db->table('warehouses')
                ->where('id', (int) ($data['warehouse_id'] ?? 0))
                ->get()
                ->getRowArray();

            log_message('error', "{$debugId} WAREHOUSE NOT FOUND DETAIL WITHOUT FILTER: " . json_encode($debugWarehouse));

            throw new \RuntimeException('Warehouse tidak ditemukan atau tidak aktif pada company aktif.');
        }

        // Untuk debug, validasi branch jangan menggagalkan dulu.
        if ($branchId !== null && (int) $warehouse->branch_id !== $branchId) {
            log_message('error', "{$debugId} WARNING warehouse branch mismatch: " . json_encode([
                'active_branch_id'    => $branchId,
                'warehouse_branch_id' => (int) $warehouse->branch_id,
                'warehouse_id'        => (int) $warehouse->id,
            ]));

            // Sementara jangan throw dulu.
            // throw new \RuntimeException('Warehouse tujuan tidak berada pada branch aktif.');
        }

        // 3. DEBUG NORMALIZED LINES
        $lines = $this->normalizeReceiptLines($data);

        log_message('error', "{$debugId} NORMALIZED LINES: " . json_encode($lines));

        if ($lines === []) {
            log_message('error', "{$debugId} FAIL no receipt lines");
            throw new \RuntimeException('Minimal satu item Goods Receipt wajib diisi.');
        }

        $preparedLines = [];
        $totalQty      = 0.0;
        $totalAmount   = 0.0;

        foreach ($lines as $idx => $line) {
            log_message('error', "{$debugId} LINE {$idx} INPUT: " . json_encode($line));

            // 4. DEBUG PO ITEM LOOKUP
            $itemBuilder = $this->db->table('purchase_order_items')
                ->where('id', (int) $line['purchase_order_item_id'])
                ->where('purchase_order_id', (int) $po->id)
                ->where('company_id', $companyId)
                ->where('deleted_at IS NULL', null, false);

            log_message('error', "{$debugId} LINE {$idx} PO ITEM SQL: " . $itemBuilder->getCompiledSelect(false));

            $item = $itemBuilder->get()->getRow();

            log_message('error', "{$debugId} LINE {$idx} PO ITEM RESULT: " . json_encode($item));

            if (! $item) {
                $debugItem = $this->db->table('purchase_order_items')
                    ->where('id', (int) $line['purchase_order_item_id'])
                    ->get()
                    ->getRowArray();

                log_message('error', "{$debugId} LINE {$idx} PO ITEM NOT FOUND DETAIL WITHOUT FILTER: " . json_encode($debugItem));

                throw new \RuntimeException('Item Purchase Order tidak ditemukan.');
            }

            $qtyReceived = (float) str_replace(',', '.', (string) $line['qty_received']);

            log_message('error', "{$debugId} LINE {$idx} QTY CHECK: " . json_encode([
                'qty_received'  => $qtyReceived,
                'qty_remaining' => (float) $item->qty_remaining,
                'qty_ordered'   => (float) ($item->qty_ordered ?? 0),
                'received_qty'  => (float) ($item->received_qty ?? 0),
            ]));

            if ($qtyReceived <= 0) {
                throw new \RuntimeException('Qty diterima harus lebih dari nol.');
            }

            if ((float) $item->qty_remaining < $qtyReceived) {
                throw new \RuntimeException(sprintf(
                    'Qty diterima (%.4f) melebihi sisa PO (%.4f).',
                    $qtyReceived,
                    (float) $item->qty_remaining
                ));
            }

            // 5. DEBUG PRODUCT LOOKUP
            $productBuilder = $this->db->table('products')
                ->where('id', (int) $item->product_id)
                ->where('company_id', $companyId)
                ->where('deleted_at IS NULL', null, false);

            log_message('error', "{$debugId} LINE {$idx} PRODUCT SQL: " . $productBuilder->getCompiledSelect(false));

            $product = $productBuilder->get()->getRow();

            log_message('error', "{$debugId} LINE {$idx} PRODUCT RESULT: " . json_encode($product));

            if (! $product) {
                $debugProduct = $this->db->table('products')
                    ->where('id', (int) $item->product_id)
                    ->get()
                    ->getRowArray();

                log_message('error', "{$debugId} LINE {$idx} PRODUCT NOT FOUND DETAIL WITHOUT FILTER: " . json_encode($debugProduct));

                throw new \RuntimeException('Produk pada PO item tidak ditemukan.');
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

        log_message('error', "{$debugId} PREPARED SUMMARY: " . json_encode([
            'line_count'   => count($preparedLines),
            'total_qty'    => $totalQty,
            'total_amount' => $totalAmount,
        ]));

        $receiptNumber = $this->nextReceiptNumber($companyId);
        $now           = date('Y-m-d H:i:s');

        log_message('error', "{$debugId} RECEIPT NUMBER: {$receiptNumber}");

        $this->db->transStart();

        log_message('error', "{$debugId} TRANS START");

        // 6. INSERT HEADER GR
        $headerData = [
            'company_id'        => $companyId,
            'branch_id'         => $po->branch_id ?? $branchId,
            'warehouse_id'      => $warehouse->id,
            'purchase_order_id' => $po->id,
            'receipt_number'    => $receiptNumber,
            'receipt_date'      => date('Y-m-d'),
            'status'            => 'draft',
            'total_qty'         => $totalQty,
            'total_amount'      => $totalAmount,
            'created_by'        => $userId,
            'updated_by'        => $userId,
            'created_at'        => $now,
            'updated_at'        => $now,
        ];

        log_message('error', "{$debugId} INSERT GR HEADER DATA: " . json_encode($headerData));

        $insertHeaderResult = $this->db->table('goods_receipts')->insert($headerData);
        $receiptId = (int) $this->db->insertID();

        log_message('error', "{$debugId} INSERT GR HEADER RESULT: " . json_encode([
            'insert_result' => $insertHeaderResult,
            'insert_id'     => $receiptId,
            'db_error'      => $this->db->error(),
        ]));

        if (! $insertHeaderResult || $receiptId <= 0) {
            $error = $this->db->error();

            throw new \RuntimeException(
                'Header Goods Receipt gagal dibuat: ' . ($error['message'] ?? 'unknown database error')
            );
        }

        // 7. INSERT ITEMS
        foreach ($preparedLines as $idx => $line) {
            $poItem = $line['po_item'];

            $itemData = [
                'goods_receipt_id'       => $receiptId,
                'purchase_order_item_id' => $poItem->id,
                'product_id'             => $poItem->product_id,
                'warehouse_id'           => $warehouse->id,
                'qty_received'           => $line['qty_received'],
                'unit_cost'              => $line['unit_cost'],
                'line_total'             => $line['line_total'],
                'status'                 => 'draft',
                'created_by'             => $userId,
                'updated_by'             => $userId,
                'created_at'             => $now,
                'updated_at'             => $now,
            ];

            log_message('error', "{$debugId} INSERT GR ITEM {$idx} DATA: " . json_encode($itemData));

            $insertItemResult = $this->db->table('goods_receipt_items')->insert($itemData);
            $itemInsertId = (int) $this->db->insertID();

            log_message('error', "{$debugId} INSERT GR ITEM {$idx} RESULT: " . json_encode([
                'insert_result' => $insertItemResult,
                'insert_id'     => $itemInsertId,
                'db_error'      => $this->db->error(),
            ]));

            if (! $insertItemResult || $itemInsertId <= 0) {
                $error = $this->db->error();

                throw new \RuntimeException(
                    'Item Goods Receipt gagal dibuat: ' . ($error['message'] ?? 'unknown database error')
                );
            }
        }

        // 8. AUDIT SEMENTARA DI-COMMENT DULU UNTUK ISOLASI
        log_message('error', "{$debugId} SKIP AUDIT TEMPORARILY");

        // $this->audit(
        //     $companyId,
        //     $userId,
        //     'GOODS_RECEIPT_CREATED',
        //     "GR draft {$receiptNumber} dibuat dari PO {$po->po_no}",
        //     $receiptId
        // );

        $this->db->transComplete();

        log_message('error', "{$debugId} TRANS COMPLETE: " . json_encode([
            'trans_status' => $this->db->transStatus(),
            'db_error'     => $this->db->error(),
            'receipt_id'   => $receiptId,
        ]));

        if ($this->db->transStatus() === false) {
            $error = $this->db->error();

            log_message('error', "{$debugId} TRANS FAILED DB ERROR: " . json_encode($error));

            throw new \RuntimeException(
                'Transaksi gagal disimpan: ' . ($error['message'] ?? 'unknown database error')
            );
        }

        log_message('error', "{$debugId} SUCCESS: " . json_encode([
            'receipt_id'     => $receiptId,
            'receipt_number' => $receiptNumber,
            'status'         => 'draft',
        ]));

        return [
            'id'             => $receiptId,
            'receipt_number' => $receiptNumber,
            'status'         => 'draft',
        ];
    }

    public function postReceipt(int $receiptId, int $companyId, int $userId): array
    {
        if ($companyId <= 0) {
            throw new \RuntimeException('Company context Goods Receipt tidak valid.');
        }

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

        $items = $this->db->table('goods_receipt_items')
            ->where('goods_receipt_id', $receipt->id)
            ->where('deleted_at', null)
            ->get()->getResult();

        if ($items === []) {
            throw new \RuntimeException('Goods Receipt tidak memiliki item.');
        }

        $poItems = [];

        foreach ($items as $item) {
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

            $poItems[(int) $item->id] = $poItem;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        foreach ($items as $item) {
            $poItem = $poItems[(int) $item->id];
            $qty    = (float) $item->qty_received;

            $this->db->table('purchase_order_items')
                ->where('id', $poItem->id)
                ->update([
                    'qty_remaining' => (float) $poItem->qty_remaining - $qty,
                    'received_qty'  => (float) ($poItem->received_qty ?? 0) + $qty,
                    'updated_by'    => $userId,
                    'updated_at'    => $now,
                ]);

            $balance = $this->db->table('stock_balances')
                ->where('company_id', $companyId)
                ->where('warehouse_id', $item->warehouse_id)
                ->where('product_id', $item->product_id)
                ->get()->getRow();

            if ($balance) {
                $this->db->table('stock_balances')
                    ->where('id', $balance->id)
                    ->update([
                        'qty_on_hand' => (float) $balance->qty_on_hand + $qty,
                        'updated_at'  => $now,
                    ]);
            } else {
                $this->db->table('stock_balances')->insert([
                    'company_id'  => $companyId,
                    'warehouse_id' => $item->warehouse_id,
                    'product_id'  => $item->product_id,
                    'qty_on_hand' => $qty,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }

            $this->db->table('stock_movements')->insert([
                'company_id'     => $companyId,
                'warehouse_id'   => $item->warehouse_id,
                'product_id'     => $item->product_id,
                'movement_type'  => 'receipt_in',
                'qty'            => $qty,
                'reference_type' => 'goods_receipt',
                'reference_id'   => $receipt->id,
                'created_by'     => $userId,
                'created_at'     => $now,
            ]);

            $this->db->table('goods_receipt_items')
                ->where('id', $item->id)
                ->update(['status' => 'posted', 'updated_by' => $userId, 'updated_at' => $now]);
        }

        $this->db->table('goods_receipts')
            ->where('id', $receipt->id)
            ->update(['status' => 'posted', 'updated_by' => $userId, 'updated_at' => $now]);

        $this->audit($companyId, $userId, 'GOODS_RECEIPT_POSTED', "GR {$receipt->receipt_number} diposting ke stock ledger", $receipt->id);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Posting gagal. Silakan coba lagi.');
        }

        return ['id' => $receipt->id, 'receipt_number' => $receipt->receipt_number, 'status' => 'posted'];
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
                $qty      = (float) ($item['qty_received'] ?? 0);

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
            'purchase_order_item_id' => (int) $data['purchase_order_item_id'],
            'qty_received'           => (float) $data['qty_received'],
        ]];
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

    // protected function audit(int $companyId, int $userId, string $event, string $description, int $referenceId): void
    // {
    //     $this->db->table('audit_logs')->insert([
    //         'company_id'     => $companyId,
    //         'user_id'        => $userId,
    //         'event_type'     => $event,
    //         'description'    => $description,
    //         'reference_type' => 'goods_receipt',
    //         'reference_id'   => $referenceId,
    //         'created_at'     => date('Y-m-d H:i:s'),
    //     ]);
    // }

    protected function audit(
        int $companyId,
        int $userId,
        string $event,
        string $description,
        int|string $referenceId
    ): void {
        (new AuditTrailService($this->db))->record(
            $event,
            'goods_receipt',
            (int) $referenceId,
            $companyId,
            null,
            $userId,
            [
                'description' => $description,
            ]
        );
    }
}
