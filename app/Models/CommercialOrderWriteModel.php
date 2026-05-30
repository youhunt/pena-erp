<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\AuditTrailService;
use CodeIgniter\Model;
use RuntimeException;

final class CommercialOrderWriteModel extends Model
{
    protected $table = 'sales_orders';

    /**
     * @param array<string, mixed> $header
     * @param array<int|string, mixed> $lines
     */
    public function createSalesOrder(array $header, array $lines, int $actorId): bool
    {
        return $this->createOrder('sales', $header, $lines, $actorId);
    }

    /**
     * @param array<string, mixed> $header
     * @param array<int|string, mixed> $lines
     */
    public function createPurchaseOrder(array $header, array $lines, int $actorId): bool
    {
        return $this->createOrder('purchasing', $header, $lines, $actorId);
    }

    public function confirmSalesOrder(int $salesOrderId, int $companyId, int $actorId): bool
    {
        $order = $this->db->table('sales_orders')
            ->where('id', $salesOrderId)
            ->where('company_id', $companyId)
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getFirstRow('array');

        if ($order === null || (string) $order['status'] !== 'draft') {
            return false;
        }

        $lineCount = $this->db->table('sales_order_items')
            ->where('sales_order_id', $salesOrderId)
            ->where('company_id', $companyId)
            ->where('deleted_at IS NULL', null, false)
            ->countAllResults();

        if ($lineCount <= 0) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        $this->db->table('sales_orders')
            ->where('id', $salesOrderId)
            ->where('company_id', $companyId)
            ->update([
                'status'     => 'confirmed',
                'updated_by' => $actorId,
                'updated_at' => $now,
            ]);

        $this->audit()->record('SALES_ORDER_CONFIRMED', 'sales_order', $salesOrderId, $companyId, (int) $order['branch_id'], $actorId, [
            'order_no'     => (string) $order['order_no'],
            'old_status'   => 'draft',
            'new_status'   => 'confirmed',
            'line_count'   => $lineCount,
            'total_amount' => (float) $order['total_amount'],
        ]);

        $this->complete('Konfirmasi Sales Order gagal dan transaksi dibatalkan.');

        return true;
    }

    /**
     * @param array<string, mixed> $header
     * @param array<int|string, mixed> $lines
     */
    private function createOrder(string $side, array $header, array $lines, int $actorId): bool
    {
        $companyId = (int) $header['company_id'];
        $warehouse = $this->record('warehouses', $companyId, (int) $header['warehouse_id'], 'is_active', true);

        if ($warehouse === null) {
            return false;
        }

        $partnerTable = $side === 'sales' ? 'customers' : 'suppliers';
        $termTable    = $side === 'sales' ? 'customer_terms' : 'supplier_terms';
        $partnerKey   = $side === 'sales' ? 'customer_id' : 'supplier_id';
        $module       = $side === 'sales' ? 'sales' : 'purchasing';

        if (! $this->activePartner($partnerTable, $companyId, (int) $header[$partnerKey])
            || ! $this->recordExists('branches', $companyId, (int) $warehouse['branch_id'])
            || ! $this->activeRecord('currencies', $companyId, (int) $header['currency_id'])
            || (($header['term_id'] ?? null) !== null && ! $this->activeRecord($termTable, $companyId, (int) $header['term_id']))
            || ! $this->transactionCodeMatches($companyId, (int) $header['transaction_code_id'], $module, (int) $warehouse['branch_id'])) {
            return false;
        }

        $linePlans = $this->prepareLines($companyId, $side, $lines);

        if ($linePlans === []) {
            return false;
        }

        $subtotal    = array_sum(array_column($linePlans, 'subtotal'));
        $taxAmount   = array_sum(array_column($linePlans, 'tax_amount'));
        $totalAmount = $subtotal + $taxAmount;
        $now         = date('Y-m-d H:i:s');

        $this->db->transStart();

        if ($side === 'sales') {
            $orderNo = $this->nextDocumentNo($companyId, (int) $header['transaction_code_id'], $actorId);
            $order = [
                'company_id'           => $companyId,
                'branch_id'            => (int) $warehouse['branch_id'],
                'customer_id'          => (int) $header['customer_id'],
                'warehouse_id'         => (int) $warehouse['id'],
                'currency_id'          => (int) $header['currency_id'],
                'term_id'              => $header['term_id'] ?? null,
                'transaction_code_id'  => (int) $header['transaction_code_id'],
                'order_no'             => $orderNo,
                'order_date'           => (string) $header['order_date'],
                'requested_ship_date'  => $header['requested_ship_date'] ?? null,
                'customer_po_no'       => $header['customer_po_no'] ?? null,
                'subtotal'             => $this->money($subtotal),
                'tax_amount'           => $this->money($taxAmount),
                'total_amount'         => $this->money($totalAmount),
                'status'               => 'draft',
                'created_by'           => $actorId,
                'created_at'           => $now,
            ];
            $this->db->table('sales_orders')->insert($order);
            $orderId = (int) $this->db->insertID();

            foreach ($linePlans as $plan) {
                $this->db->table('sales_order_items')->insert($this->salesLineData($companyId, $orderId, $plan, $actorId, $now));
            }

            $this->audit()->record('SALES_ORDER_CREATED', 'sales_order', $orderId, $companyId, (int) $warehouse['branch_id'], $actorId, $order + ['line_count' => count($linePlans)]);
        } else {
            $poNo = $this->nextDocumentNo($companyId, (int) $header['transaction_code_id'], $actorId);
            $order = [
                'company_id'             => $companyId,
                'branch_id'              => (int) $warehouse['branch_id'],
                'supplier_id'            => (int) $header['supplier_id'],
                'warehouse_id'           => (int) $warehouse['id'],
                'currency_id'            => (int) $header['currency_id'],
                'term_id'                => $header['term_id'] ?? null,
                'transaction_code_id'    => (int) $header['transaction_code_id'],
                'po_no'                  => $poNo,
                'order_date'             => (string) $header['order_date'],
                'expected_receipt_date'  => $header['expected_receipt_date'] ?? null,
                'supplier_ref_no'        => $header['supplier_ref_no'] ?? null,
                'subtotal'               => $this->money($subtotal),
                'tax_amount'             => $this->money($taxAmount),
                'total_amount'           => $this->money($totalAmount),
                'status'                 => 'draft',
                'created_by'             => $actorId,
                'created_at'             => $now,
            ];
            $this->db->table('purchase_orders')->insert($order);
            $orderId = (int) $this->db->insertID();

            foreach ($linePlans as $plan) {
                $this->db->table('purchase_order_items')->insert($this->purchaseLineData($companyId, $orderId, $plan, $actorId, $now));
            }

            $this->audit()->record('PURCHASE_ORDER_CREATED', 'purchase_order', $orderId, $companyId, (int) $warehouse['branch_id'], $actorId, $order + ['line_count' => count($linePlans)]);
        }

        $this->complete('Pembuatan commercial order gagal dan transaksi dibatalkan.');

        return true;
    }

