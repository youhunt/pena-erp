<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Site<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Site</h4>
            <div class="page-title-right">
                <a href="<?= site_url('administration/branches/new') ?>" class="btn btn-primary btn-sm me-3">Tambah Site</a>
                <ol class="breadcrumb m-0 d-inline-flex"><li class="breadcrumb-item">Administrasi</li><li class="breadcrumb-item active">Site</li></ol>
            </div>
        </div>
    </div>
</div>
<?php if (session('message') !== null) : ?>
    <div class="alert alert-success"><?= esc(session('message')) ?></div>
<?php endif; ?>
<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-3">Site Operasional</h4>
        <div class="table-responsive">
            <table class="table table-nowrap align-middle mb-0">
                <thead><tr><th>Company</th><th>Kode</th><th>Nama Site</th><th>Lokasi</th><th>Tipe</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($branches as $branch) : ?>
                    <tr>
                        <td><?= esc($branch['company_code']) ?></td>
                        <td><?= esc($branch['code']) ?></td>
                        <td><?= esc($branch['name']) ?></td>
                        <td><?= esc(trim(implode(', ', array_filter([$branch['village'], $branch['regency'], $branch['province']])))) ?></td>
                        <td><?= $branch['is_head_office'] ? 'Head Office' : 'Site' ?></td>
                        <td><span class="badge <?= $branch['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= esc($branch['status']) ?></span></td>
                        <td class="text-nowrap"><a href="<?= site_url('administration/branches/' . $branch['id'] . '/edit') ?>" class="btn btn-outline-secondary btn-sm">Edit</a> <form class="d-inline" method="post" action="<?= site_url('administration/branches/' . $branch['id'] . '/status') ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $branch['status'] === 'active' ? 'inactive' : 'active' ?>"><button class="btn btn-outline-danger btn-sm"><?= $branch['status'] === 'active' ? 'Hapus' : 'Aktifkan' ?></button></form></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
