<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\GoodsReceiptReadModel;
use App\Models\GoodsReceiptWriteModel;
use App\Services\TenantContextService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

final class GoodsReceipt extends BaseController
{
    public function index(): string
    {
        $context = $this->context('purchasing.gr.view');

        if ($context === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => 'purchasing']);
        }

        $companyId = (int) $context['company_id'];
        $reader    = new GoodsReceiptReadModel();

        return view('purchasing/receipts', [
            'tenantContext'  => $context,
            'canManage'      => $this->can($companyId, 'purchasing.gr.manage'),
            'receipts'       => $reader->listReceipts($companyId),
            'purchaseOrders' => $reader->listPurchaseOrders($companyId),
            'warehouses'     => $reader->listActiveWarehouses($companyId),
        ]);
    }

    /** AJAX: kembalikan items dari PO yang dipilih (hanya qty_remaining > 0) */
    public function poItems(int $poId): ResponseInterface
    {
        $context = $this->context('purchasing.gr.view');

        if ($context === null) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'access_denied']);
        }

        $items = (new GoodsReceiptReadModel())->poItemsForAjax((int) $context['company_id'], $poId);

        return $this->response->setJSON($items);
    }

    public function create(): RedirectResponse
    {
        $context = $this->context('purchasing.gr.manage');

        if ($context === null) {
            return $this->denied();
        }

        try {
            $result = (new GoodsReceiptWriteModel())->createDraftReceipt([
                'company_id'             => (int) $context['company_id'],
                'branch_id'              => isset($context['branch_id']) ? (int) $context['branch_id'] : null,
                'actor_id'               => $this->actorId(),
                'purchase_order_id'      => (int) $this->request->getPost('purchase_order_id'),
                'purchase_order_item_id' => (int) $this->request->getPost('purchase_order_item_id'),
                'warehouse_id'           => (int) $this->request->getPost('warehouse_id'),
                'qty_received'           => (float) $this->request->getPost('qty_received'),
            ]);

            return redirect()->to(site_url('purchasing/receipts'))
                ->with('message', 'Goods Receipt draft dibuat: ' . $result['receipt_number']);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['error' => $e->getMessage()]);
        }
    }

    public function post(int $id): RedirectResponse
    {
        log_message('error', 'GR POST CONTROLLER START: ' . json_encode([
            'receipt_id' => $id,
            'actor_id'   => $this->actorId(),
            'method'     => $this->request->getMethod(),
            'post'       => $this->request->getPost(),
        ]));

        $context = $this->context('purchasing.gr.manage');

        log_message('error', 'GR POST CONTEXT: ' . json_encode($context));

        if ($context === null) {
            log_message('error', 'GR POST DENIED: context null or permission missing');

            return $this->denied();
        }

        try {
            $result = (new GoodsReceiptWriteModel())->postReceipt(
                $id,
                (int) $context['company_id'],
                $this->actorId()
            );

            log_message('error', 'GR POST SUCCESS CONTROLLER: ' . json_encode($result));

            return redirect()->to(site_url('purchasing/receipts'))
                ->with('message', 'Goods Receipt berhasil diposting: ' . $result['id']);
        } catch (\Throwable $e) {
            log_message('error', 'GR POST CONTROLLER ERROR: ' . $e->getMessage());
            log_message('error', 'GR POST CONTROLLER TRACE: ' . $e->getTraceAsString());

            return redirect()->back()
                ->with('errors', ['error' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** @return array<string, mixed>|null */
    private function context(string $permission): ?array
    {
        $context = (new TenantContextService())->current($this->actorId());

        return $context !== null && $this->can((int) $context['company_id'], $permission)
            ? $context
            : null;
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
        return redirect()->to(site_url('workspace'))
            ->with('errors', ['access' => 'Anda tidak memiliki izin mengelola Goods Receipt pada company aktif.']);
    }
}
