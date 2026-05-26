<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18"><?= esc($title) ?></h4>
        <p class="text-muted mb-0"><?= esc($tenantContext['company_name']) ?> / <?= esc($partnerLabel) ?>, Profile, Terms, Promo dan Address</p>
    </div>
    <?php if (! $canManage) : ?><span class="badge bg-info">Read only</span><?php endif; ?>
</div>
<?php if (session('message') !== null) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
<?php if (session('errors') !== null) : ?>
    <div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<?php if ($canManage) : ?>
<div class="row">
    <div class="col-xl-4">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3"><?= esc($partnerLabel) ?> Terms</h4>
            <form method="post" action="<?= site_url($side . '/master/terms') ?>" class="row g-2">
                <?= csrf_field() ?>
                <div class="col-4"><label class="form-label">Code</label><input name="code" class="form-control" placeholder="NET30" required></div>
                <div class="col-8"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                <div class="col-4"><label class="form-label">Due Days</label><input type="number" min="0" name="due_days" value="30" class="form-control" required></div>
                <div class="col-4"><label class="form-label">Disc Days</label><input type="number" min="0" name="discount_days" value="0" class="form-control" required></div>
                <div class="col-4"><label class="form-label">Disc Rate</label><input type="number" min="0" step="0.000001" name="discount_rate" value="0" class="form-control" required></div>
                <div class="col-12"><button class="btn btn-primary mt-2">Simpan Terms</button></div>
            </form>
        </div></div>
    </div>
    <div class="col-xl-8">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3"><?= esc($partnerLabel) ?> Master</h4>
            <form method="post" action="<?= site_url($side . '/master/partners') ?>" class="row g-2 align-items-end">
                <?= csrf_field() ?>
                <div class="col-md-2"><label class="form-label">Code</label><input name="code" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">NPWP</label><input name="tax_no" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Phone</label><input name="phone" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Currency</label><select name="currency_id" class="form-select" required><?php foreach ($currencies as $currency) : ?><option value="<?= esc($currency['id']) ?>"><?= esc($currency['code']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Default Terms</label><select name="default_term_id" class="form-select"><option value="">-</option><?php foreach ($terms as $term) : ?><option value="<?= esc($term['id']) ?>"><?= esc($term['code']) ?></option><?php endforeach; ?></select></div>
                <?php if ($side === 'sales') : ?><div class="col-md-2"><label class="form-label">Credit Limit</label><input type="number" min="0" step="0.0001" name="credit_limit" value="0" class="form-control" required></div><?php endif; ?>
                <div class="col-md-2"><button class="btn btn-primary" <?= $currencies === [] ? 'disabled' : '' ?>>Simpan <?= esc($partnerLabel) ?></button></div>
            </form>
        </div></div>
    </div>
