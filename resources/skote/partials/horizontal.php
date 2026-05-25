<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="/" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="/assets/images/logo.svg" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="/assets/images/logo-dark.png" alt="" height="17">
                    </span>
                </a>

                <a href="/" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="/assets/images/logo-light.svg" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="/assets/images/logo-light.png" alt="" height="19">
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 d-lg-none header-item waves-effect waves-light" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <i class="fa fa-fw fa-bars"></i>
            </button>

            <!-- App Search-->
            <form class="app-search d-none d-lg-block">
                <div class="position-relative">
                    <input type="text" class="form-control" placeholder="<?= lang('Files.Search') ?>...">
                    <span class="bx bx-search-alt"></span>
                </div>
            </form>

            <!-- <div class="dropdown dropdown-mega d-none d-lg-block ms-2">
                <button type="button" class="btn header-item waves-effect" data-bs-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                    <span key="t-megamenu"><?= lang('Files.Mega Menu') ?></span>
                    <i class="mdi mdi-chevron-down"></i>
                </button>
                <div class="dropdown-menu dropdown-megamenu">
                    <div class="row">
                        <div class="col-sm-8">

                            <div class="row">
                                <div class="col-md-4">
                                    <h5 class="font-size-14 mt-0" key="t-ui-components"><?= lang('Files.UI Components') ?></h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="javascript:void(0);" key="t-lightbox"><?= lang('Files.Lightbox') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-range-slider"><?= lang('Files.Range Slider') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-sweet-alert"><?= lang('Files.Sweet-Alert') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-rating"><?= lang('Files.Rating') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-forms"><?= lang('Files.Forms') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-tables"><?= lang('Files.Tables') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-charts"><?= lang('Files.Charts') ?></a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <h5 class="font-size-14 mt-0" key="t-applications"><?= lang('Files.Applications') ?></h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="javascript:void(0);" key="t-ecommerce"><?= lang('Files.Ecommerce') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-calendar"><?= lang('Files.Calendars') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-email"><?= lang('Files.Email') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-projects"><?= lang('Files.Projects') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-tasks"><?= lang('Files.Tasks') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-contacts"><?= lang('Files.Contacts') ?></a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <h5 class="font-size-14 mt-0" key="t-extra-pages"><?= lang('Files.Extra Pages') ?></h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="javascript:void(0);" key="t-light-sidebar"><?= lang('Files.Light Sidebar') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-compact-sidebar"><?= lang('Files.Compact Sidebar') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-horizontal"><?= lang('Files.Horizontal Layout') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-maintenance"><?= lang('Files.Maintenance') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-coming-soon"><?= lang('Files.Coming Soon') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-timeline"><?= lang('Files.Timeline') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-faqs"><?= lang('Files.FAQs') ?></a>
                                        </li>

                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h5 class="font-size-14 mt-0" key="t-ui-components"><?= lang('Files.UI Components') ?></h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="javascript:void(0);" key="t-lightbox"><?= lang('Files.Lightbox') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-range-slider"><?= lang('Files.Range Slider') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-sweet-alert"><?= lang('Files.Sweet-Alert') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-rating"><?= lang('Files.Rating') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-forms"><?= lang('Files.Forms') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-tables"><?= lang('Files.Tables') ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-charts"><?= lang('Files.Charts') ?></a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-sm-5">
                                    <div>
                                        <img src="/assets/images/megamenu-img.png" alt="" class="img-fluid mx-auto d-block">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div> -->
        </div>

        <div class="d-flex">

            <!-- <div class="dropdown d-inline-block d-lg-none ms-2">
                <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-magnify"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">

                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div> -->

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item waves-effect" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php
                    $session = \Config\Services::session();
                    $lang = $session->get('lang');
                    switch ($lang) {
                        case 'en':
                            echo '<img src="/assets/images/flags/us.jpg" alt="Header Language" height="16">';
                            break;
                        case 'id':
                            echo '<img src="/assets/images/flags/id.jpg" alt="Header Language" height="16">';
                            break;
                        default:
                            echo '<img src="/assets/images/flags/us.jpg" alt="Header Language" height="16">';
                    }
                    ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end">

                    <!-- item-->
                    <?php if ($lang !== 'en') :  ?>
                        <a href="<?= base_url('lang/en'); ?>" class="dropdown-item notify-item language" data-lang="en">
                            <img src="/assets/images/flags/us.jpg" alt="user-image" class="me-1" height="12"> <span class="align-middle">English</span>
                        </a>
                    <?php endif ?>
                    <!-- item-->
                    <?php if ($lang !== 'id') :  ?>
                        <a href="<?= base_url('lang/id'); ?>" class="dropdown-item notify-item language" data-lang="id">
                            <img src="/assets/images/flags/id.jpg" alt="user-image" class="me-1" height="12"> <span class="align-middle">Bahasa</span>
                        </a>
                    <?php endif ?>

                </div>
            </div>

            <!-- <div class="dropdown d-none d-lg-inline-block ms-1">
                <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-customize"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <div class="px-lg-2">
                        <div class="row g-0">
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="/assets/images/brands/github.png" alt="Github">
                                    <span>GitHub</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="/assets/images/brands/bitbucket.png" alt="bitbucket">
                                    <span>Bitbucket</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="/assets/images/brands/dribbble.png" alt="dribbble">
                                    <span>Dribbble</span>
                                </a>
                            </div>
                        </div>

                        <div class="row g-0">
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="/assets/images/brands/dropbox.png" alt="dropbox">
                                    <span>Dropbox</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="/assets/images/brands/mail_chimp.png" alt="mail_chimp">
                                    <span>Mail Chimp</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="/assets/images/brands/slack.png" alt="slack">
                                    <span>Slack</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->

            <div class="dropdown d-none d-lg-inline-block ms-1">
                <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="fullscreen">
                    <i class="bx bx-fullscreen"></i>
                </button>
            </div>

            <!-- <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-bell bx-tada"></i>
                    <span class="badge bg-danger rounded-pill">3</span>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-0" key="t-notifications"> <?= lang('Files.Notifications') ?> </h6>
                            </div>
                            <div class="col-auto">
                                <a href="#!" class="small" key="t-view-all"> <?= lang('Files.View All') ?> </a>
                            </div>
                        </div>
                    </div>
                    <div data-simplebar style="max-height: 230px;">
                        <a href="" class="text-reset notification-item">
                            <div class="d-flex">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-primary rounded-circle font-size-16">
                                        <i class="bx bx-cart"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mt-0 mb-1" key="t-your-order">Your order is placed</h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1" key="t-grammer">If several languages coalesce the grammar</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-min-ago">3 min ago</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="" class="text-reset notification-item">
                            <div class="d-flex">
                                <img src="/assets/images/users/avatar-3.jpg" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                <div class="flex-grow-1">
                                    <h6 class="mt-0 mb-1">James Lemire</h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1" key="t-simplified">It will seem like simplified English.</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-hours-ago">1 hours ago</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="" class="text-reset notification-item">
                            <div class="d-flex">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-success rounded-circle font-size-16">
                                        <i class="bx bx-badge-check"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mt-0 mb-1" key="t-shipped">Your item is shipped</h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1" key="t-grammer">If several languages coalesce the grammar</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-min-ago">3 min ago</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a href="" class="text-reset notification-item">
                            <div class="d-flex">
                                <img src="/assets/images/users/avatar-4.jpg" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                <div class="flex-grow-1">
                                    <h6 class="mt-0 mb-1">Salena Layfield</h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1" key="t-occidental">As a skeptical Cambridge friend of mine occidental.</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-hours-ago">1 hours ago</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="p-2 border-top d-grid">
                        <a class="btn btn-sm btn-link font-size-14 text-center" href="javascript:void(0)">
                            <i class="mdi mdi-arrow-right-circle me-1"></i> <span key="t-view-more">View More..</span>
                        </a>
                    </div>
                </div>
            </div> -->

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="/assets/images/users/avatar-1.jpg" alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1" key="t-henry">Henry</span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a class="dropdown-item" href="#"><i class="bx bx-user font-size-16 align-middle me-1"></i> <span key="t-profile">Profile</span></a>
                    <a class="dropdown-item" href="#"><i class="bx bx-wallet font-size-16 align-middle me-1"></i> <span key="t-my-wallet">My Wallet</span></a>
                    <a class="dropdown-item d-block" href="#"><span class="badge bg-success float-end">11</span><i class="bx bx-wrench font-size-16 align-middle me-1"></i> <span key="t-settings">Settings</span></a>
                    <a class="dropdown-item" href="auth-lock-screen"><i class="bx bx-lock-open font-size-16 align-middle me-1"></i> <span key="t-lock-screen">Lock screen</span></a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="auth-login"><i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> <span key="t-logout">Logout</span></a>
                </div>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon right-bar-toggle waves-effect">
                    <i class="bx bx-cog bx-spin"></i>
                </button>
            </div>

        </div>
    </div>
