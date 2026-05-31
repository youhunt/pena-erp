<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Finance Transactions<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$purchaseOrders = $purchaseOrders ?? [];
$salesOrders = $salesOrders ?? [];
$money = static fn (mixed $value): string => number_format((float) $value, 2, ',', '.');
$statusBadge = static fn (string $status): string => match ($status) {
    'paid' => 'success',
    'partial_paid' => 'warning',
    'unpaid' => 'danger',
    'draft' => 'secondary',
    default => 'info',
};
$allocationInvoices = [
    'purchase' => array_values(array_filter($purchaseInvoices, static fn ($row) => ($row['status'] ?? '') === 'posted' && (float) ($row['outstanding_amount'] ?? 0) > 0)),
    'sales' => array_values(array_filter($salesInvoices, static fn ($row) => ($row['status'] ?? '') === 'posted' && (float) ($row['outstanding_amount'] ?? 0) > 0)),
];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18">Finance Transactions</h4>
        <p class="text-muted mb-0"><?= esc($tenantContext['company_name']) ?> / AP, AR, Payment</p>
    </div>
    <?php if ($canManage) : ?>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">Tambah Transaksi</button>
            <div class="dropdown-menu">
                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addPurchaseInvoice">Purchase Invoice</button>
                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addSalesInvoice">Sales Invoice</button>
                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addPayment">Payment</button>
            </div>
        </div>
    <?php else : ?>
        <span class="badge bg-info">Read only</span>
    <?php endif; ?>
</div>

<?php if (session('message') !== null) : ?>
    <div class="alert alert-success"><?= esc(session('message')) ?></div>
<?php endif; ?>
<?php if (session('errors') !== null) : ?>
    <div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4"><div class="card border-secondary"><div class="card-body"><h5 class="card-title">Purchase Invoices</h5><p class="display-6 mb-0"><?= count($purchaseInvoices) ?></p></div></div></div>
    <div class="col-md-4"><div class="card border-secondary"><div class="card-body"><h5 class="card-title">Sales Invoices</h5><p class="display-6 mb-0"><?= count($salesInvoices) ?></p></div></div></div>
    <div class="col-md-4"><div class="card border-secondary"><div class="card-body"><h5 class="card-title">Payments</h5><p class="display-6 mb-0"><?= count($payments) ?></p></div></div></div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h4 class="card-title mb-3">Latest Purchase Invoices</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>No</th><th>Supplier</th><th>Date</th><th>Due</th><th>Total</th><th>Paid</th><th>Outstanding</th><th>Doc</th><th>Payment</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead>
                <tbody>
                <?php foreach ($purchaseInvoices as $invoice) : ?>
                    <tr>
                        <td><strong><?= esc($invoice['invoice_no']) ?></strong></td>
                        <td><?= esc(($invoice['supplier_code'] ?? '-') . ' - ' . ($invoice['supplier_name'] ?? '-')) ?></td>
                        <td><?= esc($invoice['invoice_date']) ?></td>
                        <td><?= esc($invoice['due_date']) ?></td>
                        <td><?= esc($money($invoice['total_amount'])) ?></td>
                        <td><?= esc($money($invoice['paid_amount'] ?? 0)) ?></td>
                        <td><?= esc($money($invoice['outstanding_amount'] ?? $invoice['total_amount'])) ?></td>
                        <td><span class="badge bg-info"><?= esc($invoice['status']) ?></span></td>
                        <td><span class="badge bg-<?= esc($statusBadge((string) ($invoice['payment_status'] ?? 'draft'))) ?>"><?= esc($invoice['payment_status'] ?? 'draft') ?></span></td>
                        <?php if ($canManage) : ?>
                            <td class="text-end">
                                <?php if ($invoice['status'] === 'draft') : ?>
                                    <form method="post" action="<?= site_url('finance/invoices/purchase-invoices/' . $invoice['id'] . '/post') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-primary">Post</button></form>
                                <?php else : ?>-<?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if ($purchaseInvoices === []) : ?><tr><td colspan="10" class="text-center text-muted py-4">Belum ada purchase invoice.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h4 class="card-title mb-3">Latest Sales Invoices</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>No</th><th>Customer</th><th>Date</th><th>Due</th><th>Total</th><th>Paid</th><th>Outstanding</th><th>Doc</th><th>Payment</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead>
                <tbody>
                <?php foreach ($salesInvoices as $invoice) : ?>
                    <tr>
                        <td><strong><?= esc($invoice['invoice_no']) ?></strong></td>
                        <td><?= esc(($invoice['customer_code'] ?? '-') . ' - ' . ($invoice['customer_name'] ?? '-')) ?></td>
                        <td><?= esc($invoice['invoice_date']) ?></td>
                        <td><?= esc($invoice['due_date']) ?></td>
                        <td><?= esc($money($invoice['total_amount'])) ?></td>
                        <td><?= esc($money($invoice['paid_amount'] ?? 0)) ?></td>
                        <td><?= esc($money($invoice['outstanding_amount'] ?? $invoice['total_amount'])) ?></td>
                        <td><span class="badge bg-info"><?= esc($invoice['status']) ?></span></td>
                        <td><span class="badge bg-<?= esc($statusBadge((string) ($invoice['payment_status'] ?? 'draft'))) ?>"><?= esc($invoice['payment_status'] ?? 'draft') ?></span></td>
                        <?php if ($canManage) : ?>
                            <td class="text-end">
                                <?php if ($invoice['status'] === 'draft') : ?>
                                    <form method="post" action="<?= site_url('finance/invoices/sales-invoices/' . $invoice['id'] . '/post') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-primary">Post</button></form>
                                <?php else : ?>-<?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if ($salesInvoices === []) : ?><tr><td colspan="10" class="text-center text-muted py-4">Belum ada sales invoice.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-4"><div class="card-body">
    <h4 class="card-title mb-3">Latest Payments</h4>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>No</th><th>Type</th><th>Partner</th><th>Date</th><th>Amount</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($payments as $payment) : ?>
            <?php $partnerId = $payment['payment_type'] === 'incoming' ? ($payment['customer_id'] ?? '') : ($payment['supplier_id'] ?? ''); ?>
            <tr>
                <td><strong><?= esc($payment['payment_no']) ?></strong></td>
                <td><?= esc($payment['payment_type']) ?></td>
                <td><?= esc(($payment['supplier_code'] !== null ? $payment['supplier_code'] . ' - ' . $payment['supplier_name'] : ($payment['customer_code'] !== null ? $payment['customer_code'] . ' - ' . $payment['customer_name'] : '-'))) ?></td>
                <td><?= esc($payment['payment_date']) ?></td>
                <td><?= esc($money($payment['amount'])) ?></td>
                <td><?= esc($payment['status']) ?></td>
                <?php if ($canManage) : ?>
                    <td class="text-end">
                        <?php if ($payment['status'] === 'draft') : ?>
                            <form method="post" action="<?= site_url('finance/invoices/payments/' . $payment['id'] . '/post') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-primary">Post</button></form>
                        <?php else : ?>
                            <button type="button" class="btn btn-sm btn-secondary js-open-allocations" data-payment-id="<?= (int) $payment['id'] ?>" data-payment-no="<?= esc($payment['payment_no']) ?>" data-payment-type="<?= esc($payment['payment_type']) ?>" data-partner-id="<?= esc((string) $partnerId) ?>">Allocations</button>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        <?php if ($payments === []) : ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada pembayaran.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div></div>

