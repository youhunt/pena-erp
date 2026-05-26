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
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Mapping Menu Permission</h4>
                <form method="post" action="<?= site_url('administration/rbac/menu-mappings') ?>" class="row g-3 align-items-end">
                    <?= csrf_field() ?>
                    <div class="col-xl-3">
                        <label class="form-label">Company</label>
                        <select name="company_id" class="form-select" required>
                            <?php foreach ($companies as $company) : ?><option value="<?= esc($company['id']) ?>"><?= esc($company['code'] . ' - ' . $company['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-xl-3">
                        <label class="form-label">Menu</label>
                        <select name="menu_id" class="form-select" required>
                            <?php foreach ($menus as $menu) : ?><option value="<?= esc($menu['id']) ?>"><?= esc($menu['company_code'] . ' - ' . $menu['label']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-xl-3">
                        <label class="form-label">Permission</label>
                        <select name="permission_id" class="form-select" required>
                            <?php foreach ($permissions as $permission) : ?><option value="<?= esc($permission['id']) ?>"><?= esc($permission['company_code'] . ' - ' . $permission['code']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-xl-3">
                        <button class="btn btn-primary" type="submit">Tambahkan Mapping</button>
                        <div class="form-text">Company, menu, dan permission harus sama.</div>
                    </div>
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
                    <thead><tr><th>Company</th><th>Role</th><th>Status / Nama</th></tr></thead>
                    <tbody>
                    <?php foreach ($roles as $role) : ?>
                        <tr>
                            <td><?= esc($role['company_code']) ?></td>
                            <td><?= esc($role['name']) ?> <small class="text-muted">(<?= esc($role['code']) ?>)</small></td>
                            <td>
                                <form method="post" action="<?= site_url('administration/rbac/roles/' . $role['id']) ?>" class="d-flex gap-2">
                                    <?= csrf_field() ?>
                                    <input class="form-control form-control-sm" type="text" name="name" value="<?= esc($role['name']) ?>" required>
                                    <select class="form-select form-select-sm" name="status">
                                        <option value="active" <?= $role['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $role['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                    <button class="btn btn-outline-primary btn-sm" type="submit">Update</button>
                                </form>
                            </td>
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
                    <thead><tr><th>Company</th><th>Role</th><th>Permission</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($grants as $grant) : ?>
                        <tr>
                            <td><?= esc($grant['company_code']) ?></td>
                            <td><?= esc($grant['role_name']) ?></td>
                            <td><code><?= esc($grant['permission_code']) ?></code></td>
                            <td class="text-end">
                                <form method="post" action="<?= site_url('administration/rbac/grants/revoke') ?>" onsubmit="return confirm('Cabut permission ini dari role?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="company_id" value="<?= esc($grant['company_id']) ?>">
                                    <input type="hidden" name="grant_id" value="<?= esc($grant['id']) ?>">
                                    <button class="btn btn-outline-danger btn-sm" type="submit">Revoke</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Matriks Menu dan Permission</h4>
                <p class="text-muted">Menu sidebar tenant akan terlihat jika role user memiliki salah satu permission yang dipetakan berikut.</p>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Company</th><th>Menu</th><th>Route</th><th>Permission</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                        <?php foreach ($menuMatrix as $mapping) : ?>
                            <tr>
                                <td><?= esc($mapping['company_code']) ?></td>
                                <td><?= esc($mapping['menu_label']) ?></td>
                                <td><small><?= esc($mapping['route'] ?? '-') ?></small></td>
                                <td><code><?= esc($mapping['permission_code']) ?></code></td>
                                <td class="text-end">
                                    <form method="post" action="<?= site_url('administration/rbac/menu-mappings/revoke') ?>" onsubmit="return confirm('Cabut mapping menu-permission ini?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="company_id" value="<?= esc($mapping['company_id']) ?>">
                                        <input type="hidden" name="mapping_id" value="<?= esc($mapping['id']) ?>">
                                        <button class="btn btn-outline-danger btn-sm" type="submit">Revoke</button>
                                    </form>
                                </td>
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
