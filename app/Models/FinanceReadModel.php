<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class FinanceReadModel extends Model
{
    protected $table = 'chart_of_accounts';

    /** @return list<array<string, mixed>> */
    public function accounts(int $companyId): array
    {
        return $this->db->table('chart_of_accounts a')
            ->select('a.*, p.account_code AS parent_code')
            ->join('chart_of_accounts p', 'p.id = a.parent_id AND p.company_id = a.company_id', 'left')
            ->where('a.company_id', $companyId)->where('a.deleted_at', null)
            ->orderBy('a.account_code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function cashBankAccounts(int $companyId): array
    {
        return $this->db->table('cash_bank_accounts b')
            ->select('b.*, a.account_code, c.code AS currency_code, s.code AS branch_code')
            ->join('chart_of_accounts a', 'a.id = b.account_id AND a.company_id = b.company_id')
            ->join('currencies c', 'c.id = b.currency_id AND c.company_id = b.company_id')
            ->join('branches s', 's.id = b.branch_id AND s.company_id = b.company_id', 'left')
            ->where('b.company_id', $companyId)->where('b.deleted_at', null)
            ->orderBy('b.code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function exchangeRates(int $companyId): array
    {
        return $this->db->table('exchange_rates r')
            ->select('r.*, c.code AS currency_code')
            ->join('currencies c', 'c.id = r.currency_id AND c.company_id = r.company_id')
            ->where('r.company_id', $companyId)->where('r.deleted_at', null)
            ->orderBy('r.rate_date', 'DESC')->orderBy('c.code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function postableAccounts(int $companyId): array
    {
        return $this->db->table('chart_of_accounts')->select('id, account_code, account_name')
            ->where(['company_id' => $companyId, 'status' => 'active', 'is_postable' => true])
            ->where('deleted_at', null)->orderBy('account_code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function currencies(int $companyId): array
    {
        return $this->activeOptions('currencies', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function branches(int $companyId): array
    {
        return $this->activeOptions('branches', $companyId);
    }

    public function codeExists(string $table, int $companyId, string $code): bool
    {
        return $this->db->table($table)->where(['company_id' => $companyId, $table === 'chart_of_accounts' ? 'account_code' : 'code' => $code])
            ->where('deleted_at', null)->countAllResults() > 0;
    }

    public function exchangeRateExists(int $companyId, int $currencyId, string $rateDate, string $rateType, ?int $exceptId = null): bool
    {
        $builder = $this->db->table('exchange_rates')
            ->where(['company_id' => $companyId, 'currency_id' => $currencyId, 'rate_date' => $rateDate, 'rate_type' => $rateType])
            ->where('deleted_at', null);

        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }

        return $builder->countAllResults() > 0;
    }

    /** @return list<array<string, mixed>> */
    private function activeOptions(string $table, int $companyId): array
    {
        return $this->db->table($table)->select('id, code, name')
            ->where(['company_id' => $companyId, 'status' => 'active'])->where('deleted_at', null)
            ->orderBy('code', 'ASC')->get()->getResultArray();
    }
}
