<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Master Data Import<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Master Data Import</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Import Templates</h4>
                <p class="text-muted">Gunakan area ini sebagai pusat upload/import master data tenant.</p>
                <ul class="mb-0">
                    <?php foreach ($catalog as $type => $definition) : ?>
                        <li><strong><?= esc($definition['label']) ?></strong><br><small><?= esc(implode(', ', $definition['required_columns'])) ?></small></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Import Batches</h4>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>ID</th><th>Type</th><th>File</th><th>Status</th><th>Rows</th><th>Created</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($batches === []) : ?>
                                <tr><td colspan="6" class="text-center text-muted">Belum ada batch import.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($batches as $batch) : ?>
                                <tr>
                                    <td><?= (int) $batch['id'] ?></td>
                                    <td><?= esc($batch['import_type']) ?></td>
                                    <td><?= esc($batch['original_filename']) ?></td>
                                    <td><?= esc($batch['status']) ?></td>
                                    <td><?= (int) $batch['total_rows'] ?> / <?= (int) $batch['imported_rows'] ?></td>
                                    <td><?= esc($batch['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
