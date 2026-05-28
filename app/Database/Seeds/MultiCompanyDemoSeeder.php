<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

final class MultiCompanyDemoSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'Demo@Pena2026';

    public function run(): void
    {
        if (ENVIRONMENT === 'production') {
            throw new RuntimeException('Seeder simulasi tidak boleh dijalankan pada environment production.');
        }

        $this->call(DevelopmentFoundationSeeder::class);

        $now = date('Y-m-d H:i:s');
        $village = $this->db->table('villages')->where('code', '3174070006')->get()->getFirstRow('array');
        $villageId = $village === null ? null : (int) $village['id'];
        $countryId = $this->country('ID', 'IDN', 'Indonesia', '+62', $now);

        $tenants = [
            'PENA' => [
                'name'     => 'PT Pena ERP Demo',
                'branches' => ['JKT' => 'Jakarta Distribution Center', 'SBY' => 'Surabaya Warehouse'],
            ],
            'NUSA' => [
                'name'     => 'PT Nusa Retail Nusantara',
                'branches' => ['BDG' => 'Bandung Store Office', 'MKS' => 'Makassar Outlet'],
            ],
            'KARYA' => [
                'name'     => 'PT Karya Jasa Digital',
                'branches' => ['DPS' => 'Denpasar Service Office'],
            ],
        ];

        $tenantIds = [];
        $branchIds = [];

        foreach ($tenants as $code => $tenant) {
            $tenantIds[$code] = $this->tenant($code, $tenant['name'], $villageId, $now);

            foreach ($tenant['branches'] as $branchCode => $branchName) {
                $branchIds[$code][$branchCode] = $this->branch($tenantIds[$code], $branchCode, $branchName, $villageId, $now);
            }

            $this->provisionAccessMatrix($tenantIds[$code], $now);
        }

        $this->provisionSetupMasters($tenantIds, $branchIds, $countryId, $villageId, $now);
        $this->provisionInventoryMasters($tenantIds, $branchIds, $now);
        $this->provisionCommercialMasters($tenantIds, $now);
        $this->provisionFinanceMasters($tenantIds, $now);
        $this->provisionPosMasters($tenantIds, $now);

        $users = [
            'owner@demo.pena-erp.test'      => 'demo.owner',
            'purchasing@demo.pena-erp.test' => 'demo.purchasing',
            'warehouse@demo.pena-erp.test'  => 'demo.warehouse',
            'finance@demo.pena-erp.test'    => 'demo.finance',
            'sales@demo.pena-erp.test'      => 'demo.sales',
            'manager@demo.pena-erp.test'    => 'demo.manager',
        ];
        $userIds = [];

        foreach ($users as $email => $username) {
            $userIds[$email] = $this->user($username, $email, $now);
        }

        $this->assign($userIds['owner@demo.pena-erp.test'], $tenantIds['PENA'], 'owner', $branchIds['PENA']['JKT'], true, $now);
        $this->assign($userIds['owner@demo.pena-erp.test'], $tenantIds['NUSA'], 'owner', $branchIds['NUSA']['BDG'], false, $now);
        $this->assign($userIds['owner@demo.pena-erp.test'], $tenantIds['KARYA'], 'owner', $branchIds['KARYA']['DPS'], false, $now);
        $this->assign($userIds['purchasing@demo.pena-erp.test'], $tenantIds['PENA'], 'purchasing', $branchIds['PENA']['JKT'], true, $now);
        $this->assign($userIds['warehouse@demo.pena-erp.test'], $tenantIds['PENA'], 'warehouse', $branchIds['PENA']['SBY'], true, $now);
        $this->assign($userIds['finance@demo.pena-erp.test'], $tenantIds['PENA'], 'finance', $branchIds['PENA']['JKT'], true, $now);
        $this->assign($userIds['finance@demo.pena-erp.test'], $tenantIds['NUSA'], 'finance', $branchIds['NUSA']['BDG'], false, $now);
        $this->assign($userIds['sales@demo.pena-erp.test'], $tenantIds['NUSA'], 'sales', $branchIds['NUSA']['MKS'], true, $now);
        $this->assign($userIds['manager@demo.pena-erp.test'], $tenantIds['KARYA'], 'manager', $branchIds['KARYA']['DPS'], true, $now);
    }

    private function tenant(string $code, string $name, ?int $villageId, string $now): int
    {
        $existing = $this->db->table('companies')->where('code', $code)->get()->getFirstRow('array');

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $this->db->table('companies')->insert([
            'code'          => $code,
            'name'          => $name,
            'address'       => 'Alamat simulasi ' . $code,
            'village_id'    => $villageId,
            'postal_code'   => '11730',
            'base_currency' => 'IDR',
            'timezone'      => 'Asia/Jakarta',
            'status'        => 'active',
            'created_at'    => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function branch(int $companyId, string $code, string $name, ?int $villageId, string $now): int
    {
        $existing = $this->db->table('branches')
            ->where(['company_id' => $companyId, 'code' => $code])
            ->get()
            ->getFirstRow('array');

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $this->db->table('branches')->insert([
            'company_id'     => $companyId,
            'code'           => $code,
            'name'           => $name,
            'address'        => 'Alamat simulasi ' . $code,
            'village_id'     => $villageId,
            'postal_code'    => '11730',
            'is_head_office' => true,
            'status'         => 'active',
            'created_at'     => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function provisionAccessMatrix(int $companyId, string $now): void
    {
        $permissions = [
            'company.dashboard.view' => ['Dashboard Workspace', 'company'],
            'company.master.manage'  => ['Kelola Master Company', 'company'],
            'setup.master.view'      => ['Lihat Setup Master', 'setup'],
            'setup.master.manage'    => ['Kelola Setup Master', 'setup'],
            'inventory.stock.view'   => ['Lihat Stok', 'inventory'],
            'inventory.master.manage' => ['Kelola Master Inventory', 'inventory'],
            'purchasing.po.view'     => ['Lihat Purchase Order', 'purchasing'],
            'purchasing.master.view' => ['Lihat Purchasing Master', 'purchasing'],
            'purchasing.master.manage' => ['Kelola Purchasing Master', 'purchasing'],
            'sales.order.view'       => ['Lihat Sales Order', 'sales'],
            'sales.master.view'      => ['Lihat Sales Master', 'sales'],
            'sales.master.manage'    => ['Kelola Sales Master', 'sales'],
            'pos.master.view'        => ['Lihat POS Master', 'pos'],
            'pos.master.manage'      => ['Kelola POS Master', 'pos'],
            'finance.master.view'    => ['Lihat Finance Master', 'finance'],
            'finance.master.manage'  => ['Kelola Finance Master', 'finance'],
            'finance.invoice.view'   => ['Lihat Invoice Finance', 'finance'],
            'finance.invoice.manage' => ['Kelola Invoice Finance', 'finance'],
            'cashbank.view'          => ['Lihat Cash dan Bank', 'cashbank'],
            'reports.view'           => ['Lihat Reporting', 'reports'],
            'documents.upload'       => ['Upload Dokumen AI/OCR', 'documents'],
        ];
        $permissionIds = [];

        foreach ($permissions as $code => [$name, $module]) {
            $permissionIds[$code] = $this->permission($companyId, $code, $name, $module, $now);
        }

        $menus = [
            'dashboard'  => ['Dashboard Workspace', 'workspace', 'bx bx-grid-alt', 10, 'company.dashboard.view'],
            'setup'      => ['Setup Master', 'setup', 'bx bx-cog', 15, 'setup.master.view'],
            'inventory'  => ['Inventory', 'inventory', 'bx bx-package', 20, 'inventory.stock.view'],
            'purchasing' => ['Purchasing Master', 'purchasing/master', 'bx bx-cart', 30, 'purchasing.master.view'],
            'sales'      => ['Sales Master', 'sales/master', 'bx bx-receipt', 40, 'sales.master.view'],
            'pos'        => ['POS Master', 'pos/master', 'bx bx-store', 45, 'pos.master.view'],
            'finance'    => ['Accounting & Finance', 'finance/master', 'bx bx-calculator', 50, 'finance.master.view'],
            'cashbank'   => ['Cash & Bank', 'workspace/modules/cashbank', 'bx bx-wallet', 60, 'cashbank.view'],
            'reports'    => ['Reporting', 'workspace/modules/reports', 'bx bx-line-chart', 70, 'reports.view'],
            'documents'  => ['AI Document Processing', 'workspace/modules/documents', 'bx bx-scan', 80, 'documents.upload'],
        ];

        foreach ($menus as $code => [$label, $route, $icon, $sortOrder, $permission]) {
            $menuId = $this->menu($companyId, $code, $label, $route, $icon, $sortOrder, $now);
            $this->menuPermission($companyId, $menuId, $permissionIds[$permission], $now);
        }

        $roleGrants = [
            'owner'      => array_keys($permissions),
            'manager'    => ['company.dashboard.view', 'setup.master.view', 'setup.master.manage', 'inventory.stock.view', 'inventory.master.manage', 'purchasing.po.view', 'purchasing.master.view', 'purchasing.master.manage', 'sales.order.view', 'sales.master.view', 'sales.master.manage', 'pos.master.view', 'pos.master.manage', 'finance.master.view', 'finance.master.manage', 'finance.invoice.view', 'finance.invoice.manage', 'cashbank.view', 'reports.view', 'documents.upload'],
            'finance'    => ['company.dashboard.view', 'finance.master.view', 'finance.master.manage', 'finance.invoice.view', 'finance.invoice.manage', 'cashbank.view', 'reports.view', 'documents.upload'],
            'purchasing' => ['company.dashboard.view', 'inventory.stock.view', 'purchasing.po.view', 'purchasing.master.view', 'purchasing.master.manage', 'documents.upload'],
            'warehouse'  => ['company.dashboard.view', 'inventory.stock.view', 'inventory.master.manage', 'documents.upload'],
            'sales'      => ['company.dashboard.view', 'inventory.stock.view', 'sales.order.view', 'sales.master.view', 'sales.master.manage', 'pos.master.view'],
            'cashier'    => ['company.dashboard.view', 'sales.order.view', 'pos.master.view', 'pos.master.manage', 'cashbank.view'],
        ];

        foreach ($roleGrants as $roleCode => $grants) {
            $roleId = $this->role($companyId, $roleCode, ucwords($roleCode), $now);

            foreach ($grants as $permission) {
                $this->grant($companyId, $roleId, $permissionIds[$permission], $now);
            }
        }
    }

    /**
     * @param array<string, int>                $tenantIds
     * @param array<string, array<string, int>> $branchIds
     */
    private function provisionSetupMasters(array $tenantIds, array $branchIds, int $countryId, ?int $villageId, string $now): void
    {
        foreach ($tenantIds as $code => $companyId) {
            foreach ($branchIds[$code] as $branchCode => $branchId) {
                $existingAtSite = $this->db->table('departments')
                    ->where(['company_id' => $companyId, 'branch_id' => $branchId])
                    ->where('deleted_at', null)
                    ->get()
                    ->getFirstRow('array');

                if ($existingAtSite === null) {
                    $hasOperationsCode = $this->db->table('departments')
                        ->where(['company_id' => $companyId, 'code' => 'OPS'])
                        ->where('deleted_at', null)
                        ->countAllResults() > 0;
                    $departmentCode = $hasOperationsCode ? 'OPS-' . $branchCode : 'OPS';
                    $this->department($companyId, $branchId, $departmentCode, 'Operations ' . $branchCode, $now);
                }
            }

            $this->inventoryRecord('currencies', $companyId, 'code', 'IDR', [
                'code'       => 'IDR',
                'name'       => 'Indonesian Rupiah',
                'is_base'    => true,
                'status'     => 'active',
                'created_at' => $now,
            ]);
            $this->inventoryRecord('tax_codes', $companyId, 'code', 'PPN11', [
                'code'       => 'PPN11',
                'name'       => 'PPN 11%',
                'tax_type'   => 'both',
                'rate'       => '0.110000',
                'status'     => 'active',
                'created_at' => $now,
            ]);
            $this->inventoryRecord('addresses', $companyId, 'code', 'MAIN', [
                'code'          => 'MAIN',
                'label'         => 'Alamat Utama ' . $code,
                'address_line1' => 'Alamat simulasi ' . $code,
                'country_id'    => $countryId,
                'village_id'    => $villageId,
                'postal_code'   => '11730',
                'status'        => 'active',
                'created_at'    => $now,
            ]);

            foreach ($branchIds[$code] as $branchCode => $branchId) {
                $existing = $this->db->table('transaction_codes')->where([
                    'company_id' => $companyId,
                    'branch_id'  => $branchId,
                    'code'       => 'SO',
                ])->get()->getFirstRow('array');

                if ($existing === null) {
                    $this->db->table('transaction_codes')->insert([
                        'company_id'    => $companyId,
                        'branch_id'     => $branchId,
                        'module'        => 'sales',
                        'code'          => 'SO',
                        'prefix'        => $branchCode . '-SO-',
                        'next_number'   => 1,
                        'number_length' => 6,
                        'reset_rule'    => 'yearly',
                        'status'        => 'active',
                        'created_at'    => $now,
                    ]);
                }
            }
        }
    }

    /**
     * @param array<string, int>              $tenantIds
     * @param array<string, array<string, int>> $branchIds
     */
    private function provisionInventoryMasters(array $tenantIds, array $branchIds, string $now): void
    {
        $inventory = [
            'PENA' => [
                'uom'       => ['code' => 'REAM', 'name' => 'Rim', 'precision' => 0],
                'category'  => ['code' => 'ATK', 'name' => 'Alat Tulis Kantor'],
                'product'   => ['sku' => 'ATK-A4-80', 'name' => 'Kertas A4 80 gsm', 'product_type' => 'stock', 'standard_cost' => '65000.0000'],
                'profile'   => ['alternate_code' => 'A4-80', 'alternate_name' => 'Copy Paper A4', 'shelf_life_days' => null, 'units_per_package' => '5.000000'],
                'price'     => '72500.0000',
                'warehouse' => ['branch' => 'JKT', 'code' => 'MAIN', 'name' => 'Gudang Utama Jakarta'],
            ],
            'NUSA' => [
                'uom'       => ['code' => 'PCS', 'name' => 'Pieces', 'precision' => 0],
                'category'  => ['code' => 'RETAIL', 'name' => 'Retail Product'],
                'product'   => ['sku' => 'RTL-SNACK-01', 'name' => 'Produk Retail Demo', 'product_type' => 'stock', 'standard_cost' => '7500.0000'],
                'profile'   => ['alternate_code' => 'SNACK-01', 'alternate_name' => 'Retail Snack', 'shelf_life_days' => 180, 'units_per_package' => '12.000000'],
                'price'     => '10000.0000',
                'warehouse' => ['branch' => 'BDG', 'code' => 'STORE', 'name' => 'Stock Store Bandung'],
            ],
            'KARYA' => [
                'uom'       => ['code' => 'HOUR', 'name' => 'Jam', 'precision' => 2],
                'category'  => ['code' => 'SERVICE', 'name' => 'Jasa'],
                'product'   => ['sku' => 'SRV-CONSULT', 'name' => 'Consulting Hour', 'product_type' => 'service', 'standard_cost' => '0.0000'],
                'profile'   => ['alternate_code' => 'CONSULT', 'alternate_name' => 'Consulting Service', 'shelf_life_days' => null, 'units_per_package' => '1.000000'],
                'price'     => '350000.0000',
                'warehouse' => ['branch' => 'DPS', 'code' => 'ASSET', 'name' => 'Penyimpanan Aset Denpasar'],
            ],
        ];

        foreach ($inventory as $companyCode => $master) {
            $companyId = $tenantIds[$companyCode];
            $uomId = $this->inventoryRecord('units_of_measure', $companyId, 'code', $master['uom']['code'], $master['uom'] + ['status' => 'active', 'created_at' => $now]);
            $alternateUomId = $this->inventoryRecord('units_of_measure', $companyId, 'code', 'PCS', [
                'code'       => 'PCS',
                'name'       => 'Pieces',
                'precision'  => 0,
                'status'     => 'active',
                'created_at' => $now,
            ]);
            $categoryId = $this->inventoryRecord('product_categories', $companyId, 'code', $master['category']['code'], $master['category'] + ['status' => 'active', 'created_at' => $now]);
            $productId = $this->inventoryRecord('products', $companyId, 'sku', $master['product']['sku'], $master['product'] + [
                'category_id' => $categoryId,
                'base_uom_id' => $uomId,
                'track_lot'   => false,
                'status'      => 'active',
                'created_at'  => $now,
            ]);
            $warehouseId = $this->warehouse(
                $companyId,
                $branchIds[$companyCode][$master['warehouse']['branch']],
                (int) $this->db->table('departments')
                    ->where([
                        'company_id' => $companyId,
                        'branch_id'  => $branchIds[$companyCode][$master['warehouse']['branch']],
                    ])
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getFirstRow()->id,
                $master['warehouse']['code'],
                $master['warehouse']['name'],
                $now,
            );
            $taxId = (int) $this->db->table('tax_codes')->where(['company_id' => $companyId, 'code' => 'PPN11'])->get()->getFirstRow()->id;
            $currencyId = (int) $this->db->table('currencies')->where(['company_id' => $companyId, 'code' => 'IDR'])->get()->getFirstRow()->id;
            $this->relationRecord('product_tax_codes', [
                'company_id'  => $companyId,
                'product_id'  => $productId,
                'tax_code_id' => $taxId,
                'usage_type'  => 'sales',
            ], ['status' => 'active', 'created_at' => $now]);
            $this->relationRecord('warehouse_bins', [
                'company_id'   => $companyId,
                'branch_id'    => $branchIds[$companyCode][$master['warehouse']['branch']],
                'warehouse_id' => $warehouseId,
                'code'         => 'DEFAULT',
            ], ['name' => 'Default Location', 'status' => 'active', 'created_at' => $now]);
            $this->relationRecord('product_profiles', [
                'company_id' => $companyId,
                'product_id' => $productId,
            ], $master['profile'] + [
                'default_warehouse_id' => $warehouseId,
                'package_uom_id'       => $alternateUomId,
                'status'               => 'active',
                'created_at'           => $now,
            ]);
            $this->relationRecord('product_prices', [
                'company_id'     => $companyId,
                'product_id'     => $productId,
                'price_type'     => 'sales',
                'currency_id'    => $currencyId,
                'uom_id'         => $uomId,
                'effective_from' => '2026-05-26',
            ], [
                'unit_price' => $master['price'],
                'status'     => 'active',
                'created_at' => $now,
            ]);

            if (($master['product']['product_type'] ?? '') === 'stock' && $this->db->tableExists('stock_balances')) {
                $balanceId = $this->relationRecord('stock_balances', [
                    'company_id'   => $companyId,
                    'warehouse_id' => $warehouseId,
                    'product_id'   => $productId,
                ], [
                    'bin_id'       => null,
                    'lot_id'       => null,
                    'qty_on_hand'  => '100.0000',
                    'qty_reserved' => '0.0000',
                    'avg_cost'     => $master['product']['standard_cost'],
                    'created_at'   => $now,
                ]);

                if ($this->db->tableExists('stock_movements')) {
                    $this->relationRecord('stock_movements', [
                        'company_id'     => $companyId,
                        'reference_type' => 'opening_balance',
                        'reference_id'   => $balanceId,
                    ], [
                        'warehouse_id'   => $warehouseId,
                        'bin_id'         => null,
                        'product_id'     => $productId,
                        'lot_id'         => null,
                        'movement_type'  => 'opening_balance',
                        'reference_no'   => 'OPEN-' . $master['product']['sku'],
                        'qty'            => '100.0000',
                        'unit_cost'      => $master['product']['standard_cost'],
                        'posted_at'      => $now,
                        'created_at'     => $now,
                    ]);
                }
            }

            if ($uomId !== $alternateUomId) {
                $this->relationRecord('product_uom_conversions', [
                    'company_id'  => $companyId,
                    'product_id'  => $productId,
                    'from_uom_id' => $uomId,
                    'to_uom_id'   => $alternateUomId,
                ], ['factor' => '1.000000', 'status' => 'active', 'created_at' => $now]);
            }
        }
    }

    /**
     * @param array<string, int> $tenantIds
     */
    private function provisionCommercialMasters(array $tenantIds, string $now): void
    {
        foreach ($tenantIds as $code => $companyId) {
            $currencyId = (int) $this->db->table('currencies')->where(['company_id' => $companyId, 'code' => 'IDR'])->get()->getFirstRow()->id;
            $addressId = (int) $this->db->table('addresses')->where(['company_id' => $companyId, 'code' => 'MAIN'])->get()->getFirstRow()->id;
            $taxCodeId = (int) $this->db->table('tax_codes')->where(['company_id' => $companyId, 'code' => 'PPN11'])->get()->getFirstRow()->id;
            $warehouseId = (int) $this->db->table('warehouses')->where('company_id', $companyId)->orderBy('id', 'ASC')->get()->getFirstRow()->id;
            $customerTermId = $this->inventoryRecord('customer_terms', $companyId, 'code', 'NET30', [
                'code'          => 'NET30',
                'name'          => 'Net 30 Days',
                'due_days'      => 30,
                'discount_days' => 0,
                'discount_rate' => '0.000000',
                'status'        => 'active',
                'created_at'    => $now,
            ]);
            $supplierTermId = $this->inventoryRecord('supplier_terms', $companyId, 'code', 'NET14', [
                'code'          => 'NET14',
                'name'          => 'Net 14 Days',
                'due_days'      => 14,
                'discount_days' => 0,
                'discount_rate' => '0.000000',
                'status'        => 'active',
                'created_at'    => $now,
            ]);
            $customerId = $this->inventoryRecord('customers', $companyId, 'code', 'CUS-DEMO', [
                'code'            => 'CUS-DEMO',
                'name'            => 'Customer Demo ' . $code,
                'email'           => 'customer.' . strtolower($code) . '@example.test',
                'currency_id'     => $currencyId,
                'default_term_id' => $customerTermId,
                'credit_limit'    => '10000000.0000',
                'status'          => 'active',
                'created_at'      => $now,
            ]);
            $supplierId = $this->inventoryRecord('suppliers', $companyId, 'code', 'SUP-DEMO', [
                'code'            => 'SUP-DEMO',
                'name'            => 'Supplier Demo ' . $code,
                'email'           => 'supplier.' . strtolower($code) . '@example.test',
                'currency_id'     => $currencyId,
                'default_term_id' => $supplierTermId,
                'status'          => 'active',
                'created_at'      => $now,
            ]);

            $this->relationRecord('customer_addresses', [
                'company_id'   => $companyId,
                'customer_id'  => $customerId,
                'address_id'   => $addressId,
                'address_type' => 'billing',
            ], ['is_default' => true, 'status' => 'active', 'created_at' => $now]);
            $this->relationRecord('customer_addresses', [
                'company_id'   => $companyId,
                'customer_id'  => $customerId,
                'address_id'   => $addressId,
                'address_type' => 'mailing',
            ], ['is_default' => false, 'status' => 'active', 'created_at' => $now]);
            $this->relationRecord('supplier_addresses', [
                'company_id'   => $companyId,
                'supplier_id'  => $supplierId,
                'address_id'   => $addressId,
                'address_type' => 'office',
            ], ['is_default' => true, 'status' => 'active', 'created_at' => $now]);
            $this->relationRecord('supplier_addresses', [
                'company_id'   => $companyId,
                'supplier_id'  => $supplierId,
                'address_id'   => $addressId,
                'address_type' => 'mailing',
            ], ['is_default' => false, 'status' => 'active', 'created_at' => $now]);
            $this->relationRecord('customer_profiles', [
                'company_id'  => $companyId,
                'customer_id' => $customerId,
            ], [
                'reference_name'       => 'Customer Reference ' . $code,
                'contact_name'         => 'Contact Customer ' . $code,
                'description'          => 'Demo customer profile from normalized workbook mapping.',
                'default_tax_code_id'  => $taxCodeId,
                'default_warehouse_id' => $warehouseId,
                'account_manager_name' => 'Sales PIC ' . $code,
                'quantity_limit'       => '1000.0000',
                'limit_days'           => 30,
                'status'               => 'active',
                'created_at'           => $now,
            ]);
            $this->relationRecord('supplier_profiles', [
                'company_id'  => $companyId,
                'supplier_id' => $supplierId,
            ], [
                'reference_name'       => 'Supplier Reference ' . $code,
                'contact_name'         => 'Contact Supplier ' . $code,
                'description'          => 'Demo supplier profile from normalized workbook mapping.',
                'default_tax_code_id'  => $taxCodeId,
                'default_warehouse_id' => $warehouseId,
                'buyer_name'           => 'Purchasing PIC ' . $code,
                'amount_limit'         => '25000000.0000',
                'quantity_limit'       => '2000.0000',
                'limit_days'           => 14,
                'status'               => 'active',
                'created_at'           => $now,
            ]);
            $this->inventoryRecord('customer_promotions', $companyId, 'code', 'WELCOME5', [
                'customer_id'    => $customerId,
                'code'           => 'WELCOME5',
                'name'           => 'Welcome Customer Discount',
                'discount_type'  => 'percentage',
                'discount_value' => '5.0000',
                'starts_on'      => '2026-01-01',
                'ends_on'        => '2026-12-31',
                'status'         => 'active',
                'created_at'     => $now,
            ]);
            $this->inventoryRecord('supplier_promotions', $companyId, 'code', 'REBATE2', [
                'supplier_id'    => $supplierId,
                'code'           => 'REBATE2',
                'name'           => 'Supplier Rebate',
                'discount_type'  => 'percentage',
                'discount_value' => '2.0000',
                'starts_on'      => '2026-01-01',
                'ends_on'        => '2026-12-31',
                'status'         => 'active',
                'created_at'     => $now,
            ]);
        }
    }

    /** @param array<string, int> $tenantIds */
    private function provisionPosMasters(array $tenantIds, string $now): void
    {
        foreach ($tenantIds as $code => $companyId) {
            $warehouse = $this->db->table('warehouses')->where(['company_id' => $companyId, 'is_active' => true])
                ->orderBy('id', 'ASC')->get()->getFirstRow('array');
            $customer = $this->db->table('customers')->where(['company_id' => $companyId, 'code' => 'CUS-DEMO'])->get()->getFirstRow('array');
            $currency = $this->db->table('currencies')->where(['company_id' => $companyId, 'code' => 'IDR'])->get()->getFirstRow('array');

            if ($warehouse === null || $customer === null || $currency === null) {
                continue;
            }

            $branchId = (int) $warehouse['branch_id'];
            $transaction = $this->db->table('transaction_codes')->where([
                'company_id' => $companyId, 'branch_id' => $branchId, 'code' => 'POS',
            ])->get()->getFirstRow('array');

            if ($transaction === null) {
                $branchCode = (string) $this->db->table('branches')->select('code')->where('id', $branchId)->get()->getFirstRow()->code;
                $this->db->table('transaction_codes')->insert([
                    'company_id'    => $companyId,
                    'branch_id'     => $branchId,
                    'module'        => 'pos',
                    'code'          => 'POS',
                    'prefix'        => $branchCode . '-POS-',
                    'next_number'   => 1,
                    'number_length' => 6,
                    'reset_rule'    => 'daily',
                    'status'        => 'active',
                    'created_at'    => $now,
                ]);
                $transactionId = (int) $this->db->insertID();
            } else {
                $transactionId = (int) $transaction['id'];
            }

            $registerId = $this->inventoryRecord('pos_registers', $companyId, 'code', 'REG-01', [
                'branch_id'           => $branchId,
                'department_id'       => (int) $warehouse['department_id'],
                'warehouse_id'        => (int) $warehouse['id'],
                'default_customer_id' => (int) $customer['id'],
                'currency_id'         => (int) $currency['id'],
                'transaction_code_id' => $transactionId,
                'code'                => 'REG-01',
                'name'                => 'Register Utama ' . $code,
                'device_label'        => 'POS-' . $code . '-01',
                'status'              => 'active',
                'created_at'          => $now,
            ]);
            $cashAccount = $this->db->table('cash_bank_accounts')->where(['company_id' => $companyId, 'code' => 'CASH-MAIN'])->get()->getFirstRow('array');
            $bankAccount = $this->db->table('cash_bank_accounts')->where(['company_id' => $companyId, 'code' => 'BANK-MAIN'])->get()->getFirstRow('array');

            if ($cashAccount !== null) {
                $this->relationRecord('pos_payment_methods', [
                    'company_id'  => $companyId,
                    'register_id' => $registerId,
                    'code'        => 'CASH',
                ], [
                    'cash_bank_account_id' => (int) $cashAccount['id'],
                    'name'                 => 'Cash',
                    'payment_type'         => 'cash',
                    'is_default'           => true,
                    'sort_order'           => 10,
                    'status'               => 'active',
                    'created_at'           => $now,
                ]);
            }

            if ($bankAccount !== null) {
                $this->relationRecord('pos_payment_methods', [
                    'company_id'  => $companyId,
                    'register_id' => $registerId,
                    'code'        => 'BANK',
                ], [
                    'cash_bank_account_id' => (int) $bankAccount['id'],
                    'name'                 => 'Bank Transfer',
                    'payment_type'         => 'transfer',
                    'is_default'           => false,
                    'sort_order'           => 20,
                    'status'               => 'active',
                    'created_at'           => $now,
                ]);
            }
        }
    }

    /** @param array<string, int> $tenantIds */
    private function provisionFinanceMasters(array $tenantIds, string $now): void
    {
        foreach ($tenantIds as $code => $companyId) {
            $currency = $this->db->table('currencies')->where(['company_id' => $companyId, 'code' => 'IDR'])->get()->getFirstRow('array');
            $branch = $this->db->table('branches')->where(['company_id' => $companyId, 'status' => 'active'])->orderBy('id', 'ASC')->get()->getFirstRow('array');

            if ($currency === null || $branch === null) {
                continue;
            }

            $cashAccount = $this->inventoryRecord('chart_of_accounts', $companyId, 'account_code', '1101', [
                'account_code'   => '1101',
                'account_name'   => 'Cash on Hand',
                'account_type'   => 'asset',
                'normal_balance' => 'D',
                'is_postable'    => true,
                'status'         => 'active',
                'created_at'     => $now,
            ]);
            $bankAccount = $this->inventoryRecord('chart_of_accounts', $companyId, 'account_code', '1102', [
                'account_code'   => '1102',
                'account_name'   => 'Cash in Bank',
                'account_type'   => 'asset',
                'normal_balance' => 'D',
                'is_postable'    => true,
                'status'         => 'active',
                'created_at'     => $now,
            ]);
            $this->inventoryRecord('chart_of_accounts', $companyId, 'account_code', '1201', [
                'account_code'   => '1201',
                'account_name'   => 'Accounts Receivable',
                'account_type'   => 'asset',
                'normal_balance' => 'D',
                'is_postable'    => true,
                'status'         => 'active',
                'created_at'     => $now,
            ]);
            $this->inventoryRecord('chart_of_accounts', $companyId, 'account_code', '2101', [
                'account_code'   => '2101',
                'account_name'   => 'Accounts Payable',
                'account_type'   => 'liability',
                'normal_balance' => 'C',
                'is_postable'    => true,
                'status'         => 'active',
                'created_at'     => $now,
            ]);
            $this->inventoryRecord('chart_of_accounts', $companyId, 'account_code', '5001', [
                'account_code'   => '5001',
                'account_name'   => 'Purchases',
                'account_type'   => 'expense',
                'normal_balance' => 'D',
                'is_postable'    => true,
                'status'         => 'active',
                'created_at'     => $now,
            ]);
            $this->inventoryRecord('chart_of_accounts', $companyId, 'account_code', '4101', [
                'account_code'   => '4101',
                'account_name'   => 'Sales Revenue',
                'account_type'   => 'revenue',
                'normal_balance' => 'C',
                'is_postable'    => true,
                'status'         => 'active',
                'created_at'     => $now,
            ]);
            $this->inventoryRecord('cash_bank_accounts', $companyId, 'code', 'CASH-MAIN', [
                'branch_id'             => (int) $branch['id'],
                'account_id'            => $cashAccount,
                'currency_id'           => (int) $currency['id'],
                'code'                  => 'CASH-MAIN',
                'name'                  => 'Cash Counter ' . $code,
                'account_type'          => 'cash',
                'status'                => 'active',
                'created_at'            => $now,
            ]);
            $this->inventoryRecord('cash_bank_accounts', $companyId, 'code', 'BANK-MAIN', [
                'branch_id'             => (int) $branch['id'],
                'account_id'            => $bankAccount,
                'currency_id'           => (int) $currency['id'],
                'code'                  => 'BANK-MAIN',
                'name'                  => 'Operational Bank ' . $code,
                'account_type'          => 'bank',
                'bank_name'             => 'Demo Bank',
                'account_number_masked' => '****' . $code,
                'status'                => 'active',
                'created_at'            => $now,
            ]);
            $this->relationRecord('exchange_rates', [
                'company_id'  => $companyId,
                'currency_id' => (int) $currency['id'],
                'rate_date'   => '2026-05-26',
                'rate_type'   => 'middle',
            ], [
                'rate_to_base' => '1.00000000',
                'status'       => 'active',
                'created_at'   => $now,
            ]);
            $glBookId = $this->inventoryRecord('gl_books', $companyId, 'code', 'MAIN', [
                'currency_id'                  => (int) $currency['id'],
                'retained_earnings_account_id' => null,
                'code'                         => 'MAIN',
                'name'                         => 'Primary Ledger',
                'book_type'                    => 'primary',
                'status'                       => 'active',
                'created_at'                   => $now,
            ]);
            unset($glBookId);
            $this->inventoryRecord('gl_columns', $companyId, 'code', 'ACTUAL', [
                'code'        => 'ACTUAL',
                'name'        => 'Actual',
                'column_type' => 'actual',
                'sequence_no' => 10,
                'status'      => 'active',
                'created_at'  => $now,
            ]);
            $standardCostTypeId = $this->inventoryRecord('cost_types', $companyId, 'code', 'STD', [
                'code'             => 'STD',
                'name'             => 'Standard Cost',
                'valuation_method' => 'standard',
                'status'           => 'active',
                'created_at'       => $now,
            ]);
            $product = $this->db->table('products')->where(['company_id' => $companyId, 'product_type' => 'stock'])->orderBy('id', 'ASC')->get()->getFirstRow('array');
            if ($product !== null) {
                $this->relationRecord('item_costs', [
                    'company_id'      => $companyId,
                    'product_id'      => (int) $product['id'],
                    'cost_type_id'    => $standardCostTypeId,
                    'currency_id'     => (int) $currency['id'],
                    'effective_from'  => '2026-05-26',
                ], [
                    'unit_cost'  => $product['standard_cost'] ?? '0.0000',
                    'status'     => 'active',
                    'created_at' => $now,
                ]);
            }
            $periodId = $this->relationRecord('fiscal_periods', [
                'company_id' => $companyId,
                'year'       => 2026,
                'period'     => 5,
            ], [
                'starts_on'  => '2026-05-01',
                'ends_on'    => '2026-05-31',
                'status'     => 'open',
                'created_at' => $now,
            ]);
            $this->relationRecord('module_period_closes', [
                'company_id'        => $companyId,
                'fiscal_period_id'  => $periodId,
                'module_code'       => 'gl',
            ], [
                'status'     => 'open',
                'created_at' => $now,
            ]);
        }
    }

    /**
     * @param array<string, bool|int|string|null> $data
     */
    private function inventoryRecord(string $table, int $companyId, string $key, string $value, array $data): int
    {
        $existing = $this->db->table($table)
            ->where(['company_id' => $companyId, $key => $value])
            ->get()
            ->getFirstRow('array');

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $this->db->table($table)->insert(['company_id' => $companyId] + $data);

        return (int) $this->db->insertID();
    }

    /**
     * @param array<string, int|string>          $keys
     * @param array<string, bool|int|string|null> $data
     */
    private function relationRecord(string $table, array $keys, array $data): int
    {
        $existing = $this->db->table($table)->where($keys)->get()->getFirstRow('array');

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $this->db->table($table)->insert($keys + $data);

        return (int) $this->db->insertID();
    }

    private function country(string $iso2, string $iso3, string $name, string $phoneCode, string $now): int
    {
        $existing = $this->db->table('countries')->where('iso2', $iso2)->get()->getFirstRow('array');

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $this->db->table('countries')->insert([
            'iso2'       => $iso2,
            'iso3'       => $iso3,
            'name'       => $name,
            'phone_code' => $phoneCode,
            'is_active'  => true,
            'created_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function department(int $companyId, int $branchId, string $code, string $name, string $now): int
    {
        $existing = $this->db->table('departments')
            ->where(['company_id' => $companyId, 'branch_id' => $branchId, 'code' => $code])
            ->get()
            ->getFirstRow('array');

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $this->db->table('departments')->insert([
            'company_id' => $companyId,
            'branch_id'  => $branchId,
            'code'       => $code,
            'name'       => $name,
            'status'     => 'active',
            'created_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function warehouse(int $companyId, int $branchId, int $departmentId, string $code, string $name, string $now): int
    {
        $existing = $this->db->table('warehouses')
            ->where(['company_id' => $companyId, 'branch_id' => $branchId, 'code' => $code])
            ->get()
            ->getFirstRow('array');

        if ($existing !== null) {
            if ((int) ($existing['department_id'] ?? 0) !== $departmentId) {
                $this->db->table('warehouses')->where('id', $existing['id'])->update([
                    'department_id' => $departmentId,
                    'updated_at'    => $now,
                ]);
            }

            return (int) $existing['id'];
        }

        $this->db->table('warehouses')->insert([
            'company_id'    => $companyId,
            'branch_id'     => $branchId,
            'department_id' => $departmentId,
            'code'          => $code,
            'name'          => $name,
            'is_active'     => true,
            'created_at'    => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function role(int $companyId, string $code, string $name, string $now): int
    {
        $existing = $this->db->table('roles')->where(['company_id' => $companyId, 'code' => $code])->get()->getFirstRow('array');

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $this->db->table('roles')->insert([
            'company_id' => $companyId,
            'code'       => $code,
            'name'       => $name,
            'is_system'  => true,
            'status'     => 'active',
            'created_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function permission(int $companyId, string $code, string $name, string $module, string $now): int
    {
        $existing = $this->db->table('permissions')->where(['company_id' => $companyId, 'code' => $code])->get()->getFirstRow('array');

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $this->db->table('permissions')->insert([
            'company_id' => $companyId,
            'code'       => $code,
            'name'       => $name,
            'module'     => $module,
            'created_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function menu(int $companyId, string $code, string $label, string $route, string $icon, int $sortOrder, string $now): int
    {
        $existing = $this->db->table('menus')->where(['company_id' => $companyId, 'code' => $code])->get()->getFirstRow('array');

        if ($existing !== null) {
            $this->db->table('menus')->where('id', $existing['id'])->update([
                'label'      => $label,
                'route'      => $route,
                'icon'       => $icon,
                'sort_order' => $sortOrder,
                'updated_at' => $now,
            ]);

            return (int) $existing['id'];
        }

        $this->db->table('menus')->insert([
            'company_id' => $companyId,
            'code'       => $code,
            'label'      => $label,
            'route'      => $route,
            'icon'       => $icon,
            'sort_order' => $sortOrder,
            'created_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function grant(int $companyId, int $roleId, int $permissionId, string $now): void
    {
        if ($this->db->table('role_permissions')->where([
            'company_id'    => $companyId,
            'role_id'       => $roleId,
            'permission_id' => $permissionId,
        ])->countAllResults() > 0) {
            return;
        }

        $this->db->table('role_permissions')->insert([
            'company_id'    => $companyId,
            'role_id'       => $roleId,
            'permission_id' => $permissionId,
            'created_at'    => $now,
        ]);
    }

    private function menuPermission(int $companyId, int $menuId, int $permissionId, string $now): void
    {
        if ($this->db->table('menu_permissions')->where([
            'company_id'    => $companyId,
            'menu_id'       => $menuId,
            'permission_id' => $permissionId,
        ])->countAllResults() > 0) {
            return;
        }

        $this->db->table('menu_permissions')->insert([
            'company_id'    => $companyId,
            'menu_id'       => $menuId,
            'permission_id' => $permissionId,
            'created_at'    => $now,
        ]);
    }

    private function user(string $username, string $email, string $now): int
    {
        $identity = $this->db->table('auth_identities')->where([
            'type'   => 'email_password',
            'secret' => $email,
        ])->get()->getFirstRow('array');

        if ($identity !== null) {
            return (int) $identity['user_id'];
        }

        $this->db->table('users')->insert([
            'username'   => $username,
            'active'     => true,
            'created_at' => $now,
        ]);
        $userId = (int) $this->db->insertID();
        $this->db->table('auth_identities')->insert([
            'user_id'    => $userId,
            'type'       => 'email_password',
            'secret'     => $email,
            'secret2'    => service('passwords')->hash(self::DEMO_PASSWORD),
            'created_at' => $now,
        ]);

        return $userId;
    }

    private function assign(int $userId, int $companyId, string $roleCode, int $branchId, bool $isDefault, string $now): void
    {
        $role = $this->db->table('roles')->where(['company_id' => $companyId, 'code' => $roleCode])->get()->getFirstRow('array');

        if ($role === null) {
            throw new RuntimeException('Role demo tidak ditemukan: ' . $roleCode);
        }

        if ($this->db->table('user_company_memberships')->where(['company_id' => $companyId, 'user_id' => $userId])->countAllResults() === 0) {
            $this->db->table('user_company_memberships')->insert([
                'company_id' => $companyId,
                'user_id'    => $userId,
                'is_default' => $isDefault,
                'status'     => 'active',
                'created_at' => $now,
            ]);
        }

        if ($this->db->table('user_roles')->where(['company_id' => $companyId, 'user_id' => $userId, 'role_id' => $role['id']])->countAllResults() === 0) {
            $this->db->table('user_roles')->insert([
                'company_id'     => $companyId,
                'user_id'        => $userId,
                'role_id'        => $role['id'],
                'effective_from' => date('Y-m-d'),
                'created_at'     => $now,
            ]);
        }

        if ($this->db->table('user_branch_memberships')->where(['company_id' => $companyId, 'user_id' => $userId, 'branch_id' => $branchId])->countAllResults() === 0) {
            $this->db->table('user_branch_memberships')->insert([
                'company_id' => $companyId,
                'user_id'    => $userId,
                'branch_id'  => $branchId,
                'can_switch' => true,
                'status'     => 'active',
                'created_at' => $now,
            ]);
        }
    }
}
