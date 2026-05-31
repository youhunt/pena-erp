<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18"><?= esc($title) ?></h4>
        <p class="text-muted mb-0"><?= esc($tenantContext['company_name']) ?> / Address mapping</p>
    </div>
</div>

<?php if (session('message')) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
<?php if (session('errors')) : ?><div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div><?php endif; ?>

<div class="row">
    <div class="col-xl-4">
        <div class="card"><div class="card-body">
            <h4 class="card-title">Create <?= esc($title) ?></h4>
            <?php if (! $canManage) : ?>
                <div class="alert alert-info">Read only.</div>
            <?php else : ?>
            <form method="post" action="<?= site_url($baseRoute . '/addresses') ?>">
                <?= csrf_field() ?>
                <div class="mb-2"><label class="form-label">Partner</label><select name="<?= esc($partnerField) ?>" class="form-select" required><?php foreach ($partners as $partner) : ?><option value="<?= (int) $partner['id'] ?>"><?= esc($partner['code'] . ' - ' . $partner['name']) ?></option><?php endforeach; ?></select></div>
                <div class="mb-2"><label class="form-label">Address Master</label><select name="address_id" class="form-select" required><?php foreach ($addresses as $address) : ?><option value="<?= (int) $address['id'] ?>"><?= esc($address['code'] . ' - ' . $address['label']) ?></option><?php endforeach; ?></select></div>
                <div class="mb-2"><label class="form-label">Address Type</label><select name="address_type" class="form-select" required><option value="office">Office</option><option value="billing">Billing</option><option value="mail">Mail</option><option value="ship_to">Ship To</option></select></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDefault"><label class="form-check-label" for="isDefault">Default Address</label></div>
                <button class="btn btn-primary mt-3">Simpan</button>
            </form>
            <?php endif; ?>
        </div></div>
    </div>
    <div class="col-xl-8">
        <div class="card"><div class="card-body">
            <h4 class="card-title">Address Mapping List</h4>
            <div class="table-responsive"><table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light"><tr><th>Partner</th><th>Address</th><th>Type</th><th>Default</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($partnerAddresses as $mapping) : ?>
                        <tr><td><?= esc($mapping['partner_code'] . ' - ' . $mapping['partner_name']) ?></td><td><?= esc($mapping['address_code'] . ' - ' . $mapping['address_label']) ?></td><td><?= esc($mapping['address_type']) ?></td><td><?= $mapping['is_default'] ? 'Yes' : 'No' ?></td><td><?= esc($mapping['status']) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if ($partnerAddresses === []) : ?><tr><td colspan="5" class="text-center text-muted">Belum ada address mapping.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </div></div>
    </div>
</div>
<?= $this->endSection() ?>