<?php if ($canManage) : ?>
<div class="modal fade" id="addPurchaseInvoice"><div class="modal-dialog modal-lg"><form class="modal-content" method="post" action="<?= site_url('finance/invoices/purchase-invoices') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Purchase Invoice</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><div class="col-12"><label class="form-label">Sumber Purchase Order</label><select name="purchase_order_id" id="purchaseOrderSource" class="form-select"><option value="">Manual / tanpa PO</option><?php foreach ($purchaseOrders as $po) : ?><option value="<?= (int) $po['id'] ?>" data-partner="<?= (int) $po['supplier_id'] ?>" data-currency="<?= (int) $po['currency_id'] ?>" data-total="<?= esc((string) $po['total_amount']) ?>"><?= esc($po['po_no'] . ' - ' . ($po['supplier_name'] ?? '-') . ' - ' . $po['status']) ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Supplier</label><select name="supplier_id" id="purchaseInvoicePartner" class="form-select" required><?php foreach ($suppliers as $supplier) : ?><option value="<?= (int) $supplier['id'] ?>"><?= esc($supplier['code'] . ' - ' . $supplier['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Invoice No</label><input name="invoice_no" class="form-control" required></div><div class="col-md-6"><label class="form-label">Invoice Date</label><input type="date" name="invoice_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div><div class="col-md-6"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div><div class="col-md-6"><label class="form-label">Currency</label><select name="currency_id" id="purchaseInvoiceCurrency" class="form-select" required><?php foreach ($currencies as $currency) : ?><option value="<?= (int) $currency['id'] ?>"><?= esc($currency['code']) ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Subtotal</label><input name="subtotal" id="purchaseInvoiceSubtotal" type="number" step="0.0001" min="0" class="form-control" required></div><div class="col-md-6"><label class="form-label">Tax Amount</label><input name="tax_amount" id="purchaseInvoiceTax" type="number" step="0.0001" min="0" class="form-control" required value="0.0000"></div><div class="col-md-6"><label class="form-label">Total Amount</label><input name="total_amount" id="purchaseInvoiceTotal" type="number" step="0.0001" min="0" class="form-control" required></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Draft</button></div></form></div></div>
<div class="modal fade" id="addSalesInvoice"><div class="modal-dialog modal-lg"><form class="modal-content" method="post" action="<?= site_url('finance/invoices/sales-invoices') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Sales Invoice</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><div class="col-12"><label class="form-label">Sumber Sales Order</label><select name="sales_order_id" id="salesOrderSource" class="form-select"><option value="">Manual / tanpa SO</option><?php foreach ($salesOrders as $so) : ?><option value="<?= (int) $so['id'] ?>" data-partner="<?= (int) $so['customer_id'] ?>" data-currency="<?= (int) $so['currency_id'] ?>" data-total="<?= esc((string) $so['total_amount']) ?>"><?= esc($so['order_no'] . ' - ' . ($so['customer_name'] ?? '-') . ' - ' . $so['status']) ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Customer</label><select name="customer_id" id="salesInvoicePartner" class="form-select" required><?php foreach ($customers as $customer) : ?><option value="<?= (int) $customer['id'] ?>"><?= esc($customer['code'] . ' - ' . $customer['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Invoice No</label><input name="invoice_no" class="form-control" required></div><div class="col-md-6"><label class="form-label">Invoice Date</label><input type="date" name="invoice_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div><div class="col-md-6"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div><div class="col-md-6"><label class="form-label">Currency</label><select name="currency_id" id="salesInvoiceCurrency" class="form-select" required><?php foreach ($currencies as $currency) : ?><option value="<?= (int) $currency['id'] ?>"><?= esc($currency['code']) ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Subtotal</label><input name="subtotal" id="salesInvoiceSubtotal" type="number" step="0.0001" min="0" class="form-control" required></div><div class="col-md-6"><label class="form-label">Tax Amount</label><input name="tax_amount" id="salesInvoiceTax" type="number" step="0.0001" min="0" class="form-control" required value="0.0000"></div><div class="col-md-6"><label class="form-label">Total Amount</label><input name="total_amount" id="salesInvoiceTotal" type="number" step="0.0001" min="0" class="form-control" required></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Draft</button></div></form></div></div>
<div class="modal fade" id="addPayment"><div class="modal-dialog"><form class="modal-content" method="post" action="<?= site_url('finance/invoices/payments') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Payment</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><div class="col-md-6"><label class="form-label">Payment Type</label><select id="paymentType" name="payment_type" class="form-select" required><option value="outgoing">Outgoing</option><option value="incoming">Incoming</option></select></div><div class="col-md-6"><label class="form-label">Payment No</label><input name="payment_no" class="form-control" required></div><div class="col-md-6 js-payment-supplier"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select"><?php foreach ($suppliers as $supplier) : ?><option value="<?= (int) $supplier['id'] ?>"><?= esc($supplier['code'] . ' - ' . $supplier['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-6 js-payment-customer d-none"><label class="form-label">Customer</label><select name="customer_id" class="form-select"><?php foreach ($customers as $customer) : ?><option value="<?= (int) $customer['id'] ?>"><?= esc($customer['code'] . ' - ' . $customer['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Payment Date</label><input type="date" name="payment_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div><div class="col-md-6"><label class="form-label">Currency</label><select name="currency_id" class="form-select" required><?php foreach ($currencies as $currency) : ?><option value="<?= (int) $currency['id'] ?>"><?= esc($currency['code']) ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Bank Account</label><select name="bank_account_id" class="form-select"><option value="">-</option><?php foreach ($cashBanks as $bank) : ?><option value="<?= (int) $bank['id'] ?>"><?= esc($bank['code'] . ' - ' . $bank['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Amount</label><input name="amount" type="number" step="0.0001" min="0" class="form-control" required></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Draft</button></div></form></div></div>

<div class="modal fade" id="allocationsModal"><div class="modal-dialog modal-lg"><form class="modal-content" method="post" id="allocationForm" action=""><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Payment Allocation <span id="allocationPaymentNo" class="text-muted"></span></h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body row g-3"><div class="col-12"><div class="alert alert-info mb-0">Incoming payment dialokasikan ke Sales Invoice. Outgoing payment dialokasikan ke Purchase Invoice.</div></div><input type="hidden" name="document_type" id="allocationDocumentType"><div class="col-md-8"><label class="form-label">Invoice Candidate</label><select name="document_id" id="allocationDocumentId" class="form-select" required></select></div><div class="col-md-4"><label class="form-label">Allocated Amount</label><input name="allocated_amount" id="allocationAmount" type="number" step="0.0001" min="0" class="form-control" required></div><div class="col-12"><label class="form-label">Description</label><input name="description" class="form-control" placeholder="Optional"></div><div class="col-12"><div id="allocationExisting" class="small text-muted"></div></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Allocation</button></div></form></div></div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function(){function set(id,v){var e=document.getElementById(id);if(e&&v!==undefined&&v!==null&&v!=='')e.value=String(v)}function money(id,v){var e=document.getElementById(id);if(e)e.value=Number(v||0).toFixed(4)}function bind(source,partner,currency,subtotal,tax,total){var s=document.getElementById(source);if(!s)return;s.addEventListener('change',function(){var o=s.selectedOptions[0];if(!o||!o.value)return;set(partner,o.dataset.partner);set(currency,o.dataset.currency);money(subtotal,o.dataset.total);money(tax,0);money(total,o.dataset.total)})}bind('purchaseOrderSource','purchaseInvoicePartner','purchaseInvoiceCurrency','purchaseInvoiceSubtotal','purchaseInvoiceTax','purchaseInvoiceTotal');bind('salesOrderSource','salesInvoicePartner','salesInvoiceCurrency','salesInvoiceSubtotal','salesInvoiceTax','salesInvoiceTotal');})();
</script>
<script>
(function(){var t=document.getElementById('paymentType'),s=document.querySelector('.js-payment-supplier'),c=document.querySelector('.js-payment-customer');if(!t||!s||!c)return;function u(){if(t.value==='incoming'){s.classList.add('d-none');c.classList.remove('d-none')}else{s.classList.remove('d-none');c.classList.add('d-none')}}t.addEventListener('change',u);u();})();
</script>
<script>
(function(){
    var invoices = <?= json_encode($allocationInvoices, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    function siteUrl(path){var base='<?= site_url('') ?>';return base.replace(/\/$/,'')+'/'+path.replace(/^\//,'')}
    function format(n){return Number(n||0).toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2})}
    function fillCandidates(paymentType, partnerId){
        var select=document.getElementById('allocationDocumentId'), docType=document.getElementById('allocationDocumentType'), amount=document.getElementById('allocationAmount');
        if(!select||!docType||!amount)return;
        var pool=paymentType==='incoming'?invoices.sales:invoices.purchase;
        var type=paymentType==='incoming'?'sales_invoice':'purchase_invoice';
        var key=paymentType==='incoming'?'customer_id':'supplier_id';
        var rows=pool.filter(function(i){return String(i[key]||'')===String(partnerId||'') && Number(i.outstanding_amount||0)>0});
        docType.value=type;
        select.innerHTML=rows.map(function(i){return '<option value="'+i.id+'" data-outstanding="'+Number(i.outstanding_amount||0)+'">'+i.invoice_no+' - Outstanding '+format(i.outstanding_amount)+'</option>'}).join('');
        if(rows.length===0){select.innerHTML='<option value="">Tidak ada invoice outstanding</option>';amount.value='0.0000';return;}
        amount.value=Number(rows[0].outstanding_amount||0).toFixed(4);
        select.onchange=function(){var o=select.selectedOptions[0];amount.value=Number(o?o.dataset.outstanding||0:0).toFixed(4)};
    }
    document.querySelectorAll('.js-open-allocations').forEach(function(btn){btn.addEventListener('click',function(){
        var id=btn.dataset.paymentId, type=btn.dataset.paymentType, partner=btn.dataset.partnerId;
        document.getElementById('allocationPaymentNo').textContent=' - '+(btn.dataset.paymentNo||'');
        document.getElementById('allocationForm').action=siteUrl('finance/invoices/payments/'+id+'/allocations');
        fillCandidates(type,partner);
        var box=document.getElementById('allocationExisting');box.textContent='Loading existing allocations...';
        fetch(siteUrl('finance/invoices/payments/'+id+'/allocations')).then(function(r){return r.json()}).then(function(rows){
            if(!Array.isArray(rows)||rows.length===0){box.textContent='Belum ada allocation untuk payment ini.';return;}
            box.innerHTML='<strong>Existing:</strong><br>'+rows.map(function(r){return (r.document_no||r.document_id)+' = '+format(r.allocated_amount)}).join('<br>');
        }).catch(function(){box.textContent='Existing allocation gagal dimuat.'});
        new bootstrap.Modal(document.getElementById('allocationsModal')).show();
    })});
})();
</script>
<?= $this->endSection() ?>
