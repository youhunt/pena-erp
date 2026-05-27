<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\CommercialReadModel;
use App\Models\CommercialWriteModel;
use App\Services\TenantContextService;
use CodeIgniter\HTTP\RedirectResponse;

final class CommercialMaster extends BaseController
{
    public function sales(): string
    {
        return $this->render('sales', 'sales.master.view');
    }

    public function purchasing(): string
    {
        return $this->render('purchasing', 'purchasing.master.view');
    }

    public function createCustomerTerm(): RedirectResponse
    {
        $context = $this->manageableContext('sales');

        if ($context === null) {
            return $this->denied('sales');
        }

        $data = $this->termData((int) $context['company_id']);

        if (! $this->validateData($data, $this->termRules())) {
            return $this->invalid('sales');
        }

        if ((new CommercialReadModel())->codeExists('customer_terms', (int) $context['company_id'], $data['code'])) {
            return $this->invalid('sales', ['code' => 'Kode customer terms sudah digunakan.']);
        }

        (new CommercialWriteModel())->createCustomerTerm($data, $this->actorId());

        return $this->completed('sales', 'Customer Terms berhasil ditambahkan.');
    }

    public function createSupplierTerm(): RedirectResponse
    {
        $context = $this->manageableContext('purchasing');

        if ($context === null) {
            return $this->denied('purchasing');
        }

        $data = $this->termData((int) $context['company_id']);

        if (! $this->validateData($data, $this->termRules())) {
            return $this->invalid('purchasing');
        }

        if ((new CommercialReadModel())->codeExists('supplier_terms', (int) $context['company_id'], $data['code'])) {
            return $this->invalid('purchasing', ['code' => 'Kode supplier terms sudah digunakan.']);
        }

        (new CommercialWriteModel())->createSupplierTerm($data, $this->actorId());

        return $this->completed('purchasing', 'Supplier Terms berhasil ditambahkan.');
    }

    public function updateCustomerTerm(int $id): RedirectResponse
    {
        return $this->updateTerm('sales', $id);
    }

    public function updateSupplierTerm(int $id): RedirectResponse
    {
        return $this->updateTerm('purchasing', $id);
    }

    public function createCustomer(): RedirectResponse
    {
        return $this->createPartner('sales');
    }

    public function createSupplier(): RedirectResponse
    {
        return $this->createPartner('purchasing');
    }

    public function updateCustomer(int $id): RedirectResponse
    {
        return $this->updatePartner('sales', $id);
    }

    public function updateSupplier(int $id): RedirectResponse
    {
        return $this->updatePartner('purchasing', $id);
    }

    public function saveCustomerProfile(): RedirectResponse
    {
        return $this->saveProfile('sales');
    }

    public function saveSupplierProfile(): RedirectResponse
    {
        return $this->saveProfile('purchasing');
    }

    public function linkCustomerAddress(): RedirectResponse
    {
        return $this->linkAddress('sales');
    }

    public function linkSupplierAddress(): RedirectResponse
    {
        return $this->linkAddress('purchasing');
    }

    public function updateCustomerAddress(int $id): RedirectResponse
    {
        return $this->updateAddress('sales', $id);
    }

    public function updateSupplierAddress(int $id): RedirectResponse
    {
        return $this->updateAddress('purchasing', $id);
    }

    public function createCustomerPromotion(): RedirectResponse
    {
        return $this->createPromotion('sales');
    }

    public function createSupplierPromotion(): RedirectResponse
    {
        return $this->createPromotion('purchasing');
    }

    public function updateCustomerPromotion(int $id): RedirectResponse
    {
        return $this->updatePromotion('sales', $id);
    }

    public function updateSupplierPromotion(int $id): RedirectResponse
    {
        return $this->updatePromotion('purchasing', $id);
    }

    public function updateSalesStatus(string $master, int $id): RedirectResponse
    {
        return $this->updateStatus('sales', $master, $id);
    }

    public function updatePurchasingStatus(string $master, int $id): RedirectResponse
    {
        return $this->updateStatus('purchasing', $master, $id);
    }

    private function updateStatus(string $side, string $master, int $id): RedirectResponse
    {
        $context = $this->manageableContext($side);
        $status = (string) $this->request->getPost('status');

        if ($context === null) {
            return $this->denied($side);
        }

        if (! (new CommercialWriteModel())->updateStatus($side, $master, (int) $context['company_id'], $id, $status, $this->actorId())) {
            return $this->invalid($side, ['status' => 'Status atau commercial master tidak valid untuk company aktif.']);
        }

        return $this->completed($side, 'Status commercial master diperbarui.');
    }

