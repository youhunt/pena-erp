<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Master Wilayah<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Master Wilayah</h4>
            <div class="page-title-right"><ol class="breadcrumb m-0"><li class="breadcrumb-item">Administrasi</li><li class="breadcrumb-item active">Wilayah</li></ol></div>
        </div>
    </div>
</div>
<div class="alert alert-info">
    Master wilayah dapat disinkronkan dari API wilayah terkonfigurasi melalui command
    <code>php spark regions:sync-api &lt;source_version&gt;</code>. Kolom sumber menandai versi data terakhir;
    kelengkapan dataset tetap harus diverifikasi terhadap rujukan resmi.
</div>
<div class="row">
    <?php foreach (['provinces' => 'Provinsi', 'regencies' => 'Kabupaten/Kota', 'districts' => 'Kecamatan', 'villages' => 'Desa/Kelurahan'] as $key => $label) : ?>
        <div class="col-md-3">
            <div class="card"><div class="card-body">
                <p class="text-muted mb-1"><?= esc($label) ?></p>
                <h4 class="mb-0"><?= number_format((int) $counts[$key], 0, ',', '.') ?></h4>
            </div></div>
        </div>
    <?php endforeach; ?>
</div>
<div class="card">
    <div class="card-body">
        <div class="d-sm-flex align-items-center justify-content-between mb-3">
            <h4 class="card-title mb-sm-0">Sampel Desa/Kelurahan (maksimal 100 hasil)</h4>
            <form class="d-flex gap-2" method="get" action="<?= site_url('administration/regions') ?>">
                <input class="form-control" name="q" value="<?= esc($search, 'attr') ?>" placeholder="Cari nama atau kode">
                <button class="btn btn-primary" type="submit">Cari</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-nowrap align-middle mb-0">
                <thead><tr><th>Kode</th><th>Provinsi</th><th>Kabupaten/Kota</th><th>Kecamatan</th><th>Desa/Kelurahan</th><th>Kode Pos</th><th>Sumber</th></tr></thead>
                <tbody>
                <?php foreach ($villages as $village) : ?>
                    <tr>
                        <td><?= esc($village['code']) ?></td>
                        <td><?= esc($village['province']) ?></td>
                        <td><?= esc($village['regency']) ?></td>
                        <td><?= esc($village['district']) ?></td>
                        <td><?= esc(($village['type'] === 'desa_kelurahan' ? '' : ucfirst($village['type']) . ' ') . $village['name']) ?></td>
                        <td><?= esc($village['postal_code']) ?></td>
                        <td><?= esc($village['source_version']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($villages === []) : ?>
                    <tr><td colspan="7" class="text-center text-muted">Tidak ada wilayah yang cocok.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
