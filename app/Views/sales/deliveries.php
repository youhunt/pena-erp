<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Delivery Order<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18">Delivery Order</h4>
        <p class="text-muted mb-0"><?= esc($tenantContext['company_name']) ?> / Pengiriman barang dari Sales Order confirmed.</p>
    </div>
    <?php if ($canManage) : ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeliveryModal">
            <i class="bx bx-plus me-1"></i>Tambah DO
        </button>
    <?php else : ?>
        <span class="badge bg-info">Read only</span>
    <?php endif; ?>
</div>

<?php if (session('message') !== null) : ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= esc(session('message')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (session('errors') !== null) : ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <?php
    $draft  = array_filter((array) $deliveries, static fn ($r) => $r->status === 'draft');
    $posted = array_filter((array) $deliveries, static fn ($r) => $r->status === 'posted');
    ?>
    <div class="col-md-4"><div class="card border-secondary"><div class="card-body"><h5 class="card-title">Total DO</h5><p class="display-6 mb-0"><?= count($deliveries) ?></p></div></div></div>
    <div class="col-md-4"><div class="card border-warning"><div class="card-body"><h5 class="card-title">Draft</h5><p class="display-6 mb-0"><?= count($draft) ?></p></div></div></div>
    <div class="col-md-4"><div class="card border-success"><div class="card-body"><h5 class="card-title">Posted</h5><p class="display-6 mb-0"><?= count($posted) ?></p></div></div></div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h4 class="card-title mb-3">Daftar Delivery Order</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>No. DO</th>
                        <th>Sales Order</th>
                        <th>Customer</th>
                        <th>Warehouse</th>
                        <th>Tanggal</th>
                        <th class="text-end">Total Qty</th>
                        <th class="text-end">Total Amount</th>
                        <th>Status</th>
                        <?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deliveries as $r) : ?>
                        <tr>
                            <td><strong><?= esc($r->delivery_number) ?></strong></td>
                            <td><?= esc($r->order_no) ?></td>
                            <td><?= esc(($r->customer_code ?? '-') . ' - ' . ($r->customer_name ?? '-')) ?></td>
                            <td><?= esc(($r->warehouse_code ?? '-') . ' / ' . ($r->warehouse_name ?? '-')) ?></td>
                            <td><?= esc($r->delivery_date) ?></td>
                            <td class="text-end"><?= number_format((float) $r->total_qty, 4) ?></td>
                            <td class="text-end"><?= number_format((float) $r->total_amount, 2, ',', '.') ?></td>
                            <td>
                                <?php if ($r->status === 'draft') : ?>
                                    <span class="badge bg-warning text-dark">draft</span>
                                <?php else : ?>
                                    <span class="badge bg-success">posted</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($canManage) : ?>
                                <td class="text-end">
                                    <?php if ($r->status === 'draft') : ?>
                                        <form method="post" action="<?= site_url('sales/deliveries/' . (int) $r->id . '/post') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-success" onclick="return confirm('Post DO <?= esc($r->delivery_number) ?> dan kurangi stok?')">
                                                <i class="bx bx-upload me-1"></i>Post
                                            </button>
                                        </form>
                                    <?php else : ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($deliveries === []) : ?>
                        <tr><td colspan="<?= $canManage ? 9 : 8 ?>" class="text-center text-muted py-4">Belum ada Delivery Order.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($canManage) : ?>
