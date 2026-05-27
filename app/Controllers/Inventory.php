<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Authorization\TenantAuthorizationService;
use App\Models\InventoryReadModel;
use App\Models\InventoryWriteModel;
use App\Services\TenantContextService;
use CodeIgniter\HTTP\RedirectResponse;

final class Inventory extends BaseController
{
    public function index(): string
    {
        $context = $this->authorizedContext('inventory.stock.view');

        if ($context === null) {
            $this->response->setStatusCode(403);

            return view('workspace/module_denied', ['moduleCode' => 'inventory']);
        }

        $companyId = (int) $context['company_id'];
        $model = new InventoryReadModel();
        $warehouses = $model->warehouses($companyId);
        $products = $model->products($companyId);
        $uoms = $model->unitsOfMeasure($companyId);

        return view('inventory/index', [
            'tenantContext' => $context,
            'canManage'     => $this->canManage($companyId),
            'products'      => $products,
            'productOptions' => array_values(array_filter($products, static fn (array $product): bool => $product['status'] === 'active')),
            'warehouses'    => $warehouses,
            'warehouseOptions' => array_values(array_filter($warehouses, static fn (array $warehouse): bool => (bool) $warehouse['is_active'])),
            'uoms'          => $uoms,
            'uomOptions'    => array_values(array_filter($uoms, static fn (array $uom): bool => $uom['status'] === 'active')),
            'categories'    => $model->productCategories($companyId),
            'branches'      => $model->branchOptions($companyId),
            'departments'   => $model->departmentOptions($companyId),
            'locations'     => $model->locations($companyId),
            'conversions'   => $model->uomConversions($companyId),
            'itemTaxes'     => $model->itemTaxes($companyId),
            'batches'       => $model->batches($companyId),
            'taxCodes'      => $model->taxOptions($companyId),
            'currencies'    => $model->currencyOptions($companyId),
            'productProfiles' => $model->productProfiles($companyId),
            'productPrices' => $model->productPrices($companyId),
        ]);
    }

    public function createUnitOfMeasure(): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $companyId = (int) $context['company_id'];
        $data = [
            'company_id' => $companyId,
            'code'       => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'       => trim((string) $this->request->getPost('name')),
            'precision'  => (int) $this->request->getPost('precision'),
            'status'     => 'active',
        ];

        if (! $this->validateData($data, [
            'code'      => 'required|alpha_numeric|max_length[20]',
            'name'      => 'required|max_length[60]',
            'precision' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[6]',
        ])) {
            return $this->invalid();
        }

        if ((new InventoryReadModel())->uomCodeExists($companyId, $data['code'])) {
            return $this->invalid(['code' => 'Kode UOM sudah digunakan pada company aktif.']);
        }

        (new InventoryWriteModel())->createUnitOfMeasure($data, $this->actorId());

