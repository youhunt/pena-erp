<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\AuditTrailService;
use CodeIgniter\Model;
use RuntimeException;

final class PosWriteModel extends Model
{
    protected $table = 'pos_registers';

    /** @param array<string, mixed> $data */
    public function createRegister(array $data, int $actorId): bool
    {
        if (! $this->referencesAreActive($data)) {
            return false;
        }

        $this->db->transStart();
        $this->db->table($this->table)->insert($data + ['created_by' => $actorId, 'created_at' => date('Y-m-d H:i:s')]);
        $id = (int) $this->db->insertID();
        $this->audit()->record('POS_REGISTER_CREATED', 'pos_register', $id, (int) $data['company_id'], (int) $data['branch_id'], $actorId, $data);
        $this->complete();

        return true;
    }

    /** @param array<string, mixed> $data */
    public function updateRegister(int $companyId, int $id, array $data, int $actorId): bool
    {
        $before = $this->row($companyId, $id);

        if ($before === null || ! $this->referencesAreActive(['company_id' => $companyId] + $data)) {
            return false;
        }

        $changes = $data + ['updated_by' => $actorId, 'updated_at' => date('Y-m-d H:i:s')];
        $this->db->transStart();
        $this->db->table($this->table)->where(['id' => $id, 'company_id' => $companyId])->update($changes);
        $this->audit()->record('POS_REGISTER_UPDATED', 'pos_register', $id, $companyId, (int) $data['branch_id'], $actorId, $changes, $before);
        $this->complete();

        return true;
    }

    public function updateStatus(int $companyId, int $id, string $status, int $actorId): bool
    {
        $before = $this->row($companyId, $id);

        if ($before === null || ! in_array($status, ['active', 'inactive'], true)) {
            return false;
        }

        $changes = ['status' => $status, 'updated_by' => $actorId, 'updated_at' => date('Y-m-d H:i:s')];
        $this->db->transStart();
        $this->db->table($this->table)->where(['id' => $id, 'company_id' => $companyId])->update($changes);
        $this->audit()->record('POS_REGISTER_STATUS_UPDATED', 'pos_register', $id, $companyId, (int) $before['branch_id'], $actorId, $changes, $before);
        $this->complete();

        return true;
    }