    /**
     * @param array<int|string, mixed> $lines
     * @return list<array<string, mixed>>
     */
    private function prepareLines(int $companyId, string $side, array $lines): array
    {
        $normalized = $this->normalizeLines($lines);
        $plans = [];

        foreach ($normalized as $line) {
            $product = $this->record('products', $companyId, (int) $line['product_id']);

            if ($product === null) {
                return [];
            }

            $qty       = (float) $line['qty'];
            $unitPrice = (float) $line['unit_price'];

            if ($qty <= 0 || $unitPrice < 0) {
                return [];
            }

            $tax       = $this->taxForProduct($companyId, (int) $product['id'], $side === 'sales' ? 'sales' : 'purchase');
            $taxRate   = $tax === null ? 0.0 : (float) $tax['rate'];
            $subtotal  = round($qty * $unitPrice, 4);
            $taxAmount = round($subtotal * $taxRate, 4);

            $plans[] = [
                'product'    => $product,
                'tax'        => $tax,
                'qty'        => $qty,
                'unit_price' => $unitPrice,
                'tax_rate'   => $taxRate,
                'subtotal'   => $subtotal,
                'tax_amount' => $taxAmount,
            ];
        }

        return $plans;
    }

    /**
     * Supports both legacy single-line payload and new multi-line array payload.
     *
     * @param array<int|string, mixed> $lines
     * @return list<array{product_id:int, qty:float, unit_price:float}>
     */
    private function normalizeLines(array $lines): array
    {
        if (isset($lines['product_id'])) {
            return [[
                'product_id' => (int) $lines['product_id'],
                'qty'        => $this->decimal($lines['qty'] ?? 0),
                'unit_price' => $this->decimal($lines['unit_price'] ?? 0),
            ]];
        }

        $normalized = [];

        foreach ($lines as $line) {
            if (! is_array($line)) {
                continue;
            }

            $productId = (int) ($line['product_id'] ?? 0);
            $qty       = $this->decimal($line['qty'] ?? 0);
            $unitPrice = $this->decimal($line['unit_price'] ?? 0);

            if ($productId <= 0 && $qty <= 0) {
                continue;
            }

            $normalized[] = [
                'product_id' => $productId,
                'qty'        => $qty,
                'unit_price' => $unitPrice,
            ];
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $plan
     * @return array<string, mixed>
     */
    private function salesLineData(int $companyId, int $orderId, array $plan, int $actorId, string $now): array
    {
        $product = $plan['product'];
        $tax     = $plan['tax'];
        $qty     = (float) $plan['qty'];

        $data = [
            'company_id'     => $companyId,
            'sales_order_id' => $orderId,
            'product_id'     => (int) $product['id'],
            'uom_id'         => (int) $product['base_uom_id'],
            'tax_code_id'    => $tax === null ? null : (int) $tax['id'],
            'qty'            => $this->money($qty),
            'unit_price'     => $this->money((float) $plan['unit_price']),
            'tax_rate'       => number_format((float) $plan['tax_rate'], 6, '.', ''),
            'tax_amount'     => $this->money((float) $plan['tax_amount']),
            'line_total'     => $this->money((float) $plan['subtotal']),
            'created_by'     => $actorId,
            'created_at'     => $now,
        ];

        if ($this->db->fieldExists('qty_delivered', 'sales_order_items')) {
            $data['qty_delivered'] = $this->money(0.0);
        }

        if ($this->db->fieldExists('qty_remaining', 'sales_order_items')) {
            $data['qty_remaining'] = $this->money($qty);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $plan
     * @return array<string, mixed>
     */
    private function purchaseLineData(int $companyId, int $orderId, array $plan, int $actorId, string $now): array
    {
        $product = $plan['product'];
        $tax     = $plan['tax'];
        $qty     = (float) $plan['qty'];

        return [
            'company_id'        => $companyId,
            'purchase_order_id' => $orderId,
            'product_id'        => (int) $product['id'],
            'uom_id'            => (int) $product['base_uom_id'],
            'tax_code_id'       => $tax === null ? null : (int) $tax['id'],
            'qty'               => $this->money($qty),
            'qty_ordered'       => $this->money($qty),
            'qty_remaining'     => $this->money($qty),
            'unit_price'        => $this->money((float) $plan['unit_price']),
            'tax_rate'          => number_format((float) $plan['tax_rate'], 6, '.', ''),
            'tax_amount'        => $this->money((float) $plan['tax_amount']),
            'line_total'        => $this->money((float) $plan['subtotal']),
            'created_by'        => $actorId,
            'created_at'        => $now,
        ];
    }

    private function nextDocumentNo(int $companyId, int $transactionCodeId, int $actorId): string
    {
        $row = $this->db->table('transaction_codes')
            ->where(['company_id' => $companyId, 'id' => $transactionCodeId, 'status' => 'active'])
            ->where('deleted_at', null)
            ->get()->getFirstRow('array');

        if ($row === null) {
            throw new RuntimeException('Transaction Code order tidak valid.');
        }

        $number = (int) $row['next_number'];
        $documentNo = (string) $row['prefix'] . str_pad((string) $number, (int) $row['number_length'], '0', STR_PAD_LEFT);
        $this->db->table('transaction_codes')->where(['company_id' => $companyId, 'id' => $transactionCodeId])->update([
            'next_number' => $number + 1,
            'updated_by'  => $actorId,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        return $documentNo;
    }

    /** @return array<string, mixed>|null */
    private function taxForProduct(int $companyId, int $productId, string $usage): ?array
    {
        return $this->db->table('product_tax_codes pt')
            ->select('tc.id, tc.rate')
            ->join('tax_codes tc', 'tc.id = pt.tax_code_id AND tc.company_id = pt.company_id')
            ->where(['pt.company_id' => $companyId, 'pt.product_id' => $productId, 'pt.status' => 'active', 'tc.status' => 'active'])
            ->whereIn('pt.usage_type', [$usage, 'both'])
            ->where('pt.deleted_at', null)
            ->where('tc.deleted_at', null)
            ->orderBy('pt.usage_type', 'DESC')
            ->get()->getFirstRow('array');
    }

    /** @return array<string, mixed>|null */
    private function record(string $table, int $companyId, int $id, string $activeColumn = 'status', mixed $activeValue = 'active'): ?array
    {
        return $this->db->table($table)
            ->where(['company_id' => $companyId, 'id' => $id, $activeColumn => $activeValue])
            ->where('deleted_at', null)
            ->get()->getFirstRow('array');
    }

    private function recordExists(string $table, int $companyId, int $id): bool
    {
        return $this->db->table($table)->where(['company_id' => $companyId, 'id' => $id])
            ->where('deleted_at', null)->countAllResults() === 1;
    }

    private function activeRecord(string $table, int $companyId, int $id): bool
    {
        return $this->record($table, $companyId, $id) !== null;
    }

    private function activePartner(string $table, int $companyId, int $id): bool
    {
        return $this->db->table($table)
            ->where(['company_id' => $companyId, 'id' => $id, 'status' => 'active'])
            ->where('deleted_at', null)
            ->countAllResults() === 1;
    }

    private function transactionCodeMatches(int $companyId, int $id, string $module, int $branchId): bool
    {
        return $this->db->table('transaction_codes')
            ->where(['company_id' => $companyId, 'id' => $id, 'module' => $module, 'status' => 'active'])
            ->groupStart()->where('branch_id', $branchId)->orWhere('branch_id', null)->groupEnd()
            ->where('deleted_at', null)
            ->countAllResults() === 1;
    }

    private function decimal(mixed $value): float
    {
        return (float) str_replace(',', '.', trim((string) $value));
    }

    private function money(float $value): string
    {
        return number_format($value, 4, '.', '');
    }

    private function audit(): AuditTrailService
    {
        return new AuditTrailService($this->db);
    }

    private function complete(string $message): void
    {
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException($message);
        }
    }
}
