<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18"><?= esc($title) ?></h4>
        <p class="text-muted mb-0"><?= esc($tenantContext['company_name']) ?> / Terms master</p>
    </div>
</div>

<?php if (session('message')) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
<?php if (session('errors')) : ?><div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div><?php endif; ?>

<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Create <?= esc($title) ?></h4>
                <?php if (! $canManage) : ?>
                    <div class="alert alert-info">Read only.</div>
                <?php else : ?>
                    <form method="post" action="<?= site_url($baseRoute . '/terms') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-2"><label class="form-label">Code</label><input name="code" class="form-control" maxlength="12" required></div>
                        <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control" maxlength="120" required></div>
                        <div class="row g-2">
                            <div class="col-md-4"><label class="form-label">Due Days</label><input type="number" min="0" name="due_days" value="30" class="form-control" required></div>
                            <div class="col-md-4"><label class="form-label">Disc Days</label><input type="number" min="0" name="discount_days" value="0" class="form-control" required></div>
                            <div class="col-md-4"><label class="form-label">Disc Rate</label><input type="number" step="0.000001" min="0" name="discount_rate" value="0" class="form-control" required></div>
                        </div>
                        <button class="btn btn-primary mt-3">Simpan</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card"><div class="card-body">
            <h4 class="card-title">Terms List</h4>
            <div class="table-responsive"><table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Due Days</th><th>Discount</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($terms as $term) : ?>
                        <tr><td><strong><?= esc($term['code']) ?></strong></td><td><?= esc($term['name']) ?></td><td><?= (int) $term['due_days'] ?></td><td><?= esc($term['discount_days'] . ' / ' . $term['discount_rate']) ?></td><td><?= esc($term['status']) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if ($terms === []) : ?><tr><td colspan="5" class="text-center text-muted">Belum ada terms.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </div></div>
    </div>
</div>
<?= $this->endSection() ?>
