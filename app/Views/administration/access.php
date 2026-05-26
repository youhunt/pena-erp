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
                            <?php foreach ($users as $user) : ?><option value="<?= esc($user['id']) ?>"><?= esc($user['username'] . ' - ' . $user['email']) ?></option><?php endforeach; ?>
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
                        <label class="form-label">Branch Awal</label>
                        <select name="branch_id" class="form-select">
                            <option value="">Tanpa pembatasan branch saat ini</option>
                            <?php foreach ($branches as $branch) : ?><option value="<?= esc($branch['id']) ?>"><?= esc($branch['company_code'] . ' - ' . $branch['name']) ?></option><?php endforeach; ?>
                        </select>
                        <div class="form-text">Branch harus berasal dari company yang sama agar bisa dipilih sebagai konteks.</div>
                    </div>
                    <button class="btn btn-primary" type="submit">Simpan Akses</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Membership Aktif</h4>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Company</th><th>User</th><th>Email</th><th>Role</th><th>Branch</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                        <?php foreach ($assignments as $assignment) : ?>
                            <tr>
                                <td><?= esc($assignment['company_code'] . ' - ' . $assignment['company_name']) ?></td>
                                <td><?= esc($assignment['username']) ?></td>
                                <td><?= esc($assignment['email']) ?></td>
                                <td><?= esc($assignment['role_name'] ?? '-') ?></td>
                                <td><?= esc($assignment['branch_codes'] ?? '-') ?></td>
                                <td><span class="badge bg-success"><?= esc($assignment['status']) ?></span></td>
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
    </div>
</div>
<?= $this->endSection() ?>
