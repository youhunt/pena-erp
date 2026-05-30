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
                <thead><tr><th>No</th><th><?= esc($partnerLabel) ?></th><th>Site / Warehouse</th><th>Tanggal</th><th>Reference</th><th>Total</th><th>Status</th><?php if ($canManage && $sales) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead>
                <tbody>
                <?php foreach ($orders as $order) : ?>
                    <tr>
                        <td><strong><?= esc($order[$numberField]) ?></strong><br><small><?= esc($order['currency_code']) ?></small></td>
                        <td><?= esc(($sales ? $order['customer_code'] : $order['supplier_code']) . ' - ' . ($sales ? $order['customer_name'] : $order['supplier_name'])) ?></td>
                        <td><?= esc($order['branch_code'] . ' / ' . $order['warehouse_code']) ?></td>
                        <td><?= esc($order['order_date']) ?><br><small><?= esc($order[$dateField] ?? '-') ?></small></td>
                        <td><?= esc($order[$refField] ?? '-') ?></td>
                        <td><?= esc(number_format((float) $order['total_amount'], 2, ',', '.')) ?></td>
                        <td>
                            <?php if (($order['status'] ?? '') === 'confirmed') : ?>
                                <span class="badge bg-success">confirmed</span>
                            <?php else : ?>
                                <span class="badge bg-secondary"><?= esc($order['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <?php if ($canManage && $sales) : ?>
                            <td class="text-end">
                                <?php if (($order['status'] ?? '') === 'draft') : ?>
                                    <form method="post" action="<?= site_url('sales/orders/' . (int) $order['id'] . '/confirm') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-success" onclick="return confirm('Confirm Sales Order <?= esc($order[$numberField]) ?>?')">
                                            <i class="bx bx-check me-1"></i>Confirm
                                        </button>
                                    </form>
                                <?php else : ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if ($orders === []) : ?><tr><td colspan="<?= ($canManage && $sales) ? 8 : 7 ?>" class="text-center text-muted py-4">Belum ada draft <?= esc(strtolower($title)) ?>.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($canManage) : ?>
<div class="modal fade" id="addOrder">
    <div class="modal-dialog modal-xl">
        <form class="modal-content" method="post" action="<?= site_url($baseRoute) ?>" id="orderForm">
            <?= csrf_field() ?>
            <div class="modal-header"><h5 class="modal-title">Tambah <?= esc($title) ?></h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
            <div class="modal-body row g-3">
                <div class="col-md-6"><label class="form-label"><?= esc($partnerLabel) ?></label><select id="partnerSelect" name="<?= esc($partnerField) ?>" class="form-select" required><?php foreach ($partners as $partner) : ?><option value="<?= (int) $partner['id'] ?>" data-currency="<?= (int) $partner['currency_id'] ?>" data-term="<?= (int) ($partner['default_term_id'] ?? 0) ?>"><?= esc($partner['code'] . ' - ' . $partner['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Order Date</label><input type="date" name="order_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                <div class="col-md-3"><label class="form-label"><?= $sales ? 'Requested Ship' : 'Expected Receipt' ?></label><input type="date" name="<?= esc($dateField) ?>" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                <div class="col-md-4"><label class="form-label">Warehouse</label><select name="warehouse_id" class="form-select" required><?php foreach ($warehouses as $warehouse) : ?><option value="<?= (int) $warehouse['id'] ?>"><?= esc($warehouse['branch_code'] . ' / ' . $warehouse['code'] . ' - ' . $warehouse['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Currency</label><select id="currencySelect" name="currency_id" class="form-select" required><?php foreach ($currencies as $currency) : ?><option value="<?= (int) $currency['id'] ?>"><?= esc($currency['code']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Terms</label><select id="termSelect" name="term_id" class="form-select"><option value="">-</option><?php foreach ($terms as $term) : ?><option value="<?= (int) $term['id'] ?>"><?= esc($term['code']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><label class="form-label">Doc Code</label><select name="transaction_code_id" class="form-select" required><?php foreach ($transactionCodes as $code) : ?><option value="<?= (int) $code['id'] ?>"><?= esc(($code['branch_code'] ?? 'ALL') . ' / ' . $code['code']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label"><?= $sales ? 'Customer PO No' : 'Supplier Ref No' ?></label><input name="<?= esc($refField) ?>" class="form-control"></div>
                <div class="col-12"><div class="d-flex align-items-center justify-content-between mb-2"><label class="form-label mb-0 fw-semibold">Order Lines</label><button type="button" class="btn btn-sm btn-outline-primary" id="addLineBtn"><i class="bx bx-plus me-1"></i>Tambah Line</button></div><div class="table-responsive"><table class="table table-bordered align-middle mb-0" id="orderLinesTable"><thead class="table-light"><tr><th style="min-width:280px">Product</th><th style="width:150px" class="text-end">Qty</th><th style="width:180px" class="text-end">Unit Price</th><th style="width:180px" class="text-end">Subtotal</th><th style="width:60px" class="text-center">Aksi</th></tr></thead><tbody id="orderLinesBody"></tbody><tfoot><tr><th colspan="3" class="text-end">Subtotal Preview</th><th class="text-end" id="orderSubtotalPreview">0,00</th><th></th></tr></tfoot></table></div><div class="form-text">Draft ini dapat memiliki banyak line. Pajak dihitung ulang di backend berdasarkan master product tax.</div></div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary" id="submitOrderBtn">Simpan Draft</button></div>
        </form>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>

<?php if ($canManage) : ?>
<?= $this->section('scripts') ?>
<script>
(function () {
    'use strict';
    var products = <?= json_encode(array_map(static fn ($product) => ['id' => (int) $product['id'], 'label' => (string) ($product['sku'] . ' - ' . $product['name'] . ' / ' . $product['uom_code']), 'price' => (string) $product['unit_price']], $products), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    var form = document.getElementById('orderForm');
    var partnerSelect = document.getElementById('partnerSelect');
    var currencySelect = document.getElementById('currencySelect');
    var termSelect = document.getElementById('termSelect');
    var addLineBtn = document.getElementById('addLineBtn');
    var linesBody = document.getElementById('orderLinesBody');
    var subtotalPreview = document.getElementById('orderSubtotalPreview');
    var submitBtn = document.getElementById('submitOrderBtn');
    var lineIndex = 0;
    function normalizeDecimal(value) { return String(value || '').replace(/\s/g, '').replace(',', '.'); }
    function parseDecimal(value) { var parsed = parseFloat(normalizeDecimal(value)); return Number.isFinite(parsed) ? parsed : 0; }
    function money(value) { return value.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function productOptions(selectedId) { var html = '<option value="">-- Pilih Product --</option>'; products.forEach(function (product) { html += '<option value="' + product.id + '" data-price="' + product.price + '"' + (String(product.id) === String(selectedId || '') ? ' selected' : '') + '>' + product.label + '</option>'; }); return html; }
    function addLine(defaults) { defaults = defaults || {}; var idx = lineIndex++; var row = document.createElement('tr'); row.className = 'order-line-row'; row.innerHTML = '<td><select name="lines[' + idx + '][product_id]" class="form-select line-product" required>' + productOptions(defaults.product_id) + '</select></td>' + '<td><input name="lines[' + idx + '][qty]" type="text" inputmode="decimal" class="form-control text-end line-qty" value="' + (defaults.qty || '1.0000') + '" required></td>' + '<td><input name="lines[' + idx + '][unit_price]" type="text" inputmode="decimal" class="form-control text-end line-price" value="' + (defaults.unit_price || '0.0000') + '" required></td>' + '<td class="text-end line-subtotal">0,00</td>' + '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger line-remove"><i class="bx bx-trash"></i></button></td>'; linesBody.appendChild(row); syncLinePrice(row, defaults.unit_price ? false : true); calculateTotals(); }
    function syncLinePrice(row, force) { var productSelect = row.querySelector('.line-product'); var priceInput = row.querySelector('.line-price'); if (!productSelect || !priceInput || !productSelect.selectedOptions.length) return; var price = productSelect.selectedOptions[0].dataset.price || '0.0000'; if (force || parseDecimal(priceInput.value) <= 0) { priceInput.value = normalizeDecimal(price); } }
    function calculateTotals() { var total = 0; var hasValidLine = false; linesBody.querySelectorAll('.order-line-row').forEach(function (row) { var productId = row.querySelector('.line-product').value; var qty = parseDecimal(row.querySelector('.line-qty').value); var price = parseDecimal(row.querySelector('.line-price').value); var subtotal = qty * price; row.querySelector('.line-subtotal').textContent = money(subtotal); total += subtotal; if (productId && qty > 0 && price >= 0) hasValidLine = true; }); subtotalPreview.textContent = money(total); submitBtn.disabled = !hasValidLine; }
    function syncPartnerDefaults() { if (!partnerSelect || !partnerSelect.selectedOptions.length) return; var selected = partnerSelect.selectedOptions[0]; if (currencySelect && selected.dataset.currency) currencySelect.value = selected.dataset.currency; if (termSelect) termSelect.value = selected.dataset.term === '0' ? '' : selected.dataset.term; }
    if (partnerSelect) partnerSelect.addEventListener('change', syncPartnerDefaults);
    if (addLineBtn) addLineBtn.addEventListener('click', function () { addLine(); });
    if (linesBody) { linesBody.addEventListener('change', function (event) { var row = event.target.closest('.order-line-row'); if (!row) return; if (event.target.classList.contains('line-product')) syncLinePrice(row, true); calculateTotals(); }); linesBody.addEventListener('input', calculateTotals); linesBody.addEventListener('click', function (event) { var removeBtn = event.target.closest('.line-remove'); if (!removeBtn) return; var rows = linesBody.querySelectorAll('.order-line-row'); if (rows.length <= 1) return; removeBtn.closest('.order-line-row').remove(); calculateTotals(); }); }
    if (form) { form.addEventListener('submit', function (event) { var valid = true; linesBody.querySelectorAll('.order-line-row').forEach(function (row) { var product = row.querySelector('.line-product'); var qty = row.querySelector('.line-qty'); var price = row.querySelector('.line-price'); var qtyValue = parseDecimal(qty.value); var priceValue = parseDecimal(price.value); qty.value = qtyValue.toFixed(4); price.value = priceValue.toFixed(4); if (!product.value || qtyValue <= 0 || priceValue < 0) valid = false; }); if (!valid) { event.preventDefault(); alert('Minimal satu line harus valid. Product wajib dipilih, qty harus lebih dari nol, dan harga tidak boleh negatif.'); } }); }
    syncPartnerDefaults(); addLine();
})();
</script>
<?= $this->endSection() ?>
<?php endif; ?>
