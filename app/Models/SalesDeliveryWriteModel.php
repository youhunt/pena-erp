<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\AuditTrailService;
use CodeIgniter\Model;
use RuntimeException;

final class SalesDeliveryWriteModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /** @param array<string, mixed> $data */
    public function createDraftDelivery(array $data): array
    {
        $companyId = (int) ($data['company_id'] ?? 0);
        $branchId  = isset($data['branch_id']) ? (int) $data['branch_id'] : null;
        $userId    = (int) ($data['actor_id'] ?? auth()->id());

        if ($companyId <= 0) {
            throw new RuntimeException('Company context Delivery Order tidak valid.');
        }

        $order = $this->db->table('sales_orders')
            ->where('id', (int) ($data['sales_order_id'] ?? 0))
            ->where('company_id', $companyId)
            ->where('status', 'confirmed')
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getRow();

        if (! $order) {
            throw new RuntimeException('Sales Order tidak ditemukan atau belum confirmed pada company aktif.');
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

        $lines = $this->normalizeLines($data);

        if ($lines === []) {
            throw new RuntimeException('Minimal satu item Delivery Order wajib diisi.');
        }

        $preparedLines = [];
        $totalQty      = 0.0;
        $totalAmount   = 0.0;

        foreach ($lines as $line) {
            $item = $this->db->table('sales_order_items')
                ->where('id', (int) $line['sales_order_item_id'])
                ->where('sales_order_id', (int) $order->id)
                ->where('company_id', $companyId)
                ->where('deleted_at IS NULL', null, false)
                ->get()
                ->getRow();

            if (! $item) {
                throw new RuntimeException('Item Sales Order tidak ditemukan.');
            }

            $qtyDelivered = $this->decimal($line['qty_delivered']);

            if ($qtyDelivered <= 0) {
                continue;
            }

            if ((float) $item->qty_remaining < $qtyDelivered) {
                throw new RuntimeException(sprintf('Qty dikirim (%.4f) melebihi sisa SO (%.4f).', $qtyDelivered, (float) $item->qty_remaining));
            }

            $lineTotal = $qtyDelivered * (float) $item->unit_price;

            $preparedLines[] = [
                'so_item'       => $item,
                'qty_delivered' => $qtyDelivered,
                'unit_price'    => (float) $item->unit_price,
                'line_total'    => $lineTotal,
            ];

            $totalQty    += $qtyDelivered;
            $totalAmount += $lineTotal;
        }

        if ($preparedLines === []) {
            throw new RuntimeException('Minimal satu item harus memiliki qty kirim lebih dari nol.');
        }

        $deliveryNumber = $this->nextDeliveryNumber($companyId);
        $deliveryBranch = isset($order->branch_id) ? (int) $order->branch_id : $branchId;
        $now            = date('Y-m-d H:i:s');

        $this->db->transStart();

        $this->db->table('delivery_orders')->insert([
            'company_id'        => $companyId,
            'branch_id'         => $deliveryBranch,
            'warehouse_id'      => (int) $warehouse->id,
            'sales_order_id'    => (int) $order->id,
            'delivery_number'   => $deliveryNumber,
            'delivery_date'     => date('Y-m-d'),
            'status'            => 'draft',
            'total_qty'         => $this->money($totalQty),
            'total_amount'      => $this->money($totalAmount),
            'created_by'        => $userId,
            'updated_by'        => $userId,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $deliveryId = (int) $this->db->insertID();

        if ($deliveryId <= 0) {
            throw new RuntimeException('Header Delivery Order gagal dibuat.');
        }

        foreach ($preparedLines as $line) {
            $soItem = $line['so_item'];

            $this->db->table('delivery_order_items')->insert([
                'delivery_order_id'  => $deliveryId,
                'sales_order_item_id' => (int) $soItem->id,
                'product_id'          => (int) $soItem->product_id,
                'warehouse_id'        => (int) $warehouse->id,
                'qty_delivered'       => $this->money((float) $line['qty_delivered']),
                'unit_price'          => $this->money((float) $line['unit_price']),
                'line_total'          => $this->money((float) $line['line_total']),
                'status'              => 'draft',
                'created_by'          => $userId,
                'updated_by'          => $userId,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }

        $this->audit($companyId, $deliveryBranch, $userId, 'DELIVERY_ORDER_CREATED', $deliveryId, [
            'delivery_number' => $deliveryNumber,
            'sales_order_id'  => (int) $order->id,
            'order_no'        => (string) $order->order_no,
            'warehouse_id'    => (int) $warehouse->id,
            'total_qty'       => $totalQty,
            'total_amount'    => $totalAmount,
            'line_count'      => count($preparedLines),
        ]);

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Delivery Order gagal disimpan.');
        }

        return ['id' => $deliveryId, 'delivery_number' => $deliveryNumber, 'status' => 'draft'];
    }

    public function postDelivery(int $deliveryId, int $companyId, int $userId): array
    {
        $delivery = $this->db->table('delivery_orders')
            ->where('id', $deliveryId)
            ->where('company_id', $companyId)
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getRow();

        if (! $delivery) {
            throw new RuntimeException('Delivery Order tidak ditemukan.');
        }

        if ($delivery->status === 'posted') {
            throw new RuntimeException('Delivery Order sudah pernah diposting.');
        }

        $items = $this->db->table('delivery_order_items')
            ->where('delivery_order_id', (int) $delivery->id)
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getResult();

        if ($items === []) {
            throw new RuntimeException('Delivery Order tidak memiliki item.');
        }

        $soItems = [];

        foreach ($items as $item) {
            $soItem = $this->db->table('sales_order_items')
                ->where('id', (int) $item->sales_order_item_id)
                ->where('company_id', $companyId)
                ->where('deleted_at IS NULL', null, false)
                ->get()
                ->getRow();

            if (! $soItem) {
                throw new RuntimeException('SO item tidak ditemukan.');
            }

            if ((float) $soItem->qty_remaining < (float) $item->qty_delivered) {
                throw new RuntimeException('Sisa qty SO tidak mencukupi untuk posting delivery ini.');
            }

            $balance = $this->db->table('stock_balances')
                ->where('company_id', $companyId)
                ->where('warehouse_id', (int) $item->warehouse_id)
                ->where('product_id', (int) $item->product_id)
                ->get()
                ->getRow();

            if (! $balance || (float) $balance->qty_on_hand < (float) $item->qty_delivered) {
                throw new RuntimeException('Stock on hand tidak mencukupi untuk salah satu item delivery.');
            }

            $soItems[(int) $item->id] = ['so_item' => $soItem, 'balance' => $balance];
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        foreach ($items as $item) {
            $soItem  = $soItems[(int) $item->id]['so_item'];
            $balance = $soItems[(int) $item->id]['balance'];
            $qty     = (float) $item->qty_delivered;

            $this->db->table('sales_order_items')
                ->where('id', (int) $soItem->id)
                ->update([
                    'qty_remaining' => $this->money((float) $soItem->qty_remaining - $qty),
                    'qty_delivered' => $this->money((float) ($soItem->qty_delivered ?? 0) + $qty),
                    'updated_by'    => $userId,
                    'updated_at'    => $now,
                ]);

            $this->db->table('stock_balances')
                ->where('id', (int) $balance->id)
                ->update([
                    'qty_on_hand' => $this->money((float) $balance->qty_on_hand - $qty),
                    'updated_at'  => $now,
                ]);

            $this->db->table('stock_movements')->insert([
                'company_id'     => $companyId,
                'warehouse_id'   => (int) $item->warehouse_id,
                'product_id'     => (int) $item->product_id,
                'movement_type'  => 'delivery_out',
                'qty'            => $this->money(-1 * $qty),
                'reference_type' => 'delivery_order',
                'reference_id'   => (int) $delivery->id,
                'created_by'     => $userId,
                'created_at'     => $now,
            ]);

            $this->db->table('delivery_order_items')
                ->where('id', (int) $item->id)
                ->update([
                    'status'     => 'posted',
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);
        }

        $this->db->table('delivery_orders')
            ->where('id', (int) $delivery->id)
            ->update([
                'status'     => 'posted',
                'updated_by' => $userId,
                'updated_at' => $now,
            ]);

        $this->audit($companyId, isset($delivery->branch_id) ? (int) $delivery->branch_id : null, $userId, 'DELIVERY_ORDER_POSTED', (int) $delivery->id, [
            'delivery_number' => (string) $delivery->delivery_number,
            'sales_order_id'  => (int) $delivery->sales_order_id,
            'total_qty'       => (float) $delivery->total_qty,
            'line_count'      => count($items),
        ]);

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Posting Delivery Order gagal.');
        }

        return ['id' => (int) $delivery->id, 'delivery_number' => (string) $delivery->delivery_number, 'status' => 'posted'];
    }

    /**
     * @param array<string, mixed> $data
     * @return list<array{sales_order_item_id:int, qty_delivered:float|string}>
     */
    private function normalizeLines(array $data): array
    {
        $posted = $data['items'] ?? [];
        if (! is_array($posted)) {
            return [];
        }

        $lines = [];
        foreach ($posted as $line) {
            if (! is_array($line)) {
                continue;
            }

            $itemId = (int) ($line['sales_order_item_id'] ?? 0);
            $qty    = $line['qty_delivered'] ?? 0;

            if ($itemId <= 0 && $this->decimal($qty) <= 0) {
                continue;
            }

            $lines[] = ['sales_order_item_id' => $itemId, 'qty_delivered' => $qty];
        }

        return $lines;
    }

    private function nextDeliveryNumber(int $companyId): string
    {
        $last = $this->db->table('delivery_orders')
            ->selectMax('id', 'max_id')
            ->where('company_id', $companyId)
            ->get()
            ->getRow();

        return 'DO' . date('Ym') . '-' . str_pad((string) (((int) ($last->max_id ?? 0)) + 1), 4, '0', STR_PAD_LEFT);
    }

    /** @param array<string, mixed> $after */
    private function audit(int $companyId, ?int $branchId, int $userId, string $event, int $entityId, array $after): void
    {
        (new AuditTrailService($this->db))->record($event, 'delivery_order', $entityId, $companyId, $branchId, $userId, $after);
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
