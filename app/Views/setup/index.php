<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Setup Master<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$statusBadge = static fn (string $status): string => $status === 'active'
    ? '<span class="badge bg-success-subtle text-success">Active</span>'
    : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>';
$statusButton = static fn (string $status): string => $status === 'active' ? 'Nonaktifkan' : 'Aktifkan';
$nextStatus = static fn (string $status): string => $status === 'active' ? 'inactive' : 'active';
?>
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18">Setup Master</h4>
        <p class="text-muted mb-0"><?= esc($tenantContext['company_name']) ?> / Referensi transaksi dan organisasi</p>
    </div>
    <?php if ($canManage) : ?>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"><i class="ri-add-line me-1"></i>Tambah Master</button>
            <div class="dropdown-menu dropdown-menu-end">
                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addTransactionCode">Transaction Code</button>
                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addDepartment">Department</button>
                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addCurrency">Currency</button>
                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addTaxCode">VAT</button>
                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addAddress">Address Master</button>
            </div>
        </div>
    <?php else : ?>
        <span class="badge bg-info">Read only</span>
    <?php endif; ?>
</div>
<?php if (session('message') !== null) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
<?php if (session('errors') !== null) : ?>
    <div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<div class="row mb-3">
    <div class="col-md"><div class="card mb-0"><div class="card-body py-3"><span class="text-muted">Transaction Code</span><h4 class="mb-0"><?= count($transactionCodes) ?></h4></div></div></div>
    <div class="col-md"><div class="card mb-0"><div class="card-body py-3"><span class="text-muted">Department</span><h4 class="mb-0"><?= count($departments) ?></h4></div></div></div>
    <div class="col-md"><div class="card mb-0"><div class="card-body py-3"><span class="text-muted">Currency</span><h4 class="mb-0"><?= count($currencies) ?></h4></div></div></div>
    <div class="col-md"><div class="card mb-0"><div class="card-body py-3"><span class="text-muted">VAT</span><h4 class="mb-0"><?= count($taxCodes) ?></h4></div></div></div>
    <div class="col-md"><div class="card mb-0"><div class="card-body py-3"><span class="text-muted">Address</span><h4 class="mb-0"><?= count($addresses) ?></h4></div></div></div>
</div>

