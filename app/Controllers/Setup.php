<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\AdministrationReadModel;
use App\Models\SetupReadModel;
use App\Models\SetupWriteModel;
use App\Services\TenantContextService;
use CodeIgniter\HTTP\RedirectResponse;

final class Setup extends BaseController
{
    public function index(): string
    {
        $context = $this->context('setup.master.view');

        if ($context === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => 'setup']);
        }

        $companyId = (int) $context['company_id'];
        $model = new SetupReadModel();

        return view('setup/index', [
            'tenantContext'    => $context,
            'canManage'        => $this->can($companyId, 'setup.master.manage'),
            'departments'      => $model->departments($companyId),
            'transactionCodes' => $model->transactionCodes($companyId),
            'addresses'        => $model->addresses($companyId),
            'currencies'       => $model->currencies($companyId),
            'taxCodes'         => $model->taxCodes($companyId),
            'countries'        => $model->countries(),
            'branches'         => $model->branchOptions($companyId),
            'villages'         => (new AdministrationReadModel())->villageOptions(trim((string) $this->request->getGet('village_q'))),
            'villageSearch'    => trim((string) $this->request->getGet('village_q')),
        ]);
    }

    public function createDepartment(): RedirectResponse
    {
        $context = $this->manageableContext();

        if ($context === null) {
            return $this->denied();
        }

        $data = $this->codedData((int) $context['company_id'], 'name');

        if (! $this->validateData($data, ['code' => 'required|alpha_dash|max_length[30]', 'name' => 'required|max_length[120]'])) {
            return $this->invalid();
        }

        if ((new SetupReadModel())->codeExists('departments', (int) $context['company_id'], $data['code'])) {
            return $this->invalid(['code' => 'Kode department sudah digunakan.']);
        }

        (new SetupWriteModel())->createDepartment($data + ['status' => 'active'], $this->actorId());

        return $this->completed('Department berhasil ditambahkan.');
    }

    public function createCurrency(): RedirectResponse
    {
        $context = $this->manageableContext();

        if ($context === null) {
            return $this->denied();
        }

        $data = [
            'company_id' => (int) $context['company_id'],
            'code'       => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'       => trim((string) $this->request->getPost('name')),
            'is_base'    => $this->request->getPost('is_base') === '1',
            'status'     => 'active',
        ];

        if (! $this->validateData($data, ['code' => 'required|alpha|exact_length[3]', 'name' => 'required|max_length[60]'])) {
            return $this->invalid();
        }

        if ((new SetupReadModel())->codeExists('currencies', (int) $context['company_id'], $data['code'])) {
            return $this->invalid(['code' => 'Mata uang sudah tersedia.']);
        }

        (new SetupWriteModel())->createCurrency($data, $this->actorId());

        return $this->completed('Currency berhasil ditambahkan.');
    }

    public function createTaxCode(): RedirectResponse
    {
        $context = $this->manageableContext();

        if ($context === null) {
            return $this->denied();
        }

        $data = [
            'company_id' => (int) $context['company_id'],
            'code'       => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'       => trim((string) $this->request->getPost('name')),
            'tax_type'   => (string) $this->request->getPost('tax_type'),
            'rate'       => (string) $this->request->getPost('rate'),
            'status'     => 'active',
        ];

        if (! $this->validateData($data, [
            'code'     => 'required|alpha_numeric_punct|max_length[30]',
            'name'     => 'required|max_length[80]',
            'tax_type' => 'required|in_list[input,output,both]',
            'rate'     => 'required|decimal',
        ])) {
            return $this->invalid();
        }

        if ((new SetupReadModel())->codeExists('tax_codes', (int) $context['company_id'], $data['code'])) {
            return $this->invalid(['code' => 'Kode VAT sudah digunakan.']);
        }

        (new SetupWriteModel())->createTaxCode($data, $this->actorId());

        return $this->completed('VAT berhasil ditambahkan.');
    }

    public function createTransactionCode(): RedirectResponse
    {
        $context = $this->manageableContext();

        if ($context === null) {
            return $this->denied();
        }

        $branchId = (int) $this->request->getPost('branch_id');
        $data = [
            'company_id'    => (int) $context['company_id'],
            'branch_id'     => $branchId > 0 ? $branchId : null,
            'module'        => strtolower(trim((string) $this->request->getPost('module'))),
            'code'          => strtoupper(trim((string) $this->request->getPost('code'))),
            'prefix'        => strtoupper(trim((string) $this->request->getPost('prefix'))),
            'next_number'   => 1,
            'number_length' => (int) $this->request->getPost('number_length'),
            'reset_rule'    => (string) $this->request->getPost('reset_rule'),
            'status'        => 'active',
        ];

        if (! $this->validateData($data, [
            'module'        => 'required|alpha_dash|max_length[30]',
            'code'          => 'required|alpha_numeric_punct|max_length[40]',
            'prefix'        => 'required|alpha_numeric_punct|max_length[30]',
            'number_length' => 'required|integer|greater_than_equal_to[3]|less_than_equal_to[12]',
            'reset_rule'    => 'required|in_list[never,yearly,monthly,daily]',
        ])) {
            return $this->invalid();
        }

        if ((new SetupReadModel())->codeExists('transaction_codes', (int) $context['company_id'], $data['code'], $data['branch_id'])) {
            return $this->invalid(['code' => 'Transaction Code sudah digunakan pada scope tersebut.']);
        }

        if (! (new SetupWriteModel())->createTransactionCode($data, $this->actorId())) {
            return $this->invalid(['branch_id' => 'Site tidak valid untuk company aktif.']);
        }

        return $this->completed('Transaction Code berhasil ditambahkan.');
    }

    public function createAddress(): RedirectResponse
    {
        $context = $this->manageableContext();

        if ($context === null) {
            return $this->denied();
        }

        $villageId = (int) $this->request->getPost('village_id');
        $data = [
            'company_id'    => (int) $context['company_id'],
            'code'          => strtoupper(trim((string) $this->request->getPost('code'))),
            'label'         => trim((string) $this->request->getPost('label')),
            'address_line1' => trim((string) $this->request->getPost('address_line1')),
            'country_id'    => (int) $this->request->getPost('country_id'),
            'village_id'    => $villageId > 0 ? $villageId : null,
            'postal_code'   => trim((string) $this->request->getPost('postal_code')) ?: null,
            'status'        => 'active',
        ];

        if (! $this->validateData($data, [
            'code'          => 'required|alpha_dash|max_length[40]',
            'label'         => 'required|max_length[120]',
            'address_line1' => 'required',
            'country_id'    => 'required|is_natural_no_zero',
            'postal_code'   => 'permit_empty|max_length[10]',
        ])) {
            return $this->invalid();
        }

        if ((new SetupReadModel())->codeExists('addresses', (int) $context['company_id'], $data['code'])) {
            return $this->invalid(['code' => 'Kode alamat sudah digunakan.']);
        }

        if (! (new SetupWriteModel())->createAddress($data, $this->actorId())) {
            return $this->invalid(['location' => 'Country atau wilayah tidak valid.']);
        }

        return $this->completed('Address Master berhasil ditambahkan.');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function manageableContext(): ?array
    {
        return $this->context('setup.master.manage');
    }

    /**
     * @return array<string, mixed>|null
     */
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

    /**
     * @return array<string, int|string>
     */
    private function codedData(int $companyId, string $nameField): array
    {
        return [
            'company_id' => $companyId,
            'code'       => strtoupper(trim((string) $this->request->getPost('code'))),
            $nameField   => trim((string) $this->request->getPost($nameField)),
        ];
    }

    private function actorId(): int
    {
        return (int) auth()->id();
    }

    private function denied(): RedirectResponse
    {
        return redirect()->to(site_url('workspace'))->with('errors', ['access' => 'Anda tidak memiliki izin mengelola setup master pada company aktif.']);
    }

    /**
     * @param array<string, string>|null $errors
     */
    private function invalid(?array $errors = null): RedirectResponse
    {
        return redirect()->back()->withInput()->with('errors', $errors ?? $this->validator->getErrors());
    }

    private function completed(string $message): RedirectResponse
    {
        return redirect()->to(site_url('setup'))->with('message', $message);
    }
}
