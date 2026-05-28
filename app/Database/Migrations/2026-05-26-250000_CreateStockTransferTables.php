<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateStockTransferTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField($this->tenantFields([
            'transfer_no'       => ['type' => 'VARCHAR', 'constraint' => 50],
            'from_warehouse_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'to_warehouse_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'transfer_date'     => ['type' => 'DATE'],
            'status'            => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'posted_at'         => ['type' => 'DATETIME', 'null' => true],
            'posted_by'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'transfer_no'], 'uq_stock_transfer_no');
        $this->forge->addKey(['company_id', 'from_warehouse_id', 'to_warehouse_id', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('from_warehouse_id', 'warehouses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('to_warehouse_id', 'warehouses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('posted_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('stock_transfers', true);

        $this->forge->addField($this->tenantFields([
            'stock_transfer_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'product_id'        => ['type' => 'BIGINT', 'unsigned' => true],
            'uom_id'            => ['type' => 'BIGINT', 'unsigned' => true],
            'qty'               => ['type' => 'DECIMAL', 'constraint' => '18,4'],
            'unit_cost'         => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'stock_transfer_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('stock_transfer_id', 'stock_transfers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('uom_id', 'units_of_measure', 'id', 'CASCADE', 'RESTRICT');
        $this->addAuditForeignKeys();
        $this->forge->createTable('stock_transfer_items', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('stock_transfer_items', true);
        $this->forge->dropTable('stock_transfers', true);
    }

    /**
     * @param array<string, array<string, mixed>> $fields
     *
     * @return array<string, array<string, mixed>>
     */
    private function tenantFields(array $fields): array
    {
        return [
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            ...$fields,
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ];
    }

    private function addAuditForeignKeys(): void
    {
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
    }
}
