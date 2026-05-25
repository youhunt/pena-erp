<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\AdministrationReadModel;
use CodeIgniter\Exceptions\PageNotFoundException;

final class Workspace extends BaseController
{
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

        return view('workspace/index', ['company' => $company]);
    }
}
