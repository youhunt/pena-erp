<?php

declare(strict_types=1);

namespace App\Services\MasterImport;

final class MasterImportCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return [
            'units_of_measure' => [
                'label' => 'Unit of Measure',
                'permission' => 'setup.master.manage',
                'required_columns' => ['code', 'name'],
                'optional_columns' => ['precision', 'status'],
            ],
            'product_categories' => [
                'label' => 'Product Category',
                'permission' => 'inventory.master.manage',
                'required_columns' => ['code', 'name'],
                'optional_columns' => ['status'],
            ],
            'products' => [
                'label' => 'Item Master',
                'permission' => 'inventory.master.manage',
                'required_columns' => ['sku', 'name', 'uom_code'],
                'optional_columns' => ['category_code', 'barcode', 'product_type', 'standard_cost', 'status'],
            ],
            'warehouses' => [
                'label' => 'Warehouse',
                'permission' => 'inventory.master.manage',
                'required_columns' => ['branch_code', 'code', 'name'],
                'optional_columns' => ['department_code', 'address', 'postal_code', 'is_active'],
            ],
            'customers' => [
                'label' => 'Customer Master',
                'permission' => 'sales.master.manage',
                'required_columns' => ['code', 'name'],
                'optional_columns' => ['term_code', 'tax_number', 'status'],
            ],
            'suppliers' => [
                'label' => 'Supplier Master',
                'permission' => 'purchasing.master.manage',
                'required_columns' => ['code', 'name'],
                'optional_columns' => ['term_code', 'tax_number', 'status'],
            ],
            'chart_of_accounts' => [
                'label' => 'Chart of Accounts',
                'permission' => 'finance.master.manage',
                'required_columns' => ['account_no', 'name', 'account_type'],
                'optional_columns' => ['normal_balance', 'is_posting', 'status'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $type): ?array
    {
        $catalog = $this->all();

        return $catalog[$type] ?? null;
    }
}
