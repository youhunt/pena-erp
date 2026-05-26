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
        <p class="text-muted mb-0">Daftar modul di bawah berasal dari role dan permission pada company aktif.</p>
    </div>
</div>
<div class="row">
    <?php foreach ($menus as $menu) : ?>
        <div class="col-md-6 col-xl-3">
            <a href="<?= site_url($menu['route']) ?>" class="text-reset">
                <div class="card border">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="<?= esc($menu['icon'] ?: 'bx bx-grid-alt') ?> font-size-24 text-primary me-3"></i>
                            <div>
                                <h5 class="font-size-14 mb-1"><?= esc($menu['label']) ?></h5>
                                <p class="text-muted mb-0"><code><?= esc($menu['code']) ?></code></p>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
