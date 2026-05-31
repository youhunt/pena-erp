<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\MasterImportReadModel;
use App\Services\MasterImport\MasterImportCatalog;
use App\Services\TenantContextService;

final class MasterImport extends BaseController
{
    public function index(): string
    {
        $context = $this->authorizedContext();

        if ($context === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => 'master-import']);
        }

        $companyId = (int) $context['company_id'];

        return view('master_import/index', [
            'tenantContext' => $context,
            'catalog' => (new MasterImportCatalog())->all(),
            'batches' => (new MasterImportReadModel())->batches($companyId),
        ]);
    }

    public function show(int $batchId): string
    {
        $context = $this->authorizedContext();

        if ($context === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => 'master-import']);
        }

        $companyId = (int) $context['company_id'];
        $model = new MasterImportReadModel();
        $batch = $model->batch($companyId, $batchId);

        if ($batch === null) {
            $this->response->setStatusCode(404);

            return view('workspace/module_denied', ['moduleCode' => 'master-import']);
        }

        return view('master_import/show', [
            'tenantContext' => $context,
            'batch' => $batch,
            'rows' => $model->rows($companyId, $batchId),
        ]);
    }

    private function authorizedContext(): ?array
    {
        $userId = (int) auth()->id();
        $context = (new TenantContextService())->current($userId);

        if ($context === null) {
            return null;
        }

        $companyId = (int) $context['company_id'];
        $authz = new TenantAuthorizationService();

        foreach (['setup.master.manage', 'inventory.master.manage', 'sales.master.manage', 'purchasing.master.manage', 'finance.master.manage'] as $permission) {
            if ($authz->can($userId, $companyId, $permission)) {
                return $context;
            }
        }

        return null;
    }
}
