<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Master Data Import<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Master Data Import</h4>
                <p class="text-muted mb-0 mt-1">Upload massal master data tenant seperti Item, Customer, Supplier, Warehouse, UOM, dan COA.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card border border-primary">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <h4 class="card-title mb-2">Upload File Import</h4>
                        <p class="text-muted mb-3">Pilih jenis master, upload file CSV/XLSX, lalu sistem akan validasi header dan row sebelum data benar-benar masuk ke master.</p>
                    </div>
                    <span class="badge bg-warning text-dark">Foundation</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Jenis Master</label>
                        <select class="form-select" disabled>
                            <option>Pilih jenis master...</option>
                            <?php foreach ($catalog as $type => $definition) : ?>
                                <option><?= esc($definition['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">File CSV / XLSX</label>
                        <input type="file" class="form-control" disabled>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" disabled>Upload</button>
                    </div>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    Tahap ini menyiapkan UI, tabel batch, row log, dan template catalog. Proses upload/validasi/import akan diaktifkan pada tahap berikutnya agar aman untuk data tenant.
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
                                <th>ID</th>
                                <th>Type</th>
                                <th>File</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Valid</th>
                                <th>Error</th>
                                <th>Imported</th>
                                <th>Created</th>
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
                                    <td><?= esc($batch['created_at']) ?></td>
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
                    <div class="border rounded p-2"><strong>1. Download template</strong><br><span class="text-muted">User memakai format kolom standar.</span></div>
                    <div class="border rounded p-2"><strong>2. Upload file</strong><br><span class="text-muted">File disimpan sebagai batch import.</span></div>
                    <div class="border rounded p-2"><strong>3. Validate rows</strong><br><span class="text-muted">Header, mandatory field, duplicate, dan FK dicek.</span></div>
                    <div class="border rounded p-2"><strong>4. Review errors</strong><br><span class="text-muted">Row error dapat diperbaiki sebelum commit.</span></div>
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
