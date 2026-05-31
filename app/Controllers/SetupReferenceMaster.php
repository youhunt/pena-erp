<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\AdministrationReadModel;
use App\Models\SetupReadModel;
use App\Services\TenantContextService;

final class SetupReferenceMaster extends BaseController
{
    public function transactionCodes(): string
    {
        return $this->render('Transaction Code', 'transaction_codes', 'setup/reference_transaction_codes');
    }

    public function departments(): string
    {
        return $this->render('Department', 'departments', 'setup/reference_departments');
    }

    public function vat(): string
    {
        return $this->render('VAT', 'tax_codes', 'setup/reference_vat');
    }

    public function addressMaster(): string
    {
        return $this->render('Address Master', 'addresses', 'setup/reference_addresses');
    }

    public function region(string $type): string
    {
        $context = $this->context();
        if ($context === null) {
            $this->response->setStatusCode(403);
            return view('workspace/module_denied', ['moduleCode' => 'setup-' . $type]);
        }

        $admin = new AdministrationReadModel();
        return view('setup/reference_region', [
            'tenantContext' => $context,
            'title' => ucfirst(str_replace('-', ' ', $type)),
            'type' => $type,
            'villages' => $admin->villageOptions(trim((string) $this->request->getGet('village_q'))),
            'villageSearch' => trim((string) $this->request->getGet('village_q')),
        ]);
    }

    private function render(string $title, string $dataKey, string $view): string
    {
        $context = $this->context();
        if ($context === null) {
            $this->response->setStatusCode(403);
            return view('workspace/module_denied', ['moduleCode' => 'setup']);
        }

        $companyId = (int) $context['company_id'];
        $model = new SetupReadModel();
        $data = [
            'tenantContext' => $context,
            'title' => $title,
            'canManage' => $this->can($companyId, 'setup.master.manage'),
            'branches' => $model->branchOptions($companyId),
            'countries' => $model->countries(),
            'villages' => (new AdministrationReadModel())->villageOptions(trim((string) $this->request->getGet('village_q'))),
            'villageSearch' => trim((string) $this->request->getGet('village_q')),
        ];

        $data[$dataKey] = match ($dataKey) {
            'transaction_codes' => $model->transactionCodes($companyId),
            'departments' => $model->departments($companyId),
            'tax_codes' => $model->taxCodes($companyId),
            'addresses' => $model->addresses($companyId),
            default => [],
        };

        return view($view, $data);
    }

    private function context(): ?array
    {
        $userId = (int) auth()->id();
        $context = (new TenantContextService())->current($userId);
        if ($context === null) {
            return null;
        }
        return $this->can((int) $context['company_id'], 'setup.master.view') ? $context : null;
    }

    private function can(int $companyId, string $permission): bool
    {
        return (new TenantAuthorizationService())->can((int) auth()->id(), $companyId, $permission);
    }
}
