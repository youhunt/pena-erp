<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\AuditTrailService;
use CodeIgniter\Model;
use RuntimeException;

final class FinanceWriteModel extends Model
{
    protected $table = 'chart_of_accounts';

    /** @param array<string, mixed> $data */
    public function createAccount(array $data, int $actorId): bool
    {
        if (($data['parent_id'] ?? null) !== null && ! $this->active('chart_of_accounts', (int) $data['parent_id'], (int) $data['company_id'])) {
            return false;
        }

        $this->create('chart_of_accounts', 'CHART_ACCOUNT_CREATED', 'chart_account', $data, $actorId);

        return true;
    }

    /** @param array<string, mixed> $data */
    public function updateAccount(int $companyId, int $id, array $data, int $actorId): bool
    {
        if (($data['parent_id'] ?? null) === $id
            || (($data['parent_id'] ?? null) !== null && ! $this->active('chart_of_accounts', (int) $data['parent_id'], $companyId))) {
            return false;
        }

        return $this->updateRecord('chart_of_accounts', 'CHART_ACCOUNT_UPDATED', 'chart_account', $companyId, $id, $data, $actorId);
    }

    /** @param array<string, mixed> $data */
    public function createCashBankAccount(array $data, int $actorId): bool
    {
        if (! $this->cashBankReferencesValid($data)) {
            return false;
        }

        $this->create('cash_bank_accounts', 'CASH_BANK_ACCOUNT_CREATED', 'cash_bank_account', $data, $actorId);

        return true;
    }

    /** @param array<string, mixed> $data */
    public function updateCashBankAccount(int $companyId, int $id, array $data, int $actorId): bool
    {
        if (! $this->cashBankReferencesValid(['company_id' => $companyId] + $data)) {
            return false;
        }

        return $this->updateRecord('cash_bank_accounts', 'CASH_BANK_ACCOUNT_UPDATED', 'cash_bank_account', $companyId, $id, $data, $actorId);
    }

    /** @param array<string, mixed> $data */
    public function createExchangeRate(array $data, int $actorId): bool
    {
        if (! $this->active('currencies', (int) $data['currency_id'], (int) $data['company_id'])) {
            return false;
        }

        $this->create('exchange_rates', 'EXCHANGE_RATE_CREATED', 'exchange_rate', $data, $actorId);

        return true;
    }

    /** @param array<string, mixed> $data */
    public function updateExchangeRate(int $companyId, int $id, array $data, int $actorId): bool
    {
        if (! $this->active('currencies', (int) $data['currency_id'], $companyId)) {
            return false;
        }

        return $this->updateRecord('exchange_rates', 'EXCHANGE_RATE_UPDATED', 'exchange_rate', $companyId, $id, $data, $actorId);
    }

    public function updateStatus(string $master, int $companyId, int $id, string $status, int $actorId): bool
    {
        $map = [
            'account'       => ['chart_of_accounts', 'chart_account'],
            'cash-bank'     => ['cash_bank_accounts', 'cash_bank_account'],
            'exchange-rate' => ['exchange_rates', 'exchange_rate'],
        ];

        if (! isset($map[$master]) || ! in_array($status, ['active', 'inactive'], true)) {
            return false;
        }

        [$table, $entity] = $map[$master];
        return $this->updateRecord($table, strtoupper($entity) . '_STATUS_UPDATED', $entity, $companyId, $id, ['status' => $status], $actorId);
    }

    /** @param array<string, mixed> $data */
    private function cashBankReferencesValid(array $data): bool
    {
        $companyId = (int) $data['company_id'];
        $account = $this->db->table('chart_of_accounts')->where([
            'id' => (int) $data['account_id'], 'company_id' => $companyId, 'status' => 'active', 'is_postable' => true,
        ])->where('deleted_at', null)->countAllResults() === 1;

        return $account
            && $this->active('currencies', (int) $data['currency_id'], $companyId)
            && (($data['branch_id'] ?? null) === null || $this->active('branches', (int) $data['branch_id'], $companyId));
    }

    private function active(string $table, int $id, int $companyId): bool
    {
        return $this->db->table($table)->where(['id' => $id, 'company_id' => $companyId, 'status' => 'active'])
            ->where('deleted_at', null)->countAllResults() === 1;
    }

    /** @param array<string, mixed> $data */
    private function create(string $table, string $event, string $entity, array $data, int $actorId): void
    {
        $this->db->transStart();
        $this->db->table($table)->insert($data + ['created_by' => $actorId, 'created_at' => date('Y-m-d H:i:s')]);
        $id = (int) $this->db->insertID();
        (new AuditTrailService($this->db))->record($event, $entity, $id, (int) $data['company_id'], $data['branch_id'] ?? null, $actorId, $data);
        $this->complete();
    }

    /** @param array<string, mixed> $data */
    private function updateRecord(string $table, string $event, string $entity, int $companyId, int $id, array $data, int $actorId): bool
    {
        $before = $this->db->table($table)->where(['id' => $id, 'company_id' => $companyId])
            ->where('deleted_at', null)->get()->getFirstRow('array');

        if ($before === null) {
            return false;
        }

        $changes = $data + ['updated_by' => $actorId, 'updated_at' => date('Y-m-d H:i:s')];
        $this->db->transStart();
        $this->db->table($table)->where(['id' => $id, 'company_id' => $companyId])->update($changes);
        (new AuditTrailService($this->db))->record($event, $entity, $id, $companyId, $data['branch_id'] ?? ($before['branch_id'] ?? null), $actorId, $changes, $before);
        $this->complete();

        return true;
    }

    private function complete(): void
    {
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Perubahan Finance Master gagal dan transaksi dibatalkan.');
        }
    }
}
