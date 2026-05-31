<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Master Data Import<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Master Data Import</h4>
                <p class="text-muted mb-0 mt-1">Upload massal master data tenant. Tahap aktif: Unit of Measure dan Product Category.</p>
            </div>
        </div>
    </div>
</div>

<?php if (session('message')) : ?>
    <div class="alert alert-success"><?= esc(session('message')) ?></div>
<?php endif; ?>
<?php if (session('error')) : ?>
    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-xl-8">
        <div class="card border border-primary">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <h4 class="card-title mb-2">Upload File Import</h4>
                        <p class="text-muted mb-3">Download template CSV, isi data, upload, validasi row, lalu commit batch jika tidak ada error.</p>
                    </div>
                    <span class="badge bg-success">CSV Active</span>
                </div>

                <form method="post" action="<?= site_url('master-import/upload') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Jenis Master</label>
                            <select class="form-select" name="import_type" required>
                                <option value="">Pilih jenis master...</option>
                                <option value="units_of_measure">Unit of Measure</option>
                                <option value="product_categories">Product Category</option>
                            </select>
                            <small class="text-muted">Import lain menyusul setelah mapping FK aman.</small>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">File CSV</label>
                            <input type="file" name="import_file" class="form-control" accept=".csv,text/csv" required>
                            <small class="text-muted">Gunakan delimiter koma.</small>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Upload</button>
                        </div>
                    </div>
                </form>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('master-import/template/units_of_measure') ?>">Download UOM Template</a>
                    <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('master-import/template/product_categories') ?>">Download Category Template</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="card-title mb-0">Import Batches</h4>
                    <small class="text-muted">100 batch terbaru</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th><th>Type</th><th>File</th><th>Status</th><th>Total</th><th>Valid</th><th>Error</th><th>Imported</th><th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($batches === []) : ?>
                                <tr><td colspan="9" class="text-center text-muted py-4">Belum ada batch import.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($batches as $batch) : ?>
                                <tr>
                                    <td><?= (int) $batch['id'] ?></td>
                                    <td><?= esc($batch['import_type']) ?></td>
                                    <td><?= esc($batch['original_filename']) ?></td>
                                    <td><span class="badge bg-secondary"><?= esc($batch['status']) ?></span></td>
                                    <td><?= (int) $batch['total_rows'] ?></td>
                                    <td><?= (int) $batch['valid_rows'] ?></td>
                                    <td><?= (int) $batch['error_rows'] ?></td>
                                    <td><?= (int) $batch['imported_rows'] ?></td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('master-import/' . $batch['id']) ?>">Detail</a>
                                        <?php if ($batch['status'] === 'validated' && (int) $batch['error_rows'] === 0) : ?>
                                            <form method="post" action="<?= site_url('master-import/' . $batch['id'] . '/commit') ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-success">Commit</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Import Flow</h4>
                <div class="vstack gap-2">
                    <div class="border rounded p-2"><strong>1. Download template</strong><br><span class="text-muted">Pakai format kolom standar.</span></div>
                    <div class="border rounded p-2"><strong>2. Upload CSV</strong><br><span class="text-muted">File disimpan sebagai batch import.</span></div>
                    <div class="border rounded p-2"><strong>3. Validate rows</strong><br><span class="text-muted">Header dan mandatory field dicek.</span></div>
                    <div class="border rounded p-2"><strong>4. Review errors</strong><br><span class="text-muted">Cek detail row jika ada error.</span></div>
                    <div class="border rounded p-2"><strong>5. Commit import</strong><br><span class="text-muted">Data valid masuk ke master tenant.</span></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Available Templates</h4>
                <div class="accordion" id="importTemplateAccordion">
                    <?php $index = 0; ?>
                    <?php foreach ($catalog as $type => $definition) : ?>
                        <?php $index++; ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?= $index ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>">
                                    <?= esc($definition['label']) ?>
                                </button>
                            </h2>
                            <div id="collapse<?= $index ?>" class="accordion-collapse collapse" data-bs-parent="#importTemplateAccordion">
                                <div class="accordion-body">
                                    <div class="mb-2"><strong>Required</strong><br><code><?= esc(implode(', ', $definition['required_columns'])) ?></code></div>
                                    <div><strong>Optional</strong><br><code><?= esc(implode(', ', $definition['optional_columns'])) ?></code></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
