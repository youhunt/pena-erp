<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Finance Transactions<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18">Finance Transactions</h4>
        <p class="text-muted mb-0"><?= esc($tenantContext['company_name']) ?> / AP, AR, Payment</p>
    </div>
    <?php if ($canManage) : ?>
        <div class="dropdown"><button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">Tambah Transaksi</button><div class="dropdown-menu">
            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addPurchaseInvoice">Purchase Invoice</button>
            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addSalesInvoice">Sales Invoice</button>
            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addPayment">Payment</button>
        </div></div>
    <?php else : ?><span class="badge bg-info">Read only</span><?php endif; ?>
</div>
<?php if (session('message') !== null) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
<?php if (session('errors') !== null) : ?><div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div><?php endif; ?>

<div class="row">
    <div class="col-md-4"><div class="card border-secondary"><div class="card-body"><h5 class="card-title">Purchase Invoices</h5><p class="display-6 mb-0"><?= count($purchaseInvoices) ?></p></div></div></div>
    <div class="col-md-4"><div class="card border-secondary"><div class="card-body"><h5 class="card-title">Sales Invoices</h5><p class="display-6 mb-0"><?= count($salesInvoices) ?></p></div></div></div>
    <div class="col-md-4"><div class="card border-secondary"><div class="card-body"><h5 class="card-title">Payments</h5><p class="display-6 mb-0"><?= count($payments) ?></p></div></div></div>
</div>

<div class="row mt-4">
    <div class="col-xl-6"><div class="card"><div class="card-body"><h4 class="card-title mb-3">Latest Purchase Invoices</h4>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>No</th><th>Supplier</th><th>Date</th><th>Due</th><th>Total</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead><tbody>
            <?php foreach ($purchaseInvoices as $invoice) : ?>
                <tr>
                    <td><strong><?= esc($invoice['invoice_no']) ?></strong></td>
                    <td><?= esc($invoice['supplier_code'] . ' - ' . $invoice['supplier_name']) ?></td>
                    <td><?= esc($invoice['invoice_date']) ?></td>
                    <td><?= esc($invoice['due_date']) ?></td>
                    <td><?= esc(number_format((float) $invoice['total_amount'], 2, ',', '.')) ?></td>
                    <td><?= esc($invoice['status']) ?></td>
                    <?php if ($canManage) : ?><td class="text-end"><?php if ($invoice['status'] === 'draft') : ?><form method="post" action="<?= site_url('finance/invoices/purchase-invoices/' . $invoice['id'] . '/post') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-primary">Post</button></form><?php else : ?>-<?php endif; ?></td><?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if ($purchaseInvoices === []) : ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada purchase invoice.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div></div>
    <div class="col-xl-6"><div class="card"><div class="card-body"><h4 class="card-title mb-3">Latest Sales Invoices</h4>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>No</th><th>Customer</th><th>Date</th><th>Due</th><th>Total</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead><tbody>
            <?php foreach ($salesInvoices as $invoice) : ?>
                <tr>
                    <td><strong><?= esc($invoice['invoice_no']) ?></strong></td>
                    <td><?= esc($invoice['customer_code'] . ' - ' . $invoice['customer_name']) ?></td>
                    <td><?= esc($invoice['invoice_date']) ?></td>
                    <td><?= esc($invoice['due_date']) ?></td>
                    <td><?= esc(number_format((float) $invoice['total_amount'], 2, ',', '.')) ?></td>
                    <td><?= esc($invoice['status']) ?></td>
                    <?php if ($canManage) : ?><td class="text-end"><?php if ($invoice['status'] === 'draft') : ?><form method="post" action="<?= site_url('finance/invoices/sales-invoices/' . $invoice['id'] . '/post') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-primary">Post</button></form><?php else : ?>-<?php endif; ?></td><?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if ($salesInvoices === []) : ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada sales invoice.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div></div>
</div>

