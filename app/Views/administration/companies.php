<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Company<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Company</h4>
            <div class="page-title-right">
                <a href="<?= site_url('administration/companies/new') ?>" class="btn btn-primary btn-sm me-3">Tambah Company</a>
                <ol class="breadcrumb m-0 d-inline-flex"><li class="breadcrumb-item">Administrasi</li><li class="breadcrumb-item active">Company</li></ol>
            </div>
        </div>
    </div>
</div>
<?php if (session('message') !== null) : ?>
    <div class="alert alert-success"><?= esc(session('message')) ?></div>
<?php endif; ?>
<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-3">Legal Entity / Tenant</h4>
        <div class="table-responsive">
            <table class="table table-nowrap align-middle mb-0">
                <thead><tr><th>Kode</th><th>Nama</th><th>Lokasi</th><th>Mata Uang</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($companies as $company) : ?>
                    <tr>
                        <td><?= esc($company['code']) ?></td>
                        <td><?= esc($company['name']) ?></td>
                        <td><?= esc(trim(implode(', ', array_filter([$company['village'], $company['regency'], $company['province']])))) ?></td>
                        <td><?= esc($company['base_currency']) ?></td>
                        <td><span class="badge <?= $company['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= esc($company['status']) ?></span></td>
                        <td class="text-nowrap">
                            <a href="<?= site_url('workspace/' . $company['id']) ?>" class="btn btn-outline-primary btn-sm">Buka</a>
                            <a href="<?= site_url('administration/companies/' . $company['id'] . '/edit') ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                            <form class="d-inline" method="post" action="<?= site_url('administration/companies/' . $company['id'] . '/status') ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $company['status'] === 'active' ? 'inactive' : 'active' ?>"><button class="btn btn-outline-danger btn-sm"><?= $company['status'] === 'active' ? 'Hapus' : 'Aktifkan' ?></button></form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
