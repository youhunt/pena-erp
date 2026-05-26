<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Setup Master<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18">Setup Master</h4>
        <p class="text-muted mb-0"><?= esc($tenantContext['company_name']) ?> / Referensi transaksi dan organisasi</p>
    </div>
    <?php if (! $canManage) : ?><span class="badge bg-info">Read only</span><?php endif; ?>
</div>
<?php if (session('message') !== null) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
<?php if (session('errors') !== null) : ?>
    <div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<?php if ($canManage) : ?>
<div class="row">
    <div class="col-xl-4">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Transaction Code</h4>
            <form method="post" action="<?= site_url('setup/transaction-codes') ?>" class="row g-2">
                <?= csrf_field() ?>
                <div class="col-6"><label class="form-label">Module</label><input name="module" class="form-control" placeholder="sales" required></div>
                <div class="col-6"><label class="form-label">Site</label><select name="branch_id" class="form-select"><option value="">Company wide</option><?php foreach ($branches as $branch) : ?><option value="<?= esc($branch['id']) ?>"><?= esc($branch['code']) ?></option><?php endforeach; ?></select></div>
                <div class="col-6"><label class="form-label">Code</label><input name="code" class="form-control" placeholder="SO" required></div>
                <div class="col-6"><label class="form-label">Prefix</label><input name="prefix" class="form-control" placeholder="SO-JKT-" required></div>
                <div class="col-6"><label class="form-label">Digit</label><input type="number" min="3" max="12" name="number_length" class="form-control" value="6" required></div>
                <div class="col-6"><label class="form-label">Reset</label><select name="reset_rule" class="form-select"><option value="never">Never</option><option value="yearly">Yearly</option><option value="monthly">Monthly</option><option value="daily">Daily</option></select></div>
                <div class="col-12"><button class="btn btn-primary mt-2">Simpan Transaction Code</button></div>
            </form>
        </div></div>
    </div>
    <div class="col-xl-4">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Department</h4>
            <form method="post" action="<?= site_url('setup/departments') ?>" class="row g-2">
                <?= csrf_field() ?>
                <div class="col-4"><label class="form-label">Kode</label><input name="code" class="form-control" placeholder="OPS" required></div>
                <div class="col-8"><label class="form-label">Nama</label><input name="name" class="form-control" required></div>
                <div class="col-12"><button class="btn btn-primary mt-2">Simpan Department</button></div>
            </form>
            <hr>
            <h4 class="card-title mb-3">Currency</h4>
            <form method="post" action="<?= site_url('setup/currencies') ?>" class="row g-2 align-items-end">
                <?= csrf_field() ?>
                <div class="col-4"><label class="form-label">ISO</label><input name="code" class="form-control" placeholder="IDR" required></div>
                <div class="col-5"><label class="form-label">Nama</label><input name="name" class="form-control" required></div>
                <div class="col-3"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_base" value="1" id="is-base"><label class="form-check-label" for="is-base">Base</label></div></div>
                <div class="col-12"><button class="btn btn-primary">Simpan Currency</button></div>
            </form>
        </div></div>
    </div>
    <div class="col-xl-4">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">VAT</h4>
            <form method="post" action="<?= site_url('setup/tax-codes') ?>" class="row g-2">
                <?= csrf_field() ?>
                <div class="col-5"><label class="form-label">Code</label><input name="code" class="form-control" placeholder="PPN11" required></div>
                <div class="col-7"><label class="form-label">Nama</label><input name="name" class="form-control" placeholder="PPN 11%" required></div>
                <div class="col-6"><label class="form-label">Usage</label><select name="tax_type" class="form-select"><option value="both">Both</option><option value="input">Input</option><option value="output">Output</option></select></div>
                <div class="col-6"><label class="form-label">Rate</label><input type="number" step="0.000001" min="0" name="rate" class="form-control" placeholder="0.110000" required></div>
                <div class="col-12"><button class="btn btn-primary mt-2">Simpan VAT</button></div>
            </form>
        </div></div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h4 class="card-title">Address Master</h4>
            <form method="get" action="<?= site_url('setup') ?>" class="d-flex gap-2">
                <input class="form-control form-control-sm" name="village_q" value="<?= esc($villageSearch) ?>" placeholder="Cari desa/kota untuk pilihan alamat">
                <button class="btn btn-outline-secondary btn-sm">Cari Wilayah</button>
            </form>
        </div>
        <form method="post" action="<?= site_url('setup/addresses') ?>" class="row g-3 align-items-end">
            <?= csrf_field() ?>
            <div class="col-xl-2"><label class="form-label">Code</label><input name="code" class="form-control" placeholder="HO-JKT" required></div>
            <div class="col-xl-2"><label class="form-label">Label</label><input name="label" class="form-control" required></div>
            <div class="col-xl-3"><label class="form-label">Alamat</label><input name="address_line1" class="form-control" required></div>
            <div class="col-xl-2"><label class="form-label">Country</label><select name="country_id" class="form-select" required><?php foreach ($countries as $country) : ?><option value="<?= esc($country['id']) ?>"><?= esc($country['iso2'] . ' - ' . $country['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-xl-2"><label class="form-label">City / Village</label><select name="village_id" class="form-select"><option value="">-</option><?php foreach ($villages as $village) : ?><option value="<?= esc($village['id']) ?>"><?= esc($village['name'] . ', ' . $village['regency']) ?></option><?php endforeach; ?></select></div>
            <div class="col-xl-1"><label class="form-label">Postal</label><input name="postal_code" class="form-control"></div>
            <div class="col-12"><button class="btn btn-primary">Simpan Address Master</button></div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-xl-6">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Transaction Code Terdaftar</h4>
            <table class="table table-sm align-middle"><thead><tr><th>Module / Code</th><th>Scope</th><th>Format</th></tr></thead><tbody>
                <?php foreach ($transactionCodes as $number) : ?><tr><td><?= esc($number['module'] . ' / ' . $number['code']) ?></td><td><?= esc($number['branch_code'] ?? 'Company') ?></td><td><?= esc($number['prefix']) ?><?= str_repeat('0', (int) $number['number_length']) ?></td></tr><?php endforeach; ?>
                <?php if ($transactionCodes === []) : ?><tr><td colspan="3" class="text-muted">Belum ada transaction code.</td></tr><?php endif; ?>
            </tbody></table>
        </div></div>
    </div>
    <div class="col-xl-6">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Organization, Currency & VAT</h4>
            <div class="mb-2"><strong>Departments:</strong> <?= esc(implode(', ', array_column($departments, 'code')) ?: '-') ?></div>
            <div class="mb-2"><strong>Currencies:</strong> <?= esc(implode(', ', array_column($currencies, 'code')) ?: '-') ?></div>
            <div><strong>VAT:</strong> <?php foreach ($taxCodes as $tax) : ?><span class="badge bg-light text-dark me-1"><?= esc($tax['code']) ?> (<?= esc((string) ((float) $tax['rate'] * 100)) ?>%)</span><?php endforeach; ?><?php if ($taxCodes === []) : ?>-<?php endif; ?></div>
        </div></div>
    </div>
</div>
<div class="card"><div class="card-body">
    <h4 class="card-title mb-3">Address Master Terdaftar</h4>
    <table class="table align-middle mb-0"><thead><tr><th>Code / Label</th><th>Alamat</th><th>Country / City</th><th>Postal</th></tr></thead><tbody>
        <?php foreach ($addresses as $address) : ?><tr><td><strong><?= esc($address['code']) ?></strong><br><small><?= esc($address['label']) ?></small></td><td><?= esc($address['address_line1']) ?></td><td><?= esc($address['country_name']) ?><?= $address['city_name'] ? ' / ' . esc($address['city_name']) : '' ?></td><td><?= esc($address['postal_code'] ?? '-') ?></td></tr><?php endforeach; ?>
        <?php if ($addresses === []) : ?><tr><td colspan="4" class="text-muted text-center">Belum ada address master.</td></tr><?php endif; ?>
    </tbody></table>
</div></div>
<?= $this->endSection() ?>
