<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateInventoryMasterTables extends Migration
{
    public function up(): void
    {
        $this->createUnitsOfMeasure();
        $this->createProductCategories();
        $this->createProducts();
        $this->createWarehouses();
    }

    public function down(): void
    {
        $this->forge->dropTable('warehouses', true);
        $this->forge->dropTable('products', true);
        $this->forge->dropTable('product_categories', true);
        $this->forge->dropTable('units_of_measure', true);
    }

    private function createUnitsOfMeasure(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'code'       => ['type' => 'VARCHAR', 'constraint' => 20],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 60],
            'precision'  => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'status'     => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addKey(['company_id', 'status']);
        $this->addTenantAuditForeignKeys();
        $this->forge->createTable('units_of_measure', true);
    }

    private function createProductCategories(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'parent_id'  => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'code'       => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 120],
            'status'     => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addKey(['company_id', 'parent_id']);
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey('parent_id', 'product_categories', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('product_categories', true);
    }

    private function createProducts(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'category_id'   => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'sku'           => ['type' => 'VARCHAR', 'constraint' => 60],
            'barcode'       => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 180],
            'base_uom_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'product_type'  => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'stock'],
            'track_lot'     => ['type' => 'BOOLEAN', 'default' => false],
            'standard_cost' => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => 0],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at'    => ['type' => 'DATETIME'],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_by'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'sku']);
        $this->forge->addKey(['company_id', 'barcode']);
        $this->forge->addKey(['company_id', 'name']);
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey('category_id', 'product_categories', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('base_uom_id', 'units_of_measure', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('products', true);
    }

    private function createWarehouses(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'branch_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'code'        => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 120],
            'address'     => ['type' => 'TEXT', 'null' => true],
            'village_id'  => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'postal_code' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'is_active'   => ['type' => 'BOOLEAN', 'default' => true],
            'created_at'  => ['type' => 'DATETIME'],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true],
            'created_by'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'branch_id', 'code']);
        $this->forge->addKey(['company_id', 'branch_id', 'is_active']);
        $this->forge->addKey('village_id');
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('village_id', 'villages', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('warehouses', true);
    }

    private function addTenantAuditForeignKeys(): void
    {
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
    }
}