    private function render(string $side, string $permission): string
    {
        $context = $this->context($permission);

        if ($context === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => $side]);
        }

        $model = new CommercialReadModel();
        $companyId = (int) $context['company_id'];
        $sales = $side === 'sales';

        return view('commercial/master', [
            'side'          => $side,
            'title'         => $sales ? 'Sales Master' : 'Purchasing Master',
            'partnerLabel'  => $sales ? 'Customer' : 'Supplier',
            'tenantContext' => $context,
            'canManage'     => $this->can($companyId, $side . '.master.manage'),
            'partners'      => $sales ? $model->customers($companyId) : $model->suppliers($companyId),
            'terms'         => $sales ? $model->customerTerms($companyId) : $model->supplierTerms($companyId),
            'promotions'    => $sales ? $model->customerPromotions($companyId) : $model->supplierPromotions($companyId),
            'partnerAddresses' => $sales ? $model->customerAddresses($companyId) : $model->supplierAddresses($companyId),
            'profiles'      => $sales ? $model->customerProfiles($companyId) : $model->supplierProfiles($companyId),
            'currencies'    => $model->currencies($companyId),
            'addresses'     => $model->addresses($companyId),
            'taxCodes'      => $model->taxCodes($companyId),
            'warehouses'    => $model->warehouses($companyId),
        ]);
    }

    private function createPartner(string $side): RedirectResponse
    {
        $context = $this->manageableContext($side);

        if ($context === null) {
            return $this->denied($side);
        }

        $termsId = (int) $this->request->getPost('default_term_id');
        $data = [
            'company_id'      => (int) $context['company_id'],
            'code'            => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'            => trim((string) $this->request->getPost('name')),
            'tax_no'          => trim((string) $this->request->getPost('tax_no')) ?: null,
            'email'           => trim((string) $this->request->getPost('email')) ?: null,
            'phone'           => trim((string) $this->request->getPost('phone')) ?: null,
            'currency_id'     => (int) $this->request->getPost('currency_id'),
            'default_term_id' => $termsId > 0 ? $termsId : null,
            'status'          => 'active',
        ];

        if ($side === 'sales') {
            $data['credit_limit'] = (string) ($this->request->getPost('credit_limit') ?: '0');
        }

        $rules = [
            'code'        => 'required|alpha_numeric_punct|max_length[40]',
            'name'        => 'required|max_length[180]',
            'tax_no'      => 'permit_empty|max_length[50]',
            'email'       => 'permit_empty|valid_email|max_length[120]',
            'phone'       => 'permit_empty|max_length[40]',
            'currency_id' => 'required|is_natural_no_zero',
        ];

        if ($side === 'sales') {
            $rules['credit_limit'] = 'required|decimal|greater_than_equal_to[0]';
        }

        if (! $this->validateData($data, $rules)) {
            return $this->invalid($side);
        }

        $table = $side === 'sales' ? 'customers' : 'suppliers';

        if ((new CommercialReadModel())->codeExists($table, (int) $context['company_id'], $data['code'])) {
            return $this->invalid($side, ['code' => ($side === 'sales' ? 'Customer' : 'Supplier') . ' code sudah digunakan.']);
        }

        $writer = new CommercialWriteModel();
        $saved = $side === 'sales' ? $writer->createCustomer($data, $this->actorId()) : $writer->createSupplier($data, $this->actorId());

        if (! $saved) {
            return $this->invalid($side, ['master' => 'Currency atau terms tidak valid untuk company aktif.']);
        }

        return $this->completed($side, ($side === 'sales' ? 'Customer' : 'Supplier') . ' berhasil ditambahkan.');
    }

    private function updatePartner(string $side, int $id): RedirectResponse
    {
        $context = $this->manageableContext($side);

        if ($context === null) {
            return $this->denied($side);
        }

        $termsId = (int) $this->request->getPost('default_term_id');
        $data = [
            'name'            => trim((string) $this->request->getPost('name')),
            'tax_no'          => trim((string) $this->request->getPost('tax_no')) ?: null,
            'email'           => trim((string) $this->request->getPost('email')) ?: null,
            'phone'           => trim((string) $this->request->getPost('phone')) ?: null,
            'currency_id'     => (int) $this->request->getPost('currency_id'),
            'default_term_id' => $termsId > 0 ? $termsId : null,
        ];
        $rules = [
            'name'        => 'required|max_length[180]',
            'tax_no'      => 'permit_empty|max_length[50]',
            'email'       => 'permit_empty|valid_email|max_length[120]',
            'phone'       => 'permit_empty|max_length[40]',
            'currency_id' => 'required|is_natural_no_zero',
        ];

        if ($side === 'sales') {
            $data['credit_limit'] = (string) ($this->request->getPost('credit_limit') ?: '0');
            $rules['credit_limit'] = 'required|decimal|greater_than_equal_to[0]';
        }

        if (! $this->validateData($data, $rules)) {
            return $this->invalid($side);
        }

        $writer = new CommercialWriteModel();
        $saved = $side === 'sales'
            ? $writer->updateCustomer((int) $context['company_id'], $id, $data, $this->actorId())
            : $writer->updateSupplier((int) $context['company_id'], $id, $data, $this->actorId());

        return $saved
            ? $this->completed($side, ($side === 'sales' ? 'Customer' : 'Supplier') . ' berhasil diperbarui.')
            : $this->invalid($side, ['master' => 'Data partner, currency, atau terms tidak valid.']);
    }

    private function updateTerm(string $side, int $id): RedirectResponse
    {
        $context = $this->manageableContext($side);

        if ($context === null) {
            return $this->denied($side);
        }

        $data = $this->termData((int) $context['company_id']);
        unset($data['company_id'], $data['code'], $data['status']);

        if (! $this->validateData($data, $this->termRulesForUpdate())) {
            return $this->invalid($side);
        }

        $writer = new CommercialWriteModel();
        $saved = $side === 'sales'
            ? $writer->updateCustomerTerm((int) $context['company_id'], $id, $data, $this->actorId())
            : $writer->updateSupplierTerm((int) $context['company_id'], $id, $data, $this->actorId());

        return $saved
            ? $this->completed($side, 'Terms berhasil diperbarui.')
            : $this->invalid($side, ['term' => 'Terms tidak valid untuk company aktif.']);
    }

    private function saveProfile(string $side): RedirectResponse
    {
        $context = $this->manageableContext($side);

        if ($context === null) {
            return $this->denied($side);
        }

        $partnerField = $side === 'sales' ? 'customer_id' : 'supplier_id';
        $taxId = (int) $this->request->getPost('default_tax_code_id');
        $warehouseId = (int) $this->request->getPost('default_warehouse_id');
        $quantityLimit = trim((string) $this->request->getPost('quantity_limit'));
        $limitDays = trim((string) $this->request->getPost('limit_days'));
        $data = [
            'company_id'           => (int) $context['company_id'],
            $partnerField          => (int) $this->request->getPost($partnerField),
            'reference_name'       => trim((string) $this->request->getPost('reference_name')) ?: null,
            'contact_name'         => trim((string) $this->request->getPost('contact_name')) ?: null,
            'description'          => trim((string) $this->request->getPost('description')) ?: null,
            'default_tax_code_id'  => $taxId > 0 ? $taxId : null,
            'default_warehouse_id' => $warehouseId > 0 ? $warehouseId : null,
            'quantity_limit'       => $quantityLimit === '' ? null : $quantityLimit,
            'limit_days'           => $limitDays === '' ? null : (int) $limitDays,
            'status'               => 'active',
        ];

        if ($side === 'sales') {
            $data['account_manager_name'] = trim((string) $this->request->getPost('account_manager_name')) ?: null;
        } else {
            $amountLimit = trim((string) $this->request->getPost('amount_limit'));
            $data['buyer_name'] = trim((string) $this->request->getPost('buyer_name')) ?: null;
            $data['amount_limit'] = $amountLimit === '' ? null : $amountLimit;
        }

        $rules = [
            $partnerField          => 'required|is_natural_no_zero',
            'reference_name'       => 'permit_empty|max_length[180]',
            'contact_name'         => 'permit_empty|max_length[120]',
            'description'          => 'permit_empty|max_length[255]',
            'default_tax_code_id'  => 'permit_empty|is_natural_no_zero',
            'default_warehouse_id' => 'permit_empty|is_natural_no_zero',
            'quantity_limit'       => 'permit_empty|decimal|greater_than_equal_to[0]',
            'limit_days'           => 'permit_empty|integer|greater_than_equal_to[0]',
        ];
        $rules[$side === 'sales' ? 'account_manager_name' : 'buyer_name'] = 'permit_empty|max_length[120]';

        if ($side === 'purchasing') {
            $rules['amount_limit'] = 'permit_empty|decimal|greater_than_equal_to[0]';
        }

        if (! $this->validateData($data, $rules)) {
            return $this->invalid($side);
        }

        $writer = new CommercialWriteModel();
        $saved = $side === 'sales' ? $writer->saveCustomerProfile($data, $this->actorId()) : $writer->saveSupplierProfile($data, $this->actorId());

        if (! $saved) {
            return $this->invalid($side, ['profile' => 'Partner, VAT, atau warehouse tidak valid untuk company aktif.']);
        }

        return $this->completed($side, 'Profil dan policy ' . strtolower($side === 'sales' ? 'customer' : 'supplier') . ' berhasil disimpan.');
    }

    private function linkAddress(string $side): RedirectResponse
    {
        $context = $this->manageableContext($side);

        if ($context === null) {
            return $this->denied($side);
        }

        $partnerField = $side === 'sales' ? 'customer_id' : 'supplier_id';
        $table = $side === 'sales' ? 'customer_addresses' : 'supplier_addresses';
        $data = [
            'company_id'   => (int) $context['company_id'],
            $partnerField  => (int) $this->request->getPost($partnerField),
            'address_id'   => (int) $this->request->getPost('address_id'),
            'address_type' => (string) $this->request->getPost('address_type'),
            'is_default'   => $this->request->getPost('is_default') === '1',
            'status'       => 'active',
        ];

        if (! $this->validateData($data, [
            $partnerField  => 'required|is_natural_no_zero',
            'address_id'   => 'required|is_natural_no_zero',
            'address_type' => 'required|in_list[billing,shipping,mailing,office,pickup]',
        ])) {
            return $this->invalid($side);
        }

        if ((new CommercialReadModel())->addressLinkExists($table, $partnerField, (int) $context['company_id'], $data[$partnerField], $data['address_id'], $data['address_type'])) {
            return $this->invalid($side, ['address' => 'Relasi alamat tersebut sudah tersedia.']);
        }

        $writer = new CommercialWriteModel();
        $saved = $side === 'sales' ? $writer->linkCustomerAddress($data, $this->actorId()) : $writer->linkSupplierAddress($data, $this->actorId());

        if (! $saved) {
            return $this->invalid($side, ['address' => 'Partner atau Address Master tidak valid untuk company aktif.']);
        }

        return $this->completed($side, 'Alamat partner berhasil ditautkan.');
    }

    private function updateAddress(string $side, int $id): RedirectResponse
    {
        $context = $this->manageableContext($side);

        if ($context === null) {
            return $this->denied($side);
        }

        $partnerField = $side === 'sales' ? 'customer_id' : 'supplier_id';
        $data = [
            $partnerField  => (int) $this->request->getPost($partnerField),
            'address_id'   => (int) $this->request->getPost('address_id'),
            'address_type' => (string) $this->request->getPost('address_type'),
            'is_default'   => $this->request->getPost('is_default') === '1',
        ];

        if (! $this->validateData($data, [
            $partnerField  => 'required|is_natural_no_zero',
            'address_id'   => 'required|is_natural_no_zero',
            'address_type' => 'required|in_list[billing,shipping,mailing,office,pickup]',
        ])) {
            return $this->invalid($side);
        }

        $writer = new CommercialWriteModel();
        $saved = $side === 'sales'
            ? $writer->updateCustomerAddress((int) $context['company_id'], $id, $data, $this->actorId())
            : $writer->updateSupplierAddress((int) $context['company_id'], $id, $data, $this->actorId());

        return $saved
            ? $this->completed($side, 'Alamat partner berhasil diperbarui.')
            : $this->invalid($side, ['address' => 'Partner atau Address Master tidak valid untuk company aktif.']);
    }

    private function createPromotion(string $side): RedirectResponse
    {
        $context = $this->manageableContext($side);

        if ($context === null) {
            return $this->denied($side);
        }

        $partnerField = $side === 'sales' ? 'customer_id' : 'supplier_id';
        $partnerId = (int) $this->request->getPost($partnerField);
        $data = [
            'company_id'     => (int) $context['company_id'],
            $partnerField    => $partnerId > 0 ? $partnerId : null,
            'code'           => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'           => trim((string) $this->request->getPost('name')),
            'discount_type'  => (string) $this->request->getPost('discount_type'),
            'discount_value' => (string) $this->request->getPost('discount_value'),
            'starts_on'      => (string) $this->request->getPost('starts_on'),
            'ends_on'        => (string) $this->request->getPost('ends_on'),
            'status'         => 'active',
        ];

        if (! $this->validateData($data, [
            'code'           => 'required|alpha_numeric_punct|max_length[40]',
            'name'           => 'required|max_length[120]',
            'discount_type'  => 'required|in_list[percentage,amount]',
            'discount_value' => 'required|decimal|greater_than[0]',
            'starts_on'      => 'required|valid_date[Y-m-d]',
            'ends_on'        => 'required|valid_date[Y-m-d]',
        ])) {
            return $this->invalid($side);
        }

        if ($data['ends_on'] < $data['starts_on']) {
            return $this->invalid($side, ['period' => 'Tanggal akhir promo tidak boleh sebelum tanggal mulai.']);
        }

        $table = $side === 'sales' ? 'customer_promotions' : 'supplier_promotions';

        if ((new CommercialReadModel())->codeExists($table, (int) $context['company_id'], $data['code'])) {
            return $this->invalid($side, ['code' => 'Kode promo sudah digunakan.']);
        }

        $writer = new CommercialWriteModel();
        $saved = $side === 'sales' ? $writer->createCustomerPromotion($data, $this->actorId()) : $writer->createSupplierPromotion($data, $this->actorId());

        if (! $saved) {
            return $this->invalid($side, ['partner' => 'Partner promo tidak valid untuk company aktif.']);
        }

        return $this->completed($side, 'Promo berhasil ditambahkan.');
    }

    private function updatePromotion(string $side, int $id): RedirectResponse
    {
        $context = $this->manageableContext($side);

        if ($context === null) {
            return $this->denied($side);
        }

        $partnerField = $side === 'sales' ? 'customer_id' : 'supplier_id';
        $partnerId = (int) $this->request->getPost($partnerField);
        $data = [
            $partnerField    => $partnerId > 0 ? $partnerId : null,
            'name'           => trim((string) $this->request->getPost('name')),
            'discount_type'  => (string) $this->request->getPost('discount_type'),
            'discount_value' => (string) $this->request->getPost('discount_value'),
            'starts_on'      => (string) $this->request->getPost('starts_on'),
            'ends_on'        => (string) $this->request->getPost('ends_on'),
        ];

        if (! $this->validateData($data, [
            'name'           => 'required|max_length[120]',
            'discount_type'  => 'required|in_list[percentage,amount]',
            'discount_value' => 'required|decimal|greater_than[0]',
            'starts_on'      => 'required|valid_date[Y-m-d]',
            'ends_on'        => 'required|valid_date[Y-m-d]',
        ]) || $data['ends_on'] < $data['starts_on']) {
            return $this->invalid($side, ['promotion' => 'Data atau periode promo tidak valid.']);
        }

        $writer = new CommercialWriteModel();
        $saved = $side === 'sales'
            ? $writer->updateCustomerPromotion((int) $context['company_id'], $id, $data, $this->actorId())
            : $writer->updateSupplierPromotion((int) $context['company_id'], $id, $data, $this->actorId());

        return $saved
            ? $this->completed($side, 'Promo berhasil diperbarui.')
            : $this->invalid($side, ['partner' => 'Partner promo tidak valid untuk company aktif.']);
    }

    /** @return array<string, mixed> */
    private function termData(int $companyId): array
    {
        return [
            'company_id'    => $companyId,
            'code'          => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'          => trim((string) $this->request->getPost('name')),
            'due_days'      => (int) $this->request->getPost('due_days'),
            'discount_days' => (int) $this->request->getPost('discount_days'),
            'discount_rate' => (string) ($this->request->getPost('discount_rate') ?: '0'),
            'status'        => 'active',
        ];
    }

    /** @return array<string, string> */
    private function termRules(): array
    {
        return [
            'code'          => 'required|alpha_numeric_punct|max_length[30]',
            'name'          => 'required|max_length[100]',
            'due_days'      => 'required|integer|greater_than_equal_to[0]',
            'discount_days' => 'required|integer|greater_than_equal_to[0]',
            'discount_rate' => 'required|decimal|greater_than_equal_to[0]',
        ];
    }

    /** @return array<string, string> */
    private function termRulesForUpdate(): array
    {
        return [
            'name'          => 'required|max_length[100]',
            'due_days'      => 'required|integer|greater_than_equal_to[0]',
            'discount_days' => 'required|integer|greater_than_equal_to[0]',
            'discount_rate' => 'required|decimal|greater_than_equal_to[0]',
        ];
    }

    /** @return array<string, mixed>|null */
    private function manageableContext(string $side): ?array
    {
        return $this->context($side . '.master.manage');
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

    private function denied(string $side): RedirectResponse
    {
        return redirect()->to(site_url('workspace'))->with('errors', ['access' => 'Anda tidak memiliki izin mengelola ' . $side . ' master pada company aktif.']);
    }

    /** @param array<string, string>|null $errors */
    private function invalid(string $side, ?array $errors = null): RedirectResponse
    {
        return redirect()->to(site_url($side . '/master'))->withInput()->with('errors', $errors ?? $this->validator->getErrors());
    }

    private function completed(string $side, string $message): RedirectResponse
    {
        return redirect()->to(site_url($side . '/master'))->with('message', $message);
    }
}
