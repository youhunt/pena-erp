<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$sales = $side === 'sales';
$baseRoute = $sales ? 'sales/orders' : 'purchasing/orders';
$partnerField = $sales ? 'customer_id' : 'supplier_id';
$partnerLabel = $sales ? 'Customer' : 'Supplier';
$numberField = $sales ? 'order_no' : 'po_no';
$refField = $sales ? 'customer_po_no' : 'supplier_ref_no';
$dateField = $sales ? 'requested_ship_date' : 'expected_receipt_date';
?>
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18"><?= esc($title) ?></h4>
        <p class="text-muted mb-0"><?= esc($tenantContext['company_name']) ?> / Draft order sebelum invoice, delivery, atau receipt.</p>
    </div>
    <?php if ($canManage) : ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrder">Tambah <?= esc($title) ?></button>
    <?php else : ?>
        <span class="badge bg-info">Read only</span>
    <?php endif; ?>
</div>

<?php if (session('message') !== null) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
<?php if (session('errors') !== null) : ?><div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div><?php endif; ?>

<div class="row">
    <div class="col-md-4"><div class="card border-secondary"><div class="card-body"><h5 class="card-title">Total Draft</h5><p class="display-6 mb-0"><?= count($orders) ?></p></div></div></div>
    <div class="col-md-4"><div class="card border-secondary"><div class="card-body"><h5 class="card-title"><?= esc($partnerLabel) ?> Aktif</h5><p class="display-6 mb-0"><?= count($partners) ?></p></div></div></div>
    <div class="col-md-4"><div class="card border-secondary"><div class="card-body"><h5 class="card-title">Product Pilihan</h5><p class="display-6 mb-0"><?= count($products) ?></p></div></div></div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h4 class="card-title mb-3">Daftar <?= esc($title) ?></h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>No</th><th><?= esc($partnerLabel) ?></th><th>Site / Warehouse</th><th>Tanggal</th><th>Reference</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($orders as $order) : ?>
                    <tr>
                        <td><strong><?= esc($order[$numberField]) ?></strong><br><small><?= esc($order['currency_code']) ?></small></td>
                        <td><?= esc(($sales ? $order['customer_code'] : $order['supplier_code']) . ' - ' . ($sales ? $order['customer_name'] : $order['supplier_name'])) ?></td>
                        <td><?= esc($order['branch_code'] . ' / ' . $order['warehouse_code']) ?></td>
                        <td><?= esc($order['order_date']) ?><br><small><?= esc($order[$dateField] ?? '-') ?></small></td>
                        <td><?= esc($order[$refField] ?? '-') ?></td>
                        <td><?= esc(number_format((float) $order['total_amount'], 2, ',', '.')) ?></td>
                        <td><span class="badge bg-secondary"><?= esc($order['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($orders === []) : ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada draft <?= esc(strtolower($title)) ?>.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($canManage) : ?>
<div class="modal fade" id="addOrder">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="post" action="<?= site_url($baseRoute) ?>">
            <?= csrf_field() ?>
            <div class="modal-header"><h5 class="modal-title">Tambah <?= esc($title) ?></h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
            <div class="modal-body row g-2">
                <div class="col-md-6">
                    <label class="form-label"><?= esc($partnerLabel) ?></label>
                    <select id="partnerSelect" name="<?= esc($partnerField) ?>" class="form-select" required>
                        <?php foreach ($partners as $partner) : ?>
                            <option value="<?= (int) $partner['id'] ?>" data-currency="<?= (int) $partner['currency_id'] ?>" data-term="<?= (int) ($partner['default_term_id'] ?? 0) ?>"><?= esc($partner['code'] . ' - ' . $partner['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Order Date</label><input type="date" name="order_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                <div class="col-md-3"><label class="form-label"><?= $sales ? 'Requested Ship' : 'Expected Receipt' ?></label><input type="date" name="<?= esc($dateField) ?>" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                <div class="col-md-4">
                    <label class="form-label">Warehouse</label>
                    <select name="warehouse_id" class="form-select" required>
                        <?php foreach ($warehouses as $warehouse) : ?><option value="<?= (int) $warehouse['id'] ?>"><?= esc($warehouse['branch_code'] . ' / ' . $warehouse['code'] . ' - ' . $warehouse['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Currency</label>
                    <select id="currencySelect" name="currency_id" class="form-select" required>
                        <?php foreach ($currencies as $currency) : ?><option value="<?= (int) $currency['id'] ?>"><?= esc($currency['code']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Terms</label>
                    <select id="termSelect" name="term_id" class="form-select">
                        <option value="">-</option>
                        <?php foreach ($terms as $term) : ?><option value="<?= (int) $term['id'] ?>"><?= esc($term['code']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Doc Code</label>
                    <select name="transaction_code_id" class="form-select" required>
                        <?php foreach ($transactionCodes as $code) : ?><option value="<?= (int) $code['id'] ?>"><?= esc(($code['branch_code'] ?? 'ALL') . ' / ' . $code['code']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label"><?= $sales ? 'Customer PO No' : 'Supplier Ref No' ?></label><input name="<?= esc($refField) ?>" class="form-control"></div>
                <div class="col-md-4">
                    <label class="form-label">Product</label>
                    <select id="productSelect" name="product_id" class="form-select" required>
                        <?php foreach ($products as $product) : ?>
                            <option value="<?= (int) $product['id'] ?>" data-price="<?= esc((string) $product['unit_price'], 'attr') ?>"><?= esc($product['sku'] . ' - ' . $product['name'] . ' / ' . $product['uom_code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Qty</label><input name="qty" type="number" step="0.0001" min="0.0001" value="1" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Unit Price</label><input id="unitPrice" name="unit_price" type="number" step="0.0001" min="0" class="form-control" required></div>
                <div class="col-md-12"><div class="alert alert-info mb-0">Draft ini baru membuat header dan satu line. Posting stok, delivery/receipt, approval, dan invoice linkage masuk tahap berikutnya.</div></div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Simpan Draft</button></div>
        </form>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>

<?php if ($canManage) : ?>
<?= $this->section('scripts') ?>
<script>
(function () {
    var productSelect = document.getElementById('productSelect');
    var unitPrice = document.getElementById('unitPrice');
    var partnerSelect = document.getElementById('partnerSelect');
    var currencySelect = document.getElementById('currencySelect');
    var termSelect = document.getElementById('termSelect');

    function syncProductPrice() {
        if (!productSelect || !unitPrice || !productSelect.selectedOptions.length) return;
        unitPrice.value = productSelect.selectedOptions[0].dataset.price || '0';
    }

    function syncPartnerDefaults() {
        if (!partnerSelect || !partnerSelect.selectedOptions.length) return;
        var selected = partnerSelect.selectedOptions[0];
        if (currencySelect && selected.dataset.currency) currencySelect.value = selected.dataset.currency;
        if (termSelect) termSelect.value = selected.dataset.term === '0' ? '' : selected.dataset.term;
    }

    if (productSelect) productSelect.addEventListener('change', syncProductPrice);
    if (partnerSelect) partnerSelect.addEventListener('change', syncPartnerDefaults);
    syncProductPrice();
    syncPartnerDefaults();
})();
</script>
<?= $this->endSection() ?>
<?php endif; ?>
