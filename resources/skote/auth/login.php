<!doctype html>
<html lang="en">

<head>

    <?= $title_meta ?>

    <?= $this->include('partials/head-css') ?>

</head>

<body>
    <div class="account-pages my-5 pt-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card overflow-hidden">
                        <div class="bg-primary-subtle">
                            <div class="row">
                                <div class="col-7">
                                    <div class="text-primary p-4">
                                        <h5 class="text-primary">Welcome Back !</h5>
                                        <p>Sign in to continue to Pena Inovasi Sistem.</p>
                                    </div>
                                </div>
                                <div class="col-5 align-self-end">
                                    <img src="/assets/images/profile-img.png" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="auth-logo">
                                <a href="/" class="auth-logo-light">
                                    <div class="avatar-md profile-user-wid mb-2">
                                        <span class="avatar-title rounded-circle bg-light">
                                            <img src="/assets/images/Logo.png" alt="" class="rounded-circle" height="34">
                                        </span>
                                    </div>
                                </a>

                                <a href="/" class="auth-logo-dark">
                                    <div class="avatar-md profile-user-wid mb-2">
                                        <span class="avatar-title rounded-circle bg-light">
                                            <img src="/assets/images/Logo.png" alt="" class="rounded-circle" height="34">
                                        </span>
                                    </div>
                                </a>
                            </div>
                            <div class="p-2">
                                <!-- <form class="form-horizontal" method="post" action="auth-login"> -->
                                <?= view('Myth\Auth\Views\_message_block') ?>
                                <form action="<?= route_to('login') ?>"  class="form-horizontal" method="post">
                                    <?= csrf_field() ?>
                                    <?php if ($config->validFields === ['email']): ?>
                                        <div class="mb-3">
                                            <label for="username" class="form-label"><?=lang('Auth.email')?></label>
                                            <input type="email" class="form-control <?php if(session('errors.login')) : ?>is-invalid<?php endif ?>" id="username" placeholder="<?=lang('Auth.email')?>" name="login" value="">
                                        </div>
                                        <?php if (session('errors.login')) { ?>
                                            <div class="alert alert-danger" role="alert">
                                                <?= session('errors.login'); ?>
                                            </div>
                                        <?php } ?>
                                    <?php else: ?>
                                        <div class="mb-3">
                                            <label for="username" class="form-label"><?=lang('Auth.emailOrUsername')?></label>
                                            <input type="text" class="form-control <?php if(session('errors.login')) : ?>is-invalid<?php endif ?>" id="username" placeholder="<?=lang('Auth.emailOrUsername')?>" name="login" value="">
                                        </div>
                                        <?php if (session('errors.login')) { ?>
                                            <div class="alert alert-danger" role="alert">
                                                <?= session('errors.login'); ?>
                                            </div>
                                        <?php } ?>
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <label class="form-label"><?=lang('Auth.password')?></label>
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" class="form-control" placeholder="<?=lang('Auth.password')?>" aria-label="<?=lang('Auth.password')?>" aria-describedby="password-addon" name="password" value="">
                                            <button class="btn btn-light " type="button" id="password-addon"><i class="mdi mdi-eye-outline"></i></button>
                                        </div>
                                    </div>
                                    <?php if (session('errors.password')) { ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?= session('errors.password'); ?>
                                        </div>
                                    <?php } ?>

                                    <?php if ($config->allowRemembering): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="customCheck" <?php if(old('remember')) : ?> checked <?php endif ?> >
                                            <label class="form-check-label" for="customCheck">
                                                <?=lang('Auth.rememberMe')?>
                                            </label>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-3 d-grid">
                                        <button class="btn btn-primary waves-effect waves-light" type="submit"><?=lang('Auth.loginAction')?></button>
                                    </div>

                                    <?php if ($config->activeResetter): ?>
                                        <div class="mt-4 text-center">
                                            <a href="<?= route_to('forgot') ?>" class="text-muted"><i class="mdi mdi-lock me-1"></i> <?=lang('Auth.forgotYourPassword')?></a>
                                        </div>
                                    <?php endif; ?>
                                </form>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- end account-pages -->

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>
</body>

</html>