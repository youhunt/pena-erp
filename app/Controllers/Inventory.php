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

        return view('inventory/index', [
            'tenantContext' => $context,
            'canManage'     => $this->canManage($companyId),
            'products'      => $model->products($companyId),
            'warehouses'    => $model->warehouses($companyId),
            'uoms'          => $model->unitsOfMeasure($companyId),
            'categories'    => $model->productCategories($companyId),
            'branches'      => $model->branchOptions($companyId),
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
            'code'        => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'        => trim((string) $this->request->getPost('name')),
            'address'     => trim((string) $this->request->getPost('address')) ?: null,
            'postal_code' => trim((string) $this->request->getPost('postal_code')) ?: null,
            'is_active'   => true,
        ];

        if (! $this->validateData($data, [
            'branch_id'   => 'required|is_natural_no_zero',
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
            return $this->invalid(['branch_id' => 'Branch tidak valid untuk company aktif.']);
        }

        return $this->completed('Gudang berhasil ditambahkan.');
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