<div class="card mt-4"><div class="card-body"><h4 class="card-title mb-3">Latest Payments</h4>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>No</th><th>Type</th><th>Partner</th><th>Date</th><th>Amount</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead><tbody>
        <?php foreach ($payments as $payment) : ?>
            <tr>
                <td><strong><?= esc($payment['payment_no']) ?></strong></td>
                <td><?= esc($payment['payment_type']) ?></td>
                <td><?= esc(($payment['supplier_code'] !== null ? $payment['supplier_code'] . ' - ' . $payment['supplier_name'] : ($payment['customer_code'] !== null ? $payment['customer_code'] . ' - ' . $payment['customer_name'] : '-'))) ?></td>
                <td><?= esc($payment['payment_date']) ?></td>
                <td><?= esc(number_format((float) $payment['amount'], 2, ',', '.')) ?></td>
                <td><?= esc($payment['status']) ?></td>
                <?php if ($canManage) : ?><td class="text-end"><?php if ($payment['status'] === 'draft') : ?><form method="post" action="<?= site_url('finance/invoices/payments/' . $payment['id'] . '/post') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-primary">Post</button></form><?php else : ?><div class="btn-group"><button class="btn btn-sm btn-secondary js-open-allocations" data-payment-id="<?= (int) $payment['id'] ?>">Allocations</button></div><?php endif; ?></td><?php endif; ?>
            </tr>
        <?php endforeach; ?>
        <?php if ($payments === []) : ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada pembayaran.</td></tr><?php endif; ?>
    </tbody></table></div>
</div></div>

