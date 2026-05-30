<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class UpdateWorkflowMenuOrderByLabel extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $orders = [
            'Dashboard Workspace' => 100,
            'Setup Master' => 200,
            'Inventory' => 300,
            'Purchasing Master' => 400,
            'Purchase Order' => 410,
            'Goods Receipt' => 420,
            'Sales Master' => 500,
            'Sales Order' => 510,
            'Delivery Order' => 520,
            'POS Master' => 600,
            'Accounting & Finance' => 700,
            'Cash & Bank' => 710,
            'Reporting' => 800,
            'AI Document Processing' => 900,
        ];

        foreach ($orders as $label => $sortOrder) {
            $this->db->table('menus')
                ->where('label', $label)
                ->update([
                    'sort_order' => $sortOrder,
                    'updated_at' => $now,
                ]);
        }

        $routes = [
            'workspace/modules/dashboard' => 100,
            'setup' => 200,
            'inventory' => 300,
            'purchasing/master' => 400,
            'purchasing/orders' => 410,
            'purchasing/receipts' => 420,
            'sales/master' => 500,
            'sales/orders' => 510,
            'sales/deliveries' => 520,
            'pos/master' => 600,
            'finance/master' => 700,
            'finance/invoices' => 710,
            'reports' => 800,
            'ai/documents' => 900,
        ];

        foreach ($routes as $route => $sortOrder) {
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
        // Non-destructive ordering migration.
    }
}
