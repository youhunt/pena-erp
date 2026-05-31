<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box"><h4 class="mb-sm-0 font-size-18"><?= esc($title) ?></h4><p class="text-muted mb-0">Region reference data. Data ini dipakai oleh Address Master.</p></div>
<div class="card"><div class="card-body"><h4 class="card-title">Region Lookup</h4><form method="get" class="row g-2 mb-3"><div class="col-md-8"><input name="village_q" value="<?= esc($villageSearch) ?>" class="form-control" placeholder="Cari village/city/province..."></div><div class="col-md-4"><button class="btn btn-primary w-100">Search</button></div></form><div class="table-responsive"><table class="table table-sm table-bordered align-middle"><thead class="table-light"><tr><th>Village</th><th>City/Regency</th><th>Province</th><th>Postal</th></tr></thead><tbody><?php foreach ($villages as $row) : ?><tr><td><?= esc($row['village_name'] ?? $row['name'] ?? '-') ?></td><td><?= esc($row['regency_name'] ?? '-') ?></td><td><?= esc($row['province_name'] ?? '-') ?></td><td><?= esc($row['postal_code'] ?? '-') ?></td></tr><?php endforeach; ?><?php if ($villages === []) : ?><tr><td colspan="4" class="text-center text-muted">Cari data region untuk menampilkan hasil.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?= $this->endSection() ?>
