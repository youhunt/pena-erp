<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Document Review<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Document Review</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Source Document</h4>
                <p class="mb-1"><strong><?= esc($document['original_filename']) ?></strong></p>
                <p class="text-muted mb-1"><?= esc($document['mime_type']) ?> · <?= number_format((int) $document['file_size']) ?> bytes</p>
                <p class="mb-0">Status: <span class="badge bg-secondary"><?= esc($document['status']) ?></span></p>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">OCR / AI Extraction</h4>
                <p class="text-muted mb-2">OCR engine: <?= $ocr === null ? '-' : esc($ocr['engine']) ?></p>
                <p class="text-muted mb-2">Extraction provider: <?= $extraction === null ? '-' : esc($extraction['provider']) ?></p>
                <p class="mb-0">Fields: <?= count($fields) ?> · Items: <?= count($items) ?> · Validation: <?= count($validationLogs) ?> · Conversion: <?= count($conversionLinks) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h4 class="card-title">Processing Jobs</h4>
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead><tr><th>Stage</th><th>Status</th><th>Attempt</th><th>Created</th></tr></thead>
                <tbody>
                    <?php if ($jobs === []) : ?>
                        <tr><td colspan="4" class="text-center text-muted">Belum ada job.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($jobs as $job) : ?>
                        <tr>
                            <td><?= esc($job['stage']) ?></td>
                            <td><?= esc($job['status']) ?></td>
                            <td><?= (int) $job['attempt_no'] ?></td>
                            <td><?= esc($job['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
