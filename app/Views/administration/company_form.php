<?= $this->extend('layouts/dashboard') ?>

<?php $isEdit = $company !== null; ?>
<?= $this->section('title') ?><?= $isEdit ? 'Edit Company' : 'Tambah Company' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-sm-0 font-size-18"><?= $isEdit ? 'Edit Company' : 'Tambah Company' ?></h4>
        </div>
    </div>
</div>
<?php if (session('errors') !== null) : ?>
    <div class="alert alert-danger">
        <?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? site_url('administration/companies/' . $company['id']) : site_url('administration/companies') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kode</label>
                    <input name="code" class="form-control" value="<?= esc(old('code', $company['code'] ?? ''), 'attr') ?>" <?= $isEdit ? 'readonly' : 'required' ?>>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Nama Company</label>
                    <input name="name" class="form-control" value="<?= esc(old('name', $company['name'] ?? ''), 'attr') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">NPWP</label>
                    <input name="tax_no" class="form-control" value="<?= esc(old('tax_no', $company['tax_no'] ?? ''), 'attr') ?>">
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Alamat</label>
                    <input name="address" class="form-control" value="<?= esc(old('address', $company['address'] ?? ''), 'attr') ?>">
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Desa / Kelurahan</label>
                    <select name="village_id" class="form-select">
                        <option value="">- Pilih wilayah -</option>
                        <?php foreach ($villages as $village) : ?>
                            <?php $selected = (string) old('village_id', $company['village_id'] ?? '') === (string) $village['id']; ?>
                            <option value="<?= esc($village['id']) ?>" <?= $selected ? 'selected' : '' ?>>
                                <?= esc($village['province'] . ' / ' . $village['regency'] . ' / ' . $village['district'] . ' / ' . $village['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kode Pos</label>
                    <input name="postal_code" class="form-control" value="<?= esc(old('postal_code', $company['postal_code'] ?? ''), 'attr') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mata Uang</label>
                    <input name="base_currency" class="form-control" value="<?= esc(old('base_currency', $company['base_currency'] ?? 'IDR'), 'attr') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Timezone</label>
                    <input name="timezone" class="form-control" value="<?= esc(old('timezone', $company['timezone'] ?? 'Asia/Jakarta'), 'attr') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= old('status', $company['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= old('status', $company['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Simpan</button>
            <a class="btn btn-light" href="<?= site_url('administration/companies') ?>">Batal</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
