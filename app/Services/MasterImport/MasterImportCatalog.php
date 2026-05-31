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
                'required_columns' => ['company_code', 'site_code', 'sku', 'name', 'uom_code'],
                'optional_columns' => ['category_code', 'barcode', 'product_type', 'standard_cost', 'status'],
            ],
            'warehouses' => [
                'label' => 'Warehouse',
                'permission' => 'inventory.master.manage',
                'required_columns' => ['company_code', 'site_code', 'code', 'name'],
                'optional_columns' => ['department_code', 'address', 'postal_code', 'is_active'],
            ],
            'customers' => [
                'label' => 'Customer Master',
                'permission' => 'sales.master.manage',
                'required_columns' => ['company_code', 'site_code', 'customer_code', 'customer_name'],
                'optional_columns' => [
                    'customer_ref_name', 'contact_name', 'description', 'ship_from_whs',
                    'office_address', 'office_city', 'office_province', 'office_country', 'office_postal_code', 'office_contact_name', 'office_phone_number', 'office_handphone',
                    'tax_code', 'tax_number', 'vat_code', 'limit_amount', 'limit_qty', 'terms_code', 'limit_days',
                    'sales_code', 'sales_name', 'bank_code_1', 'bank_account_1', 'bank_code_2', 'bank_account_2',
                    'ar_parent', 'sales_parent',
                    'billing_customer', 'bill_to_code', 'billing_address', 'billing_city', 'billing_province', 'billing_country', 'billing_postal_code', 'billing_contact_name', 'billing_phone_number', 'billing_handphone',
                    'mail_customer', 'mail_code', 'mail_address', 'mail_city', 'mail_province', 'mail_country', 'mail_postal_code', 'mail_contact_name', 'mail_phone_number', 'mail_handphone',
                    'ship_to_customer', 'ship_to_code', 'ship_to_address', 'ship_to_city', 'ship_to_province', 'ship_to_country', 'ship_to_postal_code', 'ship_to_contact_name', 'ship_to_phone_number', 'ship_to_handphone',
                    'status',
                ],
            ],
            'suppliers' => [
                'label' => 'Supplier Master',
                'permission' => 'purchasing.master.manage',
                'required_columns' => ['company_code', 'site_code', 'supplier_code', 'supplier_name'],
                'optional_columns' => [
                    'supplier_ref_name', 'contact_name', 'description',
                    'office_address', 'office_city', 'office_province', 'office_country', 'office_postal_code', 'office_contact_name', 'office_phone_number', 'office_handphone',
                    'mail_address', 'mail_city', 'mail_province', 'mail_country', 'mail_postal_code', 'mail_contact_name', 'mail_phone_number', 'mail_handphone',
                    'billing_address', 'billing_city', 'billing_province', 'billing_country', 'billing_postal_code', 'billing_contact_name', 'billing_phone_number', 'billing_handphone',
                    'tax_code', 'tax_number', 'vat_code', 'limit_amount', 'limit_qty', 'terms_code', 'limit_days',
                    'employee_code', 'purchasing_name', 'bank_code_1', 'bank_account_1', 'bank_code_2', 'bank_account_2',
                    'ship_to_address', 'ship_to_city', 'ship_to_province', 'ship_to_country', 'ship_to_postal_code', 'ship_to_contact_name', 'ship_to_phone_number', 'ship_to_handphone',
                    'status',
                ],
            ],
            'chart_of_accounts' => [
                'label' => 'Chart of Accounts',
                'permission' => 'finance.master.manage',
                'required_columns' => ['company_code', 'account_no', 'name', 'account_type'],
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
