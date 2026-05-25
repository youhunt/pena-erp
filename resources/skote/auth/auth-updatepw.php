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
                        <div class="bg-primary bg-soft">
                            <div class="row">
                                <div class="col-7">
                                    <div class="text-primary p-4">
                                        <h5 class="text-primary"> Reset Password</h5>
                                        <p>Re-Password with Skote.</p>
                                    </div>
                                </div>
                                <div class="col-5 align-self-end">
                                    <img src="/assets/images/profile-img.png" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div>
                                <a href="/">
                                    <div class="avatar-md profile-user-wid mb-2">
                                        <span class="avatar-title rounded-circle bg-light">
                                            <img src="/assets/images/logo.svg" alt="" class="rounded-circle" height="34">
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="p-2">
                                <?php
                                if (isset($result)) {
                                    if ($result == 'invalid') {
                                        echo '<div class="alert alert-danger text-center mb-2" role="alert">';
                                        echo "Invalid access to this page!";
                                        echo '</div>';
                                    } else if ($result == 'success') {
                                        echo '<div class="alert alert-success text-center mb-2" role="alert">';
                                        echo "Password updated successfully!";
                                        echo '</div>';
                                    } else {
                                    }
                                }
                                ?>
                                <form class="form-horizontal" method="post" action="auth-updatepw">

                                    <div class="mb-3">
                                        <label for="useremail">Email</label>
                                        <input type="email" class="form-control" id="useremail" placeholder="Enter email" name="useremail" value="<?= $useremail ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label for="userpassword">Password</label>
                                        <input type="password" class="form-control" id="userpassword" placeholder="Enter password" name="userpassword" value="">
                                    </div>

                                    <?php if (isset($validation) && $validation->hasError('userpassword')) { ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?= $validation->getError('userpassword'); ?>
                                        </div>
                                    <?php } ?>

                                    <div class="mb-3">
                                        <label for="userpassword">Password</label>
                                        <input type="password" class="form-control" id="userpassword_confirm" placeholder="Re-Enter password" name="userpassword_confirm" value="">
                                    </div>

                                    <?php if (isset($validation) && $validation->hasError('userpassword_confirm')) { ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?= $validation->getError('userpassword_confirm'); ?>
                                        </div>
                                    <?php } ?>

                                    <div class="text-end">
                                        <button class="btn btn-primary w-md waves-effect waves-light" type="submit">Reset</button>
                                    </div>

                                </form>
                            </div>

                        </div>
                    </div>
                    <div class="mt-5 text-center">
                        <p>Remember It ? <a href="auth-login" class="fw-medium text-primary"> Sign In here</a> </p>
                        <p>© <script>
                                document.write(new Date().getFullYear())
                            </script> Skote. Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesbrand</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>
</body>

</html>