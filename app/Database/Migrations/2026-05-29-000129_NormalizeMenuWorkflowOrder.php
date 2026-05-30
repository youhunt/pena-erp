<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class NormalizeMenuWorkflowOrder extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $orders = [
            'dashboard-workspace' => 100,
            'setup-master'        => 200,
            'inventory'           => 300,
            'purchasing-master'   => 400,
            'purchase-order'      => 410,
            'goods-receipt'       => 420,
            'sales-master'        => 500,
            'sales-order'         => 510,
            'sales-delivery'      => 520,
            'pos-master'          => 600,
            'finance-master'      => 700,
            'cash-bank'           => 710,
            'reporting'           => 800,
            'ai-document'         => 900,
            'ai-document-processing' => 900,
        ];

        foreach ($orders as $code => $sortOrder) {
            $this->db->table('menus')
                ->where('code', $code)
                ->update([
                    'sort_order' => $sortOrder,
                    'updated_at' => $now,
                ]);
        }

        // Fallback by route/label in case older seed used different menu codes.
        $routeOrders = [
            'workspace/modules/dashboard' => 100,
            'setup'                       => 200,
            'inventory'                   => 300,
            'purchasing/master'           => 400,
            'purchasing/orders'           => 410,
            'purchasing/receipts'         => 420,
            'sales/master'                => 500,
            'sales/orders'                => 510,
            'sales/deliveries'            => 520,
            'pos/master'                  => 600,
            'finance/master'              => 700,
            'finance/invoices'            => 710,
            'reports'                     => 800,
            'ai/documents'                => 900,
        ];

        foreach ($routeOrders as $route => $sortOrder) {
            $this->db->table('menus')
                ->where('route', $route)
                ->update([
                    'sort_order' => $sortOrder,
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        // No destructive rollback. Menu ordering can be changed again by a newer migration or admin UI.
    }
}