<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-numbering">Transaction Code</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-department">Department</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-currency">Currency</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tax">VAT</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-address">Address Master</button></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-numbering">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Module / Code</th><th>Site</th><th>Format</th><th>Reset</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Action</th><?php endif; ?></tr></thead>
                        <tbody>
                        <?php foreach ($transactionCodes as $number) : ?>
                            <tr>
                                <td><strong><?= esc($number['code']) ?></strong><br><small class="text-muted"><?= esc($number['module']) ?></small></td>
                                <td><?= esc($number['branch_code'] ?? 'Company wide') ?></td>
                                <td><?= esc($number['prefix']) ?><?= str_repeat('0', (int) $number['number_length']) ?></td>
                                <td><?= esc(ucfirst($number['reset_rule'])) ?></td>
                                <td><?= $statusBadge((string) $number['status']) ?></td>
                                <?php if ($canManage) : ?><td class="text-end text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary js-edit-number" data-bs-toggle="modal" data-bs-target="#editTransactionCode" data-id="<?= (int) $number['id'] ?>" data-code="<?= esc($number['code'], 'attr') ?>" data-module="<?= esc($number['module'], 'attr') ?>" data-prefix="<?= esc($number['prefix'], 'attr') ?>" data-length="<?= (int) $number['number_length'] ?>" data-reset="<?= esc($number['reset_rule'], 'attr') ?>">Edit</button>
                                    <form method="post" action="<?= site_url('setup/status/transaction-code/' . $number['id']) ?>" class="d-inline"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $nextStatus((string) $number['status']) ?>"><button class="btn btn-sm btn-outline-secondary"><?= $statusButton((string) $number['status']) ?></button></form>
                                </td><?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($transactionCodes === []) : ?><tr><td colspan="<?= $canManage ? 6 : 5 ?>" class="text-center text-muted py-4">Belum ada transaction code.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="tab-department">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Code</th><th>Department Name</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Action</th><?php endif; ?></tr></thead>
                        <tbody>
                        <?php foreach ($departments as $department) : ?><tr>
                            <td><strong><?= esc($department['code']) ?></strong></td><td><?= esc($department['name']) ?></td><td><?= $statusBadge((string) $department['status']) ?></td>
                            <?php if ($canManage) : ?><td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-outline-primary js-edit-department" data-bs-toggle="modal" data-bs-target="#editDepartment" data-id="<?= (int) $department['id'] ?>" data-code="<?= esc($department['code'], 'attr') ?>" data-name="<?= esc($department['name'], 'attr') ?>">Edit</button>
                                <form method="post" action="<?= site_url('setup/status/department/' . $department['id']) ?>" class="d-inline"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $nextStatus((string) $department['status']) ?>"><button class="btn btn-sm btn-outline-secondary"><?= $statusButton((string) $department['status']) ?></button></form>
                            </td><?php endif; ?>
                        </tr><?php endforeach; ?>
                        <?php if ($departments === []) : ?><tr><td colspan="<?= $canManage ? 4 : 3 ?>" class="text-center text-muted py-4">Belum ada department.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="tab-currency">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>ISO Code</th><th>Name</th><th>Base</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Action</th><?php endif; ?></tr></thead>
                        <tbody>
                        <?php foreach ($currencies as $currency) : ?><tr>
                            <td><strong><?= esc($currency['code']) ?></strong></td><td><?= esc($currency['name']) ?></td><td><?= $currency['is_base'] ? 'Yes' : '-' ?></td><td><?= $statusBadge((string) $currency['status']) ?></td>
                            <?php if ($canManage) : ?><td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-outline-primary js-edit-currency" data-bs-toggle="modal" data-bs-target="#editCurrency" data-id="<?= (int) $currency['id'] ?>" data-code="<?= esc($currency['code'], 'attr') ?>" data-name="<?= esc($currency['name'], 'attr') ?>" data-base="<?= $currency['is_base'] ? '1' : '0' ?>">Edit</button>
                                <form method="post" action="<?= site_url('setup/status/currency/' . $currency['id']) ?>" class="d-inline"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $nextStatus((string) $currency['status']) ?>"><button class="btn btn-sm btn-outline-secondary"><?= $statusButton((string) $currency['status']) ?></button></form>
                            </td><?php endif; ?>
                        </tr><?php endforeach; ?>
                        <?php if ($currencies === []) : ?><tr><td colspan="<?= $canManage ? 5 : 4 ?>" class="text-center text-muted py-4">Belum ada currency.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="tab-tax">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Code</th><th>Name</th><th>Usage</th><th>Rate</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Action</th><?php endif; ?></tr></thead>
                        <tbody>
                        <?php foreach ($taxCodes as $tax) : ?><tr>
                            <td><strong><?= esc($tax['code']) ?></strong></td><td><?= esc($tax['name']) ?></td><td><?= esc(ucfirst($tax['tax_type'])) ?></td><td><?= esc(number_format((float) $tax['rate'] * 100, 2)) ?>%</td><td><?= $statusBadge((string) $tax['status']) ?></td>
                            <?php if ($canManage) : ?><td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-outline-primary js-edit-tax" data-bs-toggle="modal" data-bs-target="#editTaxCode" data-id="<?= (int) $tax['id'] ?>" data-code="<?= esc($tax['code'], 'attr') ?>" data-name="<?= esc($tax['name'], 'attr') ?>" data-type="<?= esc($tax['tax_type'], 'attr') ?>" data-rate="<?= esc($tax['rate'], 'attr') ?>">Edit</button>
                                <form method="post" action="<?= site_url('setup/status/tax-code/' . $tax['id']) ?>" class="d-inline"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $nextStatus((string) $tax['status']) ?>"><button class="btn btn-sm btn-outline-secondary"><?= $statusButton((string) $tax['status']) ?></button></form>
                            </td><?php endif; ?>
                        </tr><?php endforeach; ?>
                        <?php if ($taxCodes === []) : ?><tr><td colspan="<?= $canManage ? 6 : 5 ?>" class="text-center text-muted py-4">Belum ada VAT.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="tab-address">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="text-muted mb-0">Alamat legal, gudang, dan operasional company aktif.</p>
                    <form method="get" action="<?= site_url('setup') ?>" class="d-flex gap-2">
                        <input class="form-control form-control-sm" name="village_q" value="<?= esc($villageSearch) ?>" placeholder="Cari desa/kota">
                        <button class="btn btn-outline-secondary btn-sm">Cari Wilayah</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Code / Label</th><th>Address</th><th>Country / City</th><th>Postal</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Action</th><?php endif; ?></tr></thead>
                        <tbody>
                        <?php foreach ($addresses as $address) : ?><tr>
                            <td><strong><?= esc($address['code']) ?></strong><br><small><?= esc($address['label']) ?></small></td><td><?= esc($address['address_line1']) ?></td><td><?= esc($address['country_name']) ?><?= $address['city_name'] ? ' / ' . esc($address['city_name']) : '' ?></td><td><?= esc($address['postal_code'] ?? '-') ?></td><td><?= $statusBadge((string) $address['status']) ?></td>
                            <?php if ($canManage) : ?><td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-outline-primary js-edit-address" data-bs-toggle="modal" data-bs-target="#editAddress" data-id="<?= (int) $address['id'] ?>" data-code="<?= esc($address['code'], 'attr') ?>" data-label="<?= esc($address['label'], 'attr') ?>" data-line="<?= esc($address['address_line1'], 'attr') ?>" data-country="<?= (int) $address['country_id'] ?>" data-village="<?= (int) ($address['village_id'] ?? 0) ?>" data-village-label="<?= esc(trim(($address['village_name'] ?? '') . ', ' . ($address['city_name'] ?? ''), ', '), 'attr') ?>" data-postal="<?= esc($address['postal_code'] ?? '', 'attr') ?>">Edit</button>
                                <form method="post" action="<?= site_url('setup/status/address/' . $address['id']) ?>" class="d-inline"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $nextStatus((string) $address['status']) ?>"><button class="btn btn-sm btn-outline-secondary"><?= $statusButton((string) $address['status']) ?></button></form>
                            </td><?php endif; ?>
                        </tr><?php endforeach; ?>
                        <?php if ($addresses === []) : ?><tr><td colspan="<?= $canManage ? 6 : 5 ?>" class="text-center text-muted py-4">Belum ada address master.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($canManage) : ?>
