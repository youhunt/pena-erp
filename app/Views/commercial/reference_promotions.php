<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18"><?= esc($title) ?></h4>
        <p class="text-muted mb-0"><?= esc($tenantContext['company_name']) ?> / Promotion master</p>
    </div>
</div>

<?php if (session('message')) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
<?php if (session('errors')) : ?><div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div><?php endif; ?>

<div class="row">
    <div class="col-xl-4">
        <div class="card"><div class="card-body">
            <h4 class="card-title">Create <?= esc($title) ?></h4>
            <?php if (! $canManage) : ?>
                <div class="alert alert-info">Read only.</div>
            <?php else : ?>
            <form method="post" action="<?= site_url($baseRoute . '/promotions') ?>">
                <?= csrf_field() ?>
                <div class="mb-2"><label class="form-label">Code</label><input name="code" class="form-control" required></div>
                <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                <div class="mb-2"><label class="form-label">Partner</label><select name="<?= $side === 'sales' ? 'customer_id' : 'supplier_id' ?>" class="form-select"><option value="">All</option><?php foreach ($partners as $partner) : ?><option value="<?= (int) $partner['id'] ?>"><?= esc($partner['code'] . ' - ' . $partner['name']) ?></option><?php endforeach; ?></select></div>
                <div class="row g-2">
                    <div class="col-md-6"><label class="form-label">Discount Type</label><select name="discount_type" class="form-select"><option value="percent">Percent</option><option value="amount">Amount</option></select></div>
                    <div class="col-md-6"><label class="form-label">Value</label><input type="number" step="0.000001" name="discount_value" value="0" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Start</label><input type="date" name="starts_on" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">End</label><input type="date" name="ends_on" class="form-control" required></div>
                </div>
                <button class="btn btn-primary mt-3">Simpan</button>
            </form>
            <?php endif; ?>
        </div></div>
    </div>
    <div class="col-xl-8">
        <div class="card"><div class="card-body">
            <h4 class="card-title">Promotion List</h4>
            <div class="table-responsive"><table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Partner</th><th>Discount</th><th>Period</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($promotions as $promo) : ?>
                        <tr><td><strong><?= esc($promo['code']) ?></strong></td><td><?= esc($promo['name']) ?></td><td><?= esc($promo['partner_code'] ?? 'All') ?></td><td><?= esc($promo['discount_type'] . ' / ' . $promo['discount_value']) ?></td><td><?= esc($promo['starts_on'] . ' - ' . $promo['ends_on']) ?></td><td><?= esc($promo['status']) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if ($promotions === []) : ?><tr><td colspan="6" class="text-center text-muted">Belum ada promo.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </div></div>
    </div>
</div>
<?= $this->endSection() ?>
