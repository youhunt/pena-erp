<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Import Batch Detail<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Import Batch #<?= (int) $batch['id'] ?></h4>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h4 class="card-title"><?= esc($batch['import_type']) ?> - <?= esc($batch['original_filename']) ?></h4>
        <p class="text-muted">Status: <?= esc($batch['status']) ?> · Rows: <?= (int) $batch['total_rows'] ?> · Errors: <?= (int) $batch['error_rows'] ?> · Imported: <?= (int) $batch['imported_rows'] ?></p>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light"><tr><th>Row</th><th>Status</th><th>Error</th><th>Target</th></tr></thead>
                <tbody>
                    <?php if ($rows === []) : ?>
                        <tr><td colspan="4" class="text-center text-muted">Belum ada detail row.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $row) : ?>
                        <tr>
                            <td><?= (int) $row['row_number'] ?></td>
                            <td><?= esc($row['row_status']) ?></td>
                            <td><?= esc($row['error_message'] ?? '-') ?></td>
                            <td><?= esc(($row['target_table'] ?? '-') . ':' . ($row['target_id'] ?? '-')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
