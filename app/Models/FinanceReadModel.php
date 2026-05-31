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
    public function glBooks(int $companyId): array
    {
        return $this->db->table('gl_books b')
            ->select('b.*, c.code AS currency_code, a.account_code AS retained_earnings_code')
            ->join('currencies c', 'c.id = b.currency_id AND c.company_id = b.company_id')
            ->join('chart_of_accounts a', 'a.id = b.retained_earnings_account_id AND a.company_id = b.company_id', 'left')
            ->where('b.company_id', $companyId)->where('b.deleted_at', null)
            ->orderBy('b.code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function glColumns(int $companyId): array
    {
        return $this->db->table('gl_columns')
            ->where('company_id', $companyId)->where('deleted_at', null)
            ->orderBy('sequence_no', 'ASC')->orderBy('code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function costTypes(int $companyId): array
    {
        return $this->db->table('cost_types')
            ->where('company_id', $companyId)->where('deleted_at', null)
            ->orderBy('code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function itemCosts(int $companyId): array
    {
        return $this->db->table('item_costs i')
            ->select('i.*, p.sku, p.name AS product_name, t.code AS cost_type_code, c.code AS currency_code')
            ->join('products p', 'p.id = i.product_id AND p.company_id = i.company_id')
            ->join('cost_types t', 't.id = i.cost_type_id AND t.company_id = i.company_id')
            ->join('currencies c', 'c.id = i.currency_id AND c.company_id = i.company_id')
            ->where('i.company_id', $companyId)->where('i.deleted_at', null)
            ->orderBy('p.sku', 'ASC')->orderBy('i.effective_from', 'DESC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function fiscalPeriods(int $companyId): array
    {
        return $this->db->table('fiscal_periods')
            ->where('company_id', $companyId)->where('deleted_at', null)
            ->orderBy('year', 'DESC')->orderBy('period', 'DESC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function modulePeriodCloses(int $companyId): array
    {
        return $this->db->table('module_period_closes c')
            ->select('c.*, p.year, p.period, p.starts_on, p.ends_on, p.status AS fiscal_status')
            ->join('fiscal_periods p', 'p.id = c.fiscal_period_id AND p.company_id = c.company_id')
            ->where('c.company_id', $companyId)->where('c.deleted_at', null)
            ->orderBy('p.year', 'DESC')->orderBy('p.period', 'DESC')->orderBy('c.module_code', 'ASC')
            ->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function journalEntries(int $companyId): array
    {
        return $this->db->table('journal_entries j')
            ->select('j.*, b.code AS gl_book_code, SUM(l.debit) AS total_debit, SUM(l.credit) AS total_credit')
            ->join('gl_books b', 'b.id = j.gl_book_id AND b.company_id = j.company_id')
            ->join('journal_entry_lines l', 'l.journal_entry_id = j.id AND l.company_id = j.company_id')
            ->where('j.company_id', $companyId)->where('j.deleted_at', null)
            ->groupBy('j.id, b.code')
            ->orderBy('j.journal_date', 'DESC')->orderBy('j.id', 'DESC')
            ->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function purchaseInvoices(int $companyId): array
    {
        $rows = $this->db->table('purchase_invoices i')
            ->select("i.*, s.code AS supplier_code, s.name AS supplier_name, COALESCE(SUM(a.allocated_amount), 0) AS paid_amount, (i.total_amount - COALESCE(SUM(a.allocated_amount), 0)) AS outstanding_amount", false)
            ->join('suppliers s', 's.id = i.supplier_id AND s.company_id = i.company_id', 'left')
            ->join('payment_allocations a', "a.company_id = i.company_id AND a.document_type = 'purchase_invoice' AND a.document_id = i.id AND a.deleted_at IS NULL", 'left')
            ->where('i.company_id', $companyId)->where('i.deleted_at', null)
            ->groupBy('i.id, s.code, s.name')
            ->orderBy('i.invoice_date', 'DESC')->orderBy('i.id', 'DESC')
            ->get()->getResultArray();

        return $this->appendPaymentStatus($rows);
    }

    /** @return list<array<string, mixed>> */
    public function salesInvoices(int $companyId): array
    {
        $rows = $this->db->table('sales_invoices i')
            ->select("i.*, c.code AS customer_code, c.name AS customer_name, COALESCE(SUM(a.allocated_amount), 0) AS paid_amount, (i.total_amount - COALESCE(SUM(a.allocated_amount), 0)) AS outstanding_amount", false)
            ->join('customers c', 'c.id = i.customer_id AND c.company_id = i.company_id', 'left')
            ->join('payment_allocations a', "a.company_id = i.company_id AND a.document_type = 'sales_invoice' AND a.document_id = i.id AND a.deleted_at IS NULL", 'left')
            ->where('i.company_id', $companyId)->where('i.deleted_at', null)
            ->groupBy('i.id, c.code, c.name')
            ->orderBy('i.invoice_date', 'DESC')->orderBy('i.id', 'DESC')
            ->get()->getResultArray();

        return $this->appendPaymentStatus($rows);
    }

    /** @return list<array<string, mixed>> */
    public function payments(int $companyId): array
    {
        return $this->db->table('payments p')
            ->select('p.*, s.code AS supplier_code, s.name AS supplier_name, c.code AS customer_code, c.name AS customer_name')
            ->join('suppliers s', 's.id = p.supplier_id AND s.company_id = p.company_id', 'left')
            ->join('customers c', 'c.id = p.customer_id AND c.company_id = p.company_id', 'left')
            ->where('p.company_id', $companyId)->where('p.deleted_at', null)
            ->orderBy('p.payment_date', 'DESC')->orderBy('p.id', 'DESC')
            ->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function paymentAllocations(int $companyId, int $paymentId): array
    {
        return $this->db->table('payment_allocations a')
            ->select("a.*, p.payment_no, p.payment_type, COALESCE(si.invoice_no, pi.invoice_no) AS document_no", false)
            ->join('payments p', 'p.id = a.payment_id AND p.company_id = a.company_id', 'left')
            ->join('sales_invoices si', "si.id = a.document_id AND a.document_type = 'sales_invoice' AND si.company_id = a.company_id", 'left')
            ->join('purchase_invoices pi', "pi.id = a.document_id AND a.document_type = 'purchase_invoice' AND pi.company_id = a.company_id", 'left')
            ->where('a.company_id', $companyId)->where('a.payment_id', $paymentId)->where('a.deleted_at', null)
            ->orderBy('a.id', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function paymentAllocationCandidates(int $companyId, int $paymentId): array
    {
        $payment = $this->db->table('payments')->where(['company_id' => $companyId, 'id' => $paymentId, 'deleted_at' => null])->get()->getFirstRow();
        if (! $payment) {
            return [];
        }

        if (! empty($payment->customer_id)) {
            return $this->db->table('sales_invoices i')
                ->select("i.id, i.invoice_no, i.invoice_date, i.total_amount, COALESCE(SUM(a.allocated_amount), 0) AS allocated_amount, (i.total_amount - COALESCE(SUM(a.allocated_amount),0)) AS outstanding_amount, 'sales_invoice' AS document_type", false)
                ->join('payment_allocations a', "a.document_type = 'sales_invoice' AND a.document_id = i.id AND a.company_id = i.company_id AND a.deleted_at IS NULL", 'left')
                ->where('i.company_id', $companyId)->where('i.customer_id', $payment->customer_id)->where('i.status', 'posted')
                ->groupBy('i.id')
                ->having('outstanding_amount >', 0)
                ->orderBy('i.invoice_date', 'ASC')->get()->getResultArray();
        }

        if (! empty($payment->supplier_id)) {
            return $this->db->table('purchase_invoices i')
                ->select("i.id, i.invoice_no, i.invoice_date, i.total_amount, COALESCE(SUM(a.allocated_amount), 0) AS allocated_amount, (i.total_amount - COALESCE(SUM(a.allocated_amount),0)) AS outstanding_amount, 'purchase_invoice' AS document_type", false)
                ->join('payment_allocations a', "a.document_type = 'purchase_invoice' AND a.document_id = i.id AND a.company_id = i.company_id AND a.deleted_at IS NULL", 'left')
                ->where('i.company_id', $companyId)->where('i.supplier_id', $payment->supplier_id)->where('i.status', 'posted')
                ->groupBy('i.id')
                ->having('outstanding_amount >', 0)
                ->orderBy('i.invoice_date', 'ASC')->get()->getResultArray();
        }

        return [];
    }

    /** @return list<array<string, mixed>> */
    public function suppliers(int $companyId): array
    {
        return $this->activeOptions('suppliers', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function customers(int $companyId): array
    {
        return $this->activeOptions('customers', $companyId);
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
    public function products(int $companyId): array
    {
        return $this->db->table('products')->select('id, sku AS code, name')
            ->where(['company_id' => $companyId, 'status' => 'active', 'product_type' => 'stock'])
            ->where('deleted_at', null)->orderBy('sku', 'ASC')->get()->getResultArray();
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

    public function fiscalPeriodExists(int $companyId, int $year, int $period): bool
    {
        return $this->db->table('fiscal_periods')
            ->where(['company_id' => $companyId, 'year' => $year, 'period' => $period])
            ->where('deleted_at', null)->countAllResults() > 0;
    }

    /** @return list<array<string, mixed>> */
    private function activeOptions(string $table, int $companyId): array
    {
        return $this->db->table($table)->select('id, code, name')
            ->where(['company_id' => $companyId, 'status' => 'active'])
            ->where('deleted_at', null)->orderBy('code', 'ASC')->get()->getResultArray();
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function appendPaymentStatus(array $rows): array
    {
        foreach ($rows as &$row) {
            $status = (string) ($row['status'] ?? 'draft');
            $total = (float) ($row['total_amount'] ?? 0);
            $paid = (float) ($row['paid_amount'] ?? 0);
            $outstanding = max(0.0, $total - $paid);

            $row['paid_amount'] = $paid;
            $row['outstanding_amount'] = $outstanding;

            if ($status === 'draft') {
                $row['payment_status'] = 'draft';
            } elseif ($paid <= 0.00001) {
                $row['payment_status'] = 'unpaid';
            } elseif ($outstanding <= 0.00001) {
                $row['payment_status'] = 'paid';
            } else {
                $row['payment_status'] = 'partial_paid';
            }
        }
        unset($row);

        return $rows;
    }
}