</div>
<div class="row">
    <div class="col-xl-12">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3"><?= esc($partnerLabel) ?> Profile & Policy</h4>
            <form method="post" action="<?= site_url($side . '/master/profiles') ?>" class="row g-2 align-items-end">
                <?= csrf_field() ?>
                <div class="col-md-3"><label class="form-label"><?= esc($partnerLabel) ?></label><select name="<?= $side === 'sales' ? 'customer_id' : 'supplier_id' ?>" class="form-select" required><?php foreach ($partners as $partner) : ?><option value="<?= esc($partner['id']) ?>"><?= esc($partner['code'] . ' - ' . $partner['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Reference Name</label><input name="reference_name" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Contact Name</label><input name="contact_name" class="form-control"></div>
                <div class="col-md-3"><label class="form-label"><?= $side === 'sales' ? 'Account Manager' : 'Buyer PIC' ?></label><input name="<?= $side === 'sales' ? 'account_manager_name' : 'buyer_name' ?>" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Default VAT</label><select name="default_tax_code_id" class="form-select"><option value="">-</option><?php foreach ($taxCodes as $tax) : ?><option value="<?= esc($tax['id']) ?>"><?= esc($tax['code']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Default Warehouse</label><select name="default_warehouse_id" class="form-select"><option value="">-</option><?php foreach ($warehouses as $warehouse) : ?><option value="<?= esc($warehouse['id']) ?>"><?= esc($warehouse['branch_code'] . ' / ' . $warehouse['code']) ?></option><?php endforeach; ?></select></div>
                <?php if ($side === 'purchasing') : ?><div class="col-md-2"><label class="form-label">Amount Limit</label><input type="number" min="0" step="0.0001" name="amount_limit" class="form-control"></div><?php endif; ?>
                <div class="col-md-2"><label class="form-label">Qty Limit</label><input type="number" min="0" step="0.0001" name="quantity_limit" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Limit Days</label><input type="number" min="0" name="limit_days" class="form-control"></div>
                <div class="<?= $side === 'sales' ? 'col-md-6' : 'col-md-4' ?>"><label class="form-label">Description</label><input name="description" class="form-control"></div>
                <div class="col-md-2"><button class="btn btn-primary" <?= $partners === [] ? 'disabled' : '' ?>>Simpan Profile</button></div>
            </form>
        </div></div>
    </div>
    <div class="col-xl-5">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3"><?= esc($partnerLabel) ?> Address</h4>
            <form method="post" action="<?= site_url($side . '/master/addresses') ?>" class="row g-2 align-items-end">
                <?= csrf_field() ?>
                <div class="col-md-5"><label class="form-label"><?= esc($partnerLabel) ?></label><select name="<?= $side === 'sales' ? 'customer_id' : 'supplier_id' ?>" class="form-select" required><?php foreach ($partners as $partner) : ?><option value="<?= esc($partner['id']) ?>"><?= esc($partner['code'] . ' - ' . $partner['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Address Master</label><select name="address_id" class="form-select" required><?php foreach ($addresses as $address) : ?><option value="<?= esc($address['id']) ?>"><?= esc($address['code']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Type</label><select name="address_type" class="form-select"><option value="billing">Billing</option><option value="shipping">Shipping</option><option value="mailing">Mailing</option><option value="office">Office</option><option value="pickup">Pickup</option></select></div>
                <div class="col-md-7"><div class="form-check mt-3"><input type="checkbox" class="form-check-input" id="<?= esc($side) ?>-default-address" name="is_default" value="1"><label class="form-check-label" for="<?= esc($side) ?>-default-address">Default address</label></div></div>
                <div class="col-md-5"><button class="btn btn-primary" <?= $partners === [] || $addresses === [] ? 'disabled' : '' ?>>Tautkan Address</button></div>
            </form>
        </div></div>
    </div>
    <div class="col-xl-7">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3"><?= esc($partnerLabel) ?> Promo</h4>
            <form method="post" action="<?= site_url($side . '/master/promotions') ?>" class="row g-2 align-items-end">
                <?= csrf_field() ?>
                <div class="col-md-3"><label class="form-label"><?= esc($partnerLabel) ?></label><select name="<?= $side === 'sales' ? 'customer_id' : 'supplier_id' ?>" class="form-select"><option value="">All</option><?php foreach ($partners as $partner) : ?><option value="<?= esc($partner['id']) ?>"><?= esc($partner['code']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><label class="form-label">Code</label><input name="code" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label">Type</label><select name="discount_type" class="form-select"><option value="percentage">%</option><option value="amount">Amount</option></select></div>
                <div class="col-md-2"><label class="form-label">Value</label><input type="number" step="0.0001" min="0.0001" name="discount_value" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Start</label><input type="date" name="starts_on" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">End</label><input type="date" name="ends_on" class="form-control" required></div>
                <div class="col-md-3"><button class="btn btn-primary">Simpan Promo</button></div>
            </form>
        </div></div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-xl-7"><div class="card"><div class="card-body">
        <h4 class="card-title mb-3">Daftar <?= esc($partnerLabel) ?></h4>
        <table class="table align-middle mb-0"><thead><tr><th>Code / Name</th><th>Currency</th><th>Terms</th><?php if ($side === 'sales') : ?><th>Credit Limit</th><?php endif; ?><th>Status</th></tr></thead><tbody>
            <?php foreach ($partners as $partner) : ?><tr><td><strong><?= esc($partner['code']) ?></strong><br><small><?= esc($partner['name']) ?></small></td><td><?= esc($partner['currency_code']) ?></td><td><?= esc($partner['term_code'] ?? '-') ?></td><?php if ($side === 'sales') : ?><td><?= number_format((float) $partner['credit_limit'], 2, ',', '.') ?></td><?php endif; ?><td><?= esc($partner['status']) ?></td></tr><?php endforeach; ?>
            <?php if ($partners === []) : ?><tr><td colspan="<?= $side === 'sales' ? '5' : '4' ?>" class="text-muted">Belum ada partner.</td></tr><?php endif; ?>
        </tbody></table>
    </div></div></div>
    <div class="col-xl-5"><div class="card"><div class="card-body">
        <h4 class="card-title mb-3">Terms & Promo</h4>
        <div class="mb-3"><strong>Terms:</strong> <?= esc(implode(', ', array_column($terms, 'code')) ?: '-') ?></div>
        <table class="table table-sm mb-0"><tbody>
            <?php foreach ($promotions as $promo) : ?><tr><td><?= esc($promo['code']) ?><br><small><?= esc($promo['partner_code'] ?? 'All') ?> / <?= esc($promo['starts_on'] . ' s.d. ' . $promo['ends_on']) ?></small></td><td><?= esc($promo['discount_value']) ?></td></tr><?php endforeach; ?>
            <?php if ($promotions === []) : ?><tr><td class="text-muted">Belum ada promo.</td></tr><?php endif; ?>
        </tbody></table>
    </div></div></div>
</div>
<div class="card"><div class="card-body">
    <h4 class="card-title mb-3"><?= esc($partnerLabel) ?> Profile & Policy</h4>
    <table class="table table-sm mb-0"><thead><tr><th>Partner</th><th>Reference / Contact</th><th>VAT / Warehouse</th><th>PIC</th><th>Limits</th></tr></thead><tbody>
        <?php foreach ($profiles as $profile) : ?><tr><td><?= esc($profile['partner_code'] . ' - ' . $profile['partner_name']) ?></td><td><?= esc($profile['reference_name'] ?? '-') ?><br><small><?= esc($profile['contact_name'] ?? '-') ?></small></td><td><?= esc($profile['tax_code'] ?? '-') ?> / <?= esc($profile['warehouse_code'] ?? '-') ?></td><td><?= esc(($side === 'sales' ? $profile['account_manager_name'] : $profile['buyer_name']) ?? '-') ?></td><td><?= $side === 'purchasing' ? esc($profile['amount_limit'] ?? '-') . ' / ' : '' ?><?= esc($profile['quantity_limit'] ?? '-') ?> qty / <?= esc($profile['limit_days'] ?? '-') ?> days</td></tr><?php endforeach; ?>
        <?php if ($profiles === []) : ?><tr><td colspan="5" class="text-muted">Belum ada profile tambahan.</td></tr><?php endif; ?>
    </tbody></table>
</div></div>
<div class="card"><div class="card-body">
    <h4 class="card-title mb-3"><?= esc($partnerLabel) ?> Address Mapping</h4>
    <table class="table table-sm mb-0"><thead><tr><th>Partner</th><th>Address Master</th><th>Type</th><th>Default</th></tr></thead><tbody>
        <?php foreach ($partnerAddresses as $mapping) : ?><tr><td><?= esc($mapping['partner_code'] . ' - ' . $mapping['partner_name']) ?></td><td><?= esc($mapping['address_code'] . ' - ' . $mapping['address_label']) ?></td><td><?= esc($mapping['address_type']) ?></td><td><?= $mapping['is_default'] ? 'Yes' : 'No' ?></td></tr><?php endforeach; ?>
        <?php if ($partnerAddresses === []) : ?><tr><td colspan="4" class="text-muted">Belum ada address mapping.</td></tr><?php endif; ?>
    </tbody></table>
</div></div>
<?= $this->endSection() ?>
