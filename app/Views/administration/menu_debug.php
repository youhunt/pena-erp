<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Menu Access Debug<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18">Menu Access Debug</h4>
        <p class="text-muted mb-0">
            User ID <?= esc((string) $userId) ?> / Company <?= esc((string) $companyId) ?> - <?= esc($tenantContext['company_name'] ?? '-') ?>
        </p>
    </div>
    <a href="<?= site_url('workspace') ?>" class="btn btn-light">Workspace</a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card border-primary">
            <div class="card-body">
                <h5 class="card-title">Accessible Menus</h5>
                <p class="display-6 mb-0"><?= count($menus) ?></p>
                <p class="text-muted mb-0">Menu yang lolos query sidebar.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-secondary">
            <div class="card-body">
                <h5 class="card-title">Debug Rows</h5>
                <p class="display-6 mb-0"><?= count($debugRows) ?></p>
                <p class="text-muted mb-0">Semua menu company aktif beserta join RBAC.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-body">
                <h5 class="card-title">Context</h5>
                <p class="mb-1"><strong><?= esc($tenantContext['company_code'] ?? '-') ?></strong></p>
                <p class="text-muted mb-0"><?= esc($tenantContext['branch_code'] ?? '-') ?> / <?= esc($tenantContext['branch_name'] ?? '-') ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h4 class="card-title mb-3">Menu yang Tampil di Sidebar</h4>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th>Sort</th>
                    <th>Code</th>
                    <th>Label</th>
                    <th>Route</th>
                    <th>Icon</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($menus as $menu) : ?>
                    <tr>
                        <td><?= esc((string) $menu['sort_order']) ?></td>
                        <td><code><?= esc($menu['code']) ?></code></td>
                        <td><?= esc($menu['label']) ?></td>
                        <td><code><?= esc($menu['route']) ?></code></td>
                        <td><code><?= esc($menu['icon'] ?? '') ?></code></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($menus === []) : ?>
                    <tr><td colspan="5" class="text-center text-danger py-4">Tidak ada menu yang lolos query sidebar.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h4 class="card-title mb-3">Detail Join RBAC per Menu</h4>
        <div class="alert alert-info small">
            Baris dianggap siap tampil jika: menu punya route, permission terhubung, role active, user role ada, membership active, dan user active.
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Sort</th>
                    <th>Menu</th>
                    <th>Route</th>
                    <th>Permission</th>
                    <th>Role</th>
                    <th>Role Status</th>
                    <th>User</th>
                    <th>Membership</th>
                    <th>User Active</th>
                    <th>Diagnosis</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($debugRows as $row) : ?>
                    <?php
                    $problems = [];
                    if (empty($row['permission_code'])) { $problems[] = 'permission kosong'; }
                    if (empty($row['role_id'])) { $problems[] = 'role permission kosong'; }
                    if (($row['role_status'] ?? null) !== 'active') { $problems[] = 'role inactive'; }
                    if (empty($row['user_id'])) { $problems[] = 'user role kosong'; }
                    if (($row['membership_status'] ?? null) !== 'active') { $problems[] = 'membership inactive'; }
                    if ((string) ($row['user_active'] ?? '0') !== '1') { $problems[] = 'user inactive'; }
                    $ok = $problems === [];
                    ?>
                    <tr>
                        <td><?= esc((string) $row['sort_order']) ?></td>
                        <td><?= esc($row['label'] ?? '-') ?><br><small><code><?= esc($row['code'] ?? '-') ?></code></small></td>
                        <td><code><?= esc($row['route'] ?? '-') ?></code></td>
                        <td><code><?= esc($row['permission_code'] ?? '-') ?></code></td>
                        <td><?= esc($row['role_name'] ?? '-') ?><br><small><code><?= esc($row['role_code'] ?? '-') ?></code></small></td>
                        <td><?= esc($row['role_status'] ?? '-') ?></td>
                        <td><?= esc((string) ($row['user_id'] ?? '-')) ?></td>
                        <td><?= esc($row['membership_status'] ?? '-') ?></td>
                        <td><?= esc((string) ($row['user_active'] ?? '-')) ?></td>
                        <td>
                            <?php if ($ok) : ?>
                                <span class="badge bg-success">OK</span>
                            <?php else : ?>
                                <span class="badge bg-danger"><?= esc(implode(', ', $problems)) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($debugRows === []) : ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">Tidak ada menu pada company aktif.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
