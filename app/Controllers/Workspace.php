<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\AdministrationReadModel;
use App\Models\DocumentProcessingReadModel;
use App\Services\TenantMenuService;
use App\Services\TenantContextService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

final class Workspace extends BaseController
{
    public function chooser(): string
    {
        $service = new TenantContextService();
        $userId = (int) auth()->id();

        return view('workspace/chooser', [
            'contexts' => $service->availableContexts($userId),
            'active'   => $service->current($userId),
        ]);
    }

    public function select(): RedirectResponse
    {
        $companyId = (int) $this->request->getPost('company_id');
        $branchValue = $this->request->getPost('branch_id');
        $branchId = $branchValue === null || $branchValue === '' ? null : (int) $branchValue;
        $context = (new TenantContextService())->activate((int) auth()->id(), $companyId, $branchId);

        if ($context === null) {
            return redirect()->to(site_url('workspace'))
                ->with('errors', ['Context company/branch tidak tersedia untuk user ini.']);
        }

        return redirect()->to(site_url('workspace/' . $companyId))
            ->with('message', 'Workspace aktif berhasil diganti.');
    }

    public function index(int $companyId): string
    {
        $company = (new AdministrationReadModel())->company($companyId);

        if ($company === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $allowed = (new TenantAuthorizationService())->can(
            (int) auth()->id(),
            $companyId,
            'company.dashboard.view',
        );

        if (! $allowed) {
            $this->response->setStatusCode(403);

            return view('workspace/denied', ['company' => $company]);
        }

        $contextService = new TenantContextService();
        $context = $contextService->current((int) auth()->id());

        if ($context === null || (int) $context['company_id'] !== $companyId) {
            $context = $contextService->activate((int) auth()->id(), $companyId);
        }

        return view('workspace/index', [
            'company' => $company,
            'context' => $context,
            'menus'   => (new TenantMenuService())->accessibleMenus((int) auth()->id(), $companyId),
        ]);
    }

    public function module(string $moduleCode): string
    {
        $userId = (int) auth()->id();
        $context = (new TenantContextService())->current($userId);

        if ($context === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => $moduleCode]);
        }

        $menus = (new TenantMenuService())->accessibleMenus($userId, (int) $context['company_id']);
        $menu = array_values(array_filter(
            $menus,
            static fn (array $item): bool => $item['code'] === $moduleCode,
        ))[0] ?? null;

        if ($menu === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => $moduleCode]);
        }

        if ($moduleCode === 'documents') {
            return view('document_processing/index', [
                'tenantContext' => $context,
                'documents' => (new DocumentProcessingReadModel())->documents((int) $context['company_id']),
            ]);
        }

        return view('workspace/module', [
            'context' => $context,
            'menu'    => $menu,
            'menus'   => $menus,
        ]);
    }
}
