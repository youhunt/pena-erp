<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

final class ErpMenuCleanupSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $companies = $this->db->table('companies')->select('id')->where('deleted_at', null)->get()->getResultArray();
        $keep = $this->allowedCodes();

        foreach ($companies as $company) {
            $companyId = (int) $company['id'];
            $this->db->table('menus')
                ->where('company_id', $companyId)
                ->whereNotIn('code', $keep)
                ->where('deleted_at', null)
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);
        }
    }

    /** @return list<string> */
    private function allowedCodes(): array
    {
        return [
            'setup-transaction-code','setup-company','setup-site','setup-department','setup-warehouse','setup-location','setup-country','setup-province','setup-city','setup-postal-code','setup-uom','setup-uom-conversion','setup-vat','setup-item-vat','setup-address-master',
            'pos-master','pos-system',
            'sales-customer-master','sales-customer-terms','sales-customer-promo','sales-customer-address','sales-order','sales-allocation-order','sales-delivery-order','sales-period-close',
            'purchase-supplier-master','purchase-supplier-terms','purchase-supplier-promo','purchase-supplier-address','purchase-order','purchase-intransit','purchase-inventory-receipt','purchase-cost-receipt','purchase-period-close',
            'inventory-item-master','inventory-item-uom-conversion','inventory-batch-master','inventory-in-out','inventory-transfer','inventory-stock-opname','inventory-period-close',
            'planning-forecast','planning-planned-released','planning-mps','planning-mrp',
            'production-bom','production-work-center','production-routing','production-work-order','production-allocate-work-order','production-work-order-in','production-work-order-out','production-work-order-in-out','production-work-order-labor','production-period-close',
            'ap-manual-invoice','ap-purchase-invoice','ap-inventory-purchase-invoice','ap-advanced-invoice','ap-payment-invoice','ap-period-close',
            'ar-manual-invoice','ar-proforma-invoice','ar-sales-invoice','ar-inventory-sales-invoice','ar-advanced-receipt','ar-payment-receipt','ar-period-close',
            'costing-cost-type','costing-item-cost','costing-calculate-cost',
            'cash-bank-id','cash-bank-currency','cash-bank-employee-id','cash-bank-rate-master','cash-bank-cash-entry','cash-bank-bank-entry','cash-bank-bank-reconcile',
            'gl-book','gl-column','gl-account-no','gl-chart-of-account','gl-recurring','gl-entry','gl-recurring-posting','gl-period-close',
            'fa-asset-id','fa-asset-depreciation','fa-asset-period-close',
            'master-import','documents',
        ];
    }
}
