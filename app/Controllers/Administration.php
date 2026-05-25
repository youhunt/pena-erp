<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AdministrationReadModel;
use App\Models\AdministrationWriteModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Exceptions\PageNotFoundException;

final class Administration extends BaseController
{
    public function companies(): string
    {
        return view('administration/companies', [
            'companies' => (new AdministrationReadModel())->companies(),
        ]);
    }

    public function branches(): string
    {
        return view('administration/branches', [
            'branches' => (new AdministrationReadModel())->branches(),
        ]);
    }

    public function regions(): string
    {
        return view('administration/regions', [
            'villages' => (new AdministrationReadModel())->villages(),
        ]);
    }

    public function newCompany(): string
    {
        return view('administration/company_form', [
            'company'  => null,
            'villages' => (new AdministrationReadModel())->villageOptions(),
        ]);
    }

    public function createCompany(): RedirectResponse
    {
        $data = $this->companyData();

        if (! $this->validateData($data, [
            'code'          => 'required|alpha_numeric|max_length[30]|is_unique[companies.code]',
            'name'          => 'required|max_length[150]',
            'base_currency' => 'required|alpha|max_length[3]',
            'timezone'      => 'required|max_length[50]',
            'status'        => 'required|in_list[active,inactive]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new AdministrationWriteModel())->createCompany($data, $this->actorId());

        return redirect()->to(site_url('administration/companies'))->with('message', 'Company berhasil ditambahkan.');
    }

    public function editCompany(int $id): string
    {
        $company = (new AdministrationReadModel())->company($id);

        if ($company === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('administration/company_form', [
            'company'  => $company,
            'villages' => (new AdministrationReadModel())->villageOptions(),
        ]);
    }

    public function updateCompany(int $id): RedirectResponse
    {
        $model = new AdministrationReadModel();

        if ($model->company($id) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data = $this->companyData(false);

        if (! $this->validateData($data, [
            'name'          => 'required|max_length[150]',
            'base_currency' => 'required|alpha|max_length[3]',
            'timezone'      => 'required|max_length[50]',
            'status'        => 'required|in_list[active,inactive]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new AdministrationWriteModel())->updateCompany($id, $data, $this->actorId());

        return redirect()->to(site_url('administration/companies'))->with('message', 'Company berhasil diperbarui.');
    }

    public function newBranch(): string
    {
        $model = new AdministrationReadModel();

        return view('administration/branch_form', [
            'branch'    => null,
            'companies' => $model->companies(),
            'villages'  => $model->villageOptions(),
        ]);
    }

    public function createBranch(): RedirectResponse
    {
        $data = $this->branchData();
        $model = new AdministrationReadModel();

        if (! $this->validateData($data, [
            'company_id' => 'required|is_natural_no_zero',
            'code'       => 'required|alpha_numeric|max_length[30]',
            'name'       => 'required|max_length[150]',
            'status'     => 'required|in_list[active,inactive]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($model->company((int) $data['company_id']) === null) {
            return redirect()->back()->withInput()->with('errors', ['company_id' => 'Company tidak ditemukan.']);
        }

        if ($model->branchCodeExists((int) $data['company_id'], (string) $data['code'])) {
            return redirect()->back()->withInput()->with('errors', ['code' => 'Kode branch sudah digunakan pada company ini.']);
        }

        (new AdministrationWriteModel())->createBranch($data, $this->actorId());

        return redirect()->to(site_url('administration/branches'))->with('message', 'Branch berhasil ditambahkan.');
    }

    public function editBranch(int $id): string
    {
        $model  = new AdministrationReadModel();
        $branch = $model->branch($id);

        if ($branch === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('administration/branch_form', [
            'branch'    => $branch,
            'companies' => $model->companies(),
            'villages'  => $model->villageOptions(),
        ]);
    }

    public function updateBranch(int $id): RedirectResponse
    {
        $model  = new AdministrationReadModel();
        $branch = $model->branch($id);

        if ($branch === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data = $this->branchData(false);

        if (! $this->validateData($data, [
            'company_id' => 'required|is_natural_no_zero',
            'name'       => 'required|max_length[150]',
            'status'     => 'required|in_list[active,inactive]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($model->company((int) $data['company_id']) === null) {
            return redirect()->back()->withInput()->with('errors', ['company_id' => 'Company tidak ditemukan.']);
        }

        if ($model->branchCodeExists((int) $data['company_id'], (string) $branch['code'], $id)) {
            return redirect()->back()->withInput()->with('errors', ['code' => 'Kode branch sudah digunakan pada company ini.']);
        }

        (new AdministrationWriteModel())->updateBranch($id, $data, $this->actorId());

        return redirect()->to(site_url('administration/branches'))->with('message', 'Branch berhasil diperbarui.');
    }

    public function access(): string
    {
        $model = new AdministrationReadModel();

        return view('administration/access', [
            'assignments' => $model->tenantAccessAssignments(),
            'companies'   => $model->companies(),
            'roles'       => $model->roles(),
            'users'       => $model->users(),
        ]);
    }

    public function assignAccess(): RedirectResponse
    {
        $data = [
            'company_id' => $this->request->getPost('company_id'),
            'user_id'    => $this->request->getPost('user_id'),
            'role_id'    => $this->request->getPost('role_id'),
        ];

        if (! $this->validateData($data, [
            'company_id' => 'required|is_natural_no_zero',
            'user_id'    => 'required|is_natural_no_zero',
            'role_id'    => 'required|is_natural_no_zero',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $saved = (new AdministrationWriteModel())->assignRole(
            (int) $data['company_id'],
            (int) $data['user_id'],
            (int) $data['role_id'],
            $this->actorId(),
        );

        if (! $saved) {
            return redirect()->back()->withInput()->with('errors', ['role_id' => 'Role bukan milik company yang dipilih.']);
        }

        return redirect()->to(site_url('administration/access'))->with('message', 'Akses user berhasil diberikan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function companyData(bool $withCode = true): array
    {
        $data = [
            'name'          => trim((string) $this->request->getPost('name')),
            'tax_no'        => trim((string) $this->request->getPost('tax_no')) ?: null,
            'address'       => trim((string) $this->request->getPost('address')) ?: null,
            'village_id'    => $this->nullableInt('village_id'),
            'postal_code'   => trim((string) $this->request->getPost('postal_code')) ?: null,
            'base_currency' => strtoupper(trim((string) $this->request->getPost('base_currency'))),
            'timezone'      => trim((string) $this->request->getPost('timezone')),
            'status'        => (string) $this->request->getPost('status'),
        ];

        if ($withCode) {
            $data['code'] = strtoupper(trim((string) $this->request->getPost('code')));
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function branchData(bool $withCode = true): array
    {
        $data = [
            'company_id'     => $this->request->getPost('company_id'),
            'name'           => trim((string) $this->request->getPost('name')),
            'address'        => trim((string) $this->request->getPost('address')) ?: null,
            'village_id'     => $this->nullableInt('village_id'),
            'postal_code'    => trim((string) $this->request->getPost('postal_code')) ?: null,
            'is_head_office' => $this->request->getPost('is_head_office') === '1',
            'status'         => (string) $this->request->getPost('status'),
        ];

        if ($withCode) {
            $data['code'] = strtoupper(trim((string) $this->request->getPost('code')));
        }

        return $data;
    }

    private function nullableInt(string $field): ?int
    {
        $value = $this->request->getPost($field);

        return $value === null || $value === '' ? null : (int) $value;
    }

    private function actorId(): int
    {
        return (int) auth()->id();
    }
}