        return $this->completed('UOM berhasil ditambahkan.');
    }

    public function createCategory(): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $companyId = (int) $context['company_id'];
        $data = [
            'company_id' => $companyId,
            'code'       => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'       => trim((string) $this->request->getPost('name')),
            'status'     => 'active',
        ];

        if (! $this->validateData($data, [
            'code' => 'required|alpha_dash|max_length[30]',
            'name' => 'required|max_length[120]',
        ])) {
            return $this->invalid();
        }

        if ((new InventoryReadModel())->categoryCodeExists($companyId, $data['code'])) {
            return $this->invalid(['code' => 'Kode kategori sudah digunakan pada company aktif.']);
        }

        (new InventoryWriteModel())->createProductCategory($data, $this->actorId());

        return $this->completed('Kategori produk berhasil ditambahkan.');
    }

    public function createProduct(): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $companyId = (int) $context['company_id'];
        $categoryId = (int) $this->request->getPost('category_id');
        $barcode = trim((string) $this->request->getPost('barcode'));
        $data = [
            'company_id'    => $companyId,
            'category_id'   => $categoryId > 0 ? $categoryId : null,
            'sku'           => strtoupper(trim((string) $this->request->getPost('sku'))),
            'barcode'       => $barcode === '' ? null : $barcode,
            'name'          => trim((string) $this->request->getPost('name')),
            'base_uom_id'   => (int) $this->request->getPost('base_uom_id'),
            'product_type'  => (string) $this->request->getPost('product_type'),
            'track_lot'     => $this->request->getPost('track_lot') === '1',
            'standard_cost' => (string) ($this->request->getPost('standard_cost') ?: '0'),
            'status'        => 'active',
        ];

        if (! $this->validateData($data, [
            'sku'           => 'required|alpha_numeric_punct|max_length[60]',
            'name'          => 'required|max_length[180]',
            'base_uom_id'   => 'required|is_natural_no_zero',
            'product_type'  => 'required|in_list[stock,service,non_stock]',
            'standard_cost' => 'required|decimal',
        ])) {
            return $this->invalid();
        }

        $reader = new InventoryReadModel();

        if ($reader->productCodeExists($companyId, $data['sku'])) {
            return $this->invalid(['sku' => 'SKU sudah digunakan pada company aktif.']);
        }

        if (! (new InventoryWriteModel())->createProduct($data, $this->actorId())) {
            return $this->invalid(['product' => 'UOM atau kategori tidak valid untuk company aktif.']);
        }

        return $this->completed('Produk berhasil ditambahkan.');
    }

    public function createWarehouse(): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $companyId = (int) $context['company_id'];
        $data = [
            'company_id'  => $companyId,
            'branch_id'   => (int) $this->request->getPost('branch_id'),
            'department_id' => (int) $this->request->getPost('department_id'),
            'code'        => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'        => trim((string) $this->request->getPost('name')),
            'address'     => trim((string) $this->request->getPost('address')) ?: null,
            'postal_code' => trim((string) $this->request->getPost('postal_code')) ?: null,
            'is_active'   => true,
        ];

        if (! $this->validateData($data, [
            'branch_id'   => 'required|is_natural_no_zero',
            'department_id' => 'required|is_natural_no_zero',
            'code'        => 'required|alpha_dash|max_length[30]',
            'name'        => 'required|max_length[120]',
            'postal_code' => 'permit_empty|max_length[10]',
        ])) {
            return $this->invalid();
        }

        if ((new InventoryReadModel())->warehouseCodeExists($companyId, $data['branch_id'], $data['code'])) {
            return $this->invalid(['code' => 'Kode gudang sudah digunakan pada branch tersebut.']);
        }

        if (! (new InventoryWriteModel())->createWarehouse($data, $this->actorId())) {
            return $this->invalid(['department_id' => 'Site atau Department tidak valid untuk company aktif.']);
        }

        return $this->completed('Gudang berhasil ditambahkan.');
    }

    public function createLocation(): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $companyId = (int) $context['company_id'];
        $warehouseId = (int) $this->request->getPost('warehouse_id');
        $warehouses = (new InventoryReadModel())->warehouses($companyId);
        $selected = array_values(array_filter($warehouses, static fn (array $row): bool => (int) $row['id'] === $warehouseId));
        $data = [
            'company_id'    => $companyId,
            'warehouse_id'  => $warehouseId,
            'branch_id'     => $selected === [] ? 0 : (int) $selected[0]['branch_id'],
            'code'          => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'          => trim((string) $this->request->getPost('name')),
            'status'        => 'active',
        ];

        if (! $this->validateData($data, [
            'warehouse_id' => 'required|is_natural_no_zero',
            'branch_id'    => 'required|is_natural_no_zero',
            'code'         => 'required|alpha_dash|max_length[30]',
            'name'         => 'required|max_length[80]',
        ])) {
            return $this->invalid(['warehouse_id' => 'Gudang tidak valid untuk company aktif.']);
        }

        if ((new InventoryReadModel())->locationCodeExists($companyId, $warehouseId, $data['code'])) {
            return $this->invalid(['code' => 'Kode location sudah tersedia pada gudang ini.']);
        }

        if (! (new InventoryWriteModel())->createLocation($data, $this->actorId())) {
            return $this->invalid(['warehouse_id' => 'Gudang tidak valid untuk company aktif.']);
        }

        return $this->completed('Location berhasil ditambahkan.');
    }

    public function createUomConversion(): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $data = [
            'company_id'  => (int) $context['company_id'],
            'product_id'  => (int) $this->request->getPost('product_id'),
            'from_uom_id' => (int) $this->request->getPost('from_uom_id'),
            'to_uom_id'   => (int) $this->request->getPost('to_uom_id'),
            'factor'      => (string) $this->request->getPost('factor'),
            'status'      => 'active',
        ];

        if (! $this->validateData($data, [
            'product_id'  => 'required|is_natural_no_zero',
            'from_uom_id' => 'required|is_natural_no_zero',
            'to_uom_id'   => 'required|is_natural_no_zero',
            'factor'      => 'required|decimal|greater_than[0]',
        ])) {
            return $this->invalid();
        }

        if ($data['from_uom_id'] === $data['to_uom_id']) {
            return $this->invalid(['uom' => 'UOM asal dan tujuan harus berbeda.']);
        }

        if ((new InventoryReadModel())->uomConversionExists((int) $context['company_id'], $data['product_id'], $data['from_uom_id'], $data['to_uom_id'])) {
            return $this->invalid(['uom' => 'Conversion untuk item dan pasangan UOM tersebut sudah tersedia.']);
        }

        if (! (new InventoryWriteModel())->createUomConversion($data, $this->actorId())) {
            return $this->invalid(['uom' => 'Item atau UOM tidak valid untuk company aktif.']);
        }

        return $this->completed('Item UoM Conversion berhasil ditambahkan.');
    }

    public function createItemTax(): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $data = [
            'company_id'  => (int) $context['company_id'],
            'product_id'  => (int) $this->request->getPost('product_id'),
            'tax_code_id' => (int) $this->request->getPost('tax_code_id'),
            'usage_type'  => (string) $this->request->getPost('usage_type'),
            'status'      => 'active',
        ];

        if (! $this->validateData($data, [
            'product_id'  => 'required|is_natural_no_zero',
            'tax_code_id' => 'required|is_natural_no_zero',
            'usage_type'  => 'required|in_list[purchase,sales,both]',
        ])) {
            return $this->invalid();
        }

        if ((new InventoryReadModel())->itemTaxExists((int) $context['company_id'], $data['product_id'], $data['tax_code_id'], $data['usage_type'])) {
            return $this->invalid(['tax' => 'Item VAT dengan usage tersebut sudah tersedia.']);
        }

        if (! (new InventoryWriteModel())->createItemTax($data, $this->actorId())) {
            return $this->invalid(['tax' => 'Item atau VAT tidak valid untuk company aktif.']);
        }

        return $this->completed('Item VAT berhasil ditambahkan.');
    }

    public function createBatch(): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $expiry = trim((string) $this->request->getPost('expiry_date'));
        $data = [
            'company_id'  => (int) $context['company_id'],
            'product_id'  => (int) $this->request->getPost('product_id'),
            'lot_no'      => strtoupper(trim((string) $this->request->getPost('lot_no'))),
            'expiry_date' => $expiry === '' ? null : $expiry,
            'status'      => 'active',
        ];

        if (! $this->validateData($data, [
            'product_id'  => 'required|is_natural_no_zero',
            'lot_no'      => 'required|alpha_numeric_punct|max_length[60]',
            'expiry_date' => 'permit_empty|valid_date[Y-m-d]',
        ])) {
            return $this->invalid();
        }

        if ((new InventoryReadModel())->batchExists((int) $context['company_id'], $data['product_id'], $data['lot_no'])) {
            return $this->invalid(['lot_no' => 'Batch number sudah digunakan untuk item tersebut.']);
        }

        if (! (new InventoryWriteModel())->createBatch($data, $this->actorId())) {
            return $this->invalid(['product_id' => 'Item tidak valid untuk company aktif.']);
        }

        return $this->completed('Batch Master berhasil ditambahkan.');
    }

    public function saveProductProfile(): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $warehouseId = (int) $this->request->getPost('default_warehouse_id');
        $packageUomId = (int) $this->request->getPost('package_uom_id');
        $data = [
            'company_id'           => (int) $context['company_id'],
            'product_id'           => (int) $this->request->getPost('product_id'),
            'alternate_code'       => trim((string) $this->request->getPost('alternate_code')) ?: null,
            'alternate_name'       => trim((string) $this->request->getPost('alternate_name')) ?: null,
            'default_warehouse_id' => $warehouseId > 0 ? $warehouseId : null,
            'shelf_life_days'      => trim((string) $this->request->getPost('shelf_life_days')) ?: null,
            'length_cm'            => trim((string) $this->request->getPost('length_cm')) ?: null,
            'width_cm'             => trim((string) $this->request->getPost('width_cm')) ?: null,
            'height_cm'            => trim((string) $this->request->getPost('height_cm')) ?: null,
            'weight_kg'            => trim((string) $this->request->getPost('weight_kg')) ?: null,
            'package_uom_id'       => $packageUomId > 0 ? $packageUomId : null,
            'units_per_package'    => trim((string) $this->request->getPost('units_per_package')) ?: null,
            'status'               => 'active',
        ];

        if (! $this->validateData($data, [
            'product_id'        => 'required|is_natural_no_zero',
            'alternate_code'    => 'permit_empty|max_length[80]',
            'alternate_name'    => 'permit_empty|max_length[180]',
            'shelf_life_days'   => 'permit_empty|integer|greater_than_equal_to[0]',
            'length_cm'         => 'permit_empty|decimal|greater_than_equal_to[0]',
            'width_cm'          => 'permit_empty|decimal|greater_than_equal_to[0]',
            'height_cm'         => 'permit_empty|decimal|greater_than_equal_to[0]',
            'weight_kg'         => 'permit_empty|decimal|greater_than_equal_to[0]',
            'units_per_package' => 'permit_empty|decimal|greater_than[0]',
        ])) {
            return $this->invalid();
        }

        if (! (new InventoryWriteModel())->saveProductProfile($data, $this->actorId())) {
            return $this->invalid(['profile' => 'Item, gudang, atau UOM kemasan tidak valid untuk company aktif.']);
        }

        return $this->completed('Profil operasional item berhasil disimpan.');
    }

    public function createProductPrice(): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $endDate = trim((string) $this->request->getPost('effective_to'));
        $data = [
            'company_id'    => (int) $context['company_id'],
            'product_id'    => (int) $this->request->getPost('product_id'),
            'price_type'    => (string) $this->request->getPost('price_type'),
            'currency_id'   => (int) $this->request->getPost('currency_id'),
            'uom_id'        => (int) $this->request->getPost('uom_id'),
            'unit_price'    => (string) $this->request->getPost('unit_price'),
            'effective_from' => (string) $this->request->getPost('effective_from'),
            'effective_to'  => $endDate === '' ? null : $endDate,
            'status'        => 'active',
        ];

        if (! $this->validateData($data, [
            'product_id'     => 'required|is_natural_no_zero',
            'price_type'     => 'required|in_list[purchase,sales]',
            'currency_id'    => 'required|is_natural_no_zero',
            'uom_id'         => 'required|is_natural_no_zero',
            'unit_price'     => 'required|decimal|greater_than_equal_to[0]',
            'effective_from' => 'required|valid_date[Y-m-d]',
            'effective_to'   => 'permit_empty|valid_date[Y-m-d]',
        ])) {
            return $this->invalid();
        }

        if ($data['effective_to'] !== null && $data['effective_to'] < $data['effective_from']) {
            return $this->invalid(['effective_to' => 'Tanggal akhir harga tidak boleh sebelum tanggal mulai.']);
        }

        $reader = new InventoryReadModel();

        if ($reader->productPriceExists((int) $context['company_id'], $data['product_id'], $data['price_type'], $data['currency_id'], $data['uom_id'], $data['effective_from'])) {
            return $this->invalid(['price' => 'Harga item untuk tipe, currency, UOM, dan tanggal mulai tersebut sudah tersedia.']);
        }

        if (! (new InventoryWriteModel())->createProductPrice($data, $this->actorId())) {
            return $this->invalid(['price' => 'Item, currency, atau UOM tidak valid untuk company aktif.']);
        }

        return $this->completed('Baseline harga item berhasil ditambahkan.');
    }

    public function updateUnitOfMeasure(int $id): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');
        $data = [
            'name'      => trim((string) $this->request->getPost('name')),
            'precision' => (int) $this->request->getPost('precision'),
        ];

        if ($context === null) {
            return $this->denied();
        }

        if (! $this->validateData($data, ['name' => 'required|max_length[60]', 'precision' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[6]'])
            || ! (new InventoryWriteModel())->updateUnitOfMeasure((int) $context['company_id'], $id, $data, $this->actorId())) {
            return $this->invalid(['uom' => 'Data UOM tidak valid untuk company aktif.']);
        }

        return $this->completed('UOM berhasil diperbarui.');
    }

    public function updateCategory(int $id): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');
        $data = ['name' => trim((string) $this->request->getPost('name'))];

        if ($context === null) {
            return $this->denied();
        }

        if (! $this->validateData($data, ['name' => 'required|max_length[120]'])
            || ! (new InventoryWriteModel())->updateProductCategory((int) $context['company_id'], $id, $data, $this->actorId())) {
            return $this->invalid(['category' => 'Data kategori tidak valid untuk company aktif.']);
        }

        return $this->completed('Kategori produk berhasil diperbarui.');
    }

    public function updateProduct(int $id): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');

        if ($context === null) {
            return $this->denied();
        }

        $categoryId = (int) $this->request->getPost('category_id');
        $barcode = trim((string) $this->request->getPost('barcode'));
        $data = [
            'category_id'   => $categoryId > 0 ? $categoryId : null,
            'barcode'       => $barcode === '' ? null : $barcode,
            'name'          => trim((string) $this->request->getPost('name')),
            'base_uom_id'   => (int) $this->request->getPost('base_uom_id'),
            'product_type'  => (string) $this->request->getPost('product_type'),
            'track_lot'     => $this->request->getPost('track_lot') === '1',
            'standard_cost' => (string) ($this->request->getPost('standard_cost') ?: '0'),
        ];

        if (! $this->validateData($data, [
            'name'          => 'required|max_length[180]',
            'base_uom_id'   => 'required|is_natural_no_zero',
            'product_type'  => 'required|in_list[stock,service,non_stock]',
            'standard_cost' => 'required|decimal',
        ]) || ! (new InventoryWriteModel())->updateProduct((int) $context['company_id'], $id, $data, $this->actorId())) {
            return $this->invalid(['product' => 'Data produk, UOM, atau kategori tidak valid untuk company aktif.']);
        }

        return $this->completed('Produk berhasil diperbarui.');
    }

    public function updateWarehouse(int $id): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');
        $data = [
            'name'        => trim((string) $this->request->getPost('name')),
            'address'     => trim((string) $this->request->getPost('address')) ?: null,
            'postal_code' => trim((string) $this->request->getPost('postal_code')) ?: null,
        ];

        if ($context === null) {
            return $this->denied();
        }

        if (! $this->validateData($data, ['name' => 'required|max_length[120]', 'postal_code' => 'permit_empty|max_length[10]'])
            || ! (new InventoryWriteModel())->updateWarehouse((int) $context['company_id'], $id, $data, $this->actorId())) {
            return $this->invalid(['warehouse' => 'Data gudang tidak valid untuk company aktif.']);
        }

        return $this->completed('Gudang berhasil diperbarui.');
    }

    public function updateLocation(int $id): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');
        $data = ['name' => trim((string) $this->request->getPost('name'))];

        if ($context === null) {
            return $this->denied();
        }

        if (! $this->validateData($data, ['name' => 'required|max_length[80]'])
            || ! (new InventoryWriteModel())->updateLocation((int) $context['company_id'], $id, $data, $this->actorId())) {
            return $this->invalid(['location' => 'Data lokasi tidak valid untuk company aktif.']);
        }

        return $this->completed('Location berhasil diperbarui.');
    }

    public function updateBatch(int $id): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');
        $expiry = trim((string) $this->request->getPost('expiry_date'));
        $data = ['expiry_date' => $expiry === '' ? null : $expiry];

        if ($context === null) {
            return $this->denied();
        }

        if (! $this->validateData($data, ['expiry_date' => 'permit_empty|valid_date[Y-m-d]'])
            || ! (new InventoryWriteModel())->updateStockLot((int) $context['company_id'], $id, $data, $this->actorId())) {
            return $this->invalid(['batch' => 'Data batch tidak valid untuk company aktif.']);
        }

        return $this->completed('Batch berhasil diperbarui.');
    }

    public function updateProductStatus(int $id): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');
        $status = (string) $this->request->getPost('status');

        if ($context === null) {
            return $this->denied();
        }

        if (! in_array($status, ['active', 'inactive'], true)
            || ! (new InventoryWriteModel())->updateProductStatus((int) $context['company_id'], $id, $status, $this->actorId())) {
            return $this->invalid(['status' => 'Status atau produk tidak valid untuk company aktif.']);
        }

        return $this->completed('Status produk diperbarui.');
    }

    public function updateWarehouseStatus(int $id): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');
        $status = (string) $this->request->getPost('status');

        if ($context === null) {
            return $this->denied();
        }

        if (! in_array($status, ['active', 'inactive'], true)
            || ! (new InventoryWriteModel())->updateWarehouseStatus((int) $context['company_id'], $id, $status === 'active', $this->actorId())) {
            return $this->invalid(['status' => 'Status atau gudang tidak valid untuk company aktif.']);
        }

        return $this->completed('Status gudang diperbarui.');
    }

    public function updateMasterStatus(string $master, int $id): RedirectResponse
    {
        $context = $this->authorizedContext('inventory.master.manage');
        $status = (string) $this->request->getPost('status');

        if ($context === null) {
            return $this->denied();
        }

        if (! (new InventoryWriteModel())->updateMasterStatus($master, (int) $context['company_id'], $id, $status, $this->actorId())) {
            return $this->invalid(['status' => 'Status atau data inventory tidak valid untuk company aktif.']);
        }

        return $this->completed('Status master inventory diperbarui.');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function authorizedContext(string $permission): ?array
    {
        $context = (new TenantContextService())->current($this->actorId());

        if ($context === null || ! (new TenantAuthorizationService())->can($this->actorId(), (int) $context['company_id'], $permission)) {
            return null;
        }

        return $context;
    }

    private function canManage(int $companyId): bool
    {
        return (new TenantAuthorizationService())->can($this->actorId(), $companyId, 'inventory.master.manage');
    }

    private function actorId(): int
    {
        return (int) auth()->id();
    }

    private function denied(): RedirectResponse
    {
        return redirect()->to(site_url('workspace'))->with('errors', ['access' => 'Anda tidak memiliki izin mengelola master inventory pada company aktif.']);
    }

    /**
     * @param array<string, string>|null $errors
     */
    private function invalid(?array $errors = null): RedirectResponse
    {
        return redirect()->back()->withInput()->with('errors', $errors ?? $this->validator->getErrors());
    }

    private function completed(string $message): RedirectResponse
    {
        return redirect()->to(site_url('inventory'))->with('message', $message);
    }
}
