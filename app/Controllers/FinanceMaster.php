<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\FinanceReadModel;
use App\Models\FinanceWriteModel;
use App\Services\TenantContextService;
use CodeIgniter\HTTP\RedirectResponse;

final class FinanceMaster extends BaseController
{
    public function index(): string
    {
        $context = $this->context('finance.master.view');

        if ($context === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => 'finance']);
        }

        $companyId = (int) $context['company_id'];
        $reader = new FinanceReadModel();

        return view('finance/index', [
            'tenantContext' => $context,
            'canManage'     => $this->can($companyId, 'finance.master.manage'),
            'accounts'      => $reader->accounts($companyId),
            'cashBanks'     => $reader->cashBankAccounts($companyId),
            'rates'         => $reader->exchangeRates($companyId),
            'glBooks'       => $reader->glBooks($companyId),
            'glColumns'     => $reader->glColumns($companyId),
            'costTypes'     => $reader->costTypes($companyId),
            'itemCosts'     => $reader->itemCosts($companyId),
            'fiscalPeriods' => $reader->fiscalPeriods($companyId),
            'moduleCloses'  => $reader->modulePeriodCloses($companyId),
            'postable'      => $reader->postableAccounts($companyId),
            'currencies'    => $reader->currencies($companyId),
            'branches'      => $reader->branches($companyId),
            'products'      => $reader->products($companyId),
            'moduleCodes'   => ['sales', 'purchase', 'inventory', 'production', 'ap', 'ar', 'cashbank', 'gl'],
        ]);
    }

    public function createAccount(): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        $data = $this->accountData((int) $context['company_id'], true);
        if (! $this->validateData($data, $this->accountRules(true))) {
            return $this->invalid();
        }

        $reader = new FinanceReadModel();
        if ($reader->codeExists('chart_of_accounts', (int) $context['company_id'], (string) $data['account_code'])) {
            return $this->invalid(['code' => 'Kode akun sudah digunakan pada company aktif.']);
        }

        if (! (new FinanceWriteModel())->createAccount($data, $this->actorId())) {
            return $this->invalid(['reference' => 'Parent account tidak valid untuk company aktif.']);
        }

        return $this->completed('Chart of Account berhasil ditambahkan.');
    }

    public function updateAccount(int $id): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        $data = $this->accountData((int) $context['company_id'], false);
        if (! $this->validateData($data, $this->accountRules(false))) {
            return $this->invalid();
        }

        if (! (new FinanceWriteModel())->updateAccount((int) $context['company_id'], $id, $data, $this->actorId())) {
            return $this->invalid(['reference' => 'Akun atau parent account tidak valid untuk company aktif.']);
        }

        return $this->completed('Chart of Account berhasil diperbarui.');
    }

    public function createCashBank(): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        $data = $this->cashBankData((int) $context['company_id'], true);
        if (! $this->validateData($data, $this->cashBankRules(true))) {
            return $this->invalid();
        }

        if ((new FinanceReadModel())->codeExists('cash_bank_accounts', (int) $context['company_id'], (string) $data['code'])) {
            return $this->invalid(['code' => 'Kode kas/bank sudah digunakan pada company aktif.']);
        }

        if (! (new FinanceWriteModel())->createCashBankAccount($data, $this->actorId())) {
            return $this->invalid(['reference' => 'Site, akun posting, atau currency tidak valid untuk company aktif.']);
        }

        return $this->completed('Cash/Bank Account berhasil ditambahkan.');
    }

    public function updateCashBank(int $id): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        $data = $this->cashBankData((int) $context['company_id'], false);
        if (! $this->validateData($data, $this->cashBankRules(false))) {
            return $this->invalid();
        }

        if (! (new FinanceWriteModel())->updateCashBankAccount((int) $context['company_id'], $id, $data, $this->actorId())) {
            return $this->invalid(['reference' => 'Kas/bank atau referensi tidak valid untuk company aktif.']);
        }

        return $this->completed('Cash/Bank Account berhasil diperbarui.');
    }

    public function createExchangeRate(): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        $data = $this->rateData((int) $context['company_id'], true);
        if (! $this->validateData($data, $this->rateRules())) {
            return $this->invalid();
        }

        if ((new FinanceReadModel())->exchangeRateExists((int) $context['company_id'], (int) $data['currency_id'], (string) $data['rate_date'], (string) $data['rate_type'])) {
            return $this->invalid(['rate' => 'Kurs untuk tanggal dan jenis tersebut sudah tersedia.']);
        }

        if (! (new FinanceWriteModel())->createExchangeRate($data, $this->actorId())) {
            return $this->invalid(['reference' => 'Currency tidak valid untuk company aktif.']);
        }

        return $this->completed('Exchange Rate berhasil ditambahkan.');
    }

    public function updateExchangeRate(int $id): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        $data = $this->rateData((int) $context['company_id'], false);
        if (! $this->validateData($data, $this->rateRules())) {
            return $this->invalid();
        }

        if ((new FinanceReadModel())->exchangeRateExists((int) $context['company_id'], (int) $data['currency_id'], (string) $data['rate_date'], (string) $data['rate_type'], $id)) {
            return $this->invalid(['rate' => 'Kurs untuk tanggal dan jenis tersebut sudah tersedia.']);
        }

        if (! (new FinanceWriteModel())->updateExchangeRate((int) $context['company_id'], $id, $data, $this->actorId())) {
            return $this->invalid(['reference' => 'Kurs atau currency tidak valid untuk company aktif.']);
        }

        return $this->completed('Exchange Rate berhasil diperbarui.');
    }

    public function createGlBook(): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        $retained = (int) $this->request->getPost('retained_earnings_account_id');
        $data = [
            'company_id'                    => (int) $context['company_id'],
            'currency_id'                   => (int) $this->request->getPost('currency_id'),
            'retained_earnings_account_id'  => $retained > 0 ? $retained : null,
            'code'                          => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'                          => trim((string) $this->request->getPost('name')),
            'book_type'                     => (string) $this->request->getPost('book_type'),
            'status'                        => 'active',
        ];

        if (! $this->validateData($data, [
            'currency_id' => 'required|is_natural_no_zero',
            'code'        => 'required|alpha_dash|max_length[30]',
            'name'        => 'required|max_length[120]',
            'book_type'   => 'required|in_list[primary,local,tax,management]',
        ])) {
            return $this->invalid();
        }

        if ((new FinanceReadModel())->codeExists('gl_books', (int) $context['company_id'], (string) $data['code'])) {
            return $this->invalid(['code' => 'Kode GL Book sudah digunakan.']);
        }

        if (! (new FinanceWriteModel())->createGlBook($data, $this->actorId())) {
            return $this->invalid(['reference' => 'Currency atau retained earnings account tidak valid.']);
        }

        return $this->completed('GL Book berhasil ditambahkan.');
    }

    public function createGlColumn(): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        $data = [
            'company_id'   => (int) $context['company_id'],
            'code'         => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'         => trim((string) $this->request->getPost('name')),
            'column_type'  => (string) $this->request->getPost('column_type'),
            'sequence_no'  => (int) $this->request->getPost('sequence_no'),
            'status'       => 'active',
        ];

        if (! $this->validateData($data, [
            'code'        => 'required|alpha_dash|max_length[30]',
            'name'        => 'required|max_length[120]',
            'column_type' => 'required|in_list[actual,budget,variance]',
            'sequence_no' => 'required|integer|greater_than_equal_to[1]',
        ])) {
            return $this->invalid();
        }

        if ((new FinanceReadModel())->codeExists('gl_columns', (int) $context['company_id'], (string) $data['code'])) {
            return $this->invalid(['code' => 'Kode GL Column sudah digunakan.']);
        }

        (new FinanceWriteModel())->createGlColumn($data, $this->actorId());

        return $this->completed('GL Column berhasil ditambahkan.');
    }

    public function createCostType(): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        $data = [
            'company_id'        => (int) $context['company_id'],
            'code'              => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'              => trim((string) $this->request->getPost('name')),
            'valuation_method'  => (string) $this->request->getPost('valuation_method'),
            'status'            => 'active',
        ];

        if (! $this->validateData($data, [
            'code'             => 'required|alpha_dash|max_length[30]',
            'name'             => 'required|max_length[120]',
            'valuation_method' => 'required|in_list[standard,moving_average,fifo]',
        ])) {
            return $this->invalid();
        }

        if ((new FinanceReadModel())->codeExists('cost_types', (int) $context['company_id'], (string) $data['code'])) {
            return $this->invalid(['code' => 'Kode Cost Type sudah digunakan.']);
        }

        (new FinanceWriteModel())->createCostType($data, $this->actorId());

        return $this->completed('Cost Type berhasil ditambahkan.');
    }

    public function createItemCost(): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        $data = [
            'company_id'      => (int) $context['company_id'],
            'product_id'      => (int) $this->request->getPost('product_id'),
            'cost_type_id'    => (int) $this->request->getPost('cost_type_id'),
            'currency_id'     => (int) $this->request->getPost('currency_id'),
            'unit_cost'       => (string) $this->request->getPost('unit_cost'),
            'effective_from'  => (string) $this->request->getPost('effective_from'),
            'status'          => 'active',
        ];

        if (! $this->validateData($data, [
            'product_id'     => 'required|is_natural_no_zero',
            'cost_type_id'   => 'required|is_natural_no_zero',
            'currency_id'    => 'required|is_natural_no_zero',
            'unit_cost'      => 'required|decimal|greater_than_equal_to[0]',
            'effective_from' => 'required|valid_date[Y-m-d]',
        ])) {
            return $this->invalid();
        }

        if (! (new FinanceWriteModel())->createItemCost($data, $this->actorId())) {
            return $this->invalid(['reference' => 'Item, cost type, atau currency tidak valid untuk company aktif.']);
        }

        return $this->completed('Item Cost berhasil ditambahkan.');
    }

    public function createFiscalPeriod(): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        $data = [
            'company_id' => (int) $context['company_id'],
            'year'       => (int) $this->request->getPost('year'),
            'period'     => (int) $this->request->getPost('period'),
            'starts_on'  => (string) $this->request->getPost('starts_on'),
            'ends_on'    => (string) $this->request->getPost('ends_on'),
            'status'     => 'open',
        ];

        if (! $this->validateData($data, [
            'year'      => 'required|integer|greater_than_equal_to[2000]|less_than_equal_to[2100]',
            'period'    => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[13]',
            'starts_on' => 'required|valid_date[Y-m-d]',
            'ends_on'   => 'required|valid_date[Y-m-d]',
        ])) {
            return $this->invalid();
        }

        $reader = new FinanceReadModel();
        if ($reader->fiscalPeriodExists((int) $context['company_id'], (int) $data['year'], (int) $data['period'])) {
            return $this->invalid(['period' => 'Fiscal period sudah ada untuk tahun/periode tersebut.']);
        }

        if (! (new FinanceWriteModel())->createFiscalPeriod($data, $this->actorId())) {
            return $this->invalid(['period' => 'Tanggal akhir tidak boleh sebelum tanggal mulai.']);
        }

        return $this->completed('Fiscal period berhasil ditambahkan.');
    }

    public function closeFiscalPeriod(int $id): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        if (! (new FinanceWriteModel())->closeFiscalPeriod((int) $context['company_id'], $id, $this->actorId())) {
            return $this->invalid(['period' => 'Fiscal period tidak dapat dikunci.']);
        }

        return $this->completed('Fiscal period berhasil dikunci.');
    }

    public function reopenFiscalPeriod(int $id): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        if (! (new FinanceWriteModel())->reopenFiscalPeriod((int) $context['company_id'], $id, $this->actorId())) {
            return $this->invalid(['period' => 'Fiscal period tidak dapat dibuka ulang.']);
        }

        return $this->completed('Fiscal period berhasil dibuka ulang.');
    }

    public function closeModulePeriod(): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        $periodId = (int) $this->request->getPost('fiscal_period_id');
        $moduleCode = (string) $this->request->getPost('module_code');

        if (! $this->validateData(['fiscal_period_id' => $periodId, 'module_code' => $moduleCode], [
            'fiscal_period_id' => 'required|is_natural_no_zero',
            'module_code'      => 'required|in_list[sales,purchase,inventory,production,ap,ar,cashbank,gl]',
        ])) {
            return $this->invalid();
        }

        if (! (new FinanceWriteModel())->closeModulePeriod((int) $context['company_id'], $periodId, $moduleCode, $this->actorId())) {
            return $this->invalid(['period' => 'Module period tidak dapat ditutup.']);
        }

        return $this->completed('Module period berhasil ditutup.');
    }

    public function reopenModulePeriod(int $id): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        if (! (new FinanceWriteModel())->reopenModulePeriod((int) $context['company_id'], $id, $this->actorId())) {
            return $this->invalid(['period' => 'Module period tidak dapat dibuka ulang.']);
        }

        return $this->completed('Module period berhasil dibuka ulang.');
    }

    public function updateStatus(string $master, int $id): RedirectResponse
    {
        $context = $this->context('finance.master.manage');
        if ($context === null) {
            return $this->denied();
        }

        if (! (new FinanceWriteModel())->updateStatus($master, (int) $context['company_id'], $id, (string) $this->request->getPost('status'), $this->actorId())) {
            return $this->invalid(['status' => 'Status atau master finance tidak valid untuk company aktif.']);
        }

        return $this->completed('Status Finance Master diperbarui.');
    }

    /** @return array<string, mixed> */
    private function accountData(int $companyId, bool $includeCode): array
    {
        $parentId = (int) $this->request->getPost('parent_id');
        $data = [
            'company_id'     => $companyId,
            'parent_id'      => $parentId > 0 ? $parentId : null,
            'account_name'   => trim((string) $this->request->getPost('account_name')),
            'account_type'   => (string) $this->request->getPost('account_type'),
            'normal_balance' => (string) $this->request->getPost('normal_balance'),
            'is_postable'    => $this->request->getPost('is_postable') === '1',
        ];
        if ($includeCode) {
            $data['account_code'] = strtoupper(trim((string) $this->request->getPost('account_code')));
            $data['status'] = 'active';
        }

        return $data;
    }

    /** @return array<string, string> */
    private function accountRules(bool $includeCode): array
    {
        $rules = [
            'account_name'   => 'required|max_length[120]',
            'account_type'   => 'required|in_list[asset,liability,equity,revenue,expense]',
            'normal_balance' => 'required|in_list[D,C]',
        ];
        if ($includeCode) {
            $rules['account_code'] = 'required|alpha_numeric_punct|max_length[30]';
        }

        return $rules;
    }

    /** @return array<string, mixed> */
    private function cashBankData(int $companyId, bool $includeCode): array
    {
        $branchId = (int) $this->request->getPost('branch_id');
        $data = [
            'company_id'            => $companyId,
            'branch_id'             => $branchId > 0 ? $branchId : null,
            'account_id'            => (int) $this->request->getPost('account_id'),
            'currency_id'           => (int) $this->request->getPost('currency_id'),
            'name'                  => trim((string) $this->request->getPost('name')),
            'account_type'          => (string) $this->request->getPost('account_type'),
            'bank_name'             => trim((string) $this->request->getPost('bank_name')) ?: null,
            'account_number_masked' => trim((string) $this->request->getPost('account_number_masked')) ?: null,
        ];
        if ($includeCode) {
            $data['code'] = strtoupper(trim((string) $this->request->getPost('code')));
            $data['status'] = 'active';
        }

        return $data;
    }

    /** @return array<string, string> */
    private function cashBankRules(bool $includeCode): array
    {
        $rules = [
            'account_id'            => 'required|is_natural_no_zero',
            'currency_id'           => 'required|is_natural_no_zero',
            'name'                  => 'required|max_length[120]',
            'account_type'          => 'required|in_list[cash,bank]',
            'bank_name'             => 'permit_empty|max_length[100]',
            'account_number_masked' => 'permit_empty|max_length[40]',
        ];
        if ($includeCode) {
            $rules['code'] = 'required|alpha_dash|max_length[30]';
        }

        return $rules;
    }

    /** @return array<string, mixed> */
    private function rateData(int $companyId, bool $includeStatus): array
    {
        $data = [
            'company_id'   => $companyId,
            'currency_id'  => (int) $this->request->getPost('currency_id'),
            'rate_date'    => (string) $this->request->getPost('rate_date'),
            'rate_type'    => (string) $this->request->getPost('rate_type'),
            'rate_to_base' => (string) $this->request->getPost('rate_to_base'),
        ];

        if ($includeStatus) {
            $data['status'] = 'active';
        }

        return $data;
    }

    /** @return array<string, string> */
    private function rateRules(): array
    {
        return [
            'currency_id'  => 'required|is_natural_no_zero',
            'rate_date'    => 'required|valid_date[Y-m-d]',
            'rate_type'    => 'required|in_list[buy,sell,middle]',
            'rate_to_base' => 'required|decimal|greater_than[0]',
        ];
    }

    /** @return array<string, mixed>|null */
    private function context(string $permission): ?array
    {
        $context = (new TenantContextService())->current($this->actorId());

        return $context !== null && $this->can((int) $context['company_id'], $permission) ? $context : null;
    }

    private function can(int $companyId, string $permission): bool
    {
        return (new TenantAuthorizationService())->can($this->actorId(), $companyId, $permission);
    }

    private function actorId(): int
    {
        return (int) auth()->id();
    }

    private function denied(): RedirectResponse
    {
        return redirect()->to(site_url('workspace'))->with('errors', ['access' => 'Anda tidak memiliki izin mengelola Finance Master pada company aktif.']);
    }

    /** @param array<string, string>|null $errors */
    private function invalid(?array $errors = null): RedirectResponse
    {
        return redirect()->back()->withInput()->with('errors', $errors ?? $this->validator->getErrors());
    }

    private function completed(string $message): RedirectResponse
    {
        return redirect()->to(site_url('finance/master'))->with('message', $message);
    }
}
