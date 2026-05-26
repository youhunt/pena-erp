<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Audit Trail<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box"><h4 class="mb-sm-0 font-size-18">Audit Trail</h4></div>
<div class="card">
    <div class="card-body">
        <form class="row gy-2 gx-3 align-items-end mb-4" method="get" action="<?= site_url('administration/audit') ?>">
            <div class="col-md-3">
                <label class="form-label">Company</label>
                <select class="form-select" name="company_id">
                    <option value="">Semua Company</option>
                    <?php foreach ($companies as $company) : ?>
                        <option value="<?= esc($company['id']) ?>" <?= $companyId === (int) $company['id'] ? 'selected' : '' ?>><?= esc($company['code'] . ' - ' . $company['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Event</label>
                <select class="form-select" name="event_type">
                    <option value="">Semua Event</option>
                    <?php foreach ($eventTypes as $event) : ?>
                        <option value="<?= esc($event) ?>" <?= $eventType === $event ? 'selected' : '' ?>><?= esc($event) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Cari</label>
                <input class="form-control" type="text" name="q" value="<?= esc($search) ?>" placeholder="entity, user atau payload">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit">Filter</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Waktu</th><th>Company / Branch</th><th>Actor</th><th>Event</th><th>Entity</th><th>Detail</th></tr></thead>
                <tbody>
                <?php if ($logs === []) : ?>
                    <tr><td colspan="6" class="text-muted text-center py-4">Belum ada event sesuai filter.</td></tr>
                <?php endif; ?>
                <?php foreach ($logs as $log) : ?>
                    <tr>
                        <td class="text-nowrap"><?= esc($log['occurred_at']) ?></td>
                        <td><?= esc(($log['company_code'] ?? '-') . ($log['branch_code'] !== null ? ' / ' . $log['branch_code'] : '')) ?></td>
                        <td><?= esc($log['username'] ?? '-') ?></td>
                        <td><span class="badge bg-info"><?= esc($log['event_type']) ?></span></td>
                        <td><?= esc($log['entity_type'] . ($log['entity_id'] !== null ? ' #' . $log['entity_id'] : '')) ?></td>
                        <td><small class="text-muted"><?= esc($log['after_json'] ?? '-') ?></small></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="text-muted mt-3 mb-0">Menampilkan maksimum 100 event terbaru. Audit bersifat append-only; tindakan revoke menghasilkan event baru.</p>
    </div>
</div>
<?= $this->endSection() ?>
