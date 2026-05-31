<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?><?= esc($menu['label']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$parts = array_map('trim', explode('/', (string) $menu['label']));
$section = count($parts) > 1 ? $parts[0] : 'Module';
$title = count($parts) > 1 ? $parts[1] : $menu['label'];
?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18"><?= esc($title) ?></h4>
        <p class="text-muted mb-0 mt-1"><?= esc($section) ?> · <?= esc($context['company_code']) ?><?= $context['branch_code'] !== null ? ' / ' . esc($context['branch_code']) : '' ?></p>
    </div>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><?= esc($context['company_code']) ?></li>
        <li class="breadcrumb-item active"><?= esc($title) ?></li>
    </ol>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card border border-warning">
            <div class="card-body">
                <span class="badge bg-warning text-dark mb-3">Planned Module</span>
                <h4 class="card-title mb-2"><?= esc($menu['label']) ?></h4>
                <p class="text-muted mb-3">
                    Menu ini sudah tersedia dalam struktur ERP dan RBAC, tetapi controller, service, database table, dan workflow bisnisnya belum diimplementasikan penuh.
                </p>
                <div class="alert alert-info mb-0">
                    Route saat ini adalah <strong><?= esc($menu['route']) ?></strong>. Modul ini bisa dibangun bertahap tanpa mengubah struktur sidebar lagi.
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Implementation Checklist</h4>
                <ul class="mb-0">
                    <li>Design database migration</li>
                    <li>Create read/write model</li>
                    <li>Create service layer</li>
                    <li>Create controller and route</li>
                    <li>Create Skote view</li>
                    <li>Add permission and audit trail</li>
                    <li>Add import/OCR mapping if needed</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
