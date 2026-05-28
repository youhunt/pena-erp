<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\CommercialOrderReadModel;
use App\Models\CommercialOrderWriteModel;
use App\Services\TenantContextService;
use CodeIgniter\HTTP\RedirectResponse;

final class CommercialOrder extends BaseController
{
    public function sales(): string
    {
        return $this->render('sales', 'sales.order.view', 'sales.order.manage');
    }

    public function purchasing(): string
    {
        return $this->render('purchasing', 'purchasing.po.view', 'purchasing.po.manage');
    }

    public function createSalesOrder(): RedirectResponse
    {
        $context = $this->context('sales.order.manage');

        if ($context === null) {
            return $this->denied('sales');
        }

        $header = $this->salesHeader((int) $context['company_id']);
        $line = $this->line();

        if (! $this->validateData($header + $line, $this->orderRules('sales')) || ! $this->datesAreValid($header, 'requested_ship_date')) {
            return $this->invalid('sales');
        }

        if (! (new CommercialOrderWriteModel())->createSalesOrder($header, $line, $this->actorId())) {
            return $this->invalid('sales', ['reference' => 'Customer, warehouse, currency, terms, produk, atau kode dokumen tidak valid untuk company aktif.']);
        }

        return $this->completed('sales', 'Sales Order draft berhasil dibuat.');
    }

    public function createPurchaseOrder(): RedirectResponse
    {
        $context = $this->context('purchasing.po.manage');

        if ($context === null) {
            return $this->denied('purchasing');
        }

        $header = $this->purchaseHeader((int) $context['company_id']);
        $line = $this->line();

        if (! $this->validateData($header + $line, $this->orderRules('purchasing')) || ! $this->datesAreValid($header, 'expected_receipt_date')) {
            return $this->invalid('purchasing');
        }

        if (! (new CommercialOrderWriteModel())->createPurchaseOrder($header, $line, $this->actorId())) {
            return $this->invalid('purchasing', ['reference' => 'Supplier, warehouse, currency, terms, produk, atau kode dokumen tidak valid untuk company aktif.']);
        }

        return $this->completed('purchasing', 'Purchase Order draft berhasil dibuat.');
    }

    private function render(string $side, string $viewPermission, string $managePermission): string
    {
        $context = $this->context($viewPermission);

        if ($context === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => $side]);
        }

        $companyId = (int) $context['company_id'];
        $reader = new CommercialOrderReadModel();
        $sales = $side === 'sales';

        return view('commercial/orders', [
            'side'             => $side,
            'title'            => $sales ? 'Sales Order' : 'Purchase Order',
            'tenantContext'    => $context,
            'canManage'        => $this->can($companyId, $managePermission),
            'orders'           => $sales ? $reader->salesOrders($companyId) : $reader->purchaseOrders($companyId),
            'partners'         => $sales ? $reader->customers($companyId) : $reader->suppliers($companyId),
            'terms'            => $sales ? $reader->customerTerms($companyId) : $reader->supplierTerms($companyId),
            'currencies'       => $reader->currencies($companyId),
            'warehouses'       => $reader->warehouses($companyId),
            'products'         => $reader->products($companyId, $sales ? 'sales' : 'purchase'),
            'transactionCodes' => $reader->transactionCodes($companyId, $sales ? 'sales' : 'purchasing'),
        ]);
    }

    /** @return array<string, mixed> */
    private function salesHeader(int $companyId): array
    {
        return [
            'company_id'          => $companyId,
            'customer_id'         => (int) $this->request->getPost('customer_id'),
            'warehouse_id'        => (int) $this->request->getPost('warehouse_id'),
            'currency_id'         => (int) $this->request->getPost('currency_id'),
            'term_id'             => $this->nullableInt('term_id'),
            'transaction_code_id' => (int) $this->request->getPost('transaction_code_id'),
            'order_date'          => (string) $this->request->getPost('order_date'),
            'requested_ship_date' => $this->nullableString('requested_ship_date'),
            'customer_po_no'      => $this->nullableString('customer_po_no'),
        ];
    }

    /** @return array<string, mixed> */
    private function purchaseHeader(int $companyId): array
    {
        return [
            'company_id'             => $companyId,
            'supplier_id'            => (int) $this->request->getPost('supplier_id'),
            'warehouse_id'           => (int) $this->request->getPost('warehouse_id'),
            'currency_id'            => (int) $this->request->getPost('currency_id'),
            'term_id'                => $this->nullableInt('term_id'),
            'transaction_code_id'    => (int) $this->request->getPost('transaction_code_id'),
            'order_date'             => (string) $this->request->getPost('order_date'),
            'expected_receipt_date'  => $this->nullableString('expected_receipt_date'),
            'supplier_ref_no'        => $this->nullableString('supplier_ref_no'),
        ];
    }

    /** @return array<string, mixed> */
    private function line(): array
    {
        return [
            'product_id' => (int) $this->request->getPost('product_id'),
            'qty'        => (string) $this->request->getPost('qty'),
            'unit_price' => (string) $this->request->getPost('unit_price'),
        ];
    }

    /** @return array<string, string> */
    private function orderRules(string $side): array
    {
        $rules = [
            'warehouse_id'        => 'required|is_natural_no_zero',
            'currency_id'         => 'required|is_natural_no_zero',
            'transaction_code_id' => 'required|is_natural_no_zero',
            'order_date'          => 'required|valid_date[Y-m-d]',
            'product_id'          => 'required|is_natural_no_zero',
            'qty'                 => 'required|decimal|greater_than[0]',
            'unit_price'          => 'required|decimal|greater_than_equal_to[0]',
        ];

        $rules[$side === 'sales' ? 'customer_id' : 'supplier_id'] = 'required|is_natural_no_zero';

        return $rules;
    }

    /** @param array<string, mixed> $header */
    private function datesAreValid(array $header, string $field): bool
    {
        return ($header[$field] ?? null) === null || strtotime((string) $header[$field]) >= strtotime((string) $header['order_date']);
    }

    private function nullableInt(string $field): ?int
    {
        $value = (int) $this->request->getPost($field);

        return $value > 0 ? $value : null;
    }

    private function nullableString(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));

        return $value === '' ? null : $value;
    }

    /** @return array<string, mixed>|null */
    private function context(string $permission): ?array
    {
        $context = (new TenantContextService())->current($this->actorId());

        if ($context === null || ! $this->can((int) $context['company_id'], $permission)) {
            return null;
        }

        return $context;
    }

    private function can(int $companyId, string $permission): bool
    {
        return (new TenantAuthorizationService())->can($this->actorId(), $companyId, $permission);
    }

    private function actorId(): int
    {
        return (int) auth()->id();
    }

    private function denied(string $side): RedirectResponse
    {
        return redirect()->to(site_url('workspace'))->with('errors', ['access' => 'Anda tidak memiliki izin mengelola ' . $side . ' order pada company aktif.']);
    }

    /** @param array<string, string>|null $errors */
    private function invalid(string $side, ?array $errors = null): RedirectResponse
    {
        return redirect()->to(site_url($side === 'sales' ? 'sales/orders' : 'purchasing/orders'))->withInput()->with('errors', $errors ?? $this->validator->getErrors());
    }

    private function completed(string $side, string $message): RedirectResponse
    {
        return redirect()->to(site_url($side === 'sales' ? 'sales/orders' : 'purchasing/orders'))->with('message', $message);
    }
}