<div class="modal fade" id="addDepartment" tabindex="-1"><div class="modal-dialog"><form method="post" action="<?= site_url('setup/departments') ?>" class="modal-content"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Department</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-3"><div class="col-4"><label class="form-label">Code</label><input name="code" class="form-control" required></div><div class="col-8"><label class="form-label">Name</label><input name="name" class="form-control" required></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan</button></div></form></div></div>
<div class="modal fade" id="editDepartment" tabindex="-1"><div class="modal-dialog"><form method="post" data-action="<?= site_url('setup/departments') ?>" class="modal-content"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit Department</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-3"><div class="col-4"><label class="form-label">Code</label><input data-field="code" class="form-control" readonly></div><div class="col-8"><label class="form-label">Name</label><input name="name" data-field="name" class="form-control" required></div><div class="col-12"><small class="text-muted">Code dikunci setelah master digunakan.</small></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>

<div class="modal fade" id="addCurrency" tabindex="-1"><div class="modal-dialog"><form method="post" action="<?= site_url('setup/currencies') ?>" class="modal-content"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Currency</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-3"><div class="col-4"><label class="form-label">ISO</label><input name="code" class="form-control" placeholder="IDR" required></div><div class="col-8"><label class="form-label">Name</label><input name="name" class="form-control" required></div><div class="col-12 form-check ms-2"><input class="form-check-input" type="checkbox" name="is_base" value="1" id="add-is-base"><label class="form-check-label" for="add-is-base">Base currency</label></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan</button></div></form></div></div>
<div class="modal fade" id="editCurrency" tabindex="-1"><div class="modal-dialog"><form method="post" data-action="<?= site_url('setup/currencies') ?>" class="modal-content"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit Currency</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-3"><div class="col-4"><label class="form-label">ISO</label><input data-field="code" class="form-control" readonly></div><div class="col-8"><label class="form-label">Name</label><input name="name" data-field="name" class="form-control" required></div><div class="col-12 form-check ms-2"><input class="form-check-input" type="checkbox" name="is_base" value="1" data-field="base" id="edit-is-base"><label class="form-check-label" for="edit-is-base">Base currency</label></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>

