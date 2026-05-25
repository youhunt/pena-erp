<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Akses Ditolak<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="alert alert-danger">
    Anda belum memiliki role tenant yang mengizinkan akses ke workspace <strong><?= esc($company['name']) ?></strong>.
</div>
<?= $this->endSection() ?>
