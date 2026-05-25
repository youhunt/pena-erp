<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Branch<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Branch</h4>
            <div class="page-title-right">
                <a href="<?= site_url('administration/branches/new') ?>" class="btn btn-primary btn-sm me-3">Tambah Branch</a>
                <ol class="breadcrumb m-0 d-inline-flex"><li class="breadcrumb-item">Administrasi</li><li class="breadcrumb-item active">Branch</li></ol>
            </div>
        </div>
    </div>
</div>
<?php if (session('message') !== null) : ?>
    <div class="alert alert-success"><?= esc(session('message')) ?></div>
<?php endif; ?>
<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-3">Cabang Operasional</h4>
        <div class="table-responsive">
            <table class="table table-nowrap align-middle mb-0">
                <thead><tr><th>Company</th><th>Kode</th><th>Nama Cabang</th><th>Lokasi</th><th>Tipe</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($branches as $branch) : ?>
                    <tr>
                        <td><?= esc($branch['company_code']) ?></td>
                        <td><?= esc($branch['code']) ?></td>
                        <td><?= esc($branch['name']) ?></td>
                        <td><?= esc(trim(implode(', ', array_filter([$branch['village'], $branch['regency'], $branch['province']])))) ?></td>
                        <td><?= $branch['is_head_office'] ? 'Head Office' : 'Branch' ?></td>
                        <td><span class="badge bg-success"><?= esc($branch['status']) ?></span></td>
                        <td><a href="<?= site_url('administration/branches/' . $branch['id'] . '/edit') ?>" class="btn btn-outline-secondary btn-sm">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
