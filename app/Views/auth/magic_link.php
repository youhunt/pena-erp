<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Magic Link<?= $this->endSection() ?>

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
                                    <h5 class="text-primary">Tautan masuk</h5>
                                    <p>Kami akan mengirimkan tautan aman ke email Anda.</p>
                                </div>
                            </div>
                            <div class="col-5 align-self-end">
                                <img src="<?= base_url('assets/images/profile-img.png') ?>" alt="" class="img-fluid">
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <a href="<?= url_to('login') ?>">
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

                            <form action="<?= url_to('magic-link') ?>" method="post">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label for="email" class="form-label"><?= lang('Auth.email') ?></label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?= esc(old('email', auth()->user()->email ?? null), 'attr') ?>"
                                           autocomplete="email" placeholder="<?= esc(lang('Auth.email'), 'attr') ?>" required>
                                </div>
                                <div class="d-grid">
                                    <button class="btn btn-primary waves-effect waves-light" type="submit"><?= lang('Auth.send') ?></button>
                                </div>
                                <div class="mt-4 text-center">
                                    <a href="<?= url_to('login') ?>" class="text-muted"><?= lang('Auth.backToLogin') ?></a>
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
