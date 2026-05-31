<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\CommercialReadModel;
use App\Services\TenantContextService;

final class CommercialReferenceMaster extends BaseController
{
    public function customerTerms(): string
    {
        return $this->terms('sales');
    }

    public function supplierTerms(): string
    {
        return $this->terms('purchasing');
    }

    public function customerPromotions(): string
    {
        return $this->promotions('sales');
    }

    public function supplierPromotions(): string
    {
        return $this->promotions('purchasing');
    }

    public function customerAddresses(): string
    {
        return $this->addresses('sales');
    }

    public function supplierAddresses(): string
    {
        return $this->addresses('purchasing');
    }

    private function terms(string $side): string
    {
        $context = $this->context($side . '.master.view');
        if ($context === null) {
            $this->response->setStatusCode(403);
            return view('workspace/module_denied', ['moduleCode' => $side . '-terms']);
        }

        $companyId = (int) $context['company_id'];
        $model = new CommercialReadModel();
        $sales = $side === 'sales';

        return view('commercial/reference_terms', [
            'tenantContext' => $context,
            'title' => $sales ? 'Customer Terms' : 'Supplier Terms',
            'side' => $side,
            'baseRoute' => $sales ? 'sales/master' : 'purchasing/master',
            'canManage' => $this->can($companyId, $side . '.master.manage'),
            'terms' => $sales ? $model->customerTerms($companyId) : $model->supplierTerms($companyId),
        ]);
    }

    private function promotions(string $side): string
    {
        $context = $this->context($side . '.master.view');
        if ($context === null) {
            $this->response->setStatusCode(403);
            return view('workspace/module_denied', ['moduleCode' => $side . '-promotions']);
        }

        $companyId = (int) $context['company_id'];
        $model = new CommercialReadModel();
        $sales = $side === 'sales';

        return view('commercial/reference_promotions', [
            'tenantContext' => $context,
            'title' => $sales ? 'Customer Promo' : 'Supplier Promo',
            'side' => $side,
            'baseRoute' => $sales ? 'sales/master' : 'purchasing/master',
            'canManage' => $this->can($companyId, $side . '.master.manage'),
            'promotions' => $sales ? $model->customerPromotions($companyId) : $model->supplierPromotions($companyId),
            'partners' => $sales ? $model->customers($companyId) : $model->suppliers($companyId),
        ]);
    }

    private function addresses(string $side): string
    {
        $context = $this->context($side . '.master.view');
        if ($context === null) {
            $this->response->setStatusCode(403);
            return view('workspace/module_denied', ['moduleCode' => $side . '-addresses']);
        }

        $companyId = (int) $context['company_id'];
        $model = new CommercialReadModel();
        $sales = $side === 'sales';

        return view('commercial/reference_addresses', [
            'tenantContext' => $context,
            'title' => $sales ? 'Customer Address' : 'Supplier Address',
            'side' => $side,
            'baseRoute' => $sales ? 'sales/master' : 'purchasing/master',
            'canManage' => $this->can($companyId, $side . '.master.manage'),
            'partnerField' => $sales ? 'customer_id' : 'supplier_id',
            'partnerAddresses' => $sales ? $model->customerAddresses($companyId) : $model->supplierAddresses($companyId),
            'partners' => $sales ? $model->customers($companyId) : $model->suppliers($companyId),
            'addresses' => $model->addresses($companyId),
        ]);
    }

    private function context(string $permission): ?array
    {
        $userId = (int) auth()->id();
        $context = (new TenantContextService())->current($userId);
        if ($context === null) {
            return null;
        }

        return $this->can((int) $context['company_id'], $permission) ? $context : null;
    }

    private function can(int $companyId, string $permission): bool
    {
        return (new TenantAuthorizationService())->can((int) auth()->id(), $companyId, $permission);
    }
}
