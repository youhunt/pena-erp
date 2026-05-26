<?php
$tenantContext = $tenantContext ?? (new \App\Services\TenantContextService())->current((int) auth()->id());
$user = auth()->user();
$username = $user?->username ?? $user?->email ?? 'User';
$canManageCompanies = $user?->can('platform.company.manage') ?? false;
$tenantMenus = $tenantContext === null
    ? []
    : (new \App\Services\TenantMenuService())->accessibleMenus((int) auth()->id(), (int) $tenantContext['company_id']);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title><?= $this->renderSection('title') ?> | Pena ERP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pena ERP System">
    <link rel="shortcut icon" href="<?= base_url('assets/images/Logo.png') ?>">
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/css/icons.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/css/app.min.css') ?>" rel="stylesheet" type="text/css">
</head>
<body data-sidebar="dark">
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <div class="navbar-brand-box">
                        <a href="<?= base_url() ?>" class="logo logo-dark">
                            <span class="logo-sm"><img src="<?= base_url('assets/images/logo-sm-dark.png') ?>" alt="Pena" height="22"></span>
                            <span class="logo-lg"><img src="<?= base_url('assets/images/logo-dark.png') ?>" alt="Pena ERP" height="22"></span>
                        </a>
                        <a href="<?= base_url() ?>" class="logo logo-light">
                            <span class="logo-sm"><img src="<?= base_url('assets/images/logo-sm-light.png') ?>" alt="Pena" height="22"></span>
                            <span class="logo-lg"><img src="<?= base_url('assets/images/logo-light.png') ?>" alt="Pena ERP" height="22"></span>
                        </a>
                    </div>
                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>
                </div>
                <div class="d-flex">
                    <?php if ($tenantContext !== null) : ?>
                        <a class="btn header-item waves-effect d-none d-md-inline-flex align-items-center" href="<?= site_url('workspace') ?>">
                            <i class="bx bx-briefcase-alt-2 font-size-18 me-1"></i>
                            <span><?= esc($tenantContext['company_code']) ?><?= $tenantContext['branch_code'] !== null ? ' / ' . esc($tenantContext['branch_code']) : '' ?></span>
                        </a>
                    <?php endif; ?>
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" data-bs-toggle="dropdown">
                            <img class="rounded-circle header-profile-user" src="<?= base_url('assets/images/users/user-dummy-img.jpg') ?>" alt="">
                            <span class="d-none d-xl-inline-block ms-1"><?= esc($username) ?></span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item text-danger" href="<?= url_to('logout') ?>">
                                <i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="vertical-menu">
            <div data-simplebar class="h-100">
                <div id="sidebar-menu">
                    <ul class="metismenu list-unstyled" id="side-menu">
                        <li class="menu-title">Menu</li>
                        <li>
                            <a href="<?= base_url() ?>" class="waves-effect">
                                <i class="bx bx-home-circle"></i><span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('workspace') ?>" class="waves-effect">
                                <i class="bx bx-briefcase-alt-2"></i><span>Workspace</span>
                            </a>
                        </li>
                        <?php if ($tenantMenus !== []) : ?>
                            <li class="menu-title">Modul <?= esc($tenantContext['company_code']) ?></li>
                            <?php foreach ($tenantMenus as $tenantMenu) : ?>
                                <li>
                                    <a href="<?= site_url($tenantMenu['route']) ?>" class="waves-effect">
                                        <i class="<?= esc($tenantMenu['icon'] ?: 'bx bx-grid-alt') ?>"></i><span><?= esc($tenantMenu['label']) ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if ($canManageCompanies) : ?>
                            <li class="menu-title">Administrasi</li>
                            <li>
                                <a href="<?= site_url('administration/companies') ?>" class="waves-effect">
                                    <i class="bx bx-buildings"></i><span>Company</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= site_url('administration/branches') ?>" class="waves-effect">
                                    <i class="bx bx-map"></i><span>Branch</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= site_url('administration/regions') ?>" class="waves-effect">
                                    <i class="bx bx-world"></i><span>Master Wilayah</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= site_url('administration/access') ?>" class="waves-effect">
                                    <i class="bx bx-user-check"></i><span>Akses User</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= site_url('administration/rbac') ?>" class="waves-effect">
                                    <i class="bx bx-lock-open-alt"></i><span>Role & Permission</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?= $this->renderSection('content') ?>
                </div>
            </div>
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">&copy; <?= date('Y') ?> Pena ERP.</div>
                        <div class="col-sm-6"><div class="text-sm-end d-none d-sm-block">Pena Inovasi Sistem</div></div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="<?= base_url('assets/libs/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/metismenu/metisMenu.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/simplebar/simplebar.min.js') ?>"></script>
    <script src="<?= base_url('assets/libs/node-waves/waves.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
