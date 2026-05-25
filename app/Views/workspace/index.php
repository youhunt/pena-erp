<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Workspace<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box"><h4 class="mb-sm-0 font-size-18">Workspace: <?= esc($company['name']) ?></h4></div>
<div class="alert alert-success">
    Akses diberikan melalui membership company dan role tenant dengan permission <code>company.dashboard.view</code>.
</div>
<div class="card">
    <div class="card-body">
        <h4 class="card-title">Company Aktif</h4>
        <p class="mb-1"><strong><?= esc($company['code']) ?></strong> - <?= esc($company['name']) ?></p>
        <?php if ($context !== null && $context['branch_name'] !== null) : ?>
            <p class="mb-1"><strong>Branch:</strong> <?= esc($context['branch_code'] . ' - ' . $context['branch_name']) ?></p>
        <?php endif; ?>
        <p class="text-muted mb-0">Modul transaksi akan ditempatkan di workspace tenant ini setelah master dan workflow siap.</p>
    </div>
</div>
<?= $this->endSection() ?>