<?php if ($canManage) : ?>
<div class="modal fade" id="addPurchaseInvoice"><div class="modal-dialog"><form class="modal-content" method="post" action="<?= site_url('finance/invoices/purchase-invoices') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Purchase Invoice</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2">
    <div class="col-md-6"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select" required><?php foreach ($suppliers as $supplier) : ?><option value="<?= (int) $supplier['id'] ?>"><?= esc($supplier['code'] . ' - ' . $supplier['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Invoice No</label><input name="invoice_no" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Invoice Date</label><input type="date" name="invoice_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
    <div class="col-md-6"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
    <div class="col-md-6"><label class="form-label">Currency</label><select name="currency_id" class="form-select" required><?php foreach ($currencies as $currency) : ?><option value="<?= (int) $currency['id'] ?>"><?= esc($currency['code']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Subtotal</label><input name="subtotal" type="number" step="0.0001" min="0" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Tax Amount</label><input name="tax_amount" type="number" step="0.0001" min="0" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Total Amount</label><input name="total_amount" type="number" step="0.0001" min="0" class="form-control" required></div>
</div><div class="modal-footer"><button class="btn btn-primary">Simpan Draft</button></div></form></div></div>

<div class="modal fade" id="addSalesInvoice"><div class="modal-dialog"><form class="modal-content" method="post" action="<?= site_url('finance/invoices/sales-invoices') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Sales Invoice</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2">
    <div class="col-md-6"><label class="form-label">Customer</label><select name="customer_id" class="form-select" required><?php foreach ($customers as $customer) : ?><option value="<?= (int) $customer['id'] ?>"><?= esc($customer['code'] . ' - ' . $customer['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Invoice No</label><input name="invoice_no" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Invoice Date</label><input type="date" name="invoice_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
    <div class="col-md-6"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
    <div class="col-md-6"><label class="form-label">Currency</label><select name="currency_id" class="form-select" required><?php foreach ($currencies as $currency) : ?><option value="<?= (int) $currency['id'] ?>"><?= esc($currency['code']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Subtotal</label><input name="subtotal" type="number" step="0.0001" min="0" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Tax Amount</label><input name="tax_amount" type="number" step="0.0001" min="0" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Total Amount</label><input name="total_amount" type="number" step="0.0001" min="0" class="form-control" required></div>
</div><div class="modal-footer"><button class="btn btn-primary">Simpan Draft</button></div></form></div></div>

<div class="modal fade" id="addPayment"><div class="modal-dialog"><form class="modal-content" method="post" action="<?= site_url('finance/invoices/payments') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Payment</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2">
    <div class="col-md-6"><label class="form-label">Payment Type</label><select id="paymentType" name="payment_type" class="form-select" required><option value="outgoing">Outgoing</option><option value="incoming">Incoming</option></select></div>
    <div class="col-md-6"><label class="form-label">Payment No</label><input name="payment_no" class="form-control" required></div>
    <div class="col-md-6 js-payment-supplier"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select"><?php foreach ($suppliers as $supplier) : ?><option value="<?= (int) $supplier['id'] ?>"><?= esc($supplier['code'] . ' - ' . $supplier['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6 js-payment-customer d-none"><label class="form-label">Customer</label><select name="customer_id" class="form-select"><?php foreach ($customers as $customer) : ?><option value="<?= (int) $customer['id'] ?>"><?= esc($customer['code'] . ' - ' . $customer['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Payment Date</label><input type="date" name="payment_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
    <div class="col-md-6"><label class="form-label">Currency</label><select name="currency_id" class="form-select" required><?php foreach ($currencies as $currency) : ?><option value="<?= (int) $currency['id'] ?>"><?= esc($currency['code']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Bank Account</label><select name="bank_account_id" class="form-select"><option value="">-</option><?php foreach ($cashBanks as $bank) : ?><option value="<?= (int) $bank['id'] ?>"><?= esc($bank['code'] . ' - ' . $bank['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Amount</label><input name="amount" type="number" step="0.0001" min="0" class="form-control" required></div>
</div><div class="modal-footer"><button class="btn btn-primary">Simpan Draft</button></div></form></div></div>

<?php endif; ?>

<?php if ($canManage) : ?>
<div class="modal fade" id="allocationsModal"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Payment Allocations</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body">
    <div id="allocationsList" class="mb-3">Loading...</div>

    <form id="createAllocationForm" method="post" action="" class="row g-2">
        <?= csrf_field() ?>
        <input type="hidden" name="document_type" value="sales_invoice">
        <div class="col-md-4"><label class="form-label">Document</label><select name="document_id" class="form-select" required></select></div>
        <div class="col-md-4"><label class="form-label">Amount</label><input name="allocated_amount" type="number" step="0.0001" min="0" class="form-control" required></div>
        <div class="col-md-4 d-flex align-items-end"><button class="btn btn-primary">Add Allocation</button></div>
    </form>
</div></div></div></div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    var typeSelect = document.getElementById('paymentType');
    var supplierField = document.querySelector('.js-payment-supplier');
    var customerField = document.querySelector('.js-payment-customer');

    if (!typeSelect || !supplierField || !customerField) {
        return;
    }

    function updatePartnerFields() {
        if (typeSelect.value === 'incoming') {
            supplierField.classList.add('d-none');
            customerField.classList.remove('d-none');
        } else {
            supplierField.classList.remove('d-none');
            customerField.classList.add('d-none');
        }
    }

    typeSelect.addEventListener('change', updatePartnerFields);
    updatePartnerFields();
})();
</script>
<script>
(function () {
    function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
    function qsa(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

    var modalEl = qs('#allocationsModal');
    var allocationsList = qs('#allocationsList');
    var createForm = qs('#createAllocationForm');
    var docSelect = createForm ? createForm.querySelector('select[name="document_id"]') : null;

    function setFormAction(paymentId) {
        if (!createForm) return;
        createForm.action = siteUrl('finance/invoices/payments/' + paymentId + '/allocations');
    }

    function siteUrl(path) {
        var base = '<?= site_url('') ?>';
        return base.replace(/\/$/, '') + '/' + path.replace(/^\//, '');
    }

    function renderAllocations(paymentId, items) {
        if (!allocationsList) return;
        if (!Array.isArray(items) || items.length === 0) {
            allocationsList.innerHTML = '<div class="text-muted">No allocations</div>';
            if (docSelect) docSelect.innerHTML = '';
            return;
        }

        var html = ['<table class="table"><thead><tr><th>Doc</th><th>Amount</th><th>Description</th><th></th></tr></thead><tbody>'];
        items.forEach(function (it) {
            html.push('<tr>');
            html.push('<td>' + (it.document_no ?? it.document_id) + '</td>');
            html.push('<td>' + Number(it.allocated_amount).toLocaleString() + '</td>');
            html.push('<td>' + (it.description ?? '') + '</td>');
            html.push('<td><form method="post" action="' + siteUrl('finance/invoices/payments/' + paymentId + '/allocations/' + it.id + '/delete') + '"><?= csrf_field() ?><button class="btn btn-sm btn-link text-danger">Delete</button></form></td>');
            html.push('</tr>');
        });
        html.push('</tbody></table>');
        allocationsList.innerHTML = html.join('');

        if (docSelect) {
            docSelect.innerHTML = items.map(function (it) { return '<option value="' + it.document_id + '">' + (it.document_no ?? it.document_id) + '</option>'; }).join('');
        }
    }

    function loadAllocations(paymentId) {
        allocationsList.innerHTML = 'Loading...';
        fetch(siteUrl('finance/invoices/payments/' + paymentId + '/allocations'))
            .then(function (r) { return r.json(); })
            .then(function (data) { renderAllocations(paymentId, data); setFormAction(paymentId); })
            .catch(function () { allocationsList.innerHTML = '<div class="text-danger">Failed to load allocations</div>'; });
    }

    qsa('.js-open-allocations').forEach(function (btn) {
        btn.addEventListener('click', function (ev) {
            var id = btn.getAttribute('data-payment-id');
            if (!id) return;
            loadAllocations(id);
            var modal = new bootstrap.Modal(document.getElementById('allocationsModal'));
            modal.show();
        });
    });
})();
</script>
<?= $this->endSection() ?>
