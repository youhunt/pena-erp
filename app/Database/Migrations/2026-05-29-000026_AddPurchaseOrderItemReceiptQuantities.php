<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class AddPurchaseOrderItemReceiptQuantities extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('purchase_order_items')) {
            return;
        }

        $fields = $this->db->getFieldNames('purchase_order_items');
        $add    = [];

        if (! in_array('qty_ordered', $fields, true)) {
            $add['qty_ordered'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,4',
                'null'       => false,
                'default'    => '0.0000',
                'after'      => 'qty',
            ];
        }

        if (! in_array('qty_remaining', $fields, true)) {
            $add['qty_remaining'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,4',
                'null'       => false,
                'default'    => '0.0000',
                'after'      => in_array('qty_ordered', $fields, true) ? 'qty_ordered' : 'qty',
            ];
        }

        if ($add !== []) {
            $this->forge->addColumn('purchase_order_items', $add);
        }

        $fields = $this->db->getFieldNames('purchase_order_items');

        if (in_array('qty', $fields, true) && in_array('qty_ordered', $fields, true) && in_array('qty_remaining', $fields, true)) {
            $this->db->query(
                'UPDATE purchase_order_items
                 SET qty_ordered = CASE WHEN qty_ordered = 0 THEN qty ELSE qty_ordered END,
                     qty_remaining = CASE WHEN qty_remaining = 0 THEN qty ELSE qty_remaining END
                 WHERE deleted_at IS NULL'
            );
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('purchase_order_items')) {
            return;
        }

        $fields = $this->db->getFieldNames('purchase_order_items');

        if (in_array('qty_remaining', $fields, true)) {
            $this->forge->dropColumn('purchase_order_items', 'qty_remaining');
        }

        $fields = $this->db->getFieldNames('purchase_order_items');

        if (in_array('qty_ordered', $fields, true)) {
            $this->forge->dropColumn('purchase_order_items', 'qty_ordered');
        }
    }
}
