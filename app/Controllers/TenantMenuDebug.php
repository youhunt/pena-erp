<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\TenantContextService;
use App\Services\TenantMenuService;

final class TenantMenuDebug extends BaseController
{
    public function index(): string
    {
        $userId = (int) auth()->id();
        $context = (new TenantContextService())->current($userId);

        if ($context === null) {
            return view('workspace/module_denied', ['moduleCode' => 'administration']);
        }

        $companyId = (int) $context['company_id'];
        $service = new TenantMenuService();

        return view('administration/menu_debug', [
            'tenantContext' => $context,
            'menus' => $service->accessibleMenus($userId, $companyId),
            'debugRows' => $service->debugMenuAccess($userId, $companyId),
            'userId' => $userId,
            'companyId' => $companyId,
        ]);
    }
}
