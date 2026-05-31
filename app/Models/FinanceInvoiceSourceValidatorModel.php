<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class FinanceInvoiceSourceValidatorModel extends Model
{
    /** @param array<string, mixed> $data */
    public function validPurchaseInvoiceSource(array $data): bool
    {
        $purchaseOrderId = $data['purchase_order_id'] ?? null;
        if ($purchaseOrderId === null || (int) $purchaseOrderId <= 0) {
            return true;
        }

        $companyId = (int) $data['company_id'];
        $po = $this->db->table('purchase_orders')
            ->where('id', (int) $purchaseOrderId)
            ->where('company_id', $companyId)
            ->whereIn('status', ['confirmed', 'partial_received', 'received'])
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getFirstRow('array');

        if ($po === null) {
            return false;
        }

        return (int) $po['supplier_id'] === (int) $data['supplier_id']
            && (int) $po['currency_id'] === (int) $data['currency_id']
            && (float) $data['total_amount'] <= (float) $po['total_amount'] + 0.00001;
    }

    /** @param array<string, mixed> $data */
    public function validSalesInvoiceSource(array $data): bool
    {
        $salesOrderId = $data['sales_order_id'] ?? null;
        if ($salesOrderId === null || (int) $salesOrderId <= 0) {
            return true;
        }

        $companyId = (int) $data['company_id'];
        $so = $this->db->table('sales_orders')
            ->where('id', (int) $salesOrderId)
            ->where('company_id', $companyId)
            ->whereIn('status', ['confirmed', 'partial_delivered', 'delivered'])
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getFirstRow('array');

        if ($so === null) {
            return false;
        }

        return (int) $so['customer_id'] === (int) $data['customer_id']
            && (int) $so['currency_id'] === (int) $data['currency_id']
            && (float) $data['total_amount'] <= (float) $so['total_amount'] + 0.00001;
    }
}
