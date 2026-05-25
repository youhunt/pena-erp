<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Masuk<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="account-pages my-5 pt-sm-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card overflow-hidden">
                    <div class="bg-primary-subtle">
                        <div class="row">
                            <div class="col-7">
                                <div class="text-primary p-4">
                                    <h5 class="text-primary">Selamat datang kembali</h5>
                                    <p>Masuk untuk melanjutkan ke Pena ERP.</p>
                                </div>
                            </div>
                            <div class="col-5 align-self-end">
                                <img src="<?= base_url('assets/images/profile-img.png') ?>" alt="" class="img-fluid">
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <a href="<?= base_url() ?>" class="auth-logo-dark">
                            <div class="avatar-md profile-user-wid mb-3">
                                <span class="avatar-title rounded-circle bg-light">
                                    <img src="<?= base_url('assets/images/Logo.png') ?>" alt="Pena ERP" class="rounded-circle" height="40">
                                </span>
                            </div>
                        </a>
                        <div class="p-2">
                            <?php if (session('error') !== null) : ?>
                                <div class="alert alert-danger" role="alert"><?= esc(session('error')) ?></div>
                            <?php elseif (session('errors') !== null) : ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php foreach ((array) session('errors') as $error) : ?>
                                        <?= esc($error) ?><br>
                                    <?php endforeach ?>
                                </div>
                            <?php endif ?>

                            <?php if (session('message') !== null) : ?>
                                <div class="alert alert-success" role="alert"><?= esc(session('message')) ?></div>
                            <?php endif ?>

                            <form action="<?= url_to('login') ?>" method="post">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label for="email" class="form-label"><?= lang('Auth.email') ?></label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?= esc(old('email'), 'attr') ?>"
                                           autocomplete="email" placeholder="<?= esc(lang('Auth.email'), 'attr') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label"><?= lang('Auth.password') ?></label>
                                    <input type="password" class="form-control" id="password" name="password"
                                           autocomplete="current-password" placeholder="<?= esc(lang('Auth.password'), 'attr') ?>" required>
                                </div>
                                <?php if (setting('Auth.sessionConfig')['allowRemembering']) : ?>
                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="remember" class="form-check-input" id="remember"<?= old('remember') ? ' checked' : '' ?>>
                                        <label class="form-check-label" for="remember"><?= lang('Auth.rememberMe') ?></label>
                                    </div>
                                <?php endif ?>
                                <div class="d-grid">
                                    <button class="btn btn-primary waves-effect waves-light" type="submit"><?= lang('Auth.login') ?></button>
                                </div>
                                <?php if (setting('Auth.allowMagicLinkLogins')) : ?>
                                    <div class="mt-4 text-center">
                                        <a href="<?= url_to('magic-link') ?>" class="text-muted">
                                            <i class="mdi mdi-lock me-1"></i><?= lang('Auth.useMagicLink') ?>
                                        </a>
                                    </div>
                                <?php endif ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
