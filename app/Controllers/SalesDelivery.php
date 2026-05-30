<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\SalesDeliveryReadModel;
use App\Models\SalesDeliveryWriteModel;
use App\Services\TenantContextService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

final class SalesDelivery extends BaseController
{
    public function index(): string
    {
        $context = $this->context('sales.delivery.view');

        if ($context === null) {
            $this->response->setStatusCode(403);
            return view('workspace/module_denied', ['moduleCode' => 'sales']);
        }

        $companyId = (int) $context['company_id'];
        $reader = new SalesDeliveryReadModel();

        return view('sales/deliveries', [
            'tenantContext' => $context,
            'canManage' => $this->can($companyId, 'sales.delivery.manage'),
            'deliveries' => $reader->listDeliveries($companyId),
            'salesOrders' => $reader->listSalesOrders($companyId),
            'warehouses' => $reader->listActiveWarehouses($companyId),
        ]);
    }

    public function soItems(int $salesOrderId): ResponseInterface
    {
        $context = $this->context('sales.delivery.view');
        if ($context === null) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'access_denied']);
        }

        return $this->response->setJSON((new SalesDeliveryReadModel())->salesOrderItemsForAjax((int) $context['company_id'], $salesOrderId));
    }

    public function create(): RedirectResponse
    {
        $context = $this->context('sales.delivery.manage');
        if ($context === null) {
            return $this->denied();
        }

        try {
            $result = (new SalesDeliveryWriteModel())->createDraftDelivery([
                'company_id'     => (int) $context['company_id'],
                'branch_id'      => isset($context['branch_id']) ? (int) $context['branch_id'] : null,
                'actor_id'       => $this->actorId(),
                'sales_order_id' => (int) $this->request->getPost('sales_order_id'),
                'warehouse_id'   => (int) $this->request->getPost('warehouse_id'),
                'items'          => $this->deliveryItems(),
            ]);

            return redirect()->to(site_url('sales/deliveries'))->with('message', 'Delivery Order draft dibuat: ' . $result['delivery_number']);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('errors', ['error' => $e->getMessage()]);
        }
    }

    public function post(int $id): RedirectResponse
    {
        $context = $this->context('sales.delivery.manage');
        if ($context === null) {
            return $this->denied();
        }

        try {
            $result = (new SalesDeliveryWriteModel())->postDelivery($id, (int) $context['company_id'], $this->actorId());
            return redirect()->to(site_url('sales/deliveries'))->with('message', 'Delivery Order berhasil diposting: ' . $result['delivery_number']);
        } catch (\Throwable $e) {
            return redirect()->back()->with('errors', ['error' => $e->getMessage()]);
        }
    }

    /** @return list<array{sales_order_item_id:int, qty_delivered:string}> */
    private function deliveryItems(): array
    {
        $posted = $this->request->getPost('items');
        if (! is_array($posted)) {
            return [];
        }

        $items = [];
        foreach ($posted as $item) {
            if (! is_array($item)) {
                continue;
            }

            $itemId = (int) ($item['sales_order_item_id'] ?? 0);
            $qty = trim((string) ($item['qty_delivered'] ?? ''));

            if ($itemId <= 0 && $qty === '') {
                continue;
            }

            $items[] = ['sales_order_item_id' => $itemId, 'qty_delivered' => $qty];
        }

        return $items;
    }

    private function context(string $permission): ?array
    {
        $context = (new TenantContextService())->current($this->actorId());
        return $context !== null && $this->can((int) $context['company_id'], $permission) ? $context : null;
    }

    private function can(int $companyId, string $permission): bool
    {
        return (new TenantAuthorizationService())->can($this->actorId(), $companyId, $permission);
    }

    private function actorId(): int
    {
        return (int) auth()->id();
    }

    private function denied(): RedirectResponse
    {
        return redirect()->to(site_url('workspace'))->with('errors', ['access' => 'Anda tidak memiliki izin mengelola Delivery Order pada company aktif.']);
    }
}
