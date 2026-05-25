<?php

namespace App\Controllers;

use App\Services\TenantContextService;

class Home extends BaseController
{
    public function index(): string
    {
        return view('dashboard/index', [
            'tenantContext' => (new TenantContextService())->current((int) auth()->id()),
        ]);
    }
}
