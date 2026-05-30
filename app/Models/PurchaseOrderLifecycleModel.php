<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\AuditTrailService;
use CodeIgniter\Model;
use RuntimeException;

final class PurchaseOrderLifecycleModel extends Model
{
    public function confirm(int $purchaseOrderId, int $companyId, int $actorId): bool
    {
        $order = $this->db->table('purchase_orders')
            ->where('id', $purchaseOrderId)
            ->where('company_id', $companyId)
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getFirstRow('array');

        if ($order === null || (string) $order['status'] !== 'draft') {
            return false;
        }

        $lineCount = $this->db->table('purchase_order_items')
            ->where('purchase_order_id', $purchaseOrderId)
            ->where('company_id', $companyId)
            ->where('deleted_at IS NULL', null, false)
            ->countAllResults();

        if ($lineCount <= 0) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        $this->db->table('purchase_orders')
            ->where('id', $purchaseOrderId)
            ->where('company_id', $companyId)
            ->update([
                'status'     => 'confirmed',
                'updated_by' => $actorId,
                'updated_at' => $now,
            ]);

        (new AuditTrailService($this->db))->record('PURCHASE_ORDER_CONFIRMED', 'purchase_order', $purchaseOrderId, $companyId, (int) $order['branch_id'], $actorId, [
            'po_no'        => (string) $order['po_no'],
            'old_status'   => 'draft',
            'new_status'   => 'confirmed',
            'line_count'   => $lineCount,
            'total_amount' => (float) $order['total_amount'],
        ]);

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Konfirmasi Purchase Order gagal dan transaksi dibatalkan.');
        }

        return true;
    }
}
