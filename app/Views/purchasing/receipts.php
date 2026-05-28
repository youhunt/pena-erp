<?php $this->extend('layouts/dashboard'); ?>
<?= $this->section('title') ?>Goods Receipt<?= $this->endSection() ?>

<?php $this->section('content'); ?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Goods Receipt</h4>
            <p class="text-muted mb-0">Create goods receipt from purchase order and post to stock ledger.</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">Create Goods Receipt (1 item MVP)</div>
        <div class="card-body">
            <form action="<?= site_url('purchasing/receipts/create') ?>" method="post" class="row g-3">
                <?= csrf_field() ?>

                <div class="col-md-4">
                    <label class="form-label">Purchase Order</label>
                    <select name="purchase_order_id" class="form-select" required>
                        <option value="">-- select PO --</option>
                        <?php foreach ($purchaseOrders as $po): ?>
                            <option value="<?= (int) $po->id ?>"><?= esc($po->po_no) ?> (<?= esc($po->status) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">PO Item ID</label>
                    <input type="number" name="purchase_order_item_id" class="form-control" required />
                </div>

                <div class="col-md-4">
                    <label class="form-label">Warehouse ID</label>
                    <input type="number" name="warehouse_id" class="form-control" required />
                </div>

                <div class="col-md-4">
                    <label class="form-label">Qty Received</label>
                    <input type="number" step="0.0001" name="qty_received" class="form-control" value="1" required />
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save Draft</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light">Receipt List</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Receipt #</th>
                        <th>PO</th>
                        <th>Warehouse</th>
                        <th>Date</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receipts as $r): ?>
                        <tr>
                            <td><?= (int) $r->id ?></td>
                            <td><?= esc($r->receipt_number) ?></td>
                            <td><?= esc($r->po_no) ?></td>
                            <td><?= esc($r->warehouse_name) ?></td>
                            <td><?= esc($r->receipt_date) ?></td>
                            <td><?= number_format((float) $r->total_qty, 4) ?></td>
                            <td><?= esc($r->status) ?></td>
                            <td>
                                <?php if ($r->status === 'draft'): ?>
                                    <a href="<?= site_url('purchasing/receipts/post/' . (int) $r->id) ?>" class="btn btn-sm btn-success" onclick="return confirm('Post this goods receipt?')">Post</a>
                                <?php else: ?>
                                    <span class="text-muted">Posted</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>