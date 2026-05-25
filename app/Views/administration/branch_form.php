<?= $this->extend('layouts/dashboard') ?>

<?php $isEdit = $branch !== null; ?>
<?= $this->section('title') ?><?= $isEdit ? 'Edit Branch' : 'Tambah Branch' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box"><h4 class="mb-sm-0 font-size-18"><?= $isEdit ? 'Edit Branch' : 'Tambah Branch' ?></h4></div>
<?php if (session('errors') !== null) : ?>
    <div class="alert alert-danger">
        <?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>
<div class="card">
    <div class="card-body">
        <form class="row g-2 align-items-end mb-4" method="get" action="<?= $isEdit ? site_url('administration/branches/' . $branch['id'] . '/edit') : site_url('administration/branches/new') ?>">
            <div class="col-md-8">
                <label class="form-label">Cari Pilihan Desa / Kelurahan</label>
                <input name="village_q" class="form-control" value="<?= esc($villageSearch ?? '', 'attr') ?>" placeholder="Nama desa, kecamatan, kabupaten atau provinsi">
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-primary" type="submit">Tampilkan Wilayah</button>
            </div>
        </form>
        <form method="post" action="<?= $isEdit ? site_url('administration/branches/' . $branch['id']) : site_url('administration/branches') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label class="form-label">Company</label>
                    <select name="company_id" class="form-select" required>
                        <?php foreach ($companies as $company) : ?>
                            <?php $selected = (string) old('company_id', $branch['company_id'] ?? '') === (string) $company['id']; ?>
                            <option value="<?= esc($company['id']) ?>" <?= $selected ? 'selected' : '' ?>><?= esc($company['code'] . ' - ' . $company['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kode</label>
                    <input name="code" class="form-control" value="<?= esc(old('code', $branch['code'] ?? ''), 'attr') ?>" <?= $isEdit ? 'readonly' : 'required' ?>>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nama Branch</label>
                    <input name="name" class="form-control" value="<?= esc(old('name', $branch['name'] ?? ''), 'attr') ?>" required>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Alamat</label>
                    <input name="address" class="form-control" value="<?= esc(old('address', $branch['address'] ?? ''), 'attr') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kode Pos</label>
                    <input name="postal_code" class="form-control" value="<?= esc(old('postal_code', $branch['postal_code'] ?? ''), 'attr') ?>">
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Desa / Kelurahan</label>
                    <select name="village_id" class="form-select">
                        <option value="">- Pilih dari maksimal 100 hasil pencarian -</option>
                        <?php foreach ($villages as $village) : ?>
                            <?php $selected = (string) old('village_id', $branch['village_id'] ?? '') === (string) $village['id']; ?>
                            <option value="<?= esc($village['id']) ?>" <?= $selected ? 'selected' : '' ?>><?= esc($village['province'] . ' / ' . $village['regency'] . ' / ' . $village['district'] . ' / ' . $village['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= old('status', $branch['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= old('status', $branch['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input name="is_head_office" value="1" class="form-check-input" type="checkbox" id="is_head_office" <?= old('is_head_office', $branch['is_head_office'] ?? false) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_head_office">Head Office</label>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Simpan</button>
            <a class="btn btn-light" href="<?= site_url('administration/branches') ?>">Batal</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
