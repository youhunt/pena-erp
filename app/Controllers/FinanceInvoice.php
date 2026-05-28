<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\FinanceReadModel;
use App\Models\FinanceWriteModel;
use App\Services\TenantContextService;
use CodeIgniter\HTTP\RedirectResponse;

final class FinanceInvoice extends BaseController
{
    public function index(): string
    {
        $context = $this->context('finance.invoice.view');

        if ($context === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => 'finance']);
        }

        $companyId = (int) $context['company_id'];
        $reader = new FinanceReadModel();

        return view('finance/transactions', [
            'tenantContext'    => $context,
            'canManage'        => $this->can($companyId, 'finance.invoice.manage'),
            'purchaseInvoices' => $reader->purchaseInvoices($companyId),
            'salesInvoices'    => $reader->salesInvoices($companyId),
            'payments'         => $reader->payments($companyId),
            'suppliers'        => $reader->suppliers($companyId),
            'customers'        => $reader->customers($companyId),
            'currencies'       => $reader->currencies($companyId),
            'cashBanks'        => $reader->cashBankAccounts($companyId),
        ]);
    }

    public function createPurchaseInvoice(): RedirectResponse
    {
        $context = $this->context('finance.invoice.manage');
        if ($context === null) {
            return $this->denied();
        }

        $companyId = (int) $context['company_id'];
        $data = [
            'company_id'       => $companyId,
            'supplier_id'      => (int) $this->request->getPost('supplier_id'),
            'purchase_order_id' => ($this->request->getPost('purchase_order_id') !== null ? (int) $this->request->getPost('purchase_order_id') : null),
            'invoice_no'       => trim((string) $this->request->getPost('invoice_no')),
            'invoice_date'     => (string) $this->request->getPost('invoice_date'),
            'due_date'         => (string) $this->request->getPost('due_date'),
            'currency_id'      => (int) $this->request->getPost('currency_id'),
            'subtotal'         => (string) $this->request->getPost('subtotal'),
            'tax_amount'       => (string) $this->request->getPost('tax_amount'),
            'total_amount'     => (string) $this->request->getPost('total_amount'),
        ];

        $rules = [
            'supplier_id'  => 'required|is_natural_no_zero',
            'invoice_no'   => 'required|max_length[80]',
            'invoice_date' => 'required|valid_date[Y-m-d]',
            'due_date'     => 'required|valid_date[Y-m-d]',
            'currency_id'  => 'required|is_natural_no_zero',
            'subtotal'     => 'required|decimal|greater_than_equal_to[0]',
            'tax_amount'   => 'required|decimal|greater_than_equal_to[0]',
            'total_amount' => 'required|decimal|greater_than[0]',
        ];

        if (! $this->validateData($data, $rules) || ! $this->invoiceTotalsMatch($data)) {
            return $this->invalid(['total_amount' => 'Total invoice harus sama dengan subtotal ditambah pajak.']);
        }

        if (strtotime($data['due_date']) < strtotime($data['invoice_date'])) {
            return $this->invalid(['due_date' => 'Due date tidak boleh sebelum invoice date.']);
        }

        if (! (new FinanceWriteModel())->createPurchaseInvoice($data, $this->actorId())) {
            return $this->invalid(['reference' => 'Supplier, currency, atau nomor invoice tidak valid.']);
        }

        return $this->completed('Purchase Invoice berhasil disimpan.');
    }

    public function createSalesInvoice(): RedirectResponse
    {
        $context = $this->context('finance.invoice.manage');
        if ($context === null) {
            return $this->denied();
        }

        $companyId = (int) $context['company_id'];
        $data = [
            'company_id'      => $companyId,
            'customer_id'     => (int) $this->request->getPost('customer_id'),
            'sales_order_id'  => ($this->request->getPost('sales_order_id') !== null ? (int) $this->request->getPost('sales_order_id') : null),
            'invoice_no'      => trim((string) $this->request->getPost('invoice_no')),
            'invoice_date'    => (string) $this->request->getPost('invoice_date'),
            'due_date'        => (string) $this->request->getPost('due_date'),
            'currency_id'     => (int) $this->request->getPost('currency_id'),
            'subtotal'        => (string) $this->request->getPost('subtotal'),
            'tax_amount'      => (string) $this->request->getPost('tax_amount'),
            'total_amount'    => (string) $this->request->getPost('total_amount'),
        ];

        $rules = [
            'customer_id'  => 'required|is_natural_no_zero',
            'invoice_no'   => 'required|max_length[80]',
            'invoice_date' => 'required|valid_date[Y-m-d]',
            'due_date'     => 'required|valid_date[Y-m-d]',
            'currency_id'  => 'required|is_natural_no_zero',
            'subtotal'     => 'required|decimal|greater_than_equal_to[0]',
            'tax_amount'   => 'required|decimal|greater_than_equal_to[0]',
            'total_amount' => 'required|decimal|greater_than[0]',
        ];

        if (! $this->validateData($data, $rules) || ! $this->invoiceTotalsMatch($data)) {
            return $this->invalid(['total_amount' => 'Total invoice harus sama dengan subtotal ditambah pajak.']);
        }

        if (strtotime($data['due_date']) < strtotime($data['invoice_date'])) {
            return $this->invalid(['due_date' => 'Due date tidak boleh sebelum invoice date.']);
        }

        if (! (new FinanceWriteModel())->createSalesInvoice($data, $this->actorId())) {
            return $this->invalid(['reference' => 'Customer, currency, atau nomor invoice tidak valid.']);
        }

        return $this->completed('Sales Invoice berhasil disimpan.');
    }

    public function createPayment(): RedirectResponse
    {
        $context = $this->context('finance.invoice.manage');
        if ($context === null) {
            return $this->denied();
        }

        $companyId = (int) $context['company_id'];
        $paymentType = (string) $this->request->getPost('payment_type');
        $supplierId = (int) $this->request->getPost('supplier_id');
        $customerId = (int) $this->request->getPost('customer_id');
        $partnerType = $paymentType === 'incoming' ? 'customer' : 'supplier';
        $partnerId = $partnerType === 'customer' ? $customerId : $supplierId;

        $data = [
            'company_id'      => $companyId,
            'payment_no'      => trim((string) $this->request->getPost('payment_no')),
            'payment_type'    => $paymentType,
            'supplier_id'     => $supplierId > 0 ? $supplierId : null,
            'customer_id'     => $customerId > 0 ? $customerId : null,
            'payment_date'    => (string) $this->request->getPost('payment_date'),
            'currency_id'     => (int) $this->request->getPost('currency_id'),
            'bank_account_id' => ($this->request->getPost('bank_account_id') !== null ? (int) $this->request->getPost('bank_account_id') : null),
            'amount'          => (string) $this->request->getPost('amount'),
            'partner_type'    => $partnerType,
            'partner_id'      => $partnerId > 0 ? $partnerId : null,
        ];

        $rules = [
            'payment_no'   => 'required|max_length[50]',
            'payment_type' => 'required|in_list[incoming,outgoing]',
            'payment_date' => 'required|valid_date[Y-m-d]',
            'currency_id'  => 'required|is_natural_no_zero',
            'amount'       => 'required|decimal|greater_than[0]',
        ];

        if (! $this->validateData($data, $rules) || $partnerId === null) {
            return $this->invalid(['reference' => 'Customer atau supplier harus dipilih sesuai jenis pembayaran.']);
        }

        if (! (new FinanceWriteModel())->createPayment($data, $this->actorId())) {
            return $this->invalid(['reference' => 'Partner, currency, atau akun bank tidak valid.']);
        }

        return $this->completed('Payment berhasil disimpan.');
    }

    public function postPurchaseInvoice(int $invoiceId): RedirectResponse
    {
        $context = $this->context('finance.invoice.manage');
        if ($context === null) {
            return $this->denied();
        }

        if (! (new FinanceWriteModel())->postPurchaseInvoice((int) $context['company_id'], $invoiceId, $this->actorId())) {
            return $this->invalid(['reference' => 'Invoice tidak dapat diposting. Pastikan status draft dan periode AP terbuka.']);
        }

        return $this->completed('Purchase Invoice berhasil diposting.');
    }

    public function postSalesInvoice(int $invoiceId): RedirectResponse
    {
        $context = $this->context('finance.invoice.manage');
        if ($context === null) {
            return $this->denied();
        }

        if (! (new FinanceWriteModel())->postSalesInvoice((int) $context['company_id'], $invoiceId, $this->actorId())) {
            return $this->invalid(['reference' => 'Invoice tidak dapat diposting. Pastikan status draft dan periode AR terbuka.']);
        }

        return $this->completed('Sales Invoice berhasil diposting.');
    }

    public function postPayment(int $paymentId): RedirectResponse
    {
        $context = $this->context('finance.invoice.manage');
        if ($context === null) {
            return $this->denied();
        }

        if (! (new FinanceWriteModel())->postPayment((int) $context['company_id'], $paymentId, $this->actorId())) {
            return $this->invalid(['reference' => 'Payment tidak dapat diposting. Pastikan status draft dan periode Cash/Bank terbuka.']);
        }

        return $this->completed('Payment berhasil diposting.');
    }

    public function allocations(int $paymentId)
    {
        $context = $this->context('finance.invoice.view');
        if ($context === null) {
            $this->response->setStatusCode(403);
            return $this->response->setJSON(['error' => 'access_denied']);
        }

        $companyId = (int) $context['company_id'];
        $reader = new FinanceReadModel();

        return $this->response->setJSON($reader->paymentAllocations($companyId, $paymentId));
    }

    public function createAllocation(int $paymentId): RedirectResponse
    {
        $context = $this->context('finance.invoice.manage');
        if ($context === null) {
            return $this->denied();
        }

        $companyId = (int) $context['company_id'];
        $data = [
            'company_id'      => $companyId,
            'payment_id'      => $paymentId,
            'document_type'   => (string) $this->request->getPost('document_type'),
            'document_id'     => (int) $this->request->getPost('document_id'),
            'allocated_amount'=> (string) $this->request->getPost('allocated_amount'),
            'description'     => (string) $this->request->getPost('description'),
        ];

        if (! (new FinanceWriteModel())->createPaymentAllocation($data, $this->actorId())) {
            return $this->invalid(['reference' => 'Allocation gagal. Periksa dokumen dan jumlah.']);
        }

        return $this->completed('Allocation berhasil ditambahkan.');
    }

    public function deleteAllocation(int $paymentId, int $allocationId): RedirectResponse
    {
        $context = $this->context('finance.invoice.manage');
        if ($context === null) {
            return $this->denied();
        }

        $companyId = (int) $context['company_id'];

        if (! (new FinanceWriteModel())->deletePaymentAllocation($companyId, $allocationId, $this->actorId())) {
            return $this->invalid(['reference' => 'Allocation tidak dapat dihapus.']);
        }

        return $this->completed('Allocation berhasil dihapus.');
    }

    /** @return array<string, mixed>|null */
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
        return redirect()->to(site_url('workspace'))->with('errors', ['access' => 'Anda tidak memiliki izin mengelola Invoice Finance pada company aktif.']);
    }

    private function invalid(?array $errors = null): RedirectResponse
    {
        return redirect()->back()->withInput()->with('errors', $errors ?? $this->validator->getErrors());
    }

    private function completed(string $message): RedirectResponse
    {
        return redirect()->to(site_url('finance/invoices'))->with('message', $message);
    }

    /** @param array<string, mixed> $data */
    private function invoiceTotalsMatch(array $data): bool
    {
        return abs((float) $data['subtotal'] + (float) $data['tax_amount'] - (float) $data['total_amount']) < 0.0001;
    }
}
