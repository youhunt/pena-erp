<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>AI Document Processing<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">AI Document Processing</h4>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h4 class="card-title">Document Queue</h4>
        <p class="text-muted">Fondasi metadata dokumen, queue OCR, OCR result, dan AI extraction proposal sudah tersedia.</p>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>File</th>
                        <th>Status</th>
                        <th>Uploaded</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($documents === []) : ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada dokumen.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($documents as $document) : ?>
                        <tr>
                            <td><?= (int) $document['id'] ?></td>
                            <td><?= esc($document['original_filename']) ?></td>
                            <td><?= esc($document['status']) ?></td>
                            <td><?= esc($document['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
