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
