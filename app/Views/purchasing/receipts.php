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

<!-- Summary cards -->
<div class="row">
    <?php
    $draft  = array_filter((array) $receipts, fn ($r) => $r->status === 'draft');
    $posted = array_filter((array) $receipts, fn ($r) => $r->status === 'posted');
    ?>
    <div class="col-md-4">
        <div class="card border-secondary">
            <div class="card-body">
                <h5 class="card-title">Total GR</h5>
                <p class="display-6 mb-0"><?= count($receipts) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-warning">
            <div class="card-body">
                <h5 class="card-title">Draft</h5>
                <p class="display-6 mb-0"><?= count($draft) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title">Posted</h5>
                <p class="display-6 mb-0"><?= count($posted) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Tabel GR -->
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
<!-- Modal Tambah GR -->
<div class="modal fade" id="addGrModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="post" action="<?= site_url('purchasing/receipts/create') ?>" id="grForm">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Tambah Goods Receipt</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">

                <!-- Step 1: Pilih Purchase Order -->
                <div class="col-12">
                    <label class="form-label fw-semibold">Purchase Order <span class="text-danger">*</span></label>
                    <?php if ($purchaseOrders === []) : ?>
                        <div class="alert alert-warning mb-0">
                            Belum ada Purchase Order berstatus <strong>draft</strong> pada company ini.
                            Buat PO terlebih dahulu di menu <a href="<?= site_url('purchasing/orders') ?>">Purchasing Orders</a>.
                        </div>
                    <?php else : ?>
                        <select id="poSelect" name="purchase_order_id" class="form-select" required>
                            <option value="">-- Pilih Purchase Order --</option>
                            <?php foreach ($purchaseOrders as $po) : ?>
                                <option value="<?= (int) $po['id'] ?>"
                                        data-warehouse="<?= (int) $po['warehouse_id'] ?>">
                                    <?= esc($po['po_no']) ?> — <?= esc($po['supplier_name'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- Step 2: Pilih Item PO (diisi via AJAX) -->
                <div class="col-md-12" id="poItemSection" style="display:none">
                    <label class="form-label fw-semibold">Item PO <span class="text-danger">*</span></label>
                    <select id="poItemSelect" name="purchase_order_item_id" class="form-select" required>
                        <option value="">-- Pilih item setelah memilih PO --</option>
                    </select>
                    <div id="poItemLoading" class="form-text text-muted d-none">
                        <span class="spinner-border spinner-border-sm me-1"></span>Memuat items…
                    </div>
                </div>

                <!-- Step 3: Warehouse -->
                <div class="col-md-6" id="warehouseSection" style="display:none">
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

                <!-- Step 4: Qty Received -->
                <div class="col-md-3" id="qtySection" style="display:none">
                    <label class="form-label fw-semibold">Qty Diterima <span class="text-danger">*</span></label>
                    <input id="qtyReceived" type="number" name="qty_received" step="0.0001" min="0.0001"
                           class="form-control" required>
                    <div id="qtyHint" class="form-text text-muted"></div>
                </div>

                <!-- Readonly: Unit Cost -->
                <div class="col-md-3" id="costSection" style="display:none">
                    <label class="form-label fw-semibold">Unit Cost (PO)</label>
                    <input id="unitCostDisplay" type="text" class="form-control" readonly>
                </div>

                <!-- Info note -->
                <div class="col-12">
                    <div class="alert alert-info mb-0 py-2 small">
                        Setelah draft disimpan, klik <strong>Post</strong> pada baris tabel untuk memindahkan stok ke ledger.
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

    var poSelect        = document.getElementById('poSelect');
    var poItemSelect    = document.getElementById('poItemSelect');
    var poItemSection   = document.getElementById('poItemSection');
    var poItemLoading   = document.getElementById('poItemLoading');
    var warehouseSection = document.getElementById('warehouseSection');
    var warehouseSelect = document.getElementById('warehouseSelect');
    var qtySection      = document.getElementById('qtySection');
    var costSection     = document.getElementById('costSection');
    var qtyReceived     = document.getElementById('qtyReceived');
    var qtyHint         = document.getElementById('qtyHint');
    var unitCostDisplay = document.getElementById('unitCostDisplay');
    var submitBtn       = document.getElementById('grSubmitBtn');

    var baseUrl = '<?= site_url('') ?>'.replace(/\/$/, '');

    function siteUrl(path) {
        return baseUrl + '/' + path.replace(/^\//, '');
    }

    function resetItemFields() {
        poItemSelect.innerHTML = '<option value="">-- Pilih item setelah memilih PO --</option>';
        poItemSection.style.display = 'none';
        warehouseSection.style.display = 'none';
        qtySection.style.display = 'none';
        costSection.style.display = 'none';
        qtyReceived.value = '';
        unitCostDisplay.value = '';
        qtyHint.textContent = '';
        submitBtn.disabled = true;
    }

    function loadPoItems(poId) {
        resetItemFields();
        if (!poId) return;

        poItemSection.style.display = '';
        poItemLoading.classList.remove('d-none');
        poItemSelect.disabled = true;

        fetch(siteUrl('purchasing/receipts/po-items/' + poId))
            .then(function (r) {
                if (!r.ok) throw new Error('Server error ' + r.status);
                return r.json();
            })
            .then(function (items) {
                poItemLoading.classList.add('d-none');
                poItemSelect.disabled = false;

                if (!Array.isArray(items) || items.length === 0) {
                    poItemSelect.innerHTML = '<option value="">Tidak ada item tersisa pada PO ini.</option>';
                    return;
                }

                var opts = ['<option value="">-- Pilih item --</option>'];
                items.forEach(function (it) {
                    opts.push(
                        '<option value="' + it.id + '"' +
                        ' data-qty="' + it.qty_remaining + '"' +
                        ' data-cost="' + it.unit_price + '">' +
                        it.product_sku + ' — ' + it.product_name +
                        ' (sisa: ' + parseFloat(it.qty_remaining).toFixed(4) + ' ' + it.uom_code + ')' +
                        '</option>'
                    );
                });
                poItemSelect.innerHTML = opts.join('');

                // Pre-select warehouse dari PO jika ada
                var poOpt = poSelect.selectedOptions[0];
                if (poOpt && poOpt.dataset.warehouse && warehouseSelect) {
                    warehouseSelect.value = poOpt.dataset.warehouse;
                }
                warehouseSection.style.display = '';
            })
            .catch(function (err) {
                poItemLoading.classList.add('d-none');
                poItemSelect.innerHTML = '<option value="">Gagal memuat items: ' + err.message + '</option>';
                poItemSelect.disabled = false;
            });
    }

    function onItemChange() {
        var opt = poItemSelect.selectedOptions[0];
        if (!opt || !opt.value) {
            qtySection.style.display = 'none';
            costSection.style.display = 'none';
            submitBtn.disabled = true;
            return;
        }

        var qtyRemaining = parseFloat(opt.dataset.qty || 0);
        var unitCost     = parseFloat(opt.dataset.cost || 0);

        qtyReceived.max   = qtyRemaining;
        qtyReceived.value = qtyRemaining.toFixed(4);
        qtyHint.textContent = 'Maks: ' + qtyRemaining.toFixed(4);
        unitCostDisplay.value = unitCost.toLocaleString('id-ID', { minimumFractionDigits: 4 });

        qtySection.style.display = '';
        costSection.style.display = '';
        submitBtn.disabled = false;
    }

    if (poSelect) {
        poSelect.addEventListener('change', function () {
            loadPoItems(poSelect.value);
        });
    }

    if (poItemSelect) {
        poItemSelect.addEventListener('change', onItemChange);
    }

    // Reset saat modal ditutup
    var modal = document.getElementById('addGrModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            if (poSelect) poSelect.value = '';
            resetItemFields();
        });
    }
}());
</script>
<?= $this->endSection() ?>
<?php endif; ?>