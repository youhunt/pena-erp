<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('title') ?>Inventory<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-sm-0 font-size-18">Inventory Master & Warehouse</h4>
        <p class="text-muted mb-0"><?= esc($tenantContext['company_name']) ?> / <?= esc($tenantContext['branch_name'] ?? 'Semua branch') ?></p>
    </div>
    <?php if (! $canManage) : ?><span class="badge bg-info">Read only</span><?php endif; ?>
</div>
<?php if (session('message') !== null) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
<?php if (session('errors') !== null) : ?>
    <div class="alert alert-danger"><?php foreach ((array) session('errors') as $error) : ?><div><?= esc($error) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<?php if ($canManage) : ?>
<div class="mb-3 text-end">
    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#inventory-add-forms">Tambah Data</button>
</div>
<div class="collapse" id="inventory-add-forms">
<div class="row">
    <div class="col-xl-3">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Tambah UOM</h4>
            <form method="post" action="<?= site_url('inventory/uoms') ?>">
                <?= csrf_field() ?>
                <div class="mb-2"><label class="form-label">Kode</label><input class="form-control" name="code" placeholder="PCS" required></div>
                <div class="mb-2"><label class="form-label">Nama</label><input class="form-control" name="name" placeholder="Pieces" required></div>
                <div class="mb-3"><label class="form-label">Presisi Qty</label><input class="form-control" type="number" min="0" max="6" name="precision" value="0" required></div>
                <button class="btn btn-primary" type="submit">Simpan UOM</button>
            </form>
        </div></div>
    </div>
    <div class="col-xl-3">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Tambah Kategori</h4>
            <form method="post" action="<?= site_url('inventory/categories') ?>">
                <?= csrf_field() ?>
                <div class="mb-2"><label class="form-label">Kode</label><input class="form-control" name="code" placeholder="ATK" required></div>
                <div class="mb-3"><label class="form-label">Nama</label><input class="form-control" name="name" placeholder="Alat Tulis Kantor" required></div>
                <button class="btn btn-primary" type="submit">Simpan Kategori</button>
            </form>
        </div></div>
    </div>
    <div class="col-xl-6">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Tambah Gudang</h4>
            <form method="post" action="<?= site_url('inventory/warehouses') ?>" class="row g-2">
                <?= csrf_field() ?>
                <div class="col-md-6"><label class="form-label">Site</label><select class="form-select" name="branch_id" id="warehouse-site" required><?php foreach ($branches as $branch) : ?><option value="<?= esc($branch['id']) ?>"><?= esc($branch['code'] . ' - ' . $branch['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label">Department</label><select class="form-select" name="department_id" id="warehouse-department" required><?php foreach ($departments as $department) : ?><option data-site="<?= (int) $department['branch_id'] ?>" value="<?= esc($department['id']) ?>"><?= esc($department['branch_code'] . ' / ' . $department['code'] . ' - ' . $department['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label">Kode</label><input class="form-control" name="code" placeholder="MAIN" required></div>
                <div class="col-md-8"><label class="form-label">Nama</label><input class="form-control" name="name" placeholder="Gudang Utama" required></div>
                <div class="col-md-4"><label class="form-label">Kode Pos</label><input class="form-control" name="postal_code"></div>
                <div class="col-12"><label class="form-label">Alamat</label><input class="form-control" name="address"></div>
                <div class="col-12"><button class="btn btn-primary mt-2" type="submit" <?= $departments === [] ? 'disabled' : '' ?>>Simpan Gudang</button><?php if ($departments === []) : ?><div class="form-text">Tambahkan Department per Site dahulu di Setup Master.</div><?php endif; ?></div>
            </form>
        </div></div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-3">Tambah Produk</h4>
        <form method="post" action="<?= site_url('inventory/products') ?>" class="row g-3 align-items-end">
            <?= csrf_field() ?>
            <div class="col-xl-2"><label class="form-label">SKU</label><input class="form-control" name="sku" required></div>
            <div class="col-xl-3"><label class="form-label">Nama Produk</label><input class="form-control" name="name" required></div>
            <div class="col-xl-2"><label class="form-label">Kategori</label><select class="form-select" name="category_id"><option value="">-</option><?php foreach ($categories as $category) : ?><option value="<?= esc($category['id']) ?>"><?= esc($category['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-xl-1"><label class="form-label">UOM</label><select class="form-select" name="base_uom_id" required><?php foreach ($uoms as $uom) : ?><option value="<?= esc($uom['id']) ?>"><?= esc($uom['code']) ?></option><?php endforeach; ?></select></div>
            <div class="col-xl-2"><label class="form-label">Tipe</label><select class="form-select" name="product_type"><option value="stock">Stock</option><option value="non_stock">Non Stock</option><option value="service">Service</option></select></div>
            <div class="col-xl-2"><label class="form-label">Standard Cost</label><input class="form-control" type="number" step="0.0001" min="0" name="standard_cost" value="0" required></div>
            <div class="col-xl-3"><label class="form-label">Barcode</label><input class="form-control" name="barcode"></div>
            <div class="col-xl-3"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="track_lot" value="1" id="track-lot"><label class="form-check-label" for="track-lot">Trace lot/expired date</label></div></div>
            <div class="col-xl-3"><button class="btn btn-primary" type="submit" <?= $uoms === [] ? 'disabled' : '' ?>>Simpan Produk</button><?php if ($uoms === []) : ?><div class="form-text">Tambahkan UOM dahulu.</div><?php endif; ?></div>
        </form>
    </div>
</div>
<div class="row">
    <div class="col-xl-7">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Item Operational Profile</h4>
            <form method="post" action="<?= site_url('inventory/product-profiles') ?>" class="row g-2" id="product-profile-form">
                <?= csrf_field() ?>
                <div class="col-md-4"><label class="form-label">Item</label><select name="product_id" class="form-select" required><?php foreach ($productOptions as $product) : ?><option value="<?= esc($product['id']) ?>"><?= esc($product['sku']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Alternate Code</label><input name="alternate_code" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Alternate Name</label><input name="alternate_name" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Default Warehouse</label><select name="default_warehouse_id" class="form-select"><option value="">-</option><?php foreach ($warehouseOptions as $warehouse) : ?><option value="<?= esc($warehouse['id']) ?>"><?= esc($warehouse['branch_code'] . ' / ' . $warehouse['code']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><label class="form-label">Shelf Life</label><input name="shelf_life_days" type="number" min="0" class="form-control" placeholder="days"></div>
                <div class="col-md-2"><label class="form-label">Length cm</label><input name="length_cm" type="number" step="0.001" min="0" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Width cm</label><input name="width_cm" type="number" step="0.001" min="0" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Height cm</label><input name="height_cm" type="number" step="0.001" min="0" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Weight kg</label><input name="weight_kg" type="number" step="0.0001" min="0" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Package UOM</label><select name="package_uom_id" class="form-select"><option value="">-</option><?php foreach ($uomOptions as $uom) : ?><option value="<?= esc($uom['id']) ?>"><?= esc($uom['code']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Qty / Package</label><input name="units_per_package" type="number" step="0.000001" min="0.000001" class="form-control"></div>
                <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100" <?= $productOptions === [] ? 'disabled' : '' ?>>Simpan Profile</button></div>
            </form>
        </div></div>
    </div>
    <div class="col-xl-5">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Baseline Item Price</h4>
            <form method="post" action="<?= site_url('inventory/product-prices') ?>" class="row g-2">
                <?= csrf_field() ?>
                <div class="col-md-6"><label class="form-label">Item</label><select name="product_id" class="form-select" required><?php foreach ($productOptions as $product) : ?><option value="<?= esc($product['id']) ?>"><?= esc($product['sku']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label">Type</label><select name="price_type" class="form-select"><option value="sales">Sales</option><option value="purchase">Purchase</option></select></div>
                <div class="col-md-4"><label class="form-label">Currency</label><select name="currency_id" class="form-select" required><?php foreach ($currencies as $currency) : ?><option value="<?= esc($currency['id']) ?>"><?= esc($currency['code']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">UOM</label><select name="uom_id" class="form-select" required><?php foreach ($uomOptions as $uom) : ?><option value="<?= esc($uom['id']) ?>"><?= esc($uom['code']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Price</label><input name="unit_price" type="number" step="0.0001" min="0" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Effective From</label><input name="effective_from" type="date" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Effective To</label><input name="effective_to" type="date" class="form-control"></div>
                <div class="col-12"><button class="btn btn-primary" <?= $productOptions === [] || $currencies === [] || $uomOptions === [] ? 'disabled' : '' ?>>Tambah Harga</button></div>
            </form>
        </div></div>
    </div>
</div>
<div class="row">
    <div class="col-xl-3">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Location</h4>
            <form method="post" action="<?= site_url('inventory/locations') ?>">
                <?= csrf_field() ?>
                <div class="mb-2"><label class="form-label">Warehouse</label><select name="warehouse_id" class="form-select" required><?php foreach ($warehouseOptions as $warehouse) : ?><option value="<?= esc($warehouse['id']) ?>"><?= esc($warehouse['branch_code'] . ' / ' . ($warehouse['department_code'] ?? '-') . ' / ' . $warehouse['code']) ?></option><?php endforeach; ?></select></div>
                <div class="mb-2"><label class="form-label">Code</label><input name="code" class="form-control" placeholder="R01-A01" required></div>
                <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                <button class="btn btn-primary" <?= $warehouseOptions === [] ? 'disabled' : '' ?>>Simpan Location</button>
            </form>
        </div></div>
    </div>
    <div class="col-xl-3">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Item UoM Conversion</h4>
            <form method="post" action="<?= site_url('inventory/uom-conversions') ?>">
                <?= csrf_field() ?>
                <div class="mb-2"><label class="form-label">Item</label><select name="product_id" class="form-select" required><?php foreach ($products as $product) : ?><option value="<?= esc($product['id']) ?>"><?= esc($product['sku']) ?></option><?php endforeach; ?></select></div>
                <div class="row g-2 mb-2"><div class="col-6"><label class="form-label">From</label><select name="from_uom_id" class="form-select" required><?php foreach ($uoms as $uom) : ?><option value="<?= esc($uom['id']) ?>"><?= esc($uom['code']) ?></option><?php endforeach; ?></select></div><div class="col-6"><label class="form-label">To</label><select name="to_uom_id" class="form-select" required><?php foreach ($uoms as $uom) : ?><option value="<?= esc($uom['id']) ?>"><?= esc($uom['code']) ?></option><?php endforeach; ?></select></div></div>
                <div class="mb-3"><label class="form-label">Factor</label><input type="number" step="0.000001" min="0.000001" name="factor" class="form-control" required></div>
                <button class="btn btn-primary" <?= $products === [] || count($uoms) < 2 ? 'disabled' : '' ?>>Simpan Conversion</button>
            </form>
        </div></div>
    </div>
    <div class="col-xl-3">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Item VAT</h4>
            <form method="post" action="<?= site_url('inventory/item-taxes') ?>">
                <?= csrf_field() ?>
                <div class="mb-2"><label class="form-label">Item</label><select name="product_id" class="form-select" required><?php foreach ($products as $product) : ?><option value="<?= esc($product['id']) ?>"><?= esc($product['sku']) ?></option><?php endforeach; ?></select></div>
                <div class="mb-2"><label class="form-label">VAT</label><select name="tax_code_id" class="form-select" required><?php foreach ($taxCodes as $tax) : ?><option value="<?= esc($tax['id']) ?>"><?= esc($tax['code']) ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Usage</label><select name="usage_type" class="form-select"><option value="sales">Sales</option><option value="purchase">Purchase</option><option value="both">Both</option></select></div>
                <button class="btn btn-primary" <?= $products === [] || $taxCodes === [] ? 'disabled' : '' ?>>Simpan Item VAT</button>
            </form>
        </div></div>
    </div>
    <div class="col-xl-3">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Batch Master</h4>
            <form method="post" action="<?= site_url('inventory/batches') ?>">
                <?= csrf_field() ?>
                <div class="mb-2"><label class="form-label">Item</label><select name="product_id" class="form-select" required><?php foreach ($products as $product) : ?><option value="<?= esc($product['id']) ?>"><?= esc($product['sku']) ?></option><?php endforeach; ?></select></div>
                <div class="mb-2"><label class="form-label">Batch No.</label><input name="lot_no" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Expiry Date</label><input type="date" name="expiry_date" class="form-control"></div>
                <button class="btn btn-primary" <?= $products === [] ? 'disabled' : '' ?>>Simpan Batch</button>
            </form>
        </div></div>
    </div>
</div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-xl-6">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">UOM</h4>
            <table class="table table-hover table-sm align-middle mb-0"><thead><tr><th>Code</th><th>Name</th><th>Precision</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead><tbody>
            <?php foreach ($uoms as $uom) : ?><tr><td><strong><?= esc($uom['code']) ?></strong></td><td><?= esc($uom['name']) ?></td><td><?= esc($uom['precision']) ?></td><td><?= esc($uom['status']) ?></td><?php if ($canManage) : ?><td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-primary js-edit-uom" data-bs-toggle="modal" data-bs-target="#editUom" data-id="<?= (int) $uom['id'] ?>" data-code="<?= esc($uom['code'], 'attr') ?>" data-name="<?= esc($uom['name'], 'attr') ?>" data-precision="<?= (int) $uom['precision'] ?>">Edit</button> <form class="d-inline" method="post" action="<?= site_url('inventory/status/uom/' . $uom['id']) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $uom['status'] === 'active' ? 'inactive' : 'active' ?>"><button class="btn btn-sm btn-outline-danger"><?= $uom['status'] === 'active' ? 'Hapus' : 'Aktifkan' ?></button></form></td><?php endif; ?></tr><?php endforeach; ?>
            <?php if ($uoms === []) : ?><tr><td colspan="5" class="text-muted">Belum ada UOM.</td></tr><?php endif; ?></tbody></table>
        </div></div>
    </div>
    <div class="col-xl-6">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Kategori Produk</h4>
            <table class="table table-hover table-sm align-middle mb-0"><thead><tr><th>Code</th><th>Name</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead><tbody>
            <?php foreach ($categories as $category) : ?><tr><td><strong><?= esc($category['code']) ?></strong></td><td><?= esc($category['name']) ?></td><td><?= esc($category['status']) ?></td><?php if ($canManage) : ?><td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-primary js-edit-category" data-bs-toggle="modal" data-bs-target="#editCategory" data-id="<?= (int) $category['id'] ?>" data-code="<?= esc($category['code'], 'attr') ?>" data-name="<?= esc($category['name'], 'attr') ?>">Edit</button> <form class="d-inline" method="post" action="<?= site_url('inventory/status/category/' . $category['id']) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $category['status'] === 'active' ? 'inactive' : 'active' ?>"><button class="btn btn-sm btn-outline-danger"><?= $category['status'] === 'active' ? 'Hapus' : 'Aktifkan' ?></button></form></td><?php endif; ?></tr><?php endforeach; ?>
            <?php if ($categories === []) : ?><tr><td colspan="4" class="text-muted">Belum ada kategori.</td></tr><?php endif; ?></tbody></table>
        </div></div>
    </div>
</div>
<div class="row">
    <div class="col-xl-8">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Produk</h4>
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th>SKU / Nama</th><th>Kategori</th><th>UOM</th><th>Tipe</th><th>Cost</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead>
                <tbody>
                <?php foreach ($products as $product) : ?>
                    <tr>
                        <td><strong><?= esc($product['sku']) ?></strong><br><small><?= esc($product['name']) ?></small></td>
                        <td><?= esc($product['category_name'] ?? '-') ?></td>
                        <td><?= esc($product['uom_code']) ?></td>
                        <td><?= esc($product['product_type']) ?><?= $product['track_lot'] ? ' / lot' : '' ?></td>
                        <td class="text-end"><?= number_format((float) $product['standard_cost'], 2, ',', '.') ?></td>
                        <td><?= esc($product['status']) ?></td>
                        <?php if ($canManage) : ?><td class="text-end text-nowrap">
                            <button class="btn btn-outline-primary btn-sm js-edit-product" data-bs-toggle="modal" data-bs-target="#editProduct" data-id="<?= (int) $product['id'] ?>" data-sku="<?= esc($product['sku'], 'attr') ?>" data-name="<?= esc($product['name'], 'attr') ?>" data-barcode="<?= esc($product['barcode'] ?? '', 'attr') ?>" data-category="<?= (int) ($product['category_id'] ?? 0) ?>" data-uom="<?= (int) $product['base_uom_id'] ?>" data-type="<?= esc($product['product_type'], 'attr') ?>" data-cost="<?= esc($product['standard_cost'], 'attr') ?>" data-lot="<?= $product['track_lot'] ? '1' : '0' ?>">Edit</button>
                            <form method="post" action="<?= site_url('inventory/products/' . $product['id'] . '/status') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="<?= $product['status'] === 'active' ? 'inactive' : 'active' ?>">
                                <button class="btn btn-outline-danger btn-sm" type="submit"><?= $product['status'] === 'active' ? 'Hapus' : 'Aktifkan' ?></button>
                            </form>
                        </td><?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if ($products === []) : ?><tr><td colspan="<?= $canManage ? '7' : '6' ?>" class="text-muted text-center">Belum ada produk pada company ini.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </div></div>
    </div>
    <div class="col-xl-4">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Warehouse Hierarchy</h4>
            <table class="table align-middle mb-0">
                <thead><tr><th>Site / Department / Warehouse</th><th>Status</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead>
                <tbody>
                <?php foreach ($warehouses as $warehouse) : ?>
                    <tr>
                        <td><strong><?= esc($warehouse['branch_code'] . ' / ' . ($warehouse['department_code'] ?? '-') . ' / ' . $warehouse['code']) ?></strong><br><small><?= esc($warehouse['name']) ?></small></td>
                        <td><?= $warehouse['is_active'] ? 'Active' : 'Inactive' ?></td>
                        <?php if ($canManage) : ?><td class="text-end text-nowrap">
                            <button class="btn btn-outline-primary btn-sm js-edit-warehouse" data-bs-toggle="modal" data-bs-target="#editWarehouse" data-id="<?= (int) $warehouse['id'] ?>" data-code="<?= esc($warehouse['code'], 'attr') ?>" data-name="<?= esc($warehouse['name'], 'attr') ?>" data-address="<?= esc($warehouse['address'] ?? '', 'attr') ?>" data-postal="<?= esc($warehouse['postal_code'] ?? '', 'attr') ?>">Edit</button>
                            <form method="post" action="<?= site_url('inventory/warehouses/' . $warehouse['id'] . '/status') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="<?= $warehouse['is_active'] ? 'inactive' : 'active' ?>">
                                <button class="btn btn-outline-danger btn-sm" type="submit"><?= $warehouse['is_active'] ? 'Hapus' : 'Aktifkan' ?></button>
                            </form>
                        </td><?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if ($warehouses === []) : ?><tr><td colspan="<?= $canManage ? '3' : '2' ?>" class="text-muted text-center">Belum ada gudang.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div></div>
    </div>
</div>
<div class="row">
    <div class="col-xl-5"><div class="card"><div class="card-body">
        <h4 class="card-title mb-3">Stock Balances</h4>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead><tr><th>Item</th><th>Warehouse</th><th>On Hand</th><th>Reserved</th><th>Avg Cost</th></tr></thead>
            <tbody>
            <?php foreach ($stockBalances as $balance) : ?><tr><td><strong><?= esc($balance['sku']) ?></strong><br><small><?= esc($balance['product_name']) ?></small></td><td><?= esc($balance['branch_code'] . ' / ' . $balance['warehouse_code']) ?><br><small><?= esc($balance['location_code'] ?? 'Default') ?></small></td><td><?= esc($balance['qty_on_hand'] . ' ' . $balance['uom_code']) ?></td><td><?= esc($balance['qty_reserved']) ?></td><td><?= esc($balance['avg_cost']) ?></td></tr><?php endforeach; ?>
            <?php if ($stockBalances === []) : ?><tr><td colspan="5" class="text-muted">Belum ada saldo stok.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </div></div></div>
    <div class="col-xl-7"><div class="card"><div class="card-body">
        <h4 class="card-title mb-3">Stock Movements</h4>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead><tr><th>Posted</th><th>Item</th><th>Warehouse</th><th>Movement</th><th>Reference</th><th>Qty</th></tr></thead>
            <tbody>
            <?php foreach ($stockMovements as $movement) : ?><tr><td><?= esc($movement['posted_at']) ?></td><td><strong><?= esc($movement['sku']) ?></strong><br><small><?= esc($movement['product_name']) ?></small></td><td><?= esc($movement['branch_code'] . ' / ' . $movement['warehouse_code']) ?></td><td><?= esc($movement['movement_type']) ?></td><td><?= esc($movement['reference_type'] . ' / ' . ($movement['reference_no'] ?? $movement['reference_id'])) ?></td><td><?= esc($movement['qty'] . ' ' . $movement['uom_code']) ?></td></tr><?php endforeach; ?>
            <?php if ($stockMovements === []) : ?><tr><td colspan="6" class="text-muted">Belum ada stock movement.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </div></div></div>
</div>
<div class="row">
    <div class="col-xl-3"><div class="card"><div class="card-body">
        <h4 class="card-title mb-3">Locations</h4>
        <table class="table table-sm mb-0"><tbody><?php foreach ($locations as $location) : ?><tr><td><?= esc($location['branch_code'] . '/' . ($location['department_code'] ?? '-') . '/' . $location['warehouse_code']) ?><br><small><?= esc($location['code'] . ' - ' . $location['name']) ?></small></td><?php if ($canManage) : ?><td class="text-nowrap"><button class="btn btn-outline-primary btn-sm js-edit-location" data-bs-toggle="modal" data-bs-target="#editLocation" data-id="<?= (int) $location['id'] ?>" data-code="<?= esc($location['code'], 'attr') ?>" data-name="<?= esc($location['name'], 'attr') ?>">Edit</button> <form class="d-inline" method="post" action="<?= site_url('inventory/status/location/' . $location['id']) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $location['status'] === 'active' ? 'inactive' : 'active' ?>"><button class="btn btn-outline-danger btn-sm"><?= $location['status'] === 'active' ? 'Hapus' : 'Aktifkan' ?></button></form></td><?php endif; ?></tr><?php endforeach; ?><?php if ($locations === []) : ?><tr><td class="text-muted">Belum ada location.</td></tr><?php endif; ?></tbody></table>
    </div></div></div>
    <div class="col-xl-3"><div class="card"><div class="card-body">
        <h4 class="card-title mb-3">Conversions</h4>
        <table class="table table-sm mb-0"><tbody><?php foreach ($conversions as $conversion) : ?><tr><td><?= esc($conversion['sku']) ?><br><small>1 <?= esc($conversion['from_uom']) ?> = <?= esc($conversion['factor'] . ' ' . $conversion['to_uom']) ?></small></td><?php if ($canManage) : ?><td><form method="post" action="<?= site_url('inventory/status/conversion/' . $conversion['id']) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $conversion['status'] === 'active' ? 'inactive' : 'active' ?>"><button class="btn btn-outline-danger btn-sm"><?= $conversion['status'] === 'active' ? 'Hapus' : 'Aktifkan' ?></button></form></td><?php endif; ?></tr><?php endforeach; ?><?php if ($conversions === []) : ?><tr><td class="text-muted">Belum ada conversion.</td></tr><?php endif; ?></tbody></table>
    </div></div></div>
    <div class="col-xl-3"><div class="card"><div class="card-body">
        <h4 class="card-title mb-3">Item VAT</h4>
        <table class="table table-sm mb-0"><tbody><?php foreach ($itemTaxes as $tax) : ?><tr><td><?= esc($tax['sku']) ?><br><small><?= esc($tax['tax_code'] . ' / ' . $tax['usage_type']) ?></small></td><?php if ($canManage) : ?><td><form method="post" action="<?= site_url('inventory/status/item-tax/' . $tax['id']) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $tax['status'] === 'active' ? 'inactive' : 'active' ?>"><button class="btn btn-outline-danger btn-sm"><?= $tax['status'] === 'active' ? 'Hapus' : 'Aktifkan' ?></button></form></td><?php endif; ?></tr><?php endforeach; ?><?php if ($itemTaxes === []) : ?><tr><td class="text-muted">Belum ada item VAT.</td></tr><?php endif; ?></tbody></table>
    </div></div></div>
    <div class="col-xl-3"><div class="card"><div class="card-body">
        <h4 class="card-title mb-3">Batches</h4>
        <table class="table table-sm mb-0"><tbody><?php foreach ($batches as $batch) : ?><tr><td><?= esc($batch['sku']) ?><br><small><?= esc($batch['lot_no']) ?><?= $batch['expiry_date'] ? ' / ' . esc($batch['expiry_date']) : '' ?></small></td><?php if ($canManage) : ?><td class="text-nowrap"><button class="btn btn-outline-primary btn-sm js-edit-batch" data-bs-toggle="modal" data-bs-target="#editBatch" data-id="<?= (int) $batch['id'] ?>" data-lot="<?= esc($batch['lot_no'], 'attr') ?>" data-expiry="<?= esc($batch['expiry_date'] ?? '', 'attr') ?>">Edit</button> <form class="d-inline" method="post" action="<?= site_url('inventory/status/batch/' . $batch['id']) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $batch['status'] === 'active' ? 'inactive' : 'active' ?>"><button class="btn btn-outline-danger btn-sm"><?= $batch['status'] === 'active' ? 'Hapus' : 'Aktifkan' ?></button></form></td><?php endif; ?></tr><?php endforeach; ?><?php if ($batches === []) : ?><tr><td class="text-muted">Belum ada batch.</td></tr><?php endif; ?></tbody></table>
    </div></div></div>
</div>
<div class="row">
    <div class="col-xl-7"><div class="card"><div class="card-body">
        <h4 class="card-title mb-3">Item Profiles</h4>
        <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Item / Alias</th><th>Warehouse</th><th>Shelf Life</th><th>Packaging</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead><tbody>
            <?php foreach ($productProfiles as $profile) : ?><tr><td><?= esc($profile['sku']) ?><br><small><?= esc(($profile['alternate_code'] ?? '-') . ' / ' . ($profile['alternate_name'] ?? '-')) ?></small></td><td><?= esc($profile['warehouse_code'] ?? '-') ?></td><td><?= esc($profile['shelf_life_days'] ?? '-') ?></td><td><?= esc(($profile['units_per_package'] ?? '-') . ' ' . ($profile['package_uom_code'] ?? '')) ?></td><?php if ($canManage) : ?><td class="text-nowrap"><button class="btn btn-outline-primary btn-sm js-edit-profile" data-product="<?= (int) $profile['product_id'] ?>" data-code="<?= esc($profile['alternate_code'] ?? '', 'attr') ?>" data-name="<?= esc($profile['alternate_name'] ?? '', 'attr') ?>" data-warehouse="<?= (int) ($profile['default_warehouse_id'] ?? 0) ?>" data-shelf="<?= esc($profile['shelf_life_days'] ?? '', 'attr') ?>" data-length="<?= esc($profile['length_cm'] ?? '', 'attr') ?>" data-width="<?= esc($profile['width_cm'] ?? '', 'attr') ?>" data-height="<?= esc($profile['height_cm'] ?? '', 'attr') ?>" data-weight="<?= esc($profile['weight_kg'] ?? '', 'attr') ?>" data-uom="<?= (int) ($profile['package_uom_id'] ?? 0) ?>" data-units="<?= esc($profile['units_per_package'] ?? '', 'attr') ?>">Edit</button> <form class="d-inline" method="post" action="<?= site_url('inventory/status/profile/' . $profile['id']) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $profile['status'] === 'active' ? 'inactive' : 'active' ?>"><button class="btn btn-outline-danger btn-sm"><?= $profile['status'] === 'active' ? 'Hapus' : 'Aktifkan' ?></button></form></td><?php endif; ?></tr><?php endforeach; ?>
            <?php if ($productProfiles === []) : ?><tr><td colspan="<?= $canManage ? '5' : '4' ?>" class="text-muted">Belum ada operational profile item.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div></div></div>
    <div class="col-xl-5"><div class="card"><div class="card-body">
        <h4 class="card-title mb-3">Item Prices</h4>
        <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Item</th><th>Type</th><th>Price</th><th>Effective</th><?php if ($canManage) : ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead><tbody>
            <?php foreach ($productPrices as $price) : ?><tr><td><?= esc($price['sku']) ?></td><td><?= esc($price['price_type']) ?></td><td><?= esc($price['currency_code'] . ' ' . number_format((float) $price['unit_price'], 2, ',', '.') . '/' . $price['uom_code']) ?></td><td><?= esc($price['effective_from']) ?></td><?php if ($canManage) : ?><td><form method="post" action="<?= site_url('inventory/status/price/' . $price['id']) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $price['status'] === 'active' ? 'inactive' : 'active' ?>"><button class="btn btn-outline-danger btn-sm"><?= $price['status'] === 'active' ? 'Hapus' : 'Aktifkan' ?></button></form></td><?php endif; ?></tr><?php endforeach; ?>
            <?php if ($productPrices === []) : ?><tr><td colspan="<?= $canManage ? '5' : '4' ?>" class="text-muted">Belum ada baseline harga.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div></div></div>
</div>
<?php if ($canManage) : ?>
<div class="modal fade" id="editUom"><div class="modal-dialog"><form class="modal-content" method="post" data-action="<?= site_url('inventory/uoms') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit UOM</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><div class="col-4"><label class="form-label">Kode</label><input data-field="code" class="form-control" readonly></div><div class="col-5"><label class="form-label">Nama</label><input name="name" data-field="name" class="form-control" required></div><div class="col-3"><label class="form-label">Presisi</label><input name="precision" data-field="precision" type="number" min="0" max="6" class="form-control" required></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>
<div class="modal fade" id="editCategory"><div class="modal-dialog"><form class="modal-content" method="post" data-action="<?= site_url('inventory/categories') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit Kategori</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><div class="col-4"><label class="form-label">Kode</label><input data-field="code" class="form-control" readonly></div><div class="col-8"><label class="form-label">Nama</label><input name="name" data-field="name" class="form-control" required></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>
<div class="modal fade" id="editProduct"><div class="modal-dialog modal-lg"><form class="modal-content" method="post" data-action="<?= site_url('inventory/products') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit Produk</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><div class="col-md-3"><label class="form-label">SKU</label><input data-field="sku" class="form-control" readonly></div><div class="col-md-5"><label class="form-label">Nama</label><input name="name" data-field="name" class="form-control" required></div><div class="col-md-4"><label class="form-label">Barcode</label><input name="barcode" data-field="barcode" class="form-control"></div><div class="col-md-3"><label class="form-label">Kategori</label><select name="category_id" data-field="category" class="form-select"><option value="">-</option><?php foreach ($categories as $category) : ?><option value="<?= (int) $category['id'] ?>"><?= esc($category['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><label class="form-label">UOM</label><select name="base_uom_id" data-field="uom" class="form-select"><?php foreach ($uomOptions as $uom) : ?><option value="<?= (int) $uom['id'] ?>"><?= esc($uom['code']) ?></option><?php endforeach; ?></select></div><div class="col-md-3"><label class="form-label">Tipe</label><select name="product_type" data-field="type" class="form-select"><option value="stock">Stock</option><option value="non_stock">Non Stock</option><option value="service">Service</option></select></div><div class="col-md-2"><label class="form-label">Cost</label><input name="standard_cost" data-field="cost" type="number" min="0" step="0.0001" class="form-control"></div><div class="col-md-2 form-check mt-4"><input name="track_lot" value="1" data-field="lot" type="checkbox" class="form-check-input"><label class="form-check-label">Trace lot</label></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>
<div class="modal fade" id="editWarehouse"><div class="modal-dialog"><form class="modal-content" method="post" data-action="<?= site_url('inventory/warehouses') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit Gudang</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><div class="col-4"><label class="form-label">Kode</label><input data-field="code" class="form-control" readonly></div><div class="col-8"><label class="form-label">Nama</label><input name="name" data-field="name" class="form-control" required></div><div class="col-8"><label class="form-label">Alamat</label><input name="address" data-field="address" class="form-control"></div><div class="col-4"><label class="form-label">Kode Pos</label><input name="postal_code" data-field="postal" class="form-control"></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>
<div class="modal fade" id="editLocation"><div class="modal-dialog"><form class="modal-content" method="post" data-action="<?= site_url('inventory/locations') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit Location</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><div class="col-4"><label class="form-label">Kode</label><input data-field="code" class="form-control" readonly></div><div class="col-8"><label class="form-label">Nama</label><input name="name" data-field="name" class="form-control" required></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>
<div class="modal fade" id="editBatch"><div class="modal-dialog"><form class="modal-content" method="post" data-action="<?= site_url('inventory/batches') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit Batch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-2"><div class="col-6"><label class="form-label">Batch No.</label><input data-field="lot" class="form-control" readonly></div><div class="col-6"><label class="form-label">Expiry Date</label><input name="expiry_date" data-field="expiry" type="date" class="form-control"></div></div><div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>
<?php endif; ?>
<?= $this->endSection() ?>

<?php if ($canManage) : ?>
<?= $this->section('scripts') ?>
<script>
(function () {
    var site = document.getElementById('warehouse-site');
    var department = document.getElementById('warehouse-department');
    if (!site || !department) {
        return;
    }
    function filterDepartments() {
        var selected = '';
        Array.prototype.forEach.call(department.options, function (option) {
            option.hidden = option.dataset.site !== site.value;
            option.disabled = option.hidden;
            if (!option.hidden && selected === '') {
                selected = option.value;
            }
        });
        department.value = selected;
    }
    site.addEventListener('change', filterDepartments);
    filterDepartments();
}());
function fillEdit(modalId, button, fields) {
    var form = document.querySelector('#' + modalId + ' form');
    form.action = form.dataset.action + '/' + button.dataset.id;
    fields.forEach(function (key) { var element = form.querySelector('[data-field="' + key + '"]'); if (element) { element.value = button.dataset[key] === '0' ? '' : button.dataset[key]; } });
}
document.querySelectorAll('.js-edit-uom').forEach(function (button) { button.addEventListener('click', function () { fillEdit('editUom', button, ['code', 'name', 'precision']); }); });
document.querySelectorAll('.js-edit-category').forEach(function (button) { button.addEventListener('click', function () { fillEdit('editCategory', button, ['code', 'name']); }); });
document.querySelectorAll('.js-edit-product').forEach(function (button) { button.addEventListener('click', function () { fillEdit('editProduct', button, ['sku', 'name', 'barcode', 'category', 'uom', 'type', 'cost']); document.querySelector('#editProduct [data-field="lot"]').checked = button.dataset.lot === '1'; }); });
document.querySelectorAll('.js-edit-warehouse').forEach(function (button) { button.addEventListener('click', function () { fillEdit('editWarehouse', button, ['code', 'name', 'address', 'postal']); }); });
document.querySelectorAll('.js-edit-location').forEach(function (button) { button.addEventListener('click', function () { fillEdit('editLocation', button, ['code', 'name']); }); });
document.querySelectorAll('.js-edit-batch').forEach(function (button) { button.addEventListener('click', function () { fillEdit('editBatch', button, ['lot', 'expiry']); }); });
document.querySelectorAll('.js-edit-profile').forEach(function (button) { button.addEventListener('click', function () {
    var form = document.getElementById('product-profile-form');
    var values = {product_id: 'product', alternate_code: 'code', alternate_name: 'name', default_warehouse_id: 'warehouse', shelf_life_days: 'shelf', length_cm: 'length', width_cm: 'width', height_cm: 'height', weight_kg: 'weight', package_uom_id: 'uom', units_per_package: 'units'};
    Object.keys(values).forEach(function (name) { form.querySelector('[name="' + name + '"]').value = button.dataset[values[name]] === '0' ? '' : button.dataset[values[name]]; });
    bootstrap.Collapse.getOrCreateInstance(document.getElementById('inventory-add-forms')).show();
    form.scrollIntoView({behavior: 'smooth', block: 'center'});
}); });
</script>
<?= $this->endSection() ?>
<?php endif; ?>
