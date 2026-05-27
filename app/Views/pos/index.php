<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>POS Master<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $nextStatus = static fn (string $status): string => $status === 'active' ? 'inactive' : 'active'; ?>
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18">POS Master</h4>
        <p class="text-muted mb-0"><?= esc($tenantContext['company_name']) ?> / Register kasir dan default operasional</p>
    </div>
    <?php if ($canManage) : ?><div class="dropdown"><button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">Tambah Data</button><div class="dropdown-menu"><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#openShift">Open Shift</button><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addRegister">Register POS</button><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addPayment">Payment Method</button></div></div><?php else : ?><span class="badge bg-info">Read only</span><?php endif; ?>
</div>
<?php if (session('message') !== null) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
<?php if (session('errors') !== null) : ?><div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div><?php endif; ?>

<div class="card"><div class="card-body">
    <h4 class="card-title mb-3">POS Registers</h4>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Code / Name</th><th>Hierarchy</th><th>Warehouse</th><th>Default Customer</th><th>Currency / Numbering</th><th>Device</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($registers as $register) : ?><tr>
            <td><strong><?= esc($register['code']) ?></strong><br><small><?= esc($register['name']) ?></small></td>
            <td><?= esc($register['branch_code'] . ' / ' . $register['department_code']) ?></td>
            <td><?= esc($register['warehouse_code']) ?></td>
            <td><?= esc($register['customer_code'] ?? '-') ?></td>
            <td><?= esc($register['currency_code'] . ' / ' . $register['transaction_code']) ?></td>
            <td><?= esc($register['device_label'] ?? '-') ?></td>
            <td><?= esc($register['status']) ?></td>
            <?php if ($canManage) : ?><td class="text-end text-nowrap">
                <button class="btn btn-outline-primary btn-sm js-edit-register" data-bs-toggle="modal" data-bs-target="#editRegister" data-id="<?= (int) $register['id'] ?>" data-code="<?= esc($register['code'], 'attr') ?>" data-name="<?= esc($register['name'], 'attr') ?>" data-branch="<?= (int) $register['branch_id'] ?>" data-department="<?= (int) $register['department_id'] ?>" data-warehouse="<?= (int) $register['warehouse_id'] ?>" data-customer="<?= (int) ($register['default_customer_id'] ?? 0) ?>" data-currency="<?= (int) $register['currency_id'] ?>" data-transaction="<?= (int) $register['transaction_code_id'] ?>" data-device="<?= esc($register['device_label'] ?? '', 'attr') ?>">Edit</button>
                <form class="d-inline" method="post" action="<?= site_url('pos/master/registers/' . $register['id'] . '/status') ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $nextStatus($register['status']) ?>"><button class="btn btn-outline-danger btn-sm"><?= $register['status'] === 'active' ? 'Hapus' : 'Aktifkan' ?></button></form>
            </td><?php endif; ?>
        </tr><?php endforeach; ?>
        <?php if ($registers === []) : ?><tr><td colspan="<?= $canManage ? '8' : '7' ?>" class="text-muted text-center py-4">Belum ada register POS.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div></div>

<div class="card"><div class="card-body">
    <h4 class="card-title mb-3">Payment Methods</h4>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Register</th><th>Code / Name</th><th>Type</th><th>Cash/Bank Account</th><th>Default</th><th>Urutan</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($paymentMethods as $payment) : ?><tr>
            <td><?= esc($payment['register_code']) ?></td>
            <td><strong><?= esc($payment['code']) ?></strong><br><small><?= esc($payment['name']) ?></small></td>
            <td><?= esc($payment['payment_type']) ?></td>
            <td><?= esc($payment['cash_bank_code'] . ' - ' . $payment['cash_bank_name']) ?></td>
            <td><?= (bool) $payment['is_default'] ? 'Ya' : '-' ?></td>
            <td><?= (int) $payment['sort_order'] ?></td>
            <td><?= esc($payment['status']) ?></td>
            <?php if ($canManage) : ?><td class="text-end text-nowrap">
                <button class="btn btn-outline-primary btn-sm js-edit-payment" data-bs-toggle="modal" data-bs-target="#editPayment" data-id="<?= (int) $payment['id'] ?>" data-register="<?= (int) $payment['register_id'] ?>" data-bank="<?= (int) $payment['cash_bank_account_id'] ?>" data-name="<?= esc($payment['name'], 'attr') ?>" data-type="<?= esc($payment['payment_type'], 'attr') ?>" data-default="<?= (int) $payment['is_default'] ?>" data-sort="<?= (int) $payment['sort_order'] ?>">Edit</button>
                <form class="d-inline" method="post" action="<?= site_url('pos/master/payment-methods/' . $payment['id'] . '/status') ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $nextStatus($payment['status']) ?>"><button class="btn btn-outline-danger btn-sm"><?= $payment['status'] === 'active' ? 'Hapus' : 'Aktifkan' ?></button></form>
            </td><?php endif; ?>
        </tr><?php endforeach; ?>
        <?php if ($paymentMethods === []) : ?><tr><td colspan="<?= $canManage ? '8' : '7' ?>" class="text-muted text-center py-4">Belum ada payment method POS.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div></div>

