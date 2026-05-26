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
        $model  = new AdministrationReadModel();
        $search = trim((string) $this->request->getGet('q'));

        return view('administration/regions', [
            'villages' => $model->villages($search),
            'counts'   => $model->regionCounts(),
            'search'   => $search,
        ]);
    }

    public function newCompany(): string
    {
        $search = trim((string) $this->request->getGet('village_q'));

        return view('administration/company_form', [
            'company'       => null,
            'villages'      => (new AdministrationReadModel())->villageOptions($search),
            'villageSearch' => $search,
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
            'company'       => $company,
            'villages'      => (new AdministrationReadModel())->villageOptions(trim((string) $this->request->getGet('village_q')), (int) ($company['village_id'] ?? 0) ?: null),
            'villageSearch' => trim((string) $this->request->getGet('village_q')),
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
        $model  = new AdministrationReadModel();
        $search = trim((string) $this->request->getGet('village_q'));

        return view('administration/branch_form', [
            'branch'        => null,
            'companies'     => $model->companies(),
            'villages'      => $model->villageOptions($search),
            'villageSearch' => $search,
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
            'branch'        => $branch,
            'companies'     => $model->companies(),
            'villages'      => $model->villageOptions(trim((string) $this->request->getGet('village_q')), (int) ($branch['village_id'] ?? 0) ?: null),
            'villageSearch' => trim((string) $this->request->getGet('village_q')),
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
        $data['company_id'] = (int) $branch['company_id'];

        if (! $this->validateData($data, [
            'name'       => 'required|max_length[150]',
            'status'     => 'required|in_list[active,inactive]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
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
            'branches'    => $model->branchOptions(),
        ]);
    }

    public function rbac(): string
    {
        $model = new AdministrationReadModel();

        return view('administration/rbac', [
            'companies'   => $model->companies(),
            'roles'       => $model->roles(),
            'permissions' => $model->permissions(),
            'grants'      => $model->rolePermissionGrants(),
        ]);
    }

    public function audit(): string
    {
        $model = new AdministrationReadModel();
        $companyValue = (string) $this->request->getGet('company_id');
        $companyId = $companyValue === '' ? null : (int) $companyValue;
        $eventType = trim((string) $this->request->getGet('event_type'));
        $search = trim((string) $this->request->getGet('q'));

        return view('administration/audit', [
            'logs'       => $model->auditLogs($companyId, $eventType, $search),
            'companies'  => $model->companies(),
            'eventTypes' => $model->auditEventTypes(),
            'companyId'  => $companyId,
            'eventType'  => $eventType,
            'search'     => $search,
        ]);
    }

    public function createRole(): RedirectResponse
    {
        $data = [
            'company_id' => (int) $this->request->getPost('company_id'),
            'code'       => strtolower(trim((string) $this->request->getPost('code'))),
            'name'       => trim((string) $this->request->getPost('name')),
            'status'     => (string) $this->request->getPost('status'),
        ];
        $model = new AdministrationReadModel();

        if (! $this->validateData($data, [
            'company_id' => 'required|is_natural_no_zero',
            'code'       => 'required|alpha_dash|max_length[50]',
            'name'       => 'required|max_length[100]',
            'status'     => 'required|in_list[active,inactive]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($model->company($data['company_id']) === null) {
            return redirect()->back()->withInput()->with('errors', ['company_id' => 'Company tidak ditemukan.']);
        }

        if ($model->roleCodeExists($data['company_id'], $data['code'])) {
            return redirect()->back()->withInput()->with('errors', ['code' => 'Kode role sudah digunakan pada company ini.']);
        }

        (new AdministrationWriteModel())->createRole($data, $this->actorId());

        return redirect()->to(site_url('administration/rbac'))->with('message', 'Role tenant berhasil ditambahkan.');
    }

    public function createPermission(): RedirectResponse
    {
        $data = [
            'company_id' => (int) $this->request->getPost('company_id'),
            'code'       => strtolower(trim((string) $this->request->getPost('code'))),
            'name'       => trim((string) $this->request->getPost('name')),
            'module'     => strtolower(trim((string) $this->request->getPost('module'))),
        ];
        $model = new AdministrationReadModel();

        if (! $this->validateData($data, [
            'company_id' => 'required|is_natural_no_zero',
            'code'       => 'required|regex_match[/^[a-z0-9_.-]+$/]|max_length[100]',
            'name'       => 'required|max_length[120]',
            'module'     => 'required|alpha_dash|max_length[40]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($model->company($data['company_id']) === null) {
            return redirect()->back()->withInput()->with('errors', ['company_id' => 'Company tidak ditemukan.']);
        }

        if ($model->permissionCodeExists($data['company_id'], $data['code'])) {
            return redirect()->back()->withInput()->with('errors', ['code' => 'Kode permission sudah digunakan pada company ini.']);
        }

        (new AdministrationWriteModel())->createPermission($data, $this->actorId());

        return redirect()->to(site_url('administration/rbac'))->with('message', 'Permission tenant berhasil ditambahkan.');
    }

    public function grantPermission(): RedirectResponse
    {
        $data = [
            'company_id'    => $this->request->getPost('company_id'),
            'role_id'       => $this->request->getPost('role_id'),
            'permission_id' => $this->request->getPost('permission_id'),
        ];

        if (! $this->validateData($data, [
            'company_id'    => 'required|is_natural_no_zero',
            'role_id'       => 'required|is_natural_no_zero',
            'permission_id' => 'required|is_natural_no_zero',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $saved = (new AdministrationWriteModel())->grantRolePermission(
            (int) $data['company_id'],
            (int) $data['role_id'],
            (int) $data['permission_id'],
            $this->actorId(),
        );

        if (! $saved) {
            return redirect()->back()->withInput()->with('errors', ['permission_id' => 'Role dan permission harus berasal dari company aktif yang sama.']);
        }

        return redirect()->to(site_url('administration/rbac'))->with('message', 'Permission berhasil diberikan ke role.');
    }

    public function revokePermission(): RedirectResponse
    {
        $data = [
            'company_id' => $this->request->getPost('company_id'),
            'grant_id'   => $this->request->getPost('grant_id'),
        ];

        if (! $this->validateData($data, [
            'company_id' => 'required|is_natural_no_zero',
            'grant_id'   => 'required|is_natural_no_zero',
        ])) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $deleted = (new AdministrationWriteModel())->revokeRolePermission(
            (int) $data['company_id'],
            (int) $data['grant_id'],
            $this->actorId(),
        );

        if (! $deleted) {
            return redirect()->back()->with('errors', ['grant_id' => 'Grant permission tidak ditemukan pada company tersebut.']);
        }

        return redirect()->to(site_url('administration/rbac'))->with('message', 'Permission berhasil dicabut dari role.');
    }

    public function assignAccess(): RedirectResponse
    {
        $data = [
            'company_id' => $this->request->getPost('company_id'),
            'user_id'    => $this->request->getPost('user_id'),
            'role_id'    => $this->request->getPost('role_id'),
            'branch_id'  => $this->request->getPost('branch_id') ?: null,
        ];

        if (! $this->validateData($data, [
            'company_id' => 'required|is_natural_no_zero',
            'user_id'    => 'required|is_natural_no_zero',
            'role_id'    => 'required|is_natural_no_zero',
            'branch_id'  => 'permit_empty|is_natural_no_zero',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $saved = (new AdministrationWriteModel())->assignRole(
            (int) $data['company_id'],
            (int) $data['user_id'],
            (int) $data['role_id'],
            $data['branch_id'] === null ? null : (int) $data['branch_id'],
            $this->actorId(),
        );

        if (! $saved) {
            return redirect()->back()->withInput()->with('errors', ['role_id' => 'Role atau branch bukan milik company yang dipilih.']);
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
