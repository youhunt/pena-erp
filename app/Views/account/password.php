<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Ganti Password<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="account-pages my-5 pt-sm-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card overflow-hidden">
                    <div class="bg-primary-subtle">
                        <div class="row">
                            <div class="col-8">
                                <div class="text-primary p-4">
                                    <h5 class="text-primary">Keamanan Akun</h5>
                                    <p>Perbarui password login Pena ERP.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="p-2 mt-4">
                            <?php if ($required) : ?>
                                <div class="alert alert-warning">Password sementara wajib diganti sebelum Anda dapat mengakses workspace.</div>
                            <?php endif; ?>
                            <?php if (session('errors') !== null) : ?>
                                <div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div>
                            <?php endif; ?>
                            <form method="post" action="<?= site_url('account/security/password') ?>">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" name="password" class="form-control" minlength="12" required autocomplete="new-password">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ulangi Password Baru</label>
                                    <input type="password" name="password_confirm" class="form-control" minlength="12" required autocomplete="new-password">
                                </div>
                                <p class="text-muted">Minimal 12 karakter. Setelah disimpan, semua session lama dicabut dan Anda perlu login kembali.</p>
                                <div class="d-grid">
                                    <button class="btn btn-primary" type="submit">Simpan Password Baru</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