<div class="card"><div class="card-body">
    <h4 class="card-title mb-3">Cashier Shifts</h4>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Register</th><th>Cashier</th><th>Opened</th><th>Opening Cash</th><th>Closed</th><th>Closing Cash</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($shifts as $shift) : ?><tr>
            <td><?= esc($shift['register_code']) ?></td>
            <td><?= esc($shift['cashier_email'] ?? $shift['cashier_username']) ?></td>
            <td><?= esc($shift['opened_at']) ?></td>
            <td><?= esc($shift['opening_cash']) ?></td>
            <td><?= esc($shift['closed_at'] ?? '-') ?></td>
            <td><?= esc($shift['closing_cash'] ?? '-') ?></td>
            <td><?= esc($shift['status']) ?></td>
            <?php if ($canManage) : ?><td class="text-end text-nowrap">
                <?php if ($shift['status'] === 'open' && (int) $shift['cashier_user_id'] === (int) auth()->id()) : ?>
                    <button class="btn btn-outline-primary btn-sm js-close-shift" data-bs-toggle="modal" data-bs-target="#closeShift" data-id="<?= (int) $shift['id'] ?>" data-register="<?= esc($shift['register_code'], 'attr') ?>" data-opening="<?= esc($shift['opening_cash'], 'attr') ?>">Close</button>
                <?php else : ?>-<?php endif; ?>
            </td><?php endif; ?>
        </tr><?php endforeach; ?>
        <?php if ($shifts === []) : ?><tr><td colspan="<?= $canManage ? '8' : '7' ?>" class="text-muted text-center py-4">Belum ada shift POS.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div></div>

