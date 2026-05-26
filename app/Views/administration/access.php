<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Akses User<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box"><h4 class="mb-sm-0 font-size-18">Akses User per Company</h4></div>
<?php if (session('message') !== null) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
<?php if (session('errors') !== null) : ?>
    <div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Provision User Shield</h4>
                <form method="post" action="<?= site_url('administration/users') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= esc(old('username')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Login</label>
                        <input type="email" name="email" class="form-control" value="<?= esc(old('email')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Sementara</label>
                        <input type="password" name="password" class="form-control" minlength="12" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ulangi Password</label>
                        <input type="password" name="password_confirm" class="form-control" minlength="12" required>
                    </div>
                    <p class="form-text">Minimal 12 karakter. Berikan password kepada user melalui kanal aman.</p>
                    <button class="btn btn-primary" type="submit">Buat User Aktif</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Berikan Role</h4>
                <form method="post" action="<?= site_url('administration/access') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Company</label>
                        <select name="company_id" class="form-select" required>
                            <?php foreach ($companies as $company) : ?><option value="<?= esc($company['id']) ?>"><?= esc($company['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">User</label>
                        <select name="user_id" class="form-select" required>
                            <?php foreach ($users as $user) : ?>
                                <option value="<?= esc($user['id']) ?>" <?= $user['active'] ? '' : 'disabled' ?>><?= esc($user['username'] . ' - ' . $user['email'] . ($user['active'] ? '' : ' [inactive]')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select" required>
                            <?php foreach ($roles as $role) : ?><option value="<?= esc($role['id']) ?>"><?= esc($role['company_code'] . ' - ' . $role['name']) ?></option><?php endforeach; ?>
                        </select>
                        <div class="form-text">Role harus berasal dari company yang sama.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Site Awal</label>
                        <select name="branch_id" class="form-select">
                            <option value="">Tanpa pembatasan site saat ini</option>
                            <?php foreach ($branches as $branch) : ?><option value="<?= esc($branch['id']) ?>"><?= esc($branch['company_code'] . ' - ' . $branch['name']) ?></option><?php endforeach; ?>
                        </select>
                        <div class="form-text">Site harus berasal dari company yang sama agar bisa dipilih sebagai konteks.</div>
                    </div>
                    <button class="btn btn-primary" type="submit">Simpan Akses</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Lifecycle User Shield</h4>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>User</th><th>Email</th><th>Login</th><th>Reset</th><th>Password Sementara</th></tr></thead>
                        <tbody>
                        <?php foreach ($users as $user) : ?>
                            <tr>
                                <td><?= esc($user['username']) ?></td>
                                <td><?= esc($user['email']) ?></td>
                                <td>
                                    <form method="post" action="<?= site_url('administration/users/' . $user['id'] . '/status') ?>" class="d-flex gap-2">
                                        <?= csrf_field() ?>
                                        <select name="active" class="form-select form-select-sm">
                                            <option value="1" <?= $user['active'] ? 'selected' : '' ?>>Active</option>
                                            <option value="0" <?= $user['active'] ? '' : 'selected' ?>>Inactive</option>
                                        </select>
                                        <button type="submit" class="btn btn-outline-primary btn-sm">Update</button>
                                    </form>
                                </td>
                                <td>
                                    <?php if ($user['force_reset']) : ?>
                                        <span class="badge bg-warning text-dark">Wajib ganti</span>
                                    <?php else : ?>
                                        <span class="badge bg-success">Normal</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" action="<?= site_url('administration/users/' . $user['id'] . '/password') ?>" class="d-flex gap-2">
                                        <?= csrf_field() ?>
                                        <input type="password" name="password" class="form-control form-control-sm" placeholder="Password baru" minlength="12" required>
                                        <input type="password" name="password_confirm" class="form-control form-control-sm" placeholder="Konfirmasi" minlength="12" required>
                                        <button type="submit" class="btn btn-outline-warning btn-sm">Set & Wajib Ganti</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="form-text mt-3 mb-0">Menonaktifkan login atau memberi password sementara mencabut session lama. Password sementara wajib diganti user dan tidak dicatat dalam Audit Trail.</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Membership Company dan Role</h4>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Company</th><th>User</th><th>Role</th><th>Site</th><th>Status Company</th><th class="text-end">Aksi Role</th></tr></thead>
                        <tbody>
                        <?php foreach ($assignments as $assignment) : ?>
                            <tr>
                                <td><?= esc($assignment['company_code'] . ' - ' . $assignment['company_name']) ?></td>
                                <td>
                                    <?= esc($assignment['username']) ?>
                                    <?php if (! $assignment['user_active']) : ?><span class="badge bg-danger ms-1">login inactive</span><?php endif; ?>
                                </td>
                                <td><?= esc($assignment['role_name'] ?? '-') ?></td>
                                <td><?= esc($assignment['branch_codes'] ?? '-') ?></td>
                                <td>
                                    <form method="post" action="<?= site_url('administration/access/company-status') ?>" class="d-flex gap-2">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="company_id" value="<?= esc($assignment['company_id']) ?>">
                                        <input type="hidden" name="user_id" value="<?= esc($assignment['user_id']) ?>">
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="active" <?= $assignment['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= $assignment['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                        <button type="submit" class="btn btn-outline-primary btn-sm">Update</button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <?php if ($assignment['assignment_id'] !== null) : ?>
                                        <form method="post" action="<?= site_url('administration/access/revoke') ?>" onsubmit="return confirm('Cabut role user dari company ini?')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="company_id" value="<?= esc($assignment['company_id']) ?>">
                                            <input type="hidden" name="assignment_id" value="<?= esc($assignment['assignment_id']) ?>">
                                            <button class="btn btn-outline-danger btn-sm" type="submit">Cabut Role</button>
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
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Scope Site User</h4>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Company</th><th>User</th><th>Site</th><th>Status / Switch</th></tr></thead>
                        <tbody>
                        <?php foreach ($branchMemberships as $membership) : ?>
                            <tr>
                                <td><?= esc($membership['company_code']) ?></td>
                                <td><?= esc($membership['username']) ?></td>
                                <td><?= esc($membership['branch_code'] . ' - ' . $membership['branch_name']) ?></td>
                                <td>
                                    <form method="post" action="<?= site_url('administration/access/branch-status') ?>" class="d-flex gap-2 align-items-center">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="company_id" value="<?= esc($membership['company_id']) ?>">
                                        <input type="hidden" name="membership_id" value="<?= esc($membership['id']) ?>">
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="active" <?= $membership['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= $membership['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                        <div class="form-check">
                                            <input type="checkbox" name="can_switch" value="1" class="form-check-input" id="switch-<?= esc($membership['id']) ?>" <?= $membership['can_switch'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="switch-<?= esc($membership['id']) ?>">Switch</label>
                                        </div>
                                        <button type="submit" class="btn btn-outline-primary btn-sm">Update</button>
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
