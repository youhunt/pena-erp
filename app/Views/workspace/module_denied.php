<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Akses Modul Ditolak<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="alert alert-danger">
    User tidak memiliki menu atau permission aktif untuk modul <strong><?= esc($moduleCode) ?></strong> pada company context saat ini.
</div>
<a href="<?= site_url('workspace') ?>" class="btn btn-outline-primary">Kembali ke Pilih Workspace</a>
<?= $this->endSection() ?>
