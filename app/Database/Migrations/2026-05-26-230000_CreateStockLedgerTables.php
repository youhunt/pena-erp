<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateStockLedgerTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField($this->tenantFields([
            'warehouse_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'bin_id'        => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'product_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'lot_id'        => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'qty_on_hand'   => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => '0.0000'],
            'qty_reserved'  => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => '0.0000'],
            'avg_cost'      => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'warehouse_id', 'bin_id', 'product_id', 'lot_id'], 'uq_stock_balance_key');
        $this->forge->addKey(['company_id', 'product_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('bin_id', 'warehouse_bins', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('lot_id', 'stock_lots', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('stock_balances', true);

        $this->forge->addField($this->tenantFields([
            'warehouse_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'bin_id'          => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'product_id'      => ['type' => 'BIGINT', 'unsigned' => true],
            'lot_id'          => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'movement_type'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'reference_type'  => ['type' => 'VARCHAR', 'constraint' => 40],
            'reference_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'reference_no'    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'qty'             => ['type' => 'DECIMAL', 'constraint' => '18,4'],
            'unit_cost'       => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'posted_at'       => ['type' => 'DATETIME'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'product_id', 'posted_at']);
        $this->forge->addKey(['company_id', 'reference_type', 'reference_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('bin_id', 'warehouse_bins', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('lot_id', 'stock_lots', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('stock_movements', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('stock_movements', true);
        $this->forge->dropTable('stock_balances', true);
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
