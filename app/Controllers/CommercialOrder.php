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
        $lines  = $this->lines();

        if (! $this->validateData($header, $this->headerRules('sales')) || ! $this->linesAreValid($lines) || ! $this->datesAreValid($header, 'requested_ship_date')) {
            return $this->invalid('sales', $this->validator?->getErrors() ?: ['lines' => 'Minimal satu line order valid wajib diisi.']);
        }

        if (! (new CommercialOrderWriteModel())->createSalesOrder($header, $lines, $this->actorId())) {
            return $this->invalid('sales', ['reference' => 'Customer, warehouse, currency, terms, produk, atau kode dokumen tidak valid untuk company aktif.']);
        }

        return $this->completed('sales', 'Sales Order draft berhasil dibuat.');
    }

    public function confirmSalesOrder(int $id): RedirectResponse
    {
        $context = $this->context('sales.order.manage');
        if ($context === null) {
            return $this->denied('sales');
        }

        try {
            $ok = (new CommercialOrderWriteModel())->confirmSalesOrder($id, (int) $context['company_id'], $this->actorId());
            if (! $ok) {
                return $this->invalid('sales', ['status' => 'Sales Order tidak ditemukan, bukan draft, atau tidak memiliki item.']);
            }

            return $this->completed('sales', 'Sales Order berhasil dikonfirmasi.');
        } catch (\Throwable $e) {
            return $this->invalid('sales', ['error' => $e->getMessage()]);
        }
    }

    public function createPurchaseOrder(): RedirectResponse
    {
        $context = $this->context('purchasing.po.manage');
        if ($context === null) {
            return $this->denied('purchasing');
        }

        $header = $this->purchaseHeader((int) $context['company_id']);
        $lines  = $this->lines();

        if (! $this->validateData($header, $this->headerRules('purchasing')) || ! $this->linesAreValid($lines) || ! $this->datesAreValid($header, 'expected_receipt_date')) {
            return $this->invalid('purchasing', $this->validator?->getErrors() ?: ['lines' => 'Minimal satu line order valid wajib diisi.']);
        }

        if (! (new CommercialOrderWriteModel())->createPurchaseOrder($header, $lines, $this->actorId())) {
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

    private function lines(): array
    {
        $posted = $this->request->getPost('lines');
        if (! is_array($posted)) {
            return [[
                'product_id' => (int) $this->request->getPost('product_id'),
                'qty'        => (string) $this->request->getPost('qty'),
                'unit_price' => (string) $this->request->getPost('unit_price'),
            ]];
        }

        $lines = [];
        foreach ($posted as $line) {
            if (! is_array($line)) {
                continue;
            }

            $productId = (int) ($line['product_id'] ?? 0);
            $qty       = (string) ($line['qty'] ?? '0');
            $unitPrice = (string) ($line['unit_price'] ?? '0');

            if ($productId <= 0 && $this->decimal($qty) <= 0) {
                continue;
            }

            $lines[] = ['product_id' => $productId, 'qty' => $qty, 'unit_price' => $unitPrice];
        }

        return $lines;
    }

    private function headerRules(string $side): array
    {
        $rules = [
            'warehouse_id'        => 'required|is_natural_no_zero',
            'currency_id'         => 'required|is_natural_no_zero',
            'transaction_code_id' => 'required|is_natural_no_zero',
            'order_date'          => 'required|valid_date[Y-m-d]',
        ];
        $rules[$side === 'sales' ? 'customer_id' : 'supplier_id'] = 'required|is_natural_no_zero';
        return $rules;
    }

    private function linesAreValid(array $lines): bool
    {
        if ($lines === []) {
            return false;
        }

        foreach ($lines as $line) {
            if ((int) $line['product_id'] <= 0 || $this->decimal($line['qty']) <= 0 || $this->decimal($line['unit_price']) < 0) {
                return false;
            }
        }
        return true;
    }

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

    private function decimal(mixed $value): float
    {
        return (float) str_replace(',', '.', trim((string) $value));
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

    private function denied(string $side): RedirectResponse
    {
        return redirect()->to(site_url('workspace'))->with('errors', ['access' => 'Anda tidak memiliki izin mengelola ' . $side . ' order pada company aktif.']);
    }

    private function invalid(string $side, ?array $errors = null): RedirectResponse
    {
        return redirect()->to(site_url($side === 'sales' ? 'sales/orders' : 'purchasing/orders'))->withInput()->with('errors', $errors ?? $this->validator->getErrors());
    }

    private function completed(string $side, string $message): RedirectResponse
    {
        return redirect()->to(site_url($side === 'sales' ? 'sales/orders' : 'purchasing/orders'))->with('message', $message);
    }
}