</header>

<div class="topnav">
    <div class="container-fluid">
        <nav class="navbar navbar-light navbar-expand-lg topnav-menu">

            <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav">

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-dashboard" role="button">
                            <i class="bx bx-home-circle me-2"></i><span key="t-dashboards"><?= lang('Files.Dashboards') ?></span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-dashboard">

                            <a href="/" class="dropdown-item" key="t-default"><?= lang('Files.Default') ?></a>
                            <a href="dashboard-saas" class="dropdown-item" key="t-saas"><?= lang('Files.Saas') ?></a>
                            <a href="dashboard-crypto" class="dropdown-item" key="t-crypto"><?= lang('Files.Crypto') ?></a>
                            <a href="dashboard-blog" class="dropdown-item" key="t-blog"><?= lang('Files.Blog') ?></a>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-uielement" role="button">
                            <i class="bx bx-tone me-2"></i>
                            <span key="t-ui-elements"> <?= lang('Files.UI Elements') ?></span>
                            <div class="arrow-down"></div>
                        </a>

                        <div class="dropdown-menu mega-dropdown-menu px-2 dropdown-mega-menu-xl" aria-labelledby="topnav-uielement">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div>
                                        <a href="ui-alerts" class="dropdown-item" key="t-alerts"><?= lang('Files.Alerts') ?></a>
                                        <a href="ui-buttons" class="dropdown-item" key="t-buttons"><?= lang('Files.Buttons') ?></a>
                                        <a href="ui-cards" class="dropdown-item" key="t-cards"><?= lang('Files.Cards') ?></a>
                                        <a href="ui-carousel" class="dropdown-item" key="t-carousel"><?= lang('Files.Carousel') ?></a>
                                        <a href="ui-dropdowns" class="dropdown-item" key="t-dropdowns"><?= lang('Files.Dropdowns') ?></a>
                                        <a href="ui-grid" class="dropdown-item" key="t-grid"><?= lang('Files.Grid') ?></a>
                                        <a href="ui-images" class="dropdown-item" key="t-images"><?= lang('Files.Images') ?></a>
                                        <a href="ui-lightbox" class="dropdown-item" key="t-lightbox"><?= lang('Files.Lightbox') ?></a>
                                        <a href="ui-utilities" class="dropdown-item" key="t-utilities"><?= lang('Files.Utilities') ?></a>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div>
                                        <a href="ui-modals" class="dropdown-item" key="t-modals"><?= lang('Files.Modals') ?></a>
                                        <a href="ui-offcanvas" class="dropdown-item" key="t-offcanvas"><?= lang('Files.Offcanvas') ?></a>
                                        <a href="ui-rangeslider" class="dropdown-item" key="t-range-slider"><?= lang('Files.Range Slider') ?></a>
                                        <a href="ui-session-timeout" class="dropdown-item" key="t-session-timeout"><?= lang('Files.Session Timeout') ?></a>
                                        <a href="ui-progressbars" class="dropdown-item" key="t-progress-bars"><?= lang('Files.Progress Bars') ?></a>
                                        <a href="ui-placeholders" class="dropdown-item" key="t-Placeholders"><?= lang('Files.Placeholders') ?></a>
                                        <a href="ui-sweet-alert" class="dropdown-item" key="t-sweet-alert"><?= lang('Files.Sweet-Alert') ?></a>
                                        <a href="ui-tabs-accordions" class="dropdown-item" key="t-tabs-accordions"><?= lang('Files.Tabs & Accordions') ?></a>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div>
                                        <a href="ui-typography" class="dropdown-item" key="t-typography"><?= lang('Files.Typography') ?></a>
                                        <a href="ui-toasts" class="dropdown-item" key="t-toasts"><?= lang('Files.Toasts') ?></a>
                                        <a href="ui-video" class="dropdown-item" key="t-video"><?= lang('Files.Video') ?></a>
                                        <a href="ui-general" class="dropdown-item" key="t-general"><?= lang('Files.General') ?></a>
                                        <a href="ui-colors" class="dropdown-item" key="t-colors"><?= lang('Files.Colors') ?></a>
                                        <a href="ui-rating" class="dropdown-item" key="t-rating"><?= lang('Files.Rating') ?></a>
                                        <a href="ui-notifications" class="dropdown-item" key="t-notifications"><?= lang('Files.Notifications') ?></a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-pages" role="button">
                            <i class="bx bx-customize me-2"></i><span key="t-apps"><?= lang('Files.Apps') ?></span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-email" role="button">
                                    <span key="t-calendar"><?= lang('Files.Calendars') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-email">
                                    <a href="calendar" class="dropdown-item" key="t-tui-calendar"><?= lang('Files.TUI_Calendar') ?></a>
                                    <a href="calendar-full" class="dropdown-item" key="t-full-calender"><?= lang('Files.Full_Calendar') ?></a>
                                </div>
                            </div>

                            <a href="chat" class="dropdown-item" key="t-chat"><?= lang('Files.Chat') ?></a>
                            <a href="apps-filemanager" class="dropdown-item" key="t-file-manager"><?= lang('Files.File Manager') ?></a>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-email" role="button">
                                    <span key="t-email"><?= lang('Files.Email') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-email">
                                    <a href="email-inbox" class="dropdown-item" key="t-inbox"><?= lang('Files.Inbox') ?></a>
                                    <a href="email-read" class="dropdown-item" key="t-read-email"><?= lang('Files.Read Email') ?></a>

                                    <div class="dropdown">
                                        <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-blog" role="button">
                                            <span key="t-email-templates"><?= lang('Files.Templates') ?></span>
                                            <div class="arrow-down"></div>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="topnav-blog">
                                            <a href="email-template-basic" class="dropdown-item" key="t-basic-action"><?= lang('Files.Basic Action') ?></a>
                                            <a href="email-template-alert" class="dropdown-item" key="t-alert-email"><?= lang('Files.Alert Email') ?></a>
                                            <a href="email-template-billing" class="dropdown-item" key="t-bill-email"><?= lang('Files.Billing Email') ?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-ecommerce" role="button">
                                    <span key="t-ecommerce"><?= lang('Files.Ecommerce') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-ecommerce">
                                    <a href="ecommerce-products" class="dropdown-item" key="t-products"><?= lang('Files.Products') ?> </a>
                                    <a href="ecommerce-product-detail" class="dropdown-item" key="t-product-detail"><?= lang('Files.Product Detail') ?></a>
                                    <a href="ecommerce-orders" class="dropdown-item" key="t-orders"><?= lang('Files.Orders') ?></a>
                                    <a href="ecommerce-customers" class="dropdown-item" key="t-customers"><?= lang('Files.Customers') ?></a>
                                    <a href="ecommerce-cart" class="dropdown-item" key="t-cart"><?= lang('Files.Cart') ?></a>
                                    <a href="ecommerce-checkout" class="dropdown-item" key="t-checkout"><?= lang('Files.Checkout') ?></a>
                                    <a href="ecommerce-shops" class="dropdown-item" key="t-shops"><?= lang('Files.Shops') ?></a>
                                    <a href="ecommerce-add-product" class="dropdown-item" key="t-add-product"><?= lang('Files.Add Product') ?></a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-crypto" role="button">
                                    <span key="t-crypto"><?= lang('Files.Crypto') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-crypto">
                                    <a href="crypto-wallet" class="dropdown-item" key="t-wallet"><?= lang('Files.Wallet') ?></a>
                                    <a href="crypto-buy-sell" class="dropdown-item" key="t-buy"><?= lang('Files.Buy/Sell') ?></a>
                                    <a href="crypto-exchange" class="dropdown-item" key="t-exchange"><?= lang('Files.Exchange') ?></a>
                                    <a href="crypto-lending" class="dropdown-item" key="t-lending"><?= lang('Files.Lending') ?></a>
                                    <a href="crypto-orders" class="dropdown-item" key="t-orders"><?= lang('Files.Orders') ?></a>
                                    <a href="crypto-kyc-application" class="dropdown-item" key="t-kyc"><?= lang('Files.KYC Application') ?></a>
                                    <a href="crypto-ico-landing" class="dropdown-item" key="t-ico"><?= lang('Files.ICO Landing') ?></a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-project" role="button">
                                    <span key="t-projects"><?= lang('Files.Projects') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-project">
                                    <a href="projects-grid" class="dropdown-item" key="t-p-grid"><?= lang('Files.Projects Grid') ?></a>
                                    <a href="projects-list" class="dropdown-item" key="t-p-list"><?= lang('Files.Projects List') ?></a>
                                    <a href="projects-overview" class="dropdown-item" key="t-p-overview"><?= lang('Files.Project Overview') ?></a>
                                    <a href="projects-create" class="dropdown-item" key="t-create-new"><?= lang('Files.Create New') ?></a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-task" role="button">
                                    <span key="t-tasks"><?= lang('Files.Tasks') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-task">
                                    <a href="tasks-list" class="dropdown-item" key="t-task-list"><?= lang('Files.Task List') ?></a>
                                    <a href="tasks-kanban" class="dropdown-item" key="t-kanban-board"><?= lang('Files.Kanban Board') ?></a>
                                    <a href="tasks-create" class="dropdown-item" key="t-create-task"><?= lang('Files.Create Task') ?></a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-contact" role="button">
                                    <span key="t-contacts"><?= lang('Files.Contacts') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-contact">
                                    <a href="contacts-grid" class="dropdown-item" key="t-user-grid"><?= lang('Files.User Grid') ?></a>
                                    <a href="contacts-list" class="dropdown-item" key="t-user-list"><?= lang('Files.User List') ?></a>
                                    <a href="contacts-profile" class="dropdown-item" key="t-profile"><?= lang('Files.Profile') ?></a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-blog" role="button">
                                    <span key="t-blog"><?= lang('Files.Blog') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-blog">
                                    <a href="blog-list" class="dropdown-item" key="t-blog-list"><?= lang('Files.Blog List') ?></a>
                                    <a href="blog-grid" class="dropdown-item" key="t-blog-grid"><?= lang('Files.Blog Grid') ?></a>
                                    <a href="blog-details" class="dropdown-item" key="t-blog-details"><?= lang('Files.Blog Details') ?></a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-jobs" role="button">
                                    <span key="t-jobs"><?= lang('Files.Job') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-jobs">
                                    <a href="job-list" class="dropdown-item" key="t-job-list"><?= lang('Files.Job List') ?></a>
                                    <a href="job-grid" class="dropdown-item" key="t-job-grid"><?= lang('Files.Job Grid') ?></a>
                                    <a href="job-apply" class="dropdown-item" key="t-apply-job"><?= lang('Files.Apply Job') ?></a>
                                    <a href="job-details" class="dropdown-item" key="t-job-details"><?= lang('Files.Job Details') ?></a>
                                    <a href="job-categories" class="dropdown-item" key="t-Jobs-categories"><?= lang('Files.Job Categories') ?></a>
                                    <div class="dropdown">
                                        <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-candidate" role="button">
                                            <span key="t-candidate"><?= lang('Files.Candidate') ?></span>
                                            <div class="arrow-down"></div>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="topnav-candidate">
                                            <a href="candidate-list" class="dropdown-item" key="t-list"><?= lang('Files.List') ?></a>
                                            <a href="candidate-overview" class="dropdown-item" key="t-overview"><?= lang('Files.Overview') ?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-components" role="button">
                            <i class="bx bx-collection me-2"></i><span key="t-components"><?= lang('Files.Components') ?></span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-components">
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-form" role="button">
                                    <span key="t-forms"><?= lang('Files.Forms') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-form">
                                    <a href="form-elements" class="dropdown-item" key="t-form-elements"><?= lang('Files.Form Elements') ?></a>
                                    <a href="form-layouts" class="dropdown-item" key="t-form-layouts"><?= lang('Files.Form Layouts') ?></a>
                                    <a href="form-validation" class="dropdown-item" key="t-form-validation"><?= lang('Files.Form Validation') ?></a>
                                    <a href="form-advanced" class="dropdown-item" key="t-form-advanced"><?= lang('Files.Form Advanced') ?></a>
                                    <a href="form-editors" class="dropdown-item" key="t-form-editors"><?= lang('Files.Form Editors') ?></a>
                                    <a href="form-uploads" class="dropdown-item" key="t-form-upload"><?= lang('Files.Form File Upload') ?></a>
                                    <a href="form-xeditable" class="dropdown-item" key="t-form-xeditable"><?= lang('Files.Form Xeditable') ?></a>
                                    <a href="form-repeater" class="dropdown-item" key="t-form-repeater"><?= lang('Files.Form Repeater') ?></a>
                                    <a href="form-wizard" class="dropdown-item" key="t-form-wizard"><?= lang('Files.Form Wizard') ?></a>
                                    <a href="form-mask" class="dropdown-item" key="t-form-mask"><?= lang('Files.Form Mask') ?></a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-table" role="button">
                                    <span key="t-tables"><?= lang('Files.Tables') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-table">
                                    <a href="tables-basic" class="dropdown-item" key="t-basic-tables"><?= lang('Files.Basic Tables') ?></a>
                                    <a href="tables-datatable" class="dropdown-item" key="t-data-tables"><?= lang('Files.Data Tables') ?></a>
                                    <a href="tables-responsive" class="dropdown-item" key="t-responsive-table"><?= lang('Files.Responsive Table') ?></a>
                                    <a href="tables-editable" class="dropdown-item" key="t-editable-table"><?= lang('Files.Editable Table') ?></a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-charts" role="button">
                                    <span key="t-charts"><?= lang('Files.Charts') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-charts">
                                    <a href="charts-apex" class="dropdown-item" key="t-apex-charts"><?= lang('Files.Apex Charts') ?></a>
                                    <a href="charts-echart" class="dropdown-item" key="t-e-charts"><?= lang('Files.E Charts') ?></a>
                                    <a href="charts-chartjs" class="dropdown-item" key="t-chartjs-charts"><?= lang('Files.Chartjs Chart') ?></a>
                                    <a href="charts-flot" class="dropdown-item" key="t-flot-charts"><?= lang('Files.Flot Chart') ?></a>
                                    <a href="charts-tui" class="dropdown-item" key="t-ui-charts"><?= lang('Files.Toast UI Chart') ?></a>
                                    <a href="charts-knob" class="dropdown-item" key="t-knob-charts"><?= lang('Files.Jquery Knob Chart') ?></a>
                                    <a href="charts-sparkline" class="dropdown-item" key="t-sparkline-charts"><?= lang('Files.Sparkline Chart') ?></a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-icons" role="button">
                                    <span key="t-icons"><?= lang('Files.Icons') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-icons">
                                    <a href="icons-boxicons" class="dropdown-item" key="t-boxicons"><?= lang('Files.Boxicons') ?></a>
                                    <a href="icons-materialdesign" class="dropdown-item" key="t-material-design"><?= lang('Files.Material Design') ?></a>
                                    <a href="icons-dripicons" class="dropdown-item" key="t-dripicons"><?= lang('Files.Dripicons') ?></a>
                                    <a href="icons-fontawesome" class="dropdown-item" key="t-font-awesome"><?= lang('Files.Font Awesome') ?></a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-map" role="button">
                                    <span key="t-maps"><?= lang('Files.Maps') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-map">
                                    <a href="maps-google" class="dropdown-item" key="t-g-maps"><?= lang('Files.Google Maps') ?></a>
                                    <a href="maps-vector" class="dropdown-item" key="t-v-maps"><?= lang('Files.Vector Maps') ?></a>
                                    <a href="maps-leaflet" class="dropdown-item" key="t-l-maps"><?= lang('Files.Leaflet Maps') ?></a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-more" role="button">
                            <i class="bx bx-file me-2"></i><span key="t-extra-pages"><?= lang('Files.Extra Pages') ?></span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-more">
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-invoice" role="button">
                                    <span key="t-invoices"><?= lang('Files.Invoices') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-invoice">
                                    <a href="invoices-list" class="dropdown-item" key="t-invoice-list"><?= lang('Files.Invoice List') ?></a>
                                    <a href="invoices-detail" class="dropdown-item" key="t-invoice-detail"><?= lang('Files.Invoice Detail') ?></a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-auth" role="button">
                                    <span key="t-authentication"><?= lang('Files.Authentication') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-auth">
                                    <a href="auth-login" class="dropdown-item" key="t-login"><?= lang('Files.Login') ?></a>
                                    <a href="auth-login-2" class="dropdown-item" key="t-login-2"><?= lang('Files.Login') ?> 2</a>
                                    <a href="auth-register" class="dropdown-item" key="t-register"><?= lang('Files.Register') ?></a>
                                    <a href="auth-register-2" class="dropdown-item" key="t-register-2"><?= lang('Files.Register') ?> 2</a>
                                    <a href="auth-recoverpw" class="dropdown-item" key="t-recover-password"><?= lang('Files.Recover Password') ?></a>
                                    <a href="auth-recoverpw-2" class="dropdown-item" key="t-recover-password-2"><?= lang('Files.Recover Password') ?> 2</a>
                                    <a href="auth-lock-screen" class="dropdown-item" key="t-lock-screen"><?= lang('Files.Lock Screen') ?></a>
                                    <a href="auth-lock-screen-2" class="dropdown-item" key="t-lock-screen-2"><?= lang('Files.Lock Screen') ?> 2</a>
                                    <a href="auth-confirm-mail" class="dropdown-item" key="t-confirm-mail"><?= lang('Files.Confirm Mail') ?></a>
                                    <a href="auth-confirm-mail-2" class="dropdown-item" key="t-confirm-mail-2"><?= lang('Files.Confirm Mail') ?> 2</a>
                                    <a href="auth-email-verification" class="dropdown-item" key="t-email-verification"><?= lang('Files.Email Verification') ?></a>
                                    <a href="auth-email-verification-2" class="dropdown-item" key="t-email-verification-2"><?= lang('Files.Email Verification') ?> 2</a>
                                    <a href="auth-two-step-verification" class="dropdown-item" key="t-two-step-verification"><?= lang('Files.Two Step Verification') ?></a>
                                    <a href="auth-two-step-verification-2" class="dropdown-item" key="t-two-step-verification-2"><?= lang('Files.Two Step Verification') ?> 2</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-utility" role="button">
                                    <span key="t-utility"><?= lang('Files.Utility') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-utility">
                                    <a href="pages-starter" class="dropdown-item" key="t-starter-page"><?= lang('Files.Starter Page') ?></a>
                                    <a href="pages-maintenance" class="dropdown-item" key="t-maintenance"><?= lang('Files.Maintenance') ?></a>
                                    <a href="pages-comingsoon" class="dropdown-item" key="t-coming-soon"><?= lang('Files.Coming Soon') ?></a>
                                    <a href="pages-timeline" class="dropdown-item" key="t-timeline"><?= lang('Files.Timeline') ?></a>
                                    <a href="pages-faqs" class="dropdown-item" key="t-faqs"><?= lang('Files.FAQs') ?></a>
                                    <a href="pages-pricing" class="dropdown-item" key="t-pricing"><?= lang('Files.Pricing') ?></a>
                                    <a href="pages-404" class="dropdown-item" key="t-error-404"><?= lang('Files.Error') ?> 404</a>
                                    <a href="pages-500" class="dropdown-item" key="t-error-500"><?= lang('Files.Error') ?> 500</a>
                                    <a href="layouts-light-sidebar" class="dropdown-item" key="t-light-sidebar"><?= lang('Files.Light Sidebar') ?></a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-layout" role="button">
                            <i class="bx bx-layout me-2"></i><span key="t-layouts"><?= lang('Files.Layouts') ?></span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-layout">
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-layout-verti" role="button">
                                    <span key="t-vertical"><?= lang('Files.Vertical') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-layout-verti">
                                    <a href="layouts-compact-sidebar" class="dropdown-item" key="t-compact-sidebar"><?= lang('Files.Compact Sidebar') ?></a>
                                    <a href="layouts-icon-sidebar" class="dropdown-item" key="t-icon-sidebar"><?= lang('Files.Icon Sidebar') ?></a>
                                    <a href="layouts-boxed" class="dropdown-item" key="t-boxed-width"><?= lang('Files.Boxed Width') ?></a>
                                    <a href="layouts-preloader" class="dropdown-item" key="t-preloader"><?= lang('Files.Preloader') ?></a>
                                    <a href="layouts-colored-sidebar" class="dropdown-item" key="t-colored-sidebar"><?= lang('Files.Colored Sidebar') ?></a>
                                    <a href="layouts-scrollable" class="dropdown-item" key="t-scrollable"><?= lang('Files.Scrollable') ?></a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-layout-hori" role="button">
                                    <span key="t-horizontal"><?= lang('Files.Horizontal') ?></span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-layout-hori">
                                    <a href="layouts-horizontal" class="dropdown-item" key="t-horizontal"><?= lang('Files.Horizontal') ?></a>
                                    <a href="layouts-hori-topbar-light" class="dropdown-item" key="t-topbar-light"><?= lang('Files.Topbar Light') ?></a>
                                    <a href="layouts-hori-boxed-width" class="dropdown-item" key="t-boxed-width"><?= lang('Files.Boxed Width') ?></a>
                                    <a href="layouts-hori-preloader" class="dropdown-item" key="t-preloader"><?= lang('Files.Preloader') ?></a>
                                    <a href="layouts-hori-colored-header" class="dropdown-item" key="t-colored-topbar"><?= lang('Files.Colored Header') ?></a>
                                    <a href="layouts-hori-scrollable" class="dropdown-item" key="t-scrollable"><?= lang('Files.Scrollable') ?></a>
                                </div>
                            </div>
                        </div>
                    </li>

                </ul>
            </div>
        </nav>
    </div>
</div>