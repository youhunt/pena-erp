<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Pilih Workspace<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0 font-size-18">Pilih Workspace</h4>
    <ol class="breadcrumb m-0"><li class="breadcrumb-item active">Company / Branch Context</li></ol>
</div>
<?php if (session('errors') !== null) : ?>
    <div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-3">Context Tersedia</h4>
        <?php if ($contexts === []) : ?>
            <div class="alert alert-warning mb-0">User ini belum memiliki membership company aktif.</div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Company</th><th>Branch</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($contexts as $context) : ?>
                        <?php $selected = $active !== null && (int) $active['company_id'] === (int) $context['company_id'] && $active['branch_id'] === $context['branch_id']; ?>
                        <tr>
                            <td><?= esc($context['company_code'] . ' - ' . $context['company_name']) ?></td>
                            <td><?= $context['branch_name'] === null ? '-' : esc($context['branch_code'] . ' - ' . $context['branch_name']) ?></td>
                            <td><?= $selected ? '<span class="badge bg-success">Aktif</span>' : '' ?></td>
                            <td>
                                <form method="post" action="<?= site_url('workspace/context') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="company_id" value="<?= esc($context['company_id']) ?>">
                                    <input type="hidden" name="branch_id" value="<?= esc($context['branch_id'] ?? '') ?>">
                                    <button type="submit" class="btn btn-outline-primary btn-sm">Gunakan</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
