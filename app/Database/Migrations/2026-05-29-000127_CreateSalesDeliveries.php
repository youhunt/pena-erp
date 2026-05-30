<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateSalesDeliveries extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('qty_delivered', 'sales_order_items')) {
            $this->forge->addColumn('sales_order_items', [
                'qty_delivered' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '18,4',
                    'null'       => false,
                    'default'    => '0.0000',
                    'after'      => 'qty',
                ],
            ]);
        }

        if (! $this->db->fieldExists('qty_remaining', 'sales_order_items')) {
            $this->forge->addColumn('sales_order_items', [
                'qty_remaining' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '18,4',
                    'null'       => false,
                    'default'    => '0.0000',
                    'after'      => 'qty_delivered',
                ],
            ]);

            $this->db->query('UPDATE sales_order_items SET qty_remaining = qty WHERE qty_remaining = 0');
        }

        if (! $this->db->tableExists('delivery_orders')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'company_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'branch_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'warehouse_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'sales_order_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'delivery_number' => ['type' => 'VARCHAR', 'constraint' => 60],
                'delivery_date' => ['type' => 'DATE'],
                'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
                'total_qty' => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => '0.0000'],
                'total_amount' => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => '0.0000'],
                'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'updated_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
                'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['company_id', 'status']);
            $this->forge->addKey(['sales_order_id']);
            $this->forge->addUniqueKey(['company_id', 'delivery_number'], 'uq_delivery_orders_company_number');
            $this->forge->createTable('delivery_orders', true);
        }

        if (! $this->db->tableExists('delivery_order_items')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'delivery_order_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'sales_order_item_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'product_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'warehouse_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'qty_delivered' => ['type' => 'DECIMAL', 'constraint' => '18,4'],
                'unit_price' => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => '0.0000'],
                'line_total' => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => '0.0000'],
                'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
                'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'updated_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
                'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['delivery_order_id']);
            $this->forge->addKey(['sales_order_item_id']);
            $this->forge->addKey(['product_id', 'warehouse_id']);
            $this->forge->createTable('delivery_order_items', true);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('delivery_order_items', true);
        $this->forge->dropTable('delivery_orders', true);

        if ($this->db->fieldExists('qty_remaining', 'sales_order_items')) {
            $this->forge->dropColumn('sales_order_items', 'qty_remaining');
        }

        if ($this->db->fieldExists('qty_delivered', 'sales_order_items')) {
            $this->forge->dropColumn('sales_order_items', 'qty_delivered');
        }
    }
}
