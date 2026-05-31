<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

final class ErpMenuStructureSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $companies = $this->db->table('companies')->select('id')->where('deleted_at', null)->get()->getResultArray();

        foreach ($companies as $company) {
            $companyId = (int) $company['id'];
            $permissionId = $this->permission($companyId, $now);
            $sort = 1000;

            foreach ($this->rows() as $row) {
                [$code, $label, $route, $icon] = explode('|', $row);
                $sort += 10;
                $menuId = $this->upsertMenu($companyId, $code, $label, $route, $icon, $sort, $now);
                $this->linkPermission($companyId, $menuId, $permissionId, $now);
            }

            $this->grantToActiveRoles($companyId, $permissionId, $now);
        }
    }

    /** @return list<string> */
    private function rows(): array
    {
        return [
            'setup-transaction-code|Transaction Code|setup|bx bx-hash',
            'setup-company|Company|administration/companies|bx bx-buildings',
            'setup-site|Site|administration/branches|bx bx-map-pin',
            'setup-department|Department|setup|bx bx-sitemap',
            'setup-warehouse|Warehouse|inventory|bx bx-store-alt',
            'setup-location|Location|inventory|bx bx-map',
            'setup-country|Country|administration/regions|bx bx-globe',
            'setup-province|Province|administration/regions|bx bx-map-alt',
            'setup-city|City|administration/regions|bx bx-buildings',
            'setup-postal-code|Postal Code|administration/regions|bx bx-envelope',
            'setup-uom|Unit of Measure|inventory|bx bx-ruler',
            'setup-uom-conversion|UoM Conversion|inventory|bx bx-transfer',
            'setup-vat|VAT|setup|bx bx-receipt',
            'setup-item-vat|Item VAT|inventory|bx bx-purchase-tag',
            'setup-address-master|Address Master|setup|bx bx-address-book',
            'pos-master|Master / POS Master|pos/master|bx bx-calculator',
            'pos-system|Transactions / POS System|pos/master|bx bx-desktop',
            'sales-customer-master|Master / Customer Master|sales/customers|bx bx-user',
            'sales-customer-terms|Master / Customer Terms|sales/master|bx bx-calendar-check',
            'sales-customer-promo|Master / Customer Promo|sales/master|bx bx-gift',
            'sales-customer-address|Master / Customer Address|sales/master|bx bx-map',
            'sales-order|Transactions / Sales Order|sales/orders|bx bx-notepad',
            'sales-allocation-order|Transactions / Allocation Order|workspace/modules/sales-allocation-order|bx bx-check-square',
            'sales-delivery-order|Transactions / Delivery Order|sales/deliveries|bx bx-package',
            'sales-period-close|Sales Period Close|workspace/modules/sales-period-close|bx bx-lock',
            'purchase-supplier-master|Master / Supplier Master|purchasing/suppliers|bx bx-user-pin',
            'purchase-supplier-terms|Master / Supplier Terms|purchasing/master|bx bx-calendar',
            'purchase-supplier-promo|Master / Supplier Promo|purchasing/master|bx bx-gift',
            'purchase-supplier-address|Master / Supplier Address|purchasing/master|bx bx-map',
            'purchase-order|Transactions / Purchase Order|purchasing/orders|bx bx-cart',
            'purchase-intransit|Transactions / Purchase Intransit|workspace/modules/purchase-intransit|bx bx-trip',
            'purchase-inventory-receipt|Transactions / Inventory Purchase Receipt|purchasing/receipts|bx bx-download',
            'purchase-cost-receipt|Transactions / Cost Purchase Receipt|workspace/modules/purchase-cost-receipt|bx bx-money',
            'purchase-period-close|Purchase Period Close|workspace/modules/purchase-period-close|bx bx-lock',
            'inventory-item-master|Master / Item Master|inventory|bx bx-box',
            'inventory-item-uom-conversion|Master / Item UoM Conversion|inventory|bx bx-transfer',
            'inventory-batch-master|Master / Batch Master|inventory|bx bx-barcode',
            'inventory-in-out|Transactions / Inventory In Out|inventory|bx bx-log-in-circle',
            'inventory-transfer|Transactions / Inventory Transfer|inventory|bx bx-transfer-alt',
            'inventory-stock-opname|Transactions / Inventory Stock Opname|workspace/modules/inventory-stock-opname|bx bx-clipboard',
            'inventory-period-close|Inventory Period Close|workspace/modules/inventory-period-close|bx bx-lock',
            'planning-forecast|Forecast|workspace/modules/planning-forecast|bx bx-line-chart',
            'planning-planned-released|Planned Released|workspace/modules/planning-planned-released|bx bx-calendar-event',
            'planning-mps|MPS|workspace/modules/planning-mps|bx bx-grid',
            'planning-mrp|MRP|workspace/modules/planning-mrp|bx bx-network-chart',
            'production-bom|Master / BOM|workspace/modules/production-bom|bx bx-list-ul',
            'production-work-center|Master / Work Center|workspace/modules/production-work-center|bx bx-building-house',
            'production-routing|Master / Routing|workspace/modules/production-routing|bx bx-git-branch',
            'production-work-order|Transactions / Work Order|workspace/modules/production-work-order|bx bx-task',
            'production-allocate-work-order|Transactions / Allocate Work Order|workspace/modules/production-allocate-work-order|bx bx-select-multiple',
            'production-work-order-in|Transactions / Work Order In|workspace/modules/production-work-order-in|bx bx-log-in',
            'production-work-order-out|Transactions / Work Order Out|workspace/modules/production-work-order-out|bx bx-log-out',
            'production-work-order-in-out|Transactions / Work Order In Out|workspace/modules/production-work-order-in-out|bx bx-transfer',
            'production-work-order-labor|Transactions / Work Order Labor|workspace/modules/production-work-order-labor|bx bx-user-voice',
            'production-period-close|Production Period Close|workspace/modules/production-period-close|bx bx-lock',
            'ap-manual-invoice|Transactions / Manual A/P Invoice|finance/invoices|bx bx-receipt',
            'ap-purchase-invoice|Transactions / Purchase Invoice|finance/invoices|bx bx-receipt',
            'ap-inventory-purchase-invoice|Transactions / Inventory Purchase Invoice|finance/invoices|bx bx-receipt',
            'ap-advanced-invoice|Transactions / Advanced A/P Invoice|workspace/modules/ap-advanced-invoice|bx bx-receipt',
            'ap-payment-invoice|Transactions / Payment Invoice|finance/invoices|bx bx-credit-card',
            'ap-period-close|A/P Period Close|workspace/modules/ap-period-close|bx bx-lock',
            'ar-manual-invoice|Transactions / Manual A/R Invoice|finance/invoices|bx bx-receipt',
            'ar-proforma-invoice|Transactions / Proforma Invoice|workspace/modules/ar-proforma-invoice|bx bx-file',
            'ar-sales-invoice|Transactions / Sales Invoice|finance/invoices|bx bx-receipt',
            'ar-inventory-sales-invoice|Transactions / Inventory Sales Invoice|finance/invoices|bx bx-receipt',
            'ar-advanced-receipt|Transactions / Advanced A/R Receipt|workspace/modules/ar-advanced-receipt|bx bx-wallet',
            'ar-payment-receipt|Transactions / Payment Receipt|finance/invoices|bx bx-money',
            'ar-period-close|A/R Period Close|workspace/modules/ar-period-close|bx bx-lock',
            'costing-cost-type|Master / Cost Type|finance/master|bx bx-purchase-tag',
            'costing-item-cost|Master / Item Cost|finance/master|bx bx-dollar-circle',
            'costing-calculate-cost|Transactions / Calculate Cost|workspace/modules/costing-calculate-cost|bx bx-calculator',
            'cash-bank-id|Master / Cash Bank ID|finance/master|bx bx-bank',
            'cash-bank-currency|Master / Currency|setup|bx bx-coin',
            'cash-bank-employee-id|Master / Employee ID|workspace/modules/cash-bank-employee-id|bx bx-id-card',
            'cash-bank-rate-master|Master / Rate Master|finance/master|bx bx-trending-up',
            'cash-bank-cash-entry|Transactions / Cash Entry|workspace/modules/cash-bank-cash-entry|bx bx-money',
            'cash-bank-bank-entry|Transactions / Bank Entry|workspace/modules/cash-bank-bank-entry|bx bx-bank',
            'cash-bank-bank-reconcile|Transactions / Bank Reconcile|workspace/modules/cash-bank-bank-reconcile|bx bx-check-double',
            'gl-book|Master / GL Book|finance/master|bx bx-book',
            'gl-column|Master / GL Column|finance/master|bx bx-columns',
            'gl-account-no|Master / Account No.|finance/master|bx bx-list-ol',
            'gl-chart-of-account|Master / Chart of Account|finance/master|bx bx-sitemap',
            'gl-recurring|Master / Recurring|workspace/modules/gl-recurring|bx bx-repeat',
            'gl-entry|Transactions / GL Entry|finance/master|bx bx-edit',
            'gl-recurring-posting|Transactions / Recurring Posting|workspace/modules/gl-recurring-posting|bx bx-repeat',
            'gl-period-close|GL Period Close|workspace/modules/gl-period-close|bx bx-lock',
            'fa-asset-id|Master / Asset ID|workspace/modules/fa-asset-id|bx bx-building',
            'fa-asset-depreciation|Transactions / Asset Depreciation|workspace/modules/fa-asset-depreciation|bx bx-line-chart',
            'fa-asset-period-close|Asset Period Close|workspace/modules/fa-asset-period-close|bx bx-lock',
            'master-import|Master Data Import|workspace/modules/master-import|bx bx-upload',
            'documents|AI Document Processing|workspace/modules/documents|bx bx-scan',
        ];
    }

    private function permission(int $companyId, string $now): int
    {
        $existing = $this->db->table('permissions')->where(['company_id' => $companyId, 'code' => 'erp.menu.access'])->get()->getFirstRow('array');
        if ($existing !== null) {
            return (int) $existing['id'];
        }
        $this->db->table('permissions')->insert(['company_id' => $companyId, 'code' => 'erp.menu.access', 'name' => 'Akses Menu ERP', 'module' => 'erp-menu', 'created_at' => $now]);
        return (int) $this->db->insertID();
    }

    private function upsertMenu(int $companyId, string $code, string $label, string $route, string $icon, int $sort, string $now): int
    {
        $existing = $this->db->table('menus')->where(['company_id' => $companyId, 'code' => $code])->get()->getFirstRow('array');
        $payload = ['label' => $label, 'route' => $route, 'icon' => $icon, 'sort_order' => $sort, 'updated_at' => $now];
        if ($existing !== null) {
            $this->db->table('menus')->where('id', $existing['id'])->update($payload);
            return (int) $existing['id'];
        }
        $this->db->table('menus')->insert(['company_id' => $companyId, 'code' => $code, 'created_at' => $now] + $payload);
        return (int) $this->db->insertID();
    }

    private function linkPermission(int $companyId, int $menuId, int $permissionId, string $now): void
    {
        $exists = $this->db->table('menu_permissions')->where(['company_id' => $companyId, 'menu_id' => $menuId, 'permission_id' => $permissionId])->countAllResults() > 0;
        if (! $exists) {
            $this->db->table('menu_permissions')->insert(['company_id' => $companyId, 'menu_id' => $menuId, 'permission_id' => $permissionId, 'created_at' => $now]);
        }
    }

    private function grantToActiveRoles(int $companyId, int $permissionId, string $now): void
    {
        $roles = $this->db->table('roles')->select('id')->where(['company_id' => $companyId, 'status' => 'active'])->get()->getResultArray();
        foreach ($roles as $role) {
            $exists = $this->db->table('role_permissions')->where(['company_id' => $companyId, 'role_id' => (int) $role['id'], 'permission_id' => $permissionId])->countAllResults() > 0;
            if (! $exists) {
                $this->db->table('role_permissions')->insert(['company_id' => $companyId, 'role_id' => (int) $role['id'], 'permission_id' => $permissionId, 'created_at' => $now]);
            }
        }
    }
}