<?php if ($canManage) : ?>
<?php $fields = static function (bool $edit) use ($branches, $departments, $warehouses, $customers, $currencies, $transactionCodes): void { ?>
    <div class="col-md-3"><label class="form-label">Code</label><input name="<?= $edit ? '' : 'code' ?>" data-field="code" class="form-control" <?= $edit ? 'readonly' : 'required' ?>></div>
    <div class="col-md-5"><label class="form-label">Name</label><input name="name" data-field="name" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Device Label</label><input name="device_label" data-field="device" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">Site</label><select name="branch_id" data-field="branch" class="form-select" required><?php foreach ($branches as $branch) : ?><option value="<?= (int) $branch['id'] ?>"><?= esc($branch['code'] . ' - ' . $branch['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Department</label><select name="department_id" data-field="department" class="form-select" required><?php foreach ($departments as $department) : ?><option data-branch="<?= (int) $department['branch_id'] ?>" value="<?= (int) $department['id'] ?>"><?= esc($department['code']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Warehouse</label><select name="warehouse_id" data-field="warehouse" class="form-select" required><?php foreach ($warehouses as $warehouse) : ?><option data-branch="<?= (int) $warehouse['branch_id'] ?>" data-department="<?= (int) $warehouse['department_id'] ?>" value="<?= (int) $warehouse['id'] ?>"><?= esc($warehouse['code']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Default Customer</label><select name="default_customer_id" data-field="customer" class="form-select"><option value="">-</option><?php foreach ($customers as $customer) : ?><option value="<?= (int) $customer['id'] ?>"><?= esc($customer['code']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Currency</label><select name="currency_id" data-field="currency" class="form-select" required><?php foreach ($currencies as $currency) : ?><option value="<?= (int) $currency['id'] ?>"><?= esc($currency['code']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Transaction Code</label><select name="transaction_code_id" data-field="transaction" class="form-select" required><?php foreach ($transactionCodes as $transaction) : ?><option data-branch="<?= (int) ($transaction['branch_id'] ?? 0) ?>" value="<?= (int) $transaction['id'] ?>"><?= esc($transaction['code'] . ' / ' . $transaction['prefix']) ?></option><?php endforeach; ?></select></div>
<?php }; ?>
<div class="modal fade" id="addRegister"><div class="modal-dialog modal-lg"><form class="modal-content js-register-form" method="post" action="<?= site_url('pos/master/registers') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Register POS</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><?php $fields(false); ?></div><div class="modal-footer"><button class="btn btn-primary" <?= $warehouses === [] || $transactionCodes === [] ? 'disabled' : '' ?>>Simpan</button></div></form></div></div>
<div class="modal fade" id="editRegister"><div class="modal-dialog modal-lg"><form class="modal-content js-register-form" method="post" data-action="<?= site_url('pos/master/registers') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit Register POS</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><?php $fields(true); ?></div><div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>
<?php $paymentFields = static function (bool $edit) use ($registers, $cashBanks): void { ?>
    <div class="col-md-3"><label class="form-label">Code</label><input name="<?= $edit ? '' : 'code' ?>" data-field="code" class="form-control" <?= $edit ? 'readonly' : 'required' ?>></div>
    <div class="col-md-5"><label class="form-label">Name</label><input name="name" data-field="name" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Type</label><select name="payment_type" data-field="type" class="form-select" required><option value="cash">Cash</option><option value="card">Card</option><option value="transfer">Transfer</option><option value="e-wallet">E-Wallet</option><option value="other">Other</option></select></div>
    <div class="col-md-4"><label class="form-label">Register</label><select name="register_id" data-field="register" class="form-select" required><?php foreach ($registers as $register) : ?><option data-branch="<?= (int) $register['branch_id'] ?>" value="<?= (int) $register['id'] ?>"><?= esc($register['code'] . ' - ' . $register['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-5"><label class="form-label">Cash/Bank Account</label><select name="cash_bank_account_id" data-field="bank" class="form-select" required><?php foreach ($cashBanks as $bank) : ?><option data-branch="<?= (int) ($bank['branch_id'] ?? 0) ?>" value="<?= (int) $bank['id'] ?>"><?= esc($bank['code'] . ' - ' . $bank['name'] . ' (' . $bank['currency_code'] . ')') ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label">Sort Order</label><input type="number" min="0" name="sort_order" data-field="sort" value="10" class="form-control" required></div>
    <div class="col-md-4 form-check mt-4 ps-5"><input type="checkbox" name="is_default" data-field="default" value="1" class="form-check-input"><label class="form-check-label">Default untuk register</label></div>
<?php }; ?>
<div class="modal fade" id="addPayment"><div class="modal-dialog modal-lg"><form class="modal-content js-payment-form" method="post" action="<?= site_url('pos/master/payment-methods') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Payment Method</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><?php $paymentFields(false); ?></div><div class="modal-footer"><button class="btn btn-primary" <?= $registers === [] || $cashBanks === [] ? 'disabled' : '' ?>>Simpan</button></div></form></div></div>
<div class="modal fade" id="editPayment"><div class="modal-dialog modal-lg"><form class="modal-content js-payment-form" method="post" data-action="<?= site_url('pos/master/payment-methods') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit Payment Method</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><?php $paymentFields(true); ?></div><div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>
<div class="modal fade" id="openShift"><div class="modal-dialog"><form class="modal-content" method="post" action="<?= site_url('pos/master/shifts/open') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Open Shift</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><div class="col-12"><label class="form-label">Register</label><select name="register_id" class="form-select" required><?php foreach ($registers as $register) : ?><option value="<?= (int) $register['id'] ?>"><?= esc($register['code'] . ' - ' . $register['name']) ?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Opening Cash</label><input name="opening_cash" value="0" class="form-control" required></div></div><div class="modal-footer"><button class="btn btn-primary" <?= $registers === [] ? 'disabled' : '' ?>>Open Shift</button></div></form></div></div>
<div class="modal fade" id="closeShift"><div class="modal-dialog"><form class="modal-content js-close-shift-form" method="post" data-action="<?= site_url('pos/master/shifts') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Close Shift</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><div class="col-12"><div class="alert alert-info mb-2">Register <strong data-field="register"></strong>, opening cash <strong data-field="opening"></strong>.</div></div><div class="col-12"><label class="form-label">Closing Cash</label><input name="closing_cash" value="0" class="form-control" required></div></div><div class="modal-footer"><button class="btn btn-primary">Close Shift</button></div></form></div></div>
<?php endif; ?>
<?= $this->endSection() ?>

<?php if ($canManage) : ?>
<?= $this->section('scripts') ?>
<script>
function filterRegisterHierarchy(form) {
    var branch = form.querySelector('[data-field="branch"]');
    var department = form.querySelector('[data-field="department"]');
    var warehouse = form.querySelector('[data-field="warehouse"]');
    var transaction = form.querySelector('[data-field="transaction"]');
    Array.prototype.forEach.call(department.options, function (option) { option.hidden = option.dataset.branch !== branch.value; option.disabled = option.hidden; });
    if (!department.selectedOptions[0] || department.selectedOptions[0].disabled) { department.value = Array.prototype.find.call(department.options, function (option) { return !option.disabled; })?.value || ''; }
    Array.prototype.forEach.call(warehouse.options, function (option) { option.hidden = option.dataset.branch !== branch.value || option.dataset.department !== department.value; option.disabled = option.hidden; });
    if (!warehouse.selectedOptions[0] || warehouse.selectedOptions[0].disabled) { warehouse.value = Array.prototype.find.call(warehouse.options, function (option) { return !option.disabled; })?.value || ''; }
    Array.prototype.forEach.call(transaction.options, function (option) { option.hidden = option.dataset.branch !== '0' && option.dataset.branch !== branch.value; option.disabled = option.hidden; });
    if (!transaction.selectedOptions[0] || transaction.selectedOptions[0].disabled) { transaction.value = Array.prototype.find.call(transaction.options, function (option) { return !option.disabled; })?.value || ''; }
}
document.querySelectorAll('.js-register-form').forEach(function (form) {
    form.querySelector('[data-field="branch"]').addEventListener('change', function () { filterRegisterHierarchy(form); });
    form.querySelector('[data-field="department"]').addEventListener('change', function () { filterRegisterHierarchy(form); });
    filterRegisterHierarchy(form);
});
document.querySelectorAll('.js-edit-register').forEach(function (button) { button.addEventListener('click', function () {
    var form = document.querySelector('#editRegister form');
    form.action = form.dataset.action + '/' + button.dataset.id;
    ['code', 'name', 'branch', 'department', 'warehouse', 'customer', 'currency', 'transaction', 'device'].forEach(function (key) {
        form.querySelector('[data-field="' + key + '"]').value = button.dataset[key] === '0' ? '' : button.dataset[key];
    });
    filterRegisterHierarchy(form);
    form.querySelector('[data-field="department"]').value = button.dataset.department;
    form.querySelector('[data-field="warehouse"]').value = button.dataset.warehouse;
    form.querySelector('[data-field="transaction"]').value = button.dataset.transaction;
}); });
function filterPaymentBanks(form) {
    var register = form.querySelector('[data-field="register"]');
    var bank = form.querySelector('[data-field="bank"]');
    var branch = register.selectedOptions[0]?.dataset.branch || '';
    Array.prototype.forEach.call(bank.options, function (option) { option.hidden = option.dataset.branch !== '0' && option.dataset.branch !== branch; option.disabled = option.hidden; });
    if (!bank.selectedOptions[0] || bank.selectedOptions[0].disabled) { bank.value = Array.prototype.find.call(bank.options, function (option) { return !option.disabled; })?.value || ''; }
}
document.querySelectorAll('.js-payment-form').forEach(function (form) {
    form.querySelector('[data-field="register"]').addEventListener('change', function () { filterPaymentBanks(form); });
    filterPaymentBanks(form);
});
document.querySelectorAll('.js-edit-payment').forEach(function (button) { button.addEventListener('click', function () {
    var form = document.querySelector('#editPayment form');
    form.action = form.dataset.action + '/' + button.dataset.id;
    form.querySelector('[data-field="register"]').value = button.dataset.register;
    filterPaymentBanks(form);
    form.querySelector('[data-field="bank"]').value = button.dataset.bank;
    ['name', 'type', 'sort'].forEach(function (key) { form.querySelector('[data-field="' + key + '"]').value = button.dataset[key]; });
    form.querySelector('[data-field="default"]').checked = button.dataset.default === '1';
}); });
document.querySelectorAll('.js-close-shift').forEach(function (button) { button.addEventListener('click', function () {
    var form = document.querySelector('.js-close-shift-form');
    form.action = form.dataset.action + '/' + button.dataset.id + '/close';
    form.querySelector('[data-field="register"]').textContent = button.dataset.register;
    form.querySelector('[data-field="opening"]').textContent = button.dataset.opening;
}); });
</script>
<?= $this->endSection() ?>
<?php endif; ?>
