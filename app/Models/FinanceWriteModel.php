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

    /** @param array<string, mixed> $data */
    public function createGlBook(array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];
        if (! $this->active('currencies', (int) $data['currency_id'], $companyId)
            || (($data['retained_earnings_account_id'] ?? null) !== null && ! $this->active('chart_of_accounts', (int) $data['retained_earnings_account_id'], $companyId))) {
            return false;
        }

        $this->create('gl_books', 'GL_BOOK_CREATED', 'gl_book', $data, $actorId);

        return true;
    }

    /** @param array<string, mixed> $data */
    public function createGlColumn(array $data, int $actorId): void
    {
        $this->create('gl_columns', 'GL_COLUMN_CREATED', 'gl_column', $data, $actorId);
    }

    /** @param array<string, mixed> $data */
    public function createCostType(array $data, int $actorId): void
    {
        $this->create('cost_types', 'COST_TYPE_CREATED', 'cost_type', $data, $actorId);
    }

    /** @param array<string, mixed> $data */
    public function createItemCost(array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];
        $product = $this->db->table('products')->where([
            'id' => (int) $data['product_id'], 'company_id' => $companyId, 'status' => 'active', 'product_type' => 'stock',
        ])->where('deleted_at', null)->countAllResults() === 1;

        if (! $product
            || ! $this->active('cost_types', (int) $data['cost_type_id'], $companyId)
            || ! $this->active('currencies', (int) $data['currency_id'], $companyId)) {
            return false;
        }

        $this->create('item_costs', 'ITEM_COST_CREATED', 'item_cost', $data, $actorId);

        return true;
    }

    /** @param array<string, mixed> $data */
    public function createFiscalPeriod(array $data, int $actorId): bool
    {
        if (strtotime((string) $data['ends_on']) < strtotime((string) $data['starts_on'])) {
            return false;
        }

        $this->create('fiscal_periods', 'FISCAL_PERIOD_CREATED', 'fiscal_period', $data, $actorId);

        return true;
    }

    public function closeFiscalPeriod(int $companyId, int $periodId, int $actorId): bool
    {
        $period = $this->record('fiscal_periods', $companyId, $periodId);

        if ($period === null || $period['status'] !== 'open') {
            return false;
        }

        return $this->updateRecord('fiscal_periods', 'FISCAL_PERIOD_CLOSED', 'fiscal_period', $companyId, $periodId, [
            'status'    => 'closed',
            'locked_at' => date('Y-m-d H:i:s'),
            'locked_by' => $actorId,
        ], $actorId);
    }

    public function reopenFiscalPeriod(int $companyId, int $periodId, int $actorId): bool
    {
        $period = $this->record('fiscal_periods', $companyId, $periodId);

        if ($period === null || $period['status'] !== 'closed') {
            return false;
        }

        return $this->updateRecord('fiscal_periods', 'FISCAL_PERIOD_REOPENED', 'fiscal_period', $companyId, $periodId, [
            'status'    => 'open',
            'locked_at' => null,
            'locked_by' => null,
        ], $actorId);
    }

    public function closeModulePeriod(int $companyId, int $periodId, string $moduleCode, int $actorId): bool
    {
        if (! in_array($moduleCode, $this->moduleCodes(), true) || ! $this->sameTenantRecord('fiscal_periods', $periodId, $companyId)) {
            return false;
        }

        $existing = $this->db->table('module_period_closes')->where([
            'company_id' => $companyId, 'fiscal_period_id' => $periodId, 'module_code' => $moduleCode,
        ])->where('deleted_at', null)->get()->getFirstRow('array');

        if ($existing !== null && $existing['status'] === 'closed') {
            return false;
        }

        $changes = [
            'status'      => 'closed',
            'closed_at'   => date('Y-m-d H:i:s'),
            'closed_by'   => $actorId,
            'reopened_at' => null,
            'reopened_by' => null,
        ];

        if ($existing === null) {
            $this->create('module_period_closes', 'MODULE_PERIOD_CLOSED', 'module_period_close', [
                'company_id'        => $companyId,
                'fiscal_period_id'  => $periodId,
                'module_code'       => $moduleCode,
                ...$changes,
            ], $actorId);

            return true;
        }

        return $this->updateRecord('module_period_closes', 'MODULE_PERIOD_CLOSED', 'module_period_close', $companyId, (int) $existing['id'], $changes, $actorId);
    }

    public function reopenModulePeriod(int $companyId, int $closeId, int $actorId): bool
    {
        $close = $this->record('module_period_closes', $companyId, $closeId);

        if ($close === null || $close['status'] !== 'closed') {
            return false;
        }

        return $this->updateRecord('module_period_closes', 'MODULE_PERIOD_REOPENED', 'module_period_close', $companyId, $closeId, [
            'status'      => 'open',
            'reopened_at' => date('Y-m-d H:i:s'),
            'reopened_by' => $actorId,
        ], $actorId);
    }

    /**
     * @param array<string, mixed>        $data
     * @param list<array<string, mixed>>  $lines
     */
    public function createManualJournal(array $data, array $lines, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];
        if (! $this->active('gl_books', (int) $data['gl_book_id'], $companyId) || ! $this->balancedJournalLines($companyId, $lines)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transStart();
        $this->db->table('journal_entries')->insert([
            'company_id'   => $companyId,
            'gl_book_id'   => (int) $data['gl_book_id'],
            'journal_no'   => $this->nextJournalNo($companyId),
            'journal_date' => (string) $data['journal_date'],
            'source_type'  => 'manual',
            'source_id'    => null,
            'description'  => (string) $data['description'],
            'status'       => 'draft',
            'created_by'   => $actorId,
            'created_at'   => $now,
        ]);
        $journalId = (int) $this->db->insertID();

        foreach ($lines as $line) {
            $this->db->table('journal_entry_lines')->insert([
                'company_id'        => $companyId,
                'journal_entry_id'  => $journalId,
                'account_id'        => (int) $line['account_id'],
                'description'       => trim((string) ($line['description'] ?? '')) ?: null,
                'debit'             => $this->money((float) ($line['debit'] ?? 0)),
                'credit'            => $this->money((float) ($line['credit'] ?? 0)),
                'created_by'        => $actorId,
                'created_at'        => $now,
            ]);
        }

        (new AuditTrailService($this->db))->record('JOURNAL_ENTRY_CREATED', 'journal_entry', $journalId, $companyId, null, $actorId, $data);
        $this->complete();

        return true;
    }

    /** @param array<string, mixed> $data */
    public function createPurchaseInvoice(array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];
        if (! $this->active('suppliers', (int) $data['supplier_id'], $companyId)
            || ! $this->active('currencies', (int) $data['currency_id'], $companyId)
            || $this->documentNoExists('purchase_invoices', $companyId, 'invoice_no', 'supplier_id', (int) $data['supplier_id'], (string) $data['invoice_no'])) {
            return false;
        }

        $this->create('purchase_invoices', 'PURCHASE_INVOICE_CREATED', 'purchase_invoice', $data, $actorId);

        return true;
    }

    /** @param array<string, mixed> $data */
    public function createSalesInvoice(array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];
        if (! $this->active('customers', (int) $data['customer_id'], $companyId)
            || ! $this->active('currencies', (int) $data['currency_id'], $companyId)
            || $this->documentNoExists('sales_invoices', $companyId, 'invoice_no', 'customer_id', (int) $data['customer_id'], (string) $data['invoice_no'])) {
            return false;
        }

        $this->create('sales_invoices', 'SALES_INVOICE_CREATED', 'sales_invoice', $data, $actorId);

        return true;
    }

    /** @param array<string, mixed> $data */
    public function createPayment(array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];
        $paymentType = (string) $data['payment_type'];
        $supplierId = $data['supplier_id'] ?? null;
        $customerId = $data['customer_id'] ?? null;
        $bankAccountId = $data['bank_account_id'] ?? null;
        if ($bankAccountId === 0) {
            $bankAccountId = null;
        }

        if (! in_array($paymentType, ['incoming', 'outgoing'], true)
            || ! $this->active('currencies', (int) $data['currency_id'], $companyId)
            || ($paymentType === 'incoming' && ! $this->active('customers', (int) $customerId, $companyId))
            || ($paymentType === 'outgoing' && ! $this->active('suppliers', (int) $supplierId, $companyId))
            || ($bankAccountId !== null && ! $this->active('cash_bank_accounts', (int) $bankAccountId, $companyId))
            || $this->documentNoExists('payments', $companyId, 'payment_no', null, null, (string) $data['payment_no'])) {
            return false;
        }

        $this->create('payments', 'PAYMENT_CREATED', 'payment', $data, $actorId);

        return true;
    }

    public function postJournalEntry(int $companyId, int $journalId, int $actorId): bool
    {
        $journal = $this->record('journal_entries', $companyId, $journalId);

        if ($journal === null || $journal['status'] !== 'draft' || ! $this->periodOpenForPosting($companyId, 'gl', (string) $journal['journal_date'])) {
            return false;
        }

        return $this->updateRecord('journal_entries', 'JOURNAL_ENTRY_POSTED', 'journal_entry', $companyId, $journalId, [
            'status'    => 'posted',
            'posted_at' => date('Y-m-d H:i:s'),
            'posted_by' => $actorId,
        ], $actorId);
    }

    public function postPurchaseInvoice(int $companyId, int $invoiceId, int $actorId): bool
    {
        $invoice = $this->record('purchase_invoices', $companyId, $invoiceId);

        if ($invoice === null || $invoice['status'] !== 'draft' || ! $this->periodOpenForPosting($companyId, 'ap', (string) $invoice['invoice_date'])) {
            return false;
        }

        if (! $this->updateRecord('purchase_invoices', 'PURCHASE_INVOICE_POSTED', 'purchase_invoice', $companyId, $invoiceId, [
            'status'    => 'posted',
            'posted_at' => date('Y-m-d H:i:s'),
            'posted_by' => $actorId,
        ], $actorId)) {
            return false;
        }

        // Create a posting journal placeholder for later GL lines generation.
        $this->createPostingJournal('purchase_invoice', $companyId, $invoiceId, (string) $invoice['invoice_date'], 'Auto-post purchase invoice ' . ($invoice['invoice_no'] ?? $invoiceId), $actorId);

        return true;
    }

    public function postSalesInvoice(int $companyId, int $invoiceId, int $actorId): bool
    {
        $invoice = $this->record('sales_invoices', $companyId, $invoiceId);

        if ($invoice === null || $invoice['status'] !== 'draft' || ! $this->periodOpenForPosting($companyId, 'ar', (string) $invoice['invoice_date'])) {
            return false;
        }

        if (! $this->updateRecord('sales_invoices', 'SALES_INVOICE_POSTED', 'sales_invoice', $companyId, $invoiceId, [
            'status'    => 'posted',
            'posted_at' => date('Y-m-d H:i:s'),
            'posted_by' => $actorId,
        ], $actorId)) {
            return false;
        }

        $this->createPostingJournal('sales_invoice', $companyId, $invoiceId, (string) $invoice['invoice_date'], 'Auto-post sales invoice ' . ($invoice['invoice_no'] ?? $invoiceId), $actorId);

        return true;
    }

    public function postPayment(int $companyId, int $paymentId, int $actorId): bool
    {
        $payment = $this->record('payments', $companyId, $paymentId);

        if ($payment === null || $payment['status'] !== 'draft' || ! $this->periodOpenForPosting($companyId, 'cashbank', (string) $payment['payment_date'])) {
            return false;
        }

        if (! $this->updateRecord('payments', 'PAYMENT_POSTED', 'payment', $companyId, $paymentId, [
            'status'    => 'posted',
            'posted_at' => date('Y-m-d H:i:s'),
            'posted_by' => $actorId,
        ], $actorId)) {
            return false;
        }

        $this->createPostingJournal('payment', $companyId, $paymentId, (string) $payment['payment_date'], 'Auto-post payment ' . ($payment['payment_no'] ?? $paymentId), $actorId);

        // Try a simple automatic allocation: match payment to posted invoices of the same partner
        try {
            $this->autoAllocatePaymentToDocuments($companyId, $paymentId, $actorId);
        } catch (\Throwable $e) {
            // Allocation failures should not break posting; log and continue
        }

        return true;
    }

    private function autoAllocatePaymentToDocuments(int $companyId, int $paymentId, int $actorId): void
    {
        $payment = $this->record('payments', $companyId, $paymentId);
        if ($payment === null) {
            return;
        }

        $remaining = (float) ($payment['amount'] ?? 0);
        if ($remaining <= 0) {
            return;
        }

        $partnerField = $payment['payment_type'] === 'incoming' ? 'customer_id' : 'supplier_id';
        $partnerId = $payment[$partnerField] ?? null;
        if ($partnerId === null) {
            return;
        }

        $docType = $payment['payment_type'] === 'incoming' ? 'sales_invoice' : 'purchase_invoice';
        $table = $payment['payment_type'] === 'incoming' ? 'sales_invoices' : 'purchase_invoices';

        $invoices = $this->db->table($table)
            ->where(['company_id' => $companyId, $partnerField => $partnerId, 'status' => 'posted'])
            ->where('deleted_at', null)
            ->orderBy('invoice_date', 'ASC')->orderBy('id', 'ASC')
            ->get()->getResultArray();

        foreach ($invoices as $inv) {
            if ($remaining <= 0) {
                break;
            }

            $allocated = (float) $this->db->table('payment_allocations')
                ->select('IFNULL(SUM(allocated_amount),0) AS total')
                ->where(['company_id' => $companyId, 'document_type' => $docType, 'document_id' => (int) $inv['id']])
                ->get()->getFirstRow()->total ?? 0;

            $outstanding = (float) $inv['total_amount'] - (float) $allocated;
            if ($outstanding <= 0) {
                continue;
            }

            $alloc = min($remaining, $outstanding);

            $this->db->table('payment_allocations')->insert([
                'company_id'      => $companyId,
                'payment_id'      => $paymentId,
                'document_type'   => $docType,
                'document_id'     => (int) $inv['id'],
                'allocated_amount'=> $this->money($alloc),
                'description'     => 'Auto allocation from payment ' . ($payment['payment_no'] ?? $paymentId),
                'created_by'      => $actorId,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);

            $remaining -= $alloc;
        }
    }

    /** @param array<string, mixed> $data */
    public function createPaymentAllocation(array $data, int $actorId): bool
    {
        $companyId = (int) $data['company_id'];
        $payment = $this->record('payments', $companyId, (int) $data['payment_id']);
        if ($payment === null || $payment['status'] !== 'posted') {
            return false;
        }

        $docType = (string) $data['document_type'];
        $documentId = (int) $data['document_id'];

        $table = $docType === 'sales_invoice' ? 'sales_invoices' : ($docType === 'purchase_invoice' ? 'purchase_invoices' : null);
        if ($table === null) {
            return false;
        }

        $document = $this->record($table, $companyId, $documentId);
        if ($document === null || $document['status'] !== 'posted') {
            return false;
        }

        $allocatedSoFar = (float) $this->db->table('payment_allocations')
            ->select('IFNULL(SUM(allocated_amount),0) AS total')
            ->where(['company_id' => $companyId, 'document_type' => $docType, 'document_id' => $documentId])
            ->get()->getFirstRow()->total ?? 0;

        $amount = (float) $data['allocated_amount'];
        $outstanding = (float) $document['total_amount'] - $allocatedSoFar;
        if ($amount <= 0 || $amount > $outstanding + 0.00001) {
            return false;
        }

        $this->create('payment_allocations', 'PAYMENT_ALLOCATION_CREATED', 'payment_allocation', $data, $actorId);

        return true;
    }

    public function deletePaymentAllocation(int $companyId, int $allocationId, int $actorId): bool
    {
        $alloc = $this->record('payment_allocations', $companyId, $allocationId);
        if ($alloc === null) {
            return false;
        }

        return $this->updateRecord('payment_allocations', 'PAYMENT_ALLOCATION_DELETED', 'payment_allocation', $companyId, $allocationId, ['deleted_at' => date('Y-m-d H:i:s')], $actorId);
    }

    /**
     * Create a lightweight journal entry referencing a source document.
     * This creates a journal header (status 'posted') as a placeholder for later GL lines.
     *
     * @return int Inserted journal id
     */
    private function createPostingJournal(string $sourceType, int $companyId, int $sourceId, string $journalDate, string $description, int $actorId): int
    {
        $lines = match ($sourceType) {
            'purchase_invoice' => $this->buildPurchaseInvoicePostingLines($companyId, $sourceId),
            'sales_invoice'    => $this->buildSalesInvoicePostingLines($companyId, $sourceId),
            'payment'          => $this->buildPaymentPostingLines($companyId, $sourceId),
            default            => [],
        };

        if (! $this->balancedJournalLines($companyId, $lines)) {
            throw new RuntimeException('Unable to create balanced posting journal lines for ' . $sourceType);
        }

        $now = date('Y-m-d H:i:s');

        $glBook = $this->db->table('gl_books')->select('id')->where([
            'company_id' => $companyId,
            'status'     => 'active',
        ])->where('deleted_at', null)->orderBy('id', 'ASC')->get()->getFirstRow('array');

        if ($glBook === null) {
            throw new RuntimeException('No active GL book configured for company ' . $companyId);
        }

        $glBookId = (int) $glBook['id'];

        $this->db->table('journal_entries')->insert([
            'company_id'   => $companyId,
            'gl_book_id'   => $glBookId,
            'journal_no'   => $this->nextJournalNo($companyId),
            'journal_date' => $journalDate,
            'source_type'  => $sourceType,
            'source_id'    => $sourceId,
            'description'  => $description,
            'status'       => 'posted',
            'created_by'   => $actorId,
            'created_at'   => $now,
            'posted_at'    => $now,
            'posted_by'    => $actorId,
        ]);

        $journalId = (int) $this->db->insertID();

        foreach ($lines as $line) {
            $this->db->table('journal_entry_lines')->insert([
                'company_id'       => $companyId,
                'journal_entry_id' => $journalId,
                'account_id'       => (int) $line['account_id'],
                'description'      => trim((string) ($line['description'] ?? '')) ?: null,
                'debit'            => $line['debit'],
                'credit'           => $line['credit'],
                'partner_type'     => $line['partner_type'] ?? null,
                'partner_id'       => $line['partner_id'] ?? null,
                'created_by'       => $actorId,
                'created_at'       => $now,
            ]);
        }

        (new AuditTrailService($this->db))->record('JOURNAL_ENTRY_CREATED', 'journal_entry', $journalId, $companyId, null, $actorId, ['source' => $sourceType, 'source_id' => $sourceId]);

        return $journalId;
    }

    private function buildPurchaseInvoicePostingLines(int $companyId, int $invoiceId): array
    {
        $invoice = $this->record('purchase_invoices', $companyId, $invoiceId);
        if ($invoice === null) {
            return [];
        }

        $amount = (float) ($invoice['total_amount'] ?? 0);
        $expenseAccountId = $this->findDefaultAccountId($companyId, 'expense', '5001');
        $payableAccountId = $this->findDefaultAccountId($companyId, 'liability', '2101');

        if ($expenseAccountId === null || $payableAccountId === null || $amount <= 0) {
            return [];
        }

        return [
            [
                'account_id'   => $expenseAccountId,
                'description'  => 'Purchase invoice ' . ($invoice['invoice_no'] ?? $invoiceId),
                'debit'        => $this->money($amount),
                'credit'       => '0.0000',
                'partner_type' => 'supplier',
                'partner_id'   => $invoice['supplier_id'],
            ],
            [
                'account_id'   => $payableAccountId,
                'description'  => 'Accounts payable for invoice ' . ($invoice['invoice_no'] ?? $invoiceId),
                'debit'        => '0.0000',
                'credit'       => $this->money($amount),
                'partner_type' => 'supplier',
                'partner_id'   => $invoice['supplier_id'],
            ],
        ];
    }

    private function buildSalesInvoicePostingLines(int $companyId, int $invoiceId): array
    {
        $invoice = $this->record('sales_invoices', $companyId, $invoiceId);
        if ($invoice === null) {
            return [];
        }

        $amount = (float) ($invoice['total_amount'] ?? 0);
        $receivableAccountId = $this->findDefaultAccountId($companyId, 'asset', '1201', ['1101', '1102']);
        $revenueAccountId = $this->findDefaultAccountId($companyId, 'revenue', '4101');

        if ($receivableAccountId === null || $revenueAccountId === null || $amount <= 0) {
            return [];
        }

        return [
            [
                'account_id'   => $receivableAccountId,
                'description'  => 'Sales invoice ' . ($invoice['invoice_no'] ?? $invoiceId),
                'debit'        => $this->money($amount),
                'credit'       => '0.0000',
                'partner_type' => 'customer',
                'partner_id'   => $invoice['customer_id'],
            ],
            [
                'account_id'   => $revenueAccountId,
                'description'  => 'Sales revenue for invoice ' . ($invoice['invoice_no'] ?? $invoiceId),
                'debit'        => '0.0000',
                'credit'       => $this->money($amount),
                'partner_type' => 'customer',
                'partner_id'   => $invoice['customer_id'],
            ],
        ];
    }

    private function buildPaymentPostingLines(int $companyId, int $paymentId): array
    {
        $payment = $this->record('payments', $companyId, $paymentId);
        if ($payment === null) {
            return [];
        }

        $amount = (float) ($payment['amount'] ?? 0);
        $cashAccountId = $this->resolveCashBankChartAccountId($companyId, $payment['bank_account_id'] ?? null);
        if ($cashAccountId === null || $amount <= 0) {
            return [];
        }

        if (($payment['payment_type'] ?? '') === 'incoming') {
            $receivableAccountId = $this->findDefaultAccountId($companyId, 'asset', '1201', ['1101', '1102']);
            if ($receivableAccountId === null) {
                return [];
            }

            return [
                [
                    'account_id'   => $cashAccountId,
                    'description'  => 'Payment received ' . ($payment['payment_no'] ?? $paymentId),
                    'debit'        => $this->money($amount),
                    'credit'       => '0.0000',
                    'partner_type' => 'customer',
                    'partner_id'   => $payment['customer_id'],
                ],
                [
                    'account_id'   => $receivableAccountId,
                    'description'  => 'Receipt for payment ' . ($payment['payment_no'] ?? $paymentId),
                    'debit'        => '0.0000',
                    'credit'       => $this->money($amount),
                    'partner_type' => 'customer',
                    'partner_id'   => $payment['customer_id'],
                ],
            ];
        }

        $payableAccountId = $this->findDefaultAccountId($companyId, 'liability', '2101');
        if ($payableAccountId === null) {
            return [];
        }

        return [
            [
                'account_id'   => $payableAccountId,
                'description'  => 'Payment made ' . ($payment['payment_no'] ?? $paymentId),
                'debit'        => $this->money($amount),
                'credit'       => '0.0000',
                'partner_type' => 'supplier',
                'partner_id'   => $payment['supplier_id'],
            ],
            [
                'account_id'   => $cashAccountId,
                'description'  => 'Cash payment ' . ($payment['payment_no'] ?? $paymentId),
                'debit'        => '0.0000',
                'credit'       => $this->money($amount),
                'partner_type' => 'supplier',
                'partner_id'   => $payment['supplier_id'],
            ],
        ];
    }

    private function resolveCashBankChartAccountId(int $companyId, ?int $bankAccountId): ?int
    {
        if ($bankAccountId !== null) {
            $cashBank = $this->db->table('cash_bank_accounts')->select('account_id')->where([
                'company_id' => $companyId,
                'id'         => $bankAccountId,
                'status'     => 'active',
            ])->where('deleted_at', null)->get()->getFirstRow('array');

            if ($cashBank !== null && $this->postableAccount((int) $cashBank['account_id'], $companyId)) {
                return (int) $cashBank['account_id'];
            }
        }

        $account = $this->db->table('chart_of_accounts')->select('id')->where([
            'company_id'  => $companyId,
            'status'      => 'active',
            'is_postable' => true,
        ])->whereIn('account_code', ['1102', '1101'])->orderBy('account_code', 'ASC')->get()->getFirstRow('array');

        return $account['id'] ?? null;
    }

    private function findDefaultAccountId(int $companyId, string $accountType, ?string $preferredCode = null, array $excludeCodes = []): ?int
    {
        if ($preferredCode !== null) {
            $preferred = $this->db->table('chart_of_accounts')->select('id')->where([
                'company_id'  => $companyId,
                'account_code' => $preferredCode,
                'status'      => 'active',
                'is_postable' => true,
                'account_type' => $accountType,
            ])->where('deleted_at', null)->get()->getFirstRow('array');

            if ($preferred !== null) {
                return (int) $preferred['id'];
            }
        }

        $builder = $this->db->table('chart_of_accounts')->select('id')->where([
            'company_id'   => $companyId,
            'account_type' => $accountType,
            'status'       => 'active',
            'is_postable'  => true,
        ])->where('deleted_at', null);

        if ($excludeCodes !== []) {
            $builder->whereNotIn('account_code', $excludeCodes);
        }

        $account = $builder->orderBy('account_code', 'ASC')->limit(1)->get()->getFirstRow('array');

        return $account['id'] ?? null;
    }

    public function updateStatus(string $master, int $companyId, int $id, string $status, int $actorId): bool
    {
        $map = [
            'account'       => ['chart_of_accounts', 'chart_account'],
            'cash-bank'     => ['cash_bank_accounts', 'cash_bank_account'],
            'exchange-rate' => ['exchange_rates', 'exchange_rate'],
            'gl-book'       => ['gl_books', 'gl_book'],
            'gl-column'     => ['gl_columns', 'gl_column'],
            'cost-type'     => ['cost_types', 'cost_type'],
            'item-cost'     => ['item_costs', 'item_cost'],
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

    /**
     * @param list<array<string, mixed>> $lines
     */
    private function balancedJournalLines(int $companyId, array $lines): bool
    {
        if (count($lines) < 2) {
            return false;
        }

        $debit = 0.0;
        $credit = 0.0;

        foreach ($lines as $line) {
            $lineDebit = (float) ($line['debit'] ?? 0);
            $lineCredit = (float) ($line['credit'] ?? 0);

            if ($lineDebit < 0 || $lineCredit < 0 || ($lineDebit <= 0 && $lineCredit <= 0) || ($lineDebit > 0 && $lineCredit > 0)) {
                return false;
            }

            if (! $this->postableAccount((int) $line['account_id'], $companyId)) {
                return false;
            }

            $debit += $lineDebit;
            $credit += $lineCredit;
        }

        return $debit > 0 && abs($debit - $credit) < 0.00001;
    }

    private function postableAccount(int $accountId, int $companyId): bool
    {
        return $this->db->table('chart_of_accounts')->where([
            'id' => $accountId, 'company_id' => $companyId, 'status' => 'active', 'is_postable' => true,
        ])->where('deleted_at', null)->countAllResults() === 1;
    }

    private function periodOpenForPosting(int $companyId, string $moduleCode, string $date): bool
    {
        $period = $this->db->table('fiscal_periods')
            ->where('company_id', $companyId)
            ->where('starts_on <=', $date)
            ->where('ends_on >=', $date)
            ->where('deleted_at', null)
            ->get()->getFirstRow('array');

        if ($period === null || $period['status'] !== 'open') {
            return false;
        }

        $moduleClose = $this->db->table('module_period_closes')->where([
            'company_id' => $companyId, 'fiscal_period_id' => (int) $period['id'], 'module_code' => $moduleCode,
        ])->where('deleted_at', null)->get()->getFirstRow('array');

        return $moduleClose === null || $moduleClose['status'] === 'open';
    }

    private function sameTenantRecord(string $table, int $id, int $companyId): bool
    {
        return $this->db->table($table)->where(['id' => $id, 'company_id' => $companyId])
            ->where('deleted_at', null)->countAllResults() === 1;
    }

    private function documentNoExists(string $table, int $companyId, string $numberColumn, ?string $partnerColumn, ?int $partnerId, string $documentNo): bool
    {
        $builder = $this->db->table($table)->where(['company_id' => $companyId, $numberColumn => $documentNo])->where('deleted_at', null);

        if ($partnerColumn !== null && $partnerId !== null) {
            $builder->where($partnerColumn, $partnerId);
        }

        return $builder->countAllResults() > 0;
    }

    /** @return array<string, mixed>|null */
    private function record(string $table, int $companyId, int $id): ?array
    {
        return $this->db->table($table)->where(['id' => $id, 'company_id' => $companyId])
            ->where('deleted_at', null)->get()->getFirstRow('array');
    }

    /** @return list<string> */
    private function moduleCodes(): array
    {
        return ['sales', 'purchase', 'inventory', 'production', 'ap', 'ar', 'cashbank', 'gl'];
    }

    private function nextJournalNo(int $companyId): string
    {
        $prefix = 'JV-' . date('Ymd') . '-';
        $count = $this->db->table('journal_entries')
            ->where('company_id', $companyId)
            ->like('journal_no', $prefix, 'after')
            ->countAllResults();

        return $prefix . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }

    private function money(float $value): string
    {
        return number_format($value, 4, '.', '');
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