<div class="modal fade" id="addTaxCode" tabindex="-1"><div class="modal-dialog"><form method="post" action="<?= site_url('setup/tax-codes') ?>" class="modal-content"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah VAT</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-3"><div class="col-5"><label class="form-label">Code</label><input name="code" class="form-control" required></div><div class="col-7"><label class="form-label">Name</label><input name="name" class="form-control" required></div><div class="col-6"><label class="form-label">Usage</label><select name="tax_type" class="form-select"><option value="both">Both</option><option value="input">Input</option><option value="output">Output</option></select></div><div class="col-6"><label class="form-label">Rate</label><input type="number" step="0.000001" min="0" name="rate" class="form-control" required></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan</button></div></form></div></div>
<div class="modal fade" id="editTaxCode" tabindex="-1"><div class="modal-dialog"><form method="post" data-action="<?= site_url('setup/tax-codes') ?>" class="modal-content"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit VAT</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-3"><div class="col-5"><label class="form-label">Code</label><input data-field="code" class="form-control" readonly></div><div class="col-7"><label class="form-label">Name</label><input name="name" data-field="name" class="form-control" required></div><div class="col-6"><label class="form-label">Usage</label><select name="tax_type" data-field="type" class="form-select"><option value="both">Both</option><option value="input">Input</option><option value="output">Output</option></select></div><div class="col-6"><label class="form-label">Rate</label><input type="number" step="0.000001" min="0" name="rate" data-field="rate" class="form-control" required></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>

<div class="modal fade" id="addTransactionCode" tabindex="-1"><div class="modal-dialog"><form method="post" action="<?= site_url('setup/transaction-codes') ?>" class="modal-content"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Transaction Code</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-3"><div class="col-6"><label class="form-label">Module</label><input name="module" class="form-control" required></div><div class="col-6"><label class="form-label">Site</label><select name="branch_id" class="form-select"><option value="">Company wide</option><?php foreach ($branches as $branch) : ?><option value="<?= (int) $branch['id'] ?>"><?= esc($branch['code']) ?></option><?php endforeach; ?></select></div><div class="col-6"><label class="form-label">Code</label><input name="code" class="form-control" required></div><div class="col-6"><label class="form-label">Prefix</label><input name="prefix" class="form-control" required></div><div class="col-6"><label class="form-label">Digit</label><input type="number" min="3" max="12" name="number_length" value="6" class="form-control" required></div><div class="col-6"><label class="form-label">Reset</label><select name="reset_rule" class="form-select"><option value="never">Never</option><option value="yearly">Yearly</option><option value="monthly">Monthly</option><option value="daily">Daily</option></select></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan</button></div></form></div></div>
<div class="modal fade" id="editTransactionCode" tabindex="-1"><div class="modal-dialog"><form method="post" data-action="<?= site_url('setup/transaction-codes') ?>" class="modal-content"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit Transaction Code</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-3"><div class="col-6"><label class="form-label">Code</label><input data-field="code" class="form-control" readonly></div><div class="col-6"><label class="form-label">Module</label><input name="module" data-field="module" class="form-control" required></div><div class="col-6"><label class="form-label">Prefix</label><input name="prefix" data-field="prefix" class="form-control" required></div><div class="col-3"><label class="form-label">Digit</label><input type="number" min="3" max="12" name="number_length" data-field="length" class="form-control" required></div><div class="col-3"><label class="form-label">Reset</label><select name="reset_rule" data-field="reset" class="form-select"><option value="never">Never</option><option value="yearly">Yearly</option><option value="monthly">Monthly</option><option value="daily">Daily</option></select></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>

