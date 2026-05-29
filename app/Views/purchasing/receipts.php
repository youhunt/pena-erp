<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Goods Receipt<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18">Goods Receipt</h4>
        <p class="text-muted mb-0"><?= esc($tenantContext['company_name']) ?> / Penerimaan barang dari Purchase Order ke stock ledger.</p>
    </div>
    <?php if ($canManage) : ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGrModal">
            <i class="bx bx-plus me-1"></i>Tambah GR
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
    $draft  = array_filter((array) $receipts, static fn ($r) => $r->status === 'draft');
    $posted = array_filter((array) $receipts, static fn ($r) => $r->status === 'posted');
    ?>
    <div class="col-md-4">
        <div class="card border-secondary"><div class="card-body"><h5 class="card-title">Total GR</h5><p class="display-6 mb-0"><?= count($receipts) ?></p></div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-warning"><div class="card-body"><h5 class="card-title">Draft</h5><p class="display-6 mb-0"><?= count($draft) ?></p></div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-success"><div class="card-body"><h5 class="card-title">Posted</h5><p class="display-6 mb-0"><?= count($posted) ?></p></div></div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h4 class="card-title mb-3">Daftar Goods Receipt</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>No. GR</th>
                        <th>Purchase Order</th>
                        <th>Supplier</th>
                        <th>Warehouse</th>
                        <th>Tanggal</th>
                        <th class="text-end">Total Qty</th>
                        <th class="text-end">Total Amount</th>
                        <th>Status</th>
                        <?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receipts as $r) : ?>
                        <tr>
                            <td><strong><?= esc($r->receipt_number) ?></strong></td>
                            <td><?= esc($r->po_no) ?></td>
                            <td><?= esc($r->supplier_name ?? '-') ?></td>
                            <td><?= esc($r->warehouse_name) ?></td>
                            <td><?= esc($r->receipt_date) ?></td>
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
                                        <form method="post" action="<?= site_url('purchasing/receipts/' . (int) $r->id . '/post') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-success" onclick="return confirm('Post GR <?= esc($r->receipt_number) ?> ke stock ledger?')">
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
                    <?php if ($receipts === []) : ?>
                        <tr>
                            <td colspan="<?= $canManage ? 9 : 8 ?>" class="text-center text-muted py-4">
                                Belum ada Goods Receipt. Buat draft dari Purchase Order yang ada.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($canManage) : ?>
