<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Role & Permission<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box"><h4 class="mb-sm-0 font-size-18">Role & Permission Tenant</h4></div>
<?php if (session('message') !== null) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
<?php if (session('errors') !== null) : ?>
    <div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Tambah Role</h4>
                <form method="post" action="<?= site_url('administration/rbac/roles') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Company</label>
                        <select name="company_id" class="form-select" required>
                            <?php foreach ($companies as $company) : ?><option value="<?= esc($company['id']) ?>"><?= esc($company['code'] . ' - ' . $company['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Role</label>
                        <input type="text" name="code" class="form-control" placeholder="purchasing_manager" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Role</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select>
                    </div>
                    <button class="btn btn-primary" type="submit">Simpan Role</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Tambah Permission</h4>
                <form method="post" action="<?= site_url('administration/rbac/permissions') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Company</label>
                        <select name="company_id" class="form-select" required>
                            <?php foreach ($companies as $company) : ?><option value="<?= esc($company['id']) ?>"><?= esc($company['code'] . ' - ' . $company['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Permission</label>
                        <input type="text" name="code" class="form-control" placeholder="purchasing.po.view" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Permission</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Modul</label>
                        <input type="text" name="module" class="form-control" placeholder="purchasing" required>
                    </div>
                    <button class="btn btn-primary" type="submit">Simpan Permission</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Grant Permission</h4>
                <form method="post" action="<?= site_url('administration/rbac/grants') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Company</label>
                        <select name="company_id" class="form-select" required>
                            <?php foreach ($companies as $company) : ?><option value="<?= esc($company['id']) ?>"><?= esc($company['code'] . ' - ' . $company['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select" required>
                            <?php foreach ($roles as $role) : ?><option value="<?= esc($role['id']) ?>"><?= esc($role['company_code'] . ' - ' . $role['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permission</label>
                        <select name="permission_id" class="form-select" required>
                            <?php foreach ($permissions as $permission) : ?><option value="<?= esc($permission['id']) ?>"><?= esc($permission['company_code'] . ' - ' . $permission['code']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <p class="form-text">Role dan permission wajib milik company yang sama.</p>
                    <button class="btn btn-primary" type="submit">Berikan Permission</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-5">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Daftar Role</h4>
                <table class="table align-middle mb-0">
                    <thead><tr><th>Company</th><th>Role</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($roles as $role) : ?>
                        <tr>
                            <td><?= esc($role['company_code']) ?></td>
                            <td><?= esc($role['name']) ?> <small class="text-muted">(<?= esc($role['code']) ?>)</small></td>
                            <td><?= esc($role['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Grant Saat Ini</h4>
                <table class="table align-middle mb-0">
                    <thead><tr><th>Company</th><th>Role</th><th>Permission</th></tr></thead>
                    <tbody>
                    <?php foreach ($grants as $grant) : ?>
                        <tr>
                            <td><?= esc($grant['company_code']) ?></td>
                            <td><?= esc($grant['role_name']) ?></td>
                            <td><code><?= esc($grant['permission_code']) ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
