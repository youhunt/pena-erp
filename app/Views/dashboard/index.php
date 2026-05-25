<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Dashboard</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item active">Pena ERP</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Pena ERP</h4>
                <p class="card-text text-muted mb-0">
                    Application shell Skote sudah aktif. Foundation company, branch, dan master wilayah mulai tersedia untuk administrasi platform.
                </p>
            </div>
        </div>
    </div>
</div>
<?php if ($tenantContext !== null) : ?>
    <div class="row">
        <div class="col-xl-8">
            <div class="card border border-primary">
                <div class="card-body">
                    <h4 class="card-title">Workspace Aktif</h4>
                    <p class="mb-1"><strong><?= esc($tenantContext['company_code']) ?></strong> - <?= esc($tenantContext['company_name']) ?></p>
                    <p class="text-muted"><?= $tenantContext['branch_name'] !== null ? esc($tenantContext['branch_code'] . ' - ' . $tenantContext['branch_name']) : 'Tidak ada branch yang ditetapkan.' ?></p>
                    <a href="<?= site_url('workspace/' . $tenantContext['company_id']) ?>" class="btn btn-primary btn-sm">Masuk Workspace</a>
                    <a href="<?= site_url('workspace') ?>" class="btn btn-outline-secondary btn-sm">Ganti Context</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (auth()->user()?->can('platform.company.manage')) : ?>
    <div class="row">
        <div class="col-md-4">
            <a href="<?= site_url('administration/companies') ?>" class="text-reset">
                <div class="card">
                    <div class="card-body">
                        <h5 class="font-size-14">Company</h5>
                        <p class="text-muted mb-0">Kelola legal entity dan tenant awal.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= site_url('administration/branches') ?>" class="text-reset">
                <div class="card">
                    <div class="card-body">
                        <h5 class="font-size-14">Branch</h5>
                        <p class="text-muted mb-0">Daftar cabang per company.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= site_url('administration/regions') ?>" class="text-reset">
                <div class="card">
                    <div class="card-body">
                        <h5 class="font-size-14">Master Wilayah</h5>
                        <p class="text-muted mb-0">Referensi alamat Indonesia global.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= site_url('administration/rbac') ?>" class="text-reset">
                <div class="card">
                    <div class="card-body">
                        <h5 class="font-size-14">Role & Permission</h5>
                        <p class="text-muted mb-0">Atur grant akses dinamis per company.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