<div class="modal fade" id="addGrModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form class="modal-content" method="post" action="<?= site_url('purchasing/receipts/create') ?>" id="grForm">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Tambah Goods Receipt</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-7">
                    <label class="form-label fw-semibold">Purchase Order <span class="text-danger">*</span></label>
                    <?php if ($purchaseOrders === []) : ?>
                        <div class="alert alert-warning mb-0">
                            Belum ada Purchase Order berstatus <strong>draft</strong> yang masih punya item tersisa.
                            Buat PO terlebih dahulu di menu <a href="<?= site_url('purchasing/orders') ?>">Purchasing Orders</a>.
                        </div>
                    <?php else : ?>
                        <select id="poSelect" name="purchase_order_id" class="form-select" required>
                            <option value="">-- Pilih Purchase Order --</option>
                            <?php foreach ($purchaseOrders as $po) : ?>
                                <option value="<?= (int) $po['id'] ?>" data-warehouse="<?= (int) $po['warehouse_id'] ?>">
                                    <?= esc($po['po_no']) ?> — <?= esc($po['supplier_name'] ?? '') ?>
                                    <?php if (isset($po['total_qty_remaining'])) : ?>
                                        (sisa <?= esc(number_format((float) $po['total_qty_remaining'], 4)) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="col-md-5" id="warehouseSection" style="display:none">
                    <label class="form-label fw-semibold">Warehouse Tujuan <span class="text-danger">*</span></label>
                    <select id="warehouseSelect" name="warehouse_id" class="form-select" required>
                        <option value="">-- Pilih Warehouse --</option>
                        <?php foreach ($warehouses as $wh) : ?>
                            <option value="<?= (int) $wh['id'] ?>">
                                <?= esc(($wh['branch_code'] ?? '-') . ' / ' . $wh['code'] . ' — ' . $wh['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12" id="poItemSection" style="display:none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label fw-semibold mb-0">Item PO <span class="text-danger">*</span></label>
                        <div id="poItemLoading" class="form-text text-muted d-none">
                            <span class="spinner-border spinner-border-sm me-1"></span>Memuat items…
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end" style="width:130px">Sisa PO</th>
                                    <th class="text-end" style="width:150px">Qty Diterima</th>
                                    <th class="text-end" style="width:160px">Unit Cost</th>
                                    <th class="text-end" style="width:170px">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="receiptItemsBody">
                                <tr><td colspan="5" class="text-center text-muted py-3">Pilih PO terlebih dahulu.</td></tr>
                            </tbody>
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
                    <div id="qtyHint" class="form-text text-muted">Isi qty diterima pada minimal satu item. Boleh memakai koma atau titik.</div>
                </div>

                <div class="col-12">
                    <div class="alert alert-info mb-0 py-2 small">
                        Draft GR belum memindahkan stok. Klik <strong>Post</strong> pada baris GR untuk update stok, stock movement, dan sisa PO.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="grSubmitBtn" disabled>
                    <i class="bx bx-save me-1"></i>Simpan Draft
                </button>
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

    var form = document.getElementById('grForm');
    var poSelect = document.getElementById('poSelect');
    var poItemSection = document.getElementById('poItemSection');
    var poItemLoading = document.getElementById('poItemLoading');
    var warehouseSection = document.getElementById('warehouseSection');
    var warehouseSelect = document.getElementById('warehouseSelect');
    var itemsBody = document.getElementById('receiptItemsBody');
    var submitBtn = document.getElementById('grSubmitBtn');
    var totalQtyPreview = document.getElementById('totalQtyPreview');
    var totalAmountPreview = document.getElementById('totalAmountPreview');
    var qtyHint = document.getElementById('qtyHint');

    var baseUrl = '<?= site_url('') ?>'.replace(/\/$/, '');

    function siteUrl(path) {
        return baseUrl + '/' + path.replace(/^\//, '');
    }

    function normalizeDecimal(value) {
        return String(value || '').replace(/\s/g, '').replace(',', '.');
    }

    function parseDecimal(value) {
        var parsed = parseFloat(normalizeDecimal(value));
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function money(value) {
        return value.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function resetItems() {
        if (itemsBody) {
            itemsBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Pilih PO terlebih dahulu.</td></tr>';
        }
        poItemSection.style.display = 'none';
        warehouseSection.style.display = 'none';
        totalQtyPreview.textContent = '0.0000';
        totalAmountPreview.textContent = '0,00';
        qtyHint.textContent = 'Isi qty diterima pada minimal satu item. Boleh memakai koma atau titik.';
        submitBtn.disabled = true;
    }

    function calculateTotals() {
        var totalQty = 0;
        var totalAmount = 0;
        var valid = false;
        var invalid = false;

        itemsBody.querySelectorAll('.receipt-line-row').forEach(function (row) {
            var qtyInput = row.querySelector('.receipt-qty');
            var maxQty = parseDecimal(qtyInput.dataset.max);
            var qty = parseDecimal(qtyInput.value);
            var unitCost = parseDecimal(qtyInput.dataset.cost);
            var subtotal = qty * unitCost;

            row.querySelector('.receipt-subtotal').textContent = money(subtotal);

            if (qty > 0) {
                valid = true;
                totalQty += qty;
                totalAmount += subtotal;
            }

            if (qty < 0 || qty > maxQty) {
                invalid = true;
            }
        });

        totalQtyPreview.textContent = totalQty.toFixed(4);
        totalAmountPreview.textContent = money(totalAmount);

        if (invalid) {
            qtyHint.textContent = 'Ada qty yang melebihi sisa PO atau bernilai negatif.';
            submitBtn.disabled = true;
            return false;
        }

        if (!valid) {
            qtyHint.textContent = 'Minimal satu item harus memiliki qty diterima lebih dari nol.';
            submitBtn.disabled = true;
            return false;
        }

        qtyHint.textContent = 'Total qty diterima: ' + totalQty.toFixed(4);
        submitBtn.disabled = false;
        return true;
    }

    function renderItems(items) {
        if (!Array.isArray(items) || items.length === 0) {
            itemsBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada item tersisa pada PO ini.</td></tr>';
            submitBtn.disabled = true;
            return;
        }

        var html = '';
        items.forEach(function (it, idx) {
            var remaining = parseDecimal(it.qty_remaining);
            var unitCost = parseDecimal(it.unit_price);
            var productLabel = String((it.product_sku || '-') + ' — ' + (it.product_name || '-') + ' / ' + (it.uom_code || '-'));

            html += '<tr class="receipt-line-row">' +
                '<td>' +
                    '<strong>' + productLabel + '</strong>' +
                    '<input type="hidden" name="items[' + idx + '][purchase_order_item_id]" value="' + it.id + '">' +
                '</td>' +
                '<td class="text-end">' + remaining.toFixed(4) + '</td>' +
                '<td><input type="text" inputmode="decimal" class="form-control text-end receipt-qty" name="items[' + idx + '][qty_received]" value="0.0000" data-max="' + remaining.toFixed(4) + '" data-cost="' + unitCost.toFixed(4) + '"></td>' +
                '<td class="text-end">' + money(unitCost) + '</td>' +
                '<td class="text-end receipt-subtotal">0,00</td>' +
            '</tr>';
        });

        itemsBody.innerHTML = html;
        calculateTotals();
    }

    function loadPoItems(poId) {
        resetItems();
        if (!poId) return;

        poItemSection.style.display = '';
        warehouseSection.style.display = '';
        poItemLoading.classList.remove('d-none');

        fetch(siteUrl('purchasing/receipts/po-items/' + poId))
            .then(function (r) {
                if (!r.ok) throw new Error('Server error ' + r.status);
                return r.json();
            })
            .then(function (items) {
                poItemLoading.classList.add('d-none');
                renderItems(items);

                var poOpt = poSelect.selectedOptions[0];
                if (poOpt && poOpt.dataset.warehouse && warehouseSelect) {
                    warehouseSelect.value = poOpt.dataset.warehouse;
                }
            })
            .catch(function (err) {
                poItemLoading.classList.add('d-none');
                itemsBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Gagal memuat items: ' + err.message + '</td></tr>';
                submitBtn.disabled = true;
            });
    }

    if (poSelect) {
        poSelect.addEventListener('change', function () {
            loadPoItems(poSelect.value);
        });
    }

    if (itemsBody) {
        itemsBody.addEventListener('input', function (event) {
            if (!event.target.classList.contains('receipt-qty')) return;
            calculateTotals();
        });
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            if (!calculateTotals()) {
                event.preventDefault();
                return;
            }

            itemsBody.querySelectorAll('.receipt-qty').forEach(function (input) {
                input.value = parseDecimal(input.value).toFixed(4);
            });
        });
    }

    var modal = document.getElementById('addGrModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            if (poSelect) poSelect.value = '';
            resetItems();
        });
    }
}());
</script>
<?= $this->endSection() ?>
<?php endif; ?>
