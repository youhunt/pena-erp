<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGoodsReceiptTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'branch_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'warehouse_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'purchase_order_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'receipt_number' => ['type' => 'VARCHAR', 'constraint' => 50],
            'receipt_date' => ['type' => 'DATE'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'total_qty' => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => 0],
            'total_amount' => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => 0],
            'created_by' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('company_id');
        $this->forge->addKey('purchase_order_id');
        $this->forge->addKey('warehouse_id');
        $this->forge->addKey('status');
        $this->forge->createTable('goods_receipts', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'goods_receipt_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'purchase_order_item_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'product_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'warehouse_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'qty_received' => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => 0],
            'unit_cost' => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => 0],
            'line_total' => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => 0],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'created_by' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('goods_receipt_id');
        $this->forge->addKey('purchase_order_item_id');
        $this->forge->addKey('product_id');
        $this->forge->addKey('warehouse_id');
        $this->forge->addKey('status');
        $this->forge->createTable('goods_receipt_items', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('goods_receipt_items', true);
        $this->forge->dropTable('goods_receipts', true);
    }
}
