<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Customer Master<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18">Customer Master</h4>
        <p class="text-muted mb-0"><?= esc($tenantContext['company_name']) ?> / Detailed customer master</p>
    </div>
</div>

<?php if (session('message')) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
<?php if (session('errors')) : ?><div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div><?php endif; ?>

<div class="row">
    <div class="col-xl-5">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Create Customer</h4>
                <?php if (! $canManage) : ?>
                    <div class="alert alert-info">Read only.</div>
                <?php else : ?>
                <form method="post" action="<?= site_url('sales/customers') ?>">
                    <?= csrf_field() ?>
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label">Company Code</label><input class="form-control" value="<?= esc($tenantContext['company_code'] ?? '') ?>" disabled></div>
                        <div class="col-md-4"><label class="form-label">Site Code</label><input class="form-control" value="<?= esc($tenantContext['branch_code'] ?? '') ?>" disabled></div>
                        <div class="col-md-4"><label class="form-label">Customer Code</label><input name="code" class="form-control" maxlength="12" required></div>
                        <div class="col-md-6"><label class="form-label">Customer Name</label><input name="name" class="form-control" maxlength="500" required></div>
                        <div class="col-md-6"><label class="form-label">Customer Ref Name</label><input name="ref_name" class="form-control" maxlength="500"></div>
                        <div class="col-md-6"><label class="form-label">Contact Name</label><input name="office_contact_name" class="form-control" maxlength="100"></div>
                        <div class="col-md-6"><label class="form-label">Description</label><input name="description" class="form-control" maxlength="500"></div>
                        <div class="col-md-6"><label class="form-label">Currency</label><select name="currency_id" class="form-select" required><?php foreach ($currencies as $currency) : ?><option value="<?= (int) $currency['id'] ?>"><?= esc($currency['code']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Terms Code</label><select name="default_term_id" class="form-select"><option value="">-</option><?php foreach ($terms as $term) : ?><option value="<?= (int) $term['id'] ?>"><?= esc($term['code']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Ship From Whs</label><select name="ship_from_whs" class="form-select"><option value="">-</option><?php foreach ($warehouses as $warehouse) : ?><option value="<?= esc($warehouse['code']) ?>"><?= esc($warehouse['branch_code'] . ' / ' . $warehouse['code']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Tax Number</label><input name="tax_no" class="form-control" maxlength="50"></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input name="email" type="email" class="form-control" maxlength="120"></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" maxlength="40"></div>
                        <div class="col-md-6"><label class="form-label">Limit Amount</label><input name="credit_limit" type="number" step="0.000001" class="form-control" value="0"></div>
                        <div class="col-md-6"><label class="form-label">Limit Qty</label><input name="limit_qty" type="number" step="0.000001" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Limit Days</label><input name="limit_days" type="number" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Sales Code</label><input name="sales_code" class="form-control" maxlength="10"></div>
                        <div class="col-md-6"><label class="form-label">Sales Name</label><input name="sales_name" class="form-control" maxlength="100"></div>
                    </div>

                    <hr>
                    <h5>Office Address</h5>
                    <div class="row g-2">
                        <div class="col-12"><textarea name="office_address" class="form-control" placeholder="Office Address"></textarea></div>
                        <div class="col-md-6"><input name="office_city" class="form-control" placeholder="City"></div><div class="col-md-6"><input name="office_province" class="form-control" placeholder="Province"></div>
                        <div class="col-md-6"><input name="office_country" class="form-control" placeholder="Country"></div><div class="col-md-6"><input name="office_postal_code" class="form-control" placeholder="Postal Code"></div>
                        <div class="col-md-6"><input name="office_phone_number" class="form-control" placeholder="Phone"></div><div class="col-md-6"><input name="office_handphone" class="form-control" placeholder="Handphone"></div>
                    </div>

                    <hr>
                    <h5>Billing / Mail / Ship To</h5>
                    <div class="row g-2">
                        <div class="col-md-6"><input name="billing_customer" class="form-control" placeholder="Billing Customer"></div><div class="col-md-6"><input name="bill_to_code" class="form-control" placeholder="Bill To Code"></div>
                        <div class="col-12"><textarea name="billing_address" class="form-control" placeholder="Billing Address"></textarea></div>
                        <div class="col-md-6"><input name="billing_city" class="form-control" placeholder="Billing City"></div><div class="col-md-6"><input name="billing_contact_name" class="form-control" placeholder="Billing Contact"></div>
                        <div class="col-md-6"><input name="mail_customer" class="form-control" placeholder="Mail Customer"></div><div class="col-md-6"><input name="mail_code" class="form-control" placeholder="Mail Code"></div>
                        <div class="col-12"><textarea name="mail_address" class="form-control" placeholder="Mail Address"></textarea></div>
                        <div class="col-md-6"><input name="ship_to_customer" class="form-control" placeholder="Ship To Customer"></div><div class="col-md-6"><input name="ship_to_code" class="form-control" placeholder="Ship To Code"></div>
                        <div class="col-12"><textarea name="ship_to_address" class="form-control" placeholder="Ship To Address"></textarea></div>
                    </div>

                    <hr>
                    <h5>Bank & Parent</h5>
                    <div class="row g-2">
                        <div class="col-md-6"><input name="bank_code_1" class="form-control" placeholder="Bank Code 1"></div><div class="col-md-6"><input name="bank_account_1" class="form-control" placeholder="Bank Account 1"></div>
                        <div class="col-md-6"><input name="bank_code_2" class="form-control" placeholder="Bank Code 2"></div><div class="col-md-6"><input name="bank_account_2" class="form-control" placeholder="Bank Account 2"></div>
                        <div class="col-md-6"><input name="ar_parent" class="form-control" placeholder="A/R Parent"></div><div class="col-md-6"><input name="sales_parent" class="form-control" placeholder="Sales Parent"></div>
                    </div>
                    <div class="mt-3"><button class="btn btn-primary">Simpan Customer</button></div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card"><div class="card-body"><h4 class="card-title">Customer List</h4><div class="table-responsive"><table class="table table-sm table-bordered align-middle"><thead class="table-light"><tr><th>Code</th><th>Name</th><th>Currency</th><th>Terms</th><th>Limit</th><th>Status</th></tr></thead><tbody><?php foreach ($customers as $customer) : ?><tr><td><?= esc($customer['code']) ?></td><td><?= esc($customer['name']) ?></td><td><?= esc($customer['currency_code']) ?></td><td><?= esc($customer['term_code'] ?? '-') ?></td><td><?= esc($customer['credit_limit'] ?? '0') ?></td><td><?= esc($customer['status']) ?></td></tr><?php endforeach; ?><?php if ($customers === []) : ?><tr><td colspan="6" class="text-muted text-center">Belum ada customer.</td></tr><?php endif; ?></tbody></table></div></div></div>
    </div>
</div>
<?= $this->endSection() ?>
