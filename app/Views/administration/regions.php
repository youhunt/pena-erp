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
    Data saat ini adalah bootstrap development terbatas. Import dataset wilayah resmi lengkap menjadi pekerjaan berikutnya.
</div>
<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-3">Provinsi / Kabupaten-Kota / Kecamatan / Desa-Kelurahan</h4>
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
                        <td><?= esc(ucfirst($village['type']) . ' ' . $village['name']) ?></td>
                        <td><?= esc($village['postal_code']) ?></td>
                        <td><?= esc($village['source_version']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