<div class="modal fade" id="addAddress" tabindex="-1"><div class="modal-dialog modal-lg"><form method="post" action="<?= site_url('setup/addresses') ?>" class="modal-content"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Address Master</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-3"><div class="col-md-3"><label class="form-label">Code</label><input name="code" class="form-control" required></div><div class="col-md-4"><label class="form-label">Label</label><input name="label" class="form-control" required></div><div class="col-md-5"><label class="form-label">Address</label><input name="address_line1" class="form-control" required></div><div class="col-md-4"><label class="form-label">Country</label><select name="country_id" class="form-select" required><?php foreach ($countries as $country) : ?><option value="<?= (int) $country['id'] ?>"><?= esc($country['iso2'] . ' - ' . $country['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-5"><label class="form-label">Village / City</label><select name="village_id" class="form-select"><option value="">-</option><?php foreach ($villages as $village) : ?><option value="<?= (int) $village['id'] ?>"><?= esc($village['name'] . ', ' . $village['regency']) ?></option><?php endforeach; ?></select><small class="text-muted">Gunakan pencarian wilayah pada tab Address sebelum memilih.</small></div><div class="col-md-3"><label class="form-label">Postal</label><input name="postal_code" class="form-control"></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan</button></div></form></div></div>
<div class="modal fade" id="editAddress" tabindex="-1"><div class="modal-dialog modal-lg"><form method="post" data-action="<?= site_url('setup/addresses') ?>" class="modal-content"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit Address Master</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-3"><div class="col-md-3"><label class="form-label">Code</label><input data-field="code" class="form-control" readonly></div><div class="col-md-4"><label class="form-label">Label</label><input name="label" data-field="label" class="form-control" required></div><div class="col-md-5"><label class="form-label">Address</label><input name="address_line1" data-field="line" class="form-control" required></div><div class="col-md-4"><label class="form-label">Country</label><select name="country_id" data-field="country" class="form-select" required><?php foreach ($countries as $country) : ?><option value="<?= (int) $country['id'] ?>"><?= esc($country['iso2'] . ' - ' . $country['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-5"><label class="form-label">Village / City</label><select name="village_id" data-field="village" class="form-select"><option value="">-</option><?php foreach ($villages as $village) : ?><option value="<?= (int) $village['id'] ?>"><?= esc($village['name'] . ', ' . $village['regency']) ?></option><?php endforeach; ?></select></div><div class="col-md-3"><label class="form-label">Postal</label><input name="postal_code" data-field="postal" class="form-control"></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>
<?php endif; ?>
<?= $this->endSection() ?>

<?php if ($canManage) : ?>
<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.js-edit-department').forEach(function (button) {
    button.addEventListener('click', function () {
        var form = document.querySelector('#editDepartment form');
        form.action = form.dataset.action + '/' + button.dataset.id;
        form.querySelector('[data-field="code"]').value = button.dataset.code;
        form.querySelector('[data-field="name"]').value = button.dataset.name;
    });
});
document.querySelectorAll('.js-edit-currency').forEach(function (button) {
    button.addEventListener('click', function () {
        var form = document.querySelector('#editCurrency form');
        form.action = form.dataset.action + '/' + button.dataset.id;
        form.querySelector('[data-field="code"]').value = button.dataset.code;
        form.querySelector('[data-field="name"]').value = button.dataset.name;
        form.querySelector('[data-field="base"]').checked = button.dataset.base === '1';
    });
});
document.querySelectorAll('.js-edit-tax').forEach(function (button) {
    button.addEventListener('click', function () {
        var form = document.querySelector('#editTaxCode form');
        form.action = form.dataset.action + '/' + button.dataset.id;
        form.querySelector('[data-field="code"]').value = button.dataset.code;
        form.querySelector('[data-field="name"]').value = button.dataset.name;
        form.querySelector('[data-field="type"]').value = button.dataset.type;
        form.querySelector('[data-field="rate"]').value = button.dataset.rate;
    });
});
document.querySelectorAll('.js-edit-number').forEach(function (button) {
    button.addEventListener('click', function () {
        var form = document.querySelector('#editTransactionCode form');
        form.action = form.dataset.action + '/' + button.dataset.id;
        form.querySelector('[data-field="code"]').value = button.dataset.code;
        form.querySelector('[data-field="module"]').value = button.dataset.module;
        form.querySelector('[data-field="prefix"]').value = button.dataset.prefix;
        form.querySelector('[data-field="length"]').value = button.dataset.length;
        form.querySelector('[data-field="reset"]').value = button.dataset.reset;
    });
});
document.querySelectorAll('.js-edit-address').forEach(function (button) {
    button.addEventListener('click', function () {
        var form = document.querySelector('#editAddress form');
        var village = form.querySelector('[data-field="village"]');
        var id = button.dataset.village;
        form.action = form.dataset.action + '/' + button.dataset.id;
        form.querySelector('[data-field="code"]').value = button.dataset.code;
        form.querySelector('[data-field="label"]').value = button.dataset.label;
        form.querySelector('[data-field="line"]').value = button.dataset.line;
        form.querySelector('[data-field="country"]').value = button.dataset.country;
        form.querySelector('[data-field="postal"]').value = button.dataset.postal;
        if (id !== '0' && ! village.querySelector('option[value="' + id + '"]')) {
            village.add(new Option(button.dataset.villageLabel, id));
        }
        village.value = id === '0' ? '' : id;
    });
});
</script>
<?= $this->endSection() ?>
<?php endif; ?>