<div class="modal fade" id="addDeliveryModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form class="modal-content" method="post" action="<?= site_url('sales/deliveries/create') ?>" id="deliveryForm">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Tambah Delivery Order</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-7">
                    <label class="form-label fw-semibold">Sales Order <span class="text-danger">*</span></label>
                    <?php if ($salesOrders === []) : ?>
                        <div class="alert alert-warning mb-0">
                            Belum ada Sales Order <strong>confirmed</strong> yang masih punya item tersisa.
                        </div>
                    <?php else : ?>
                        <select id="soSelect" name="sales_order_id" class="form-select" required>
                            <option value="">-- Pilih Sales Order --</option>
                            <?php foreach ($salesOrders as $so) : ?>
                                <option value="<?= (int) $so['id'] ?>" data-warehouse="<?= (int) $so['warehouse_id'] ?>">
                                    <?= esc($so['order_no']) ?> — <?= esc($so['customer_name'] ?? '') ?>
                                    <?php if (isset($so['total_qty_remaining'])) : ?>(sisa <?= esc(number_format((float) $so['total_qty_remaining'], 4)) ?>)<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="col-md-5" id="warehouseSection" style="display:none">
                    <label class="form-label fw-semibold">Warehouse Sumber <span class="text-danger">*</span></label>
                    <select id="warehouseSelect" name="warehouse_id" class="form-select" required>
                        <option value="">-- Pilih Warehouse --</option>
                        <?php foreach ($warehouses as $wh) : ?>
                            <option value="<?= (int) $wh['id'] ?>"><?= esc(($wh['branch_code'] ?? '-') . ' / ' . $wh['code'] . ' — ' . $wh['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12" id="soItemSection" style="display:none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label fw-semibold mb-0">Item SO <span class="text-danger">*</span></label>
                        <div id="soItemLoading" class="form-text text-muted d-none"><span class="spinner-border spinner-border-sm me-1"></span>Memuat items…</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end" style="width:130px">Sisa SO</th>
                                    <th class="text-end" style="width:150px">Qty Kirim</th>
                                    <th class="text-end" style="width:160px">Unit Price</th>
                                    <th class="text-end" style="width:170px">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="deliveryItemsBody"><tr><td colspan="5" class="text-center text-muted py-3">Pilih SO terlebih dahulu.</td></tr></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-end">Total</th>
                                    <th class="text-end" id="totalQtyPreview">0.0000</th>
                                    <th></th>
                                    <th class="text-end" id="totalAmountPreview">0,00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="qtyHint" class="form-text text-muted">Isi qty kirim pada minimal satu item. Boleh memakai koma atau titik.</div>
                </div>

                <div class="col-12">
                    <div class="alert alert-info mb-0 py-2 small">
                        Draft DO belum mengurangi stok. Klik <strong>Post</strong> untuk mengurangi stok dan mencatat stock movement keluar.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="deliverySubmitBtn" disabled><i class="bx bx-save me-1"></i>Simpan Draft</button>
            </div>
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
    var form = document.getElementById('deliveryForm');
    var soSelect = document.getElementById('soSelect');
    var soItemSection = document.getElementById('soItemSection');
    var soItemLoading = document.getElementById('soItemLoading');
    var warehouseSection = document.getElementById('warehouseSection');
    var warehouseSelect = document.getElementById('warehouseSelect');
    var itemsBody = document.getElementById('deliveryItemsBody');
    var submitBtn = document.getElementById('deliverySubmitBtn');
    var totalQtyPreview = document.getElementById('totalQtyPreview');
    var totalAmountPreview = document.getElementById('totalAmountPreview');
    var qtyHint = document.getElementById('qtyHint');
    var baseUrl = '<?= site_url('') ?>'.replace(/\/$/, '');

    function siteUrl(path) { return baseUrl + '/' + path.replace(/^\//, ''); }
    function normalizeDecimal(value) { return String(value || '').replace(/\s/g, '').replace(',', '.'); }
    function parseDecimal(value) { var parsed = parseFloat(normalizeDecimal(value)); return Number.isFinite(parsed) ? parsed : 0; }
    function money(value) { return value.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

    function resetItems() {
        if (itemsBody) itemsBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Pilih SO terlebih dahulu.</td></tr>';
        soItemSection.style.display = 'none';
        warehouseSection.style.display = 'none';
        totalQtyPreview.textContent = '0.0000';
        totalAmountPreview.textContent = '0,00';
        qtyHint.textContent = 'Isi qty kirim pada minimal satu item. Boleh memakai koma atau titik.';
        submitBtn.disabled = true;
    }

    function calculateTotals() {
        var totalQty = 0;
        var totalAmount = 0;
        var valid = false;
        var invalid = false;
        itemsBody.querySelectorAll('.delivery-line-row').forEach(function (row) {
            var qtyInput = row.querySelector('.delivery-qty');
            var maxQty = parseDecimal(qtyInput.dataset.max);
            var qty = parseDecimal(qtyInput.value);
            var unitPrice = parseDecimal(qtyInput.dataset.price);
            var subtotal = qty * unitPrice;
            row.querySelector('.delivery-subtotal').textContent = money(subtotal);
            if (qty > 0) { valid = true; totalQty += qty; totalAmount += subtotal; }
            if (qty < 0 || qty > maxQty) invalid = true;
        });
        totalQtyPreview.textContent = totalQty.toFixed(4);
        totalAmountPreview.textContent = money(totalAmount);
        if (invalid) { qtyHint.textContent = 'Ada qty yang melebihi sisa SO atau bernilai negatif.'; submitBtn.disabled = true; return false; }
        if (!valid) { qtyHint.textContent = 'Minimal satu item harus memiliki qty kirim lebih dari nol.'; submitBtn.disabled = true; return false; }
        qtyHint.textContent = 'Total qty kirim: ' + totalQty.toFixed(4);
        submitBtn.disabled = false;
        return true;
    }

    function renderItems(items) {
        if (!Array.isArray(items) || items.length === 0) {
            itemsBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada item tersisa pada SO ini.</td></tr>';
            submitBtn.disabled = true;
            return;
        }
        var html = '';
        items.forEach(function (it, idx) {
            var remaining = parseDecimal(it.qty_remaining);
            var price = parseDecimal(it.unit_price);
            var label = String((it.product_sku || '-') + ' — ' + (it.product_name || '-') + ' / ' + (it.uom_code || '-'));
            html += '<tr class="delivery-line-row">' +
                '<td><strong>' + label + '</strong><input type="hidden" name="items[' + idx + '][sales_order_item_id]" value="' + it.id + '"></td>' +
                '<td class="text-end">' + remaining.toFixed(4) + '</td>' +
                '<td><input type="text" inputmode="decimal" class="form-control text-end delivery-qty" name="items[' + idx + '][qty_delivered]" value="0.0000" data-max="' + remaining.toFixed(4) + '" data-price="' + price.toFixed(4) + '"></td>' +
                '<td class="text-end">' + money(price) + '</td>' +
                '<td class="text-end delivery-subtotal">0,00</td>' +
            '</tr>';
        });
        itemsBody.innerHTML = html;
        calculateTotals();
    }

    function loadSoItems(soId) {
        resetItems();
        if (!soId) return;
        soItemSection.style.display = '';
        warehouseSection.style.display = '';
        soItemLoading.classList.remove('d-none');
        fetch(siteUrl('sales/deliveries/so-items/' + soId))
            .then(function (r) { if (!r.ok) throw new Error('Server error ' + r.status); return r.json(); })
            .then(function (items) {
                soItemLoading.classList.add('d-none');
                renderItems(items);
                var opt = soSelect.selectedOptions[0];
                if (opt && opt.dataset.warehouse && warehouseSelect) warehouseSelect.value = opt.dataset.warehouse;
            })
            .catch(function (err) {
                soItemLoading.classList.add('d-none');
                itemsBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Gagal memuat items: ' + err.message + '</td></tr>';
                submitBtn.disabled = true;
            });
    }

    if (soSelect) soSelect.addEventListener('change', function () { loadSoItems(soSelect.value); });
    if (itemsBody) itemsBody.addEventListener('input', function (event) { if (event.target.classList.contains('delivery-qty')) calculateTotals(); });
    if (form) form.addEventListener('submit', function (event) {
        if (!calculateTotals()) { event.preventDefault(); return; }
        itemsBody.querySelectorAll('.delivery-qty').forEach(function (input) { input.value = parseDecimal(input.value).toFixed(4); });
    });
    var modal = document.getElementById('addDeliveryModal');
    if (modal) modal.addEventListener('hidden.bs.modal', function () { if (soSelect) soSelect.value = ''; resetItems(); });
}());
</script>
<?= $this->endSection() ?>
<?php endif; ?>
