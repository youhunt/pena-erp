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
     * @param array<string, mixed> $line
     */
    public function createSalesOrder(array $header, array $line, int $actorId): bool
    {
        return $this->createOrder('sales', $header, $line, $actorId);
    }

    /**
     * @param array<string, mixed> $header
     * @param array<string, mixed> $line
     */
    public function createPurchaseOrder(array $header, array $line, int $actorId): bool
    {
        return $this->createOrder('purchasing', $header, $line, $actorId);
    }

    /**
     * @param array<string, mixed> $header
     * @param array<string, mixed> $line
     */
    private function createOrder(string $side, array $header, array $line, int $actorId): bool
    {
        $companyId = (int) $header['company_id'];
        $warehouse = $this->record('warehouses', $companyId, (int) $header['warehouse_id'], 'is_active', true);
        $product = $this->record('products', $companyId, (int) $line['product_id']);

        if ($warehouse === null || $product === null) {
            return false;
        }

        $partnerTable = $side === 'sales' ? 'customers' : 'suppliers';
        $termTable = $side === 'sales' ? 'customer_terms' : 'supplier_terms';
        $partnerKey = $side === 'sales' ? 'customer_id' : 'supplier_id';
        $module = $side === 'sales' ? 'sales' : 'purchasing';

        if (! $this->activePartner($partnerTable, $companyId, (int) $header[$partnerKey])
            || ! $this->recordExists('branches', $companyId, (int) $warehouse['branch_id'])
            || ! $this->activeRecord('currencies', $companyId, (int) $header['currency_id'])
            || (($header['term_id'] ?? null) !== null && ! $this->activeRecord($termTable, $companyId, (int) $header['term_id']))
            || ! $this->transactionCodeMatches($companyId, (int) $header['transaction_code_id'], $module, (int) $warehouse['branch_id'])) {
            return false;
        }

        $qty = (float) $line['qty'];
        $unitPrice = (float) $line['unit_price'];

        if ($qty <= 0 || $unitPrice < 0) {
            return false;
        }

        $tax = $this->taxForProduct($companyId, (int) $product['id'], $side === 'sales' ? 'sales' : 'purchase');
        $taxRate = $tax === null ? 0.0 : (float) $tax['rate'];
        $subtotal = round($qty * $unitPrice, 4);
        $taxAmount = round($subtotal * $taxRate, 4);
        $totalAmount = round($subtotal + $taxAmount, 4);
        $now = date('Y-m-d H:i:s');

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
            $this->db->table('sales_order_items')->insert($this->lineData($companyId, 'sales_order_id', $orderId, $product, $tax, $qty, $unitPrice, $subtotal, $taxRate, $taxAmount, $actorId, $now));
            $this->audit()->record('SALES_ORDER_CREATED', 'sales_order', $orderId, $companyId, (int) $warehouse['branch_id'], $actorId, $order);
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
            $this->db->table('purchase_order_items')->insert($this->lineData($companyId, 'purchase_order_id', $orderId, $product, $tax, $qty, $unitPrice, $subtotal, $taxRate, $taxAmount, $actorId, $now));
            $this->audit()->record('PURCHASE_ORDER_CREATED', 'purchase_order', $orderId, $companyId, (int) $warehouse['branch_id'], $actorId, $order);
        }

        $this->complete();

        return true;
    }

    /**
     * @param array<string, mixed>      $product
     * @param array<string, mixed>|null $tax
     *
     * @return array<string, mixed>
     */
    private function lineData(int $companyId, string $orderKey, int $orderId, array $product, ?array $tax, float $qty, float $unitPrice, float $subtotal, float $taxRate, float $taxAmount, int $actorId, string $now): array
    {
        return [
            'company_id'  => $companyId,
            $orderKey     => $orderId,
            'product_id'  => (int) $product['id'],
            'uom_id'      => (int) $product['base_uom_id'],
            'tax_code_id' => $tax === null ? null : (int) $tax['id'],
            'qty'         => $this->money($qty),
            'unit_price'  => $this->money($unitPrice),
            'tax_rate'    => number_format($taxRate, 6, '.', ''),
            'tax_amount'  => $this->money($taxAmount),
            'line_total'  => $this->money($subtotal),
            'created_by'  => $actorId,
            'created_at'  => $now,
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

    private function money(float $value): string
    {
        return number_format($value, 4, '.', '');
    }

    private function audit(): AuditTrailService
    {
        return new AuditTrailService($this->db);
    }

    private function complete(): void
    {
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Pembuatan commercial order gagal dan transaksi dibatalkan.');
        }
    }
}
