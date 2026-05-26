<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?><?= esc($menu['label']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0 font-size-18"><?= esc($menu['label']) ?></h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><?= esc($context['company_code']) ?></li>
        <li class="breadcrumb-item active"><?= esc($menu['label']) ?></li>
    </ol>
</div>
<div class="alert alert-info">
    Menu ini tampil karena role user pada company aktif memiliki permission yang dipetakan ke modul <strong><?= esc($menu['label']) ?></strong>.
</div>
<div class="card">
    <div class="card-body">
        <h4 class="card-title">Simulasi Modul: <?= esc($menu['label']) ?></h4>
        <p class="mb-1"><strong>Company:</strong> <?= esc($context['company_code'] . ' - ' . $context['company_name']) ?></p>
        <p class="mb-3"><strong>Branch:</strong> <?= $context['branch_name'] === null ? '-' : esc($context['branch_code'] . ' - ' . $context['branch_name']) ?></p>
        <p class="text-muted mb-0">Halaman ini menjadi placeholder berizin sebelum transaksi, approval, dan document processing modul tersebut dibangun.</p>
    </div>
</div>
<?= $this->endSection() ?>
