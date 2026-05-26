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
                <div class="col-md-6"><label class="form-label">Branch</label><select class="form-select" name="branch_id" required><?php foreach ($branches as $branch) : ?><option value="<?= esc($branch['id']) ?>"><?= esc($branch['code'] . ' - ' . $branch['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label">Kode</label><input class="form-control" name="code" placeholder="MAIN" required></div>
                <div class="col-md-8"><label class="form-label">Nama</label><input class="form-control" name="name" placeholder="Gudang Utama" required></div>
                <div class="col-md-4"><label class="form-label">Kode Pos</label><input class="form-control" name="postal_code"></div>
                <div class="col-12"><label class="form-label">Alamat</label><input class="form-control" name="address"></div>
                <div class="col-12"><button class="btn btn-primary mt-2" type="submit">Simpan Gudang</button></div>
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
<?php endif; ?>

<div class="row">
    <div class="col-xl-8">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Produk</h4>
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th>SKU / Nama</th><th>Kategori</th><th>UOM</th><th>Tipe</th><th>Cost</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($products as $product) : ?>
                    <tr>
                        <td><strong><?= esc($product['sku']) ?></strong><br><small><?= esc($product['name']) ?></small></td>
                        <td><?= esc($product['category_name'] ?? '-') ?></td>
                        <td><?= esc($product['uom_code']) ?></td>
                        <td><?= esc($product['product_type']) ?><?= $product['track_lot'] ? ' / lot' : '' ?></td>
                        <td class="text-end"><?= number_format((float) $product['standard_cost'], 2, ',', '.') ?></td>
                        <td>
                            <?php if ($canManage) : ?>
                            <form method="post" action="<?= site_url('inventory/products/' . $product['id'] . '/status') ?>" class="d-flex gap-1">
                                <?= csrf_field() ?>
                                <select class="form-select form-select-sm" name="status"><option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option></select>
                                <button class="btn btn-outline-primary btn-sm" type="submit">Set</button>
                            </form>
                            <?php else : ?><?= esc($product['status']) ?><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($products === []) : ?><tr><td colspan="6" class="text-muted text-center">Belum ada produk pada company ini.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </div></div>
    </div>
    <div class="col-xl-4">
        <div class="card"><div class="card-body">
            <h4 class="card-title mb-3">Gudang</h4>
            <table class="table align-middle mb-0">
                <thead><tr><th>Branch / Gudang</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($warehouses as $warehouse) : ?>
                    <tr>
                        <td><strong><?= esc($warehouse['branch_code'] . ' / ' . $warehouse['code']) ?></strong><br><small><?= esc($warehouse['name']) ?></small></td>
                        <td>
                            <?php if ($canManage) : ?>
                            <form method="post" action="<?= site_url('inventory/warehouses/' . $warehouse['id'] . '/status') ?>" class="d-flex gap-1">
                                <?= csrf_field() ?>
                                <select class="form-select form-select-sm" name="status"><option value="active" <?= $warehouse['is_active'] ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ! $warehouse['is_active'] ? 'selected' : '' ?>>Inactive</option></select>
                                <button class="btn btn-outline-primary btn-sm" type="submit">Set</button>
                            </form>
                            <?php else : ?><?= $warehouse['is_active'] ? 'Active' : 'Inactive' ?><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($warehouses === []) : ?><tr><td colspan="2" class="text-muted text-center">Belum ada gudang.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div></div>
    </div>
</div>
<?= $this->endSection() ?>
