<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class PosReadModel extends Model
{
    protected $table = 'pos_registers';

    /** @return list<array<string, mixed>> */
    public function registers(int $companyId): array
    {
        return $this->db->table('pos_registers r')
            ->select('r.*, b.code AS branch_code, d.code AS department_code, w.code AS warehouse_code, c.code AS customer_code, cur.code AS currency_code, t.code AS transaction_code')
            ->join('branches b', 'b.id = r.branch_id AND b.company_id = r.company_id')
            ->join('departments d', 'd.id = r.department_id AND d.company_id = r.company_id')
            ->join('warehouses w', 'w.id = r.warehouse_id AND w.company_id = r.company_id')
            ->join('customers c', 'c.id = r.default_customer_id AND c.company_id = r.company_id', 'left')
            ->join('currencies cur', 'cur.id = r.currency_id AND cur.company_id = r.company_id')
            ->join('transaction_codes t', 't.id = r.transaction_code_id AND t.company_id = r.company_id')
            ->where('r.company_id', $companyId)
            ->where('r.deleted_at', null)
            ->orderBy('r.code', 'ASC')
            ->get()
            ->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function paymentMethods(int $companyId): array
    {
        return $this->db->table('pos_payment_methods p')
            ->select('p.*, r.code AS register_code, b.code AS cash_bank_code, b.name AS cash_bank_name')
            ->join('pos_registers r', 'r.id = p.register_id AND r.company_id = p.company_id')
            ->join('cash_bank_accounts b', 'b.id = p.cash_bank_account_id AND b.company_id = p.company_id')
            ->where('p.company_id', $companyId)->where('p.deleted_at', null)
            ->orderBy('r.code', 'ASC')->orderBy('p.sort_order', 'ASC')->orderBy('p.code', 'ASC')
            ->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function shifts(int $companyId): array
    {
        return $this->db->table('pos_shifts s')
            ->select('s.*, r.code AS register_code, u.username AS cashier_username, i.secret AS cashier_email')
            ->join('pos_registers r', 'r.id = s.register_id AND r.company_id = s.company_id')
            ->join('users u', 'u.id = s.cashier_user_id')
            ->join('auth_identities i', "i.user_id = u.id AND i.type = 'email_password'", 'left')
            ->where('s.company_id', $companyId)->where('s.deleted_at', null)
            ->orderBy('s.opened_at', 'DESC')->orderBy('s.id', 'DESC')
            ->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function branches(int $companyId): array
    {
        return $this->options('branches', $companyId, 'code, name');
    }

    /** @return list<array<string, mixed>> */
    public function departments(int $companyId): array
    {
        return $this->db->table('departments')
            ->select('id, branch_id, code, name')
            ->where(['company_id' => $companyId, 'status' => 'active'])
            ->where('deleted_at', null)
            ->orderBy('code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function warehouses(int $companyId): array
    {
        return $this->db->table('warehouses')
            ->select('id, branch_id, department_id, code, name')
            ->where(['company_id' => $companyId, 'is_active' => true])
            ->where('deleted_at', null)
            ->orderBy('code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function customers(int $companyId): array
    {
        return $this->options('customers', $companyId, 'code, name');
    }

    /** @return list<array<string, mixed>> */
    public function currencies(int $companyId): array
    {
        return $this->options('currencies', $companyId, 'code, name');
    }

    /** @return list<array<string, mixed>> */
    public function transactionCodes(int $companyId): array
    {
        return $this->db->table('transaction_codes')
            ->select('id, branch_id, code, prefix')
            ->where(['company_id' => $companyId, 'status' => 'active'])
            ->whereIn('module', ['pos', 'sales'])
            ->where('deleted_at', null)
            ->orderBy('code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function cashBankAccounts(int $companyId): array
    {
        return $this->db->table('cash_bank_accounts b')
            ->select('b.id, b.branch_id, b.code, b.name, b.account_type, c.code AS currency_code')
            ->join('currencies c', 'c.id = b.currency_id AND c.company_id = b.company_id')
            ->where(['b.company_id' => $companyId, 'b.status' => 'active'])
            ->where('b.deleted_at', null)
            ->orderBy('b.code', 'ASC')->get()->getResultArray();
    }

    public function codeExists(int $companyId, string $code): bool
    {
        return $this->db->table('pos_registers')
            ->where(['company_id' => $companyId, 'code' => $code])
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }

    public function paymentCodeExists(int $companyId, int $registerId, string $code): bool
    {
        return $this->db->table('pos_payment_methods')
            ->where(['company_id' => $companyId, 'register_id' => $registerId, 'code' => $code])
            ->where('deleted_at', null)->countAllResults() > 0;
    }

    /** @return list<array<string, mixed>> */
    private function options(string $table, int $companyId, string $select): array
    {
        return $this->db->table($table)
            ->select('id, ' . $select)
            ->where(['company_id' => $companyId, 'status' => 'active'])
            ->where('deleted_at', null)
            ->orderBy('code', 'ASC')->get()->getResultArray();
    }
}
