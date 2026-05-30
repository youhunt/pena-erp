<?php
$tenantContext = $tenantContext ?? (new \App\Services\TenantContextService())->current((int) auth()->id());
$user = auth()->user();
$username = $user?->username ?? $user?->email ?? 'User';
$tenantMenus = $tenantContext === null
    ? []
    : (new \App\Services\TenantMenuService())->accessibleMenus((int) auth()->id(), (int) $tenantContext['company_id']);

$groupedTenantMenus = [];
$groupMeta = [
    'administration' => ['label' => 'Administration', 'icon' => 'bx bx-cog', 'order' => 10],
    'master'         => ['label' => 'Master Data', 'icon' => 'bx bx-data', 'order' => 20],
    'purchasing'     => ['label' => 'Purchasing', 'icon' => 'bx bx-cart-alt', 'order' => 30],
    'sales'          => ['label' => 'Sales', 'icon' => 'bx bx-store', 'order' => 40],
    'inventory'      => ['label' => 'Inventory', 'icon' => 'bx bx-package', 'order' => 50],
    'finance'        => ['label' => 'Finance', 'icon' => 'bx bx-wallet', 'order' => 60],
    'pos'            => ['label' => 'POS', 'icon' => 'bx bx-calculator', 'order' => 70],
    'reporting_ai'   => ['label' => 'Reporting & AI', 'icon' => 'bx bx-bar-chart-alt-2', 'order' => 80],
    'other'          => ['label' => 'Other Modules', 'icon' => 'bx bx-grid-alt', 'order' => 99],
];

$resolveMenuGroup = static function (array $menu): string {
    $route = (string) ($menu['route'] ?? '');
    $code  = (string) ($menu['code'] ?? '');

    if (str_starts_with($route, 'administration/')) {
        return 'administration';
    }

    if (in_array($route, ['setup', 'sales/master', 'purchasing/master'], true)) {
        return 'master';
    }

    if ($route === 'inventory') {
        return 'inventory';
    }

    if (str_starts_with($route, 'purchasing/orders') || str_starts_with($route, 'purchasing/receipts')) {
        return 'purchasing';
    }

    if (str_starts_with($route, 'sales/orders') || str_starts_with($route, 'sales/deliveries')) {
        return 'sales';
    }

    if (str_starts_with($route, 'finance/')) {
        return 'finance';
    }

    if (str_starts_with($route, 'pos/')) {
        return 'pos';
    }

    if (str_contains($route, 'report') || str_contains($code, 'report') || str_contains($route, 'ai/') || str_contains($code, 'ai-')) {
        return 'reporting_ai';
    }

    return 'other';
};

foreach ($tenantMenus as $tenantMenu) {
    $groupedTenantMenus[$resolveMenuGroup($tenantMenu)][] = $tenantMenu;
}

uksort($groupedTenantMenus, static function (string $a, string $b) use ($groupMeta): int {
    return ($groupMeta[$a]['order'] ?? 999) <=> ($groupMeta[$b]['order'] ?? 999);
});
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
                            <a class="dropdown-item" href="<?= site_url('account/security/password') ?>">
                                <i class="bx bx-key font-size-16 align-middle me-1"></i> Ubah Password
                            </a>
                            <div class="dropdown-divider"></div>
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

                        <?php if ($groupedTenantMenus !== [] && $tenantContext !== null) : ?>
                            <li class="menu-title">Modul <?= esc($tenantContext['company_code']) ?></li>
                            <?php foreach ($groupedTenantMenus as $groupKey => $menus) : ?>
                                <?php $meta = $groupMeta[$groupKey] ?? $groupMeta['other']; ?>
                                <?php if (count($menus) === 1 && $groupKey === 'other') : ?>
                                    <?php $menu = $menus[0]; ?>
                                    <li>
                                        <a href="<?= site_url($menu['route']) ?>" class="waves-effect">
                                            <i class="<?= esc($menu['icon'] ?: 'bx bx-grid-alt') ?>"></i><span><?= esc($menu['label']) ?></span>
                                        </a>
                                    </li>
                                <?php else : ?>
                                    <li>
                                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                                            <i class="<?= esc($meta['icon']) ?>"></i><span><?= esc($meta['label']) ?></span>
                                        </a>
                                        <ul class="sub-menu" aria-expanded="false">
                                            <?php foreach ($menus as $menu) : ?>
                                                <li><a href="<?= site_url($menu['route']) ?>"><?= esc($menu['label']) ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
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
