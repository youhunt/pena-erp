<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\PosReadModel;
use App\Models\PosWriteModel;
use App\Services\TenantContextService;
use CodeIgniter\HTTP\RedirectResponse;

final class PosMaster extends BaseController
{
    public function index(): string
    {
        $context = $this->context('pos.master.view');

        if ($context === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => 'pos']);
        }

        $companyId = (int) $context['company_id'];
        $reader = new PosReadModel();

        return view('pos/index', [
            'tenantContext'    => $context,
            'canManage'        => $this->can($companyId, 'pos.master.manage'),
            'registers'        => $reader->registers($companyId),
            'branches'         => $reader->branches($companyId),
            'departments'      => $reader->departments($companyId),
            'warehouses'       => $reader->warehouses($companyId),
            'customers'        => $reader->customers($companyId),
            'currencies'       => $reader->currencies($companyId),
            'transactionCodes' => $reader->transactionCodes($companyId),
            'paymentMethods'   => $reader->paymentMethods($companyId),
            'cashBanks'        => $reader->cashBankAccounts($companyId),
            'shifts'           => $reader->shifts($companyId),
            'sales'            => $reader->sales($companyId),
            'saleProducts'     => $reader->saleProducts($companyId),
        ]);
    }

    public function createRegister(): RedirectResponse
    {
        $context = $this->context('pos.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $data = $this->registerData((int) $context['company_id'], true);

        if (! $this->validateRegister($data, true)) {
            return $this->invalid();
        }

        if ((new PosReadModel())->codeExists((int) $context['company_id'], (string) $data['code'])) {
            return $this->invalid(['code' => 'Kode register POS sudah digunakan pada company aktif.']);
        }

        if (! (new PosWriteModel())->createRegister($data, $this->actorId())) {
            return $this->invalid(['reference' => 'Site, department, warehouse, customer, currency, atau transaction code tidak valid.']);
        }

        return $this->completed('Register POS berhasil ditambahkan.');
    }

    public function updateRegister(int $id): RedirectResponse
    {
        $context = $this->context('pos.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $data = $this->registerData((int) $context['company_id'], false);

        if (! $this->validateRegister($data, false)) {
            return $this->invalid();
        }

        if (! (new PosWriteModel())->updateRegister((int) $context['company_id'], $id, $data, $this->actorId())) {
            return $this->invalid(['reference' => 'Register atau referensi POS tidak valid untuk company aktif.']);
        }

        return $this->completed('Register POS berhasil diperbarui.');
    }

    public function updateStatus(int $id): RedirectResponse
    {
        $context = $this->context('pos.master.manage');
        $status = (string) $this->request->getPost('status');

        if ($context === null) {
            return $this->denied();
        }

        if (! (new PosWriteModel())->updateStatus((int) $context['company_id'], $id, $status, $this->actorId())) {
            return $this->invalid(['status' => 'Status atau register POS tidak valid untuk company aktif.']);
        }

        return $this->completed('Status register POS diperbarui.');
    }

    public function createPaymentMethod(): RedirectResponse
    {
        $context = $this->context('pos.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $data = $this->paymentData((int) $context['company_id'], true);

        if (! $this->validateData($data, $this->paymentRules(true))) {
            return $this->invalid();
        }

        if ((new PosReadModel())->paymentCodeExists((int) $context['company_id'], (int) $data['register_id'], (string) $data['code'])) {
            return $this->invalid(['code' => 'Kode payment method sudah digunakan pada register ini.']);
        }

        if (! (new PosWriteModel())->createPaymentMethod($data, $this->actorId())) {
            return $this->invalid(['reference' => 'Register atau Cash/Bank Account tidak valid untuk company aktif.']);
        }

        return $this->completed('Payment method POS berhasil ditambahkan.');
    }

    public function updatePaymentMethod(int $id): RedirectResponse
    {
        $context = $this->context('pos.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $data = $this->paymentData((int) $context['company_id'], false);

        if (! $this->validateData($data, $this->paymentRules(false))) {
            return $this->invalid();
        }

        if (! (new PosWriteModel())->updatePaymentMethod((int) $context['company_id'], $id, $data, $this->actorId())) {
            return $this->invalid(['reference' => 'Payment method atau referensi kas/bank tidak valid untuk company aktif.']);
        }

        return $this->completed('Payment method POS berhasil diperbarui.');
    }

    public function updatePaymentStatus(int $id): RedirectResponse
    {
        $context = $this->context('pos.master.manage');
        $status = (string) $this->request->getPost('status');

        if ($context === null) {
            return $this->denied();
        }

        if (! (new PosWriteModel())->updatePaymentStatus((int) $context['company_id'], $id, $status, $this->actorId())) {
            return $this->invalid(['status' => 'Status atau payment method tidak valid untuk company aktif.']);
        }

        return $this->completed('Status payment method POS diperbarui.');
    }

    public function openShift(): RedirectResponse
    {
        $context = $this->context('pos.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $data = [
            'company_id'      => (int) $context['company_id'],
            'register_id'     => (int) $this->request->getPost('register_id'),
            'cashier_user_id' => $this->actorId(),
            'opened_at'       => date('Y-m-d H:i:s'),
            'opening_cash'    => (string) ($this->request->getPost('opening_cash') ?: '0'),
        ];

        if (! $this->validateData($data, [
            'register_id'  => 'required|is_natural_no_zero',
            'opening_cash' => 'required|decimal|greater_than_equal_to[0]',
        ])) {
            return $this->invalid();
        }

        if (! (new PosWriteModel())->openShift($data, $this->actorId())) {
            return $this->invalid(['shift' => 'Register tidak valid, user tidak punya akses site, atau masih ada shift open.']);
        }

        return $this->completed('Shift POS berhasil dibuka.');
    }

    public function closeShift(int $id): RedirectResponse
    {
        $context = $this->context('pos.master.manage');
        $closingCash = (string) ($this->request->getPost('closing_cash') ?: '0');

        if ($context === null) {
            return $this->denied();
        }

        if (! $this->validateData(['closing_cash' => $closingCash], ['closing_cash' => 'required|decimal|greater_than_equal_to[0]'])) {
            return $this->invalid();
        }

        if (! (new PosWriteModel())->closeShift((int) $context['company_id'], $id, $closingCash, $this->actorId())) {
            return $this->invalid(['shift' => 'Shift tidak valid, sudah closed, atau bukan milik cashier aktif.']);
        }

        return $this->completed('Shift POS berhasil ditutup.');
    }

    public function createSale(): RedirectResponse
    {
        $context = $this->context('pos.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $customerId = (int) $this->request->getPost('customer_id');
        $data = [
            'company_id'        => (int) $context['company_id'],
            'shift_id'          => (int) $this->request->getPost('shift_id'),
            'customer_id'       => $customerId > 0 ? $customerId : null,
            'product_id'        => (int) $this->request->getPost('product_id'),
            'qty'               => (string) ($this->request->getPost('qty') ?: '0'),
            'unit_price'        => (string) ($this->request->getPost('unit_price') ?: '0'),
            'payment_method_id' => (int) $this->request->getPost('payment_method_id'),
            'payment_amount'    => (string) ($this->request->getPost('payment_amount') ?: '0'),
        ];

        if (! $this->validateData($data, [
            'shift_id'          => 'required|is_natural_no_zero',
            'product_id'        => 'required|is_natural_no_zero',
            'qty'               => 'required|decimal|greater_than[0]',
            'unit_price'        => 'required|decimal|greater_than_equal_to[0]',
            'payment_method_id' => 'required|is_natural_no_zero',
            'payment_amount'    => 'required|decimal|greater_than_equal_to[0]',
        ])) {
            return $this->invalid();
        }

        if (! (new PosWriteModel())->createSale($data, $this->actorId())) {
            return $this->invalid(['sale' => 'Receipt tidak valid: shift harus open milik cashier aktif, payment harus sesuai register, item aktif, dan nominal bayar harus cukup.']);
        }

        return $this->completed('Receipt POS berhasil dibuat.');
    }

    /** @return array<string, mixed> */
    private function registerData(int $companyId, bool $includeCode): array
    {
        $customerId = (int) $this->request->getPost('default_customer_id');
        $data = [
            'company_id'          => $companyId,
            'branch_id'           => (int) $this->request->getPost('branch_id'),
            'department_id'       => (int) $this->request->getPost('department_id'),
            'warehouse_id'        => (int) $this->request->getPost('warehouse_id'),
            'default_customer_id' => $customerId > 0 ? $customerId : null,
            'currency_id'         => (int) $this->request->getPost('currency_id'),
            'transaction_code_id' => (int) $this->request->getPost('transaction_code_id'),
            'name'                => trim((string) $this->request->getPost('name')),
            'device_label'        => trim((string) $this->request->getPost('device_label')) ?: null,
        ];

        if ($includeCode) {
            $data['code'] = strtoupper(trim((string) $this->request->getPost('code')));
            $data['status'] = 'active';
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    private function validateRegister(array $data, bool $includeCode): bool
    {
        $rules = [
            'branch_id'           => 'required|is_natural_no_zero',
            'department_id'       => 'required|is_natural_no_zero',
            'warehouse_id'        => 'required|is_natural_no_zero',
            'currency_id'         => 'required|is_natural_no_zero',
            'transaction_code_id' => 'required|is_natural_no_zero',
            'name'                => 'required|max_length[120]',
            'device_label'        => 'permit_empty|max_length[80]',
        ];

        if ($includeCode) {
            $rules['code'] = 'required|alpha_dash|max_length[30]';
        }

        return $this->validateData($data, $rules);
    }

    /** @return array<string, mixed> */
    private function paymentData(int $companyId, bool $includeCode): array
    {
        $data = [
            'company_id'           => $companyId,
            'register_id'          => (int) $this->request->getPost('register_id'),
            'cash_bank_account_id' => (int) $this->request->getPost('cash_bank_account_id'),
            'name'                 => trim((string) $this->request->getPost('name')),
            'payment_type'         => (string) $this->request->getPost('payment_type'),
            'is_default'           => $this->request->getPost('is_default') === '1',
            'sort_order'           => (int) ($this->request->getPost('sort_order') ?: 10),
        ];

        if ($includeCode) {
            $data['code'] = strtoupper(trim((string) $this->request->getPost('code')));
            $data['status'] = 'active';
        }

        return $data;
    }

    /** @return array<string, string> */
    private function paymentRules(bool $includeCode): array
    {
        $rules = [
            'register_id'          => 'required|is_natural_no_zero',
            'cash_bank_account_id' => 'required|is_natural_no_zero',
            'name'                 => 'required|max_length[120]',
            'payment_type'         => 'required|in_list[cash,card,transfer,e-wallet,other]',
            'sort_order'           => 'required|integer|greater_than_equal_to[0]',
        ];

        if ($includeCode) {
            $rules['code'] = 'required|alpha_dash|max_length[30]';
        }

        return $rules;
    }

    /** @return array<string, mixed>|null */
    private function context(string $permission): ?array
    {
        $context = (new TenantContextService())->current($this->actorId());

        if ($context === null || ! $this->can((int) $context['company_id'], $permission)) {
            return null;
        }

        return $context;
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
        return redirect()->to(site_url('workspace'))->with('errors', ['access' => 'Anda tidak memiliki izin mengelola POS Master pada company aktif.']);
    }

    /** @param array<string, string>|null $errors */
    private function invalid(?array $errors = null): RedirectResponse
    {
        return redirect()->back()->withInput()->with('errors', $errors ?? $this->validator->getErrors());
    }

    private function completed(string $message): RedirectResponse
    {
        return redirect()->to(site_url('pos/master'))->with('message', $message);
    }
}
