<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\PurchaseOrderLifecycleModel;
use App\Services\TenantContextService;
use CodeIgniter\HTTP\RedirectResponse;

final class PurchaseOrderLifecycle extends BaseController
{
    public function confirm(int $id): RedirectResponse
    {
        $context = $this->context('purchasing.po.confirm');

        if ($context === null) {
            return redirect()->to(site_url('purchasing/orders'))
                ->with('errors', ['access' => 'Anda tidak memiliki izin confirm Purchase Order pada company aktif.']);
        }

        try {
            $ok = (new PurchaseOrderLifecycleModel())->confirm($id, (int) $context['company_id'], (int) auth()->id());

            if (! $ok) {
                return redirect()->to(site_url('purchasing/orders'))
                    ->with('errors', ['status' => 'Purchase Order tidak ditemukan, bukan draft, atau tidak memiliki item.']);
            }

            return redirect()->to(site_url('purchasing/orders'))
                ->with('message', 'Purchase Order berhasil dikonfirmasi.');
        } catch (\Throwable $e) {
            return redirect()->to(site_url('purchasing/orders'))
                ->with('errors', ['error' => $e->getMessage()]);
        }
    }

    /** @return array<string, mixed>|null */
    private function context(string $permission): ?array
    {
        $context = (new TenantContextService())->current((int) auth()->id());

        return $context !== null && (new TenantAuthorizationService())->can((int) auth()->id(), (int) $context['company_id'], $permission)
            ? $context
            : null;
    }
}