    /** @param array<string, mixed> $data */
    public function createPaymentMethod(array $data, int $actorId): bool
    {
        if (! $this->paymentReferencesAreActive($data)) {
            return false;
        }

        $this->db->transStart();
        if ((bool) $data['is_default']) {
            $this->db->table('pos_payment_methods')->where([
                'company_id' => (int) $data['company_id'], 'register_id' => (int) $data['register_id'],
            ])->update(['is_default' => false, 'updated_by' => $actorId, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        $this->db->table('pos_payment_methods')->insert($data + ['created_by' => $actorId, 'created_at' => date('Y-m-d H:i:s')]);
        $id = (int) $this->db->insertID();
        $this->audit()->record('POS_PAYMENT_METHOD_CREATED', 'pos_payment_method', $id, (int) $data['company_id'], $this->branchForRegister((int) $data['register_id']), $actorId, $data);
        $this->complete();

        return true;
    }

    /** @param array<string, mixed> $data */
    public function updatePaymentMethod(int $companyId, int $id, array $data, int $actorId): bool
    {
        $before = $this->paymentRow($companyId, $id);

        if ($before === null || ! $this->paymentReferencesAreActive(['company_id' => $companyId] + $data)) {
            return false;
        }

        $changes = $data + ['updated_by' => $actorId, 'updated_at' => date('Y-m-d H:i:s')];
        $this->db->transStart();
        if ((bool) $data['is_default']) {
            $this->db->table('pos_payment_methods')->where([
                'company_id' => $companyId, 'register_id' => (int) $data['register_id'],
            ])->where('id !=', $id)->update(['is_default' => false, 'updated_by' => $actorId, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        $this->db->table('pos_payment_methods')->where(['id' => $id, 'company_id' => $companyId])->update($changes);
        $this->audit()->record('POS_PAYMENT_METHOD_UPDATED', 'pos_payment_method', $id, $companyId, $this->branchForRegister((int) $data['register_id']), $actorId, $changes, $before);
        $this->complete();

        return true;
    }

    public function updatePaymentStatus(int $companyId, int $id, string $status, int $actorId): bool
    {
        $before = $this->paymentRow($companyId, $id);

        if ($before === null || ! in_array($status, ['active', 'inactive'], true)) {
            return false;
        }

        $changes = ['status' => $status, 'updated_by' => $actorId, 'updated_at' => date('Y-m-d H:i:s')];
        $this->db->transStart();
        $this->db->table('pos_payment_methods')->where(['id' => $id, 'company_id' => $companyId])->update($changes);
        $this->audit()->record('POS_PAYMENT_METHOD_STATUS_UPDATED', 'pos_payment_method', $id, $companyId, $this->branchForRegister((int) $before['register_id']), $actorId, $changes, $before);
        $this->complete();

        return true;
    }

    /** @param array<string, mixed> $data */
    public function openShift(array $data, int $actorId): bool
    {
        $register = $this->row((int) $data['company_id'], (int) $data['register_id']);

        if ($register === null || $register['status'] !== 'active'
            || ! $this->cashierHasRegisterAccess((int) $data['company_id'], (int) $register['branch_id'], (int) $data['cashier_user_id'])
            || $this->hasOpenShift((int) $data['company_id'], (int) $data['register_id'], (int) $data['cashier_user_id'])) {
            return false;
        }

        $this->db->transStart();
        $this->db->table('pos_shifts')->insert($data + ['status' => 'open', 'created_by' => $actorId, 'created_at' => date('Y-m-d H:i:s')]);
        $id = (int) $this->db->insertID();
        $this->audit()->record('POS_SHIFT_OPENED', 'pos_shift', $id, (int) $data['company_id'], (int) $register['branch_id'], $actorId, $data);
        $this->complete();

        return true;
    }

    public function closeShift(int $companyId, int $id, string $closingCash, int $actorId): bool
    {
        $before = $this->shiftRow($companyId, $id);

        if ($before === null || $before['status'] !== 'open' || (int) $before['cashier_user_id'] !== $actorId) {
            return false;
        }

        $changes = [
            'closing_cash' => $closingCash,
            'closed_at'    => date('Y-m-d H:i:s'),
            'status'       => 'closed',
            'updated_by'   => $actorId,
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
        $this->db->transStart();
        $this->db->table('pos_shifts')->where(['id' => $id, 'company_id' => $companyId])->update($changes);
        $this->audit()->record('POS_SHIFT_CLOSED', 'pos_shift', $id, $companyId, $this->branchForRegister((int) $before['register_id']), $actorId, $changes, $before);
        $this->complete();

        return true;
    }

    /** @param array<string, mixed> $data */
    public function createSale(array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];
        $shift = $this->shiftRow($companyId, (int) $data['shift_id']);

        if ($shift === null || $shift['status'] !== 'open' || (int) $shift['cashier_user_id'] !== $actorId) {
            return false;
        }

        $register = $this->row($companyId, (int) $shift['register_id']);
        $payment = $this->paymentMethodForSale($companyId, (int) $data['payment_method_id'], (int) $shift['register_id']);
        $product = $this->productForSale($companyId, (int) $data['product_id']);

        if ($register === null || $register['status'] !== 'active' || $payment === null || $product === null) {
            return false;
        }

        $customerId = (int) ($data['customer_id'] ?: ($register['default_customer_id'] ?? 0));
        if ($customerId > 0 && ! $this->active('customers', $customerId, $companyId)) {
            return false;
        }

        $qty = (float) $data['qty'];
        $unitPrice = (float) $data['unit_price'];
        $paidAmount = (float) $data['payment_amount'];

        if ($qty <= 0 || $unitPrice < 0 || $paidAmount < 0) {
            return false;
        }

        $tax = $this->taxForProduct($companyId, (int) $product['id']);
        $taxRate = $tax === null ? 0.0 : (float) $tax['rate'];
        $subtotal = round($qty * $unitPrice, 4);
        $taxAmount = round($subtotal * $taxRate, 4);
        $totalAmount = round($subtotal + $taxAmount, 4);

        if ($paidAmount < $totalAmount) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();
        $receiptNo = $this->nextReceiptNo($companyId, (int) $register['transaction_code_id'], $actorId);
        $sale = [
            'company_id'     => $companyId,
            'shift_id'       => (int) $shift['id'],
            'register_id'    => (int) $register['id'],
            'customer_id'    => $customerId > 0 ? $customerId : null,
            'currency_id'    => (int) $register['currency_id'],
            'receipt_no'     => $receiptNo,
            'sold_at'        => $now,
            'subtotal'       => $this->money($subtotal),
            'tax_amount'     => $this->money($taxAmount),
            'total_amount'   => $this->money($totalAmount),
            'paid_amount'    => $this->money($paidAmount),
            'change_amount'  => $this->money($paidAmount - $totalAmount),
            'status'         => 'paid',
            'created_by'     => $actorId,
            'created_at'     => $now,
        ];
        $this->db->table('pos_sales')->insert($sale);
        $saleId = (int) $this->db->insertID();

        $this->db->table('pos_sale_items')->insert([
            'company_id'   => $companyId,
            'pos_sale_id'  => $saleId,
            'product_id'   => (int) $product['id'],
            'uom_id'       => (int) $product['base_uom_id'],
            'tax_code_id'  => $tax === null ? null : (int) $tax['id'],
            'qty'          => $this->money($qty),
            'unit_price'   => $this->money($unitPrice),
            'tax_rate'     => number_format($taxRate, 6, '.', ''),
            'tax_amount'   => $this->money($taxAmount),
            'line_total'   => $this->money($subtotal),
            'created_by'   => $actorId,
            'created_at'   => $now,
        ]);
        $this->db->table('pos_sale_payments')->insert([
            'company_id'         => $companyId,
            'pos_sale_id'        => $saleId,
            'payment_method_id'  => (int) $payment['id'],
            'amount'             => $this->money($paidAmount),
            'created_by'         => $actorId,
            'created_at'         => $now,
        ]);
        $this->audit()->record('POS_SALE_PAID', 'pos_sale', $saleId, $companyId, (int) $register['branch_id'], $actorId, $sale);
        $this->complete();

        return true;
    }

    /** @param array<string, mixed> $data */
    private function referencesAreActive(array $data): bool
    {
        $companyId = (int) $data['company_id'];
        $branchId = (int) $data['branch_id'];
        $departmentId = (int) $data['department_id'];
        $warehouseId = (int) $data['warehouse_id'];

        if (! $this->active('branches', (int) $data['branch_id'], $companyId)
            || ! $this->active('currencies', (int) $data['currency_id'], $companyId)
            || (($data['default_customer_id'] ?? null) !== null && ! $this->active('customers', (int) $data['default_customer_id'], $companyId))) {
            return false;
        }

        if ($this->db->table('departments')->where([
            'id' => $departmentId, 'company_id' => $companyId, 'branch_id' => $branchId, 'status' => 'active',
        ])->where('deleted_at', null)->countAllResults() !== 1) {
            return false;
        }

        if ($this->db->table('warehouses')->where([
            'id' => $warehouseId, 'company_id' => $companyId, 'branch_id' => $branchId,
            'department_id' => $departmentId, 'is_active' => true,
        ])->where('deleted_at', null)->countAllResults() !== 1) {
            return false;
        }

        return $this->db->table('transaction_codes')->where([
            'id' => $data['transaction_code_id'], 'company_id' => $companyId, 'status' => 'active',
        ])->groupStart()->where('branch_id', $branchId)->orWhere('branch_id', null)->groupEnd()
            ->whereIn('module', ['pos', 'sales'])->where('deleted_at', null)->countAllResults() === 1;
    }

    private function active(string $table, int $id, int $companyId): bool
    {
        return $this->db->table($table)->where(['id' => $id, 'company_id' => $companyId, 'status' => 'active'])
            ->where('deleted_at', null)->countAllResults() === 1;
    }

    /** @param array<string, mixed> $data */
    private function paymentReferencesAreActive(array $data): bool
    {
        $companyId = (int) $data['company_id'];
        $register = $this->row($companyId, (int) $data['register_id']);

        if ($register === null || $register['status'] !== 'active') {
            return false;
        }

        return $this->db->table('cash_bank_accounts')
            ->where(['id' => (int) $data['cash_bank_account_id'], 'company_id' => $companyId, 'status' => 'active'])
            ->groupStart()->where('branch_id', (int) $register['branch_id'])->orWhere('branch_id', null)->groupEnd()
            ->where('deleted_at', null)->countAllResults() === 1;
    }

    private function cashierHasRegisterAccess(int $companyId, int $branchId, int $cashierUserId): bool
    {
        return $this->db->table('user_company_memberships cm')
            ->join('users u', 'u.id = cm.user_id AND u.active = 1')
            ->join('user_branch_memberships bm', 'bm.company_id = cm.company_id AND bm.user_id = cm.user_id')
            ->where(['cm.company_id' => $companyId, 'cm.user_id' => $cashierUserId, 'cm.status' => 'active'])
            ->where(['bm.branch_id' => $branchId, 'bm.status' => 'active'])
            ->countAllResults() === 1;
    }

    private function hasOpenShift(int $companyId, int $registerId, int $cashierUserId): bool
    {
        return $this->db->table('pos_shifts')
            ->where(['company_id' => $companyId, 'status' => 'open'])
            ->groupStart()->where('register_id', $registerId)->orWhere('cashier_user_id', $cashierUserId)->groupEnd()
            ->where('deleted_at', null)->countAllResults() > 0;
    }

    /** @return array<string, mixed>|null */
    private function paymentMethodForSale(int $companyId, int $id, int $registerId): ?array
    {
        return $this->db->table('pos_payment_methods')
            ->where([
                'company_id'  => $companyId,
                'id'          => $id,
                'register_id' => $registerId,
                'status'      => 'active',
            ])
            ->where('deleted_at', null)
            ->get()->getFirstRow('array');
    }

    /** @return array<string, mixed>|null */
    private function productForSale(int $companyId, int $id): ?array
    {
        return $this->db->table('products')
            ->where(['company_id' => $companyId, 'id' => $id, 'status' => 'active'])
            ->where('deleted_at', null)
            ->get()->getFirstRow('array');
    }

    /** @return array<string, mixed>|null */
    private function taxForProduct(int $companyId, int $productId): ?array
    {
        return $this->db->table('product_tax_codes pt')
            ->select('tc.id, tc.rate')
            ->join('tax_codes tc', 'tc.id = pt.tax_code_id AND tc.company_id = pt.company_id')
            ->where(['pt.company_id' => $companyId, 'pt.product_id' => $productId, 'pt.status' => 'active', 'tc.status' => 'active'])
            ->whereIn('pt.usage_type', ['sales', 'both'])
            ->where('pt.deleted_at', null)
            ->where('tc.deleted_at', null)
            ->orderBy('pt.usage_type', 'DESC')
            ->get()->getFirstRow('array');
    }

    private function nextReceiptNo(int $companyId, int $transactionCodeId, int $actorId): string
    {
        $row = $this->db->table('transaction_codes')
            ->where(['company_id' => $companyId, 'id' => $transactionCodeId, 'status' => 'active'])
            ->where('deleted_at', null)
            ->get()->getFirstRow('array');

        if ($row === null) {
            throw new RuntimeException('Transaction Code POS tidak valid.');
        }

        $number = (int) $row['next_number'];
        $receiptNo = (string) $row['prefix'] . str_pad((string) $number, (int) $row['number_length'], '0', STR_PAD_LEFT);
        $this->db->table('transaction_codes')
            ->where(['company_id' => $companyId, 'id' => $transactionCodeId])
            ->update(['next_number' => $number + 1, 'updated_by' => $actorId, 'updated_at' => date('Y-m-d H:i:s')]);

        return $receiptNo;
    }

    private function money(float $value): string
    {
        return number_format($value, 4, '.', '');
    }

    /** @return array<string, mixed>|null */
    private function row(int $companyId, int $id): ?array
    {
        return $this->db->table($this->table)->where(['company_id' => $companyId, 'id' => $id])
            ->where('deleted_at', null)->get()->getFirstRow('array');
    }

    /** @return array<string, mixed>|null */
    private function paymentRow(int $companyId, int $id): ?array
    {
        return $this->db->table('pos_payment_methods')->where(['company_id' => $companyId, 'id' => $id])
            ->where('deleted_at', null)->get()->getFirstRow('array');
    }

    /** @return array<string, mixed>|null */
    private function shiftRow(int $companyId, int $id): ?array
    {
        return $this->db->table('pos_shifts')->where(['company_id' => $companyId, 'id' => $id])
            ->where('deleted_at', null)->get()->getFirstRow('array');
    }

    private function branchForRegister(int $registerId): ?int
    {
        $register = $this->db->table('pos_registers')->select('branch_id')->where('id', $registerId)->get()->getFirstRow('array');

        return $register === null ? null : (int) $register['branch_id'];
    }

    private function audit(): AuditTrailService
    {
        return new AuditTrailService($this->db);
    }

    private function complete(): void
    {
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Perubahan POS Master gagal dan transaksi dibatalkan.');
        }
    }
}
