<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\CommercialReadModel;
use App\Models\CommercialWriteModel;
use App\Services\TenantContextService;
use CodeIgniter\HTTP\RedirectResponse;

final class SupplierMaster extends BaseController
{
    public function index(): string
    {
        $context = $this->context();
        if ($context === null) {
            $this->response->setStatusCode(403);
            return view('workspace/module_denied', ['moduleCode' => 'supplier-master']);
        }

        $companyId = (int) $context['company_id'];
        $model = new CommercialReadModel();

        return view('commercial/supplier_master', [
            'tenantContext' => $context,
            'canManage' => (new TenantAuthorizationService())->can((int) auth()->id(), $companyId, 'purchasing.master.manage'),
            'suppliers' => $model->suppliers($companyId),
            'terms' => $model->supplierTerms($companyId),
            'currencies' => $model->currencies($companyId),
        ]);
    }

    public function store(): RedirectResponse
    {
        $context = $this->context('purchasing.master.manage');
        if ($context === null) {
            return redirect()->to(site_url('purchasing/suppliers'))->with('errors', ['Akses ditolak.']);
        }

        $termsId = (int) $this->request->getPost('default_term_id');
        $data = [
            'company_id' => (int) $context['company_id'],
            'code' => strtoupper(trim((string) $this->request->getPost('code'))),
            'name' => trim((string) $this->request->getPost('name')),
            'tax_no' => trim((string) $this->request->getPost('tax_no')) ?: null,
            'email' => trim((string) $this->request->getPost('email')) ?: null,
            'phone' => trim((string) $this->request->getPost('phone')) ?: null,
            'currency_id' => (int) $this->request->getPost('currency_id'),
            'default_term_id' => $termsId > 0 ? $termsId : null,
            'extended_data' => json_encode($this->extendedFields(), JSON_THROW_ON_ERROR),
            'status' => 'active',
        ];

        if (! $this->validateData($data, [
            'code' => 'required|alpha_numeric_punct|max_length[40]',
            'name' => 'required|max_length[180]',
            'tax_no' => 'permit_empty|max_length[50]',
            'email' => 'permit_empty|valid_email|max_length[120]',
            'phone' => 'permit_empty|max_length[40]',
            'currency_id' => 'required|is_natural_no_zero',
        ])) {
            return redirect()->to(site_url('purchasing/suppliers'))->with('errors', $this->validator->getErrors())->withInput();
        }

        if ((new CommercialReadModel())->codeExists('suppliers', (int) $context['company_id'], $data['code'])) {
            return redirect()->to(site_url('purchasing/suppliers'))->with('errors', ['code' => 'Supplier code sudah digunakan.'])->withInput();
        }

        $saved = (new CommercialWriteModel())->createSupplier($data, (int) auth()->id());
        return $saved
            ? redirect()->to(site_url('purchasing/suppliers'))->with('message', 'Supplier berhasil ditambahkan.')
            : redirect()->to(site_url('purchasing/suppliers'))->with('errors', ['master' => 'Currency atau terms tidak valid.'])->withInput();
    }

    private function context(string $permission = 'purchasing.master.view'): ?array
    {
        $userId = (int) auth()->id();
        $context = (new TenantContextService())->current($userId);
        if ($context === null) {
            return null;
        }
        return (new TenantAuthorizationService())->can($userId, (int) $context['company_id'], $permission) ? $context : null;
    }

    /** @return array<string, string> */
    private function extendedFields(): array
    {
        $fields = [
            'ref_name', 'contact_name', 'description', 'limit_amount', 'limit_qty', 'limit_days', 'employee_code', 'purchasing_name', 'bank_code_1', 'bank_account_1', 'bank_code_2', 'bank_account_2',
            'office_address', 'office_city', 'office_province', 'office_country', 'office_postal_code', 'office_contact_name', 'office_phone_number', 'office_handphone',
            'mail_address', 'mail_city', 'mail_province', 'mail_country', 'mail_postal_code', 'mail_contact_name', 'mail_phone_number', 'mail_handphone',
            'billing_address', 'billing_city', 'billing_province', 'billing_country', 'billing_postal_code', 'billing_contact_name', 'billing_phone_number', 'billing_handphone',
            'ship_to_address', 'ship_to_city', 'ship_to_province', 'ship_to_country', 'ship_to_postal_code', 'ship_to_contact_name', 'ship_to_phone_number', 'ship_to_handphone',
        ];
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = trim((string) $this->request->getPost($field));
        }
        return $data;
    }
}
