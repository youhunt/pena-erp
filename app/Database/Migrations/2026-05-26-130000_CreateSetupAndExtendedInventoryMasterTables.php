<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateSetupAndExtendedInventoryMasterTables extends Migration
{
    public function up(): void
    {
        $this->createCountries();
        $this->createDepartments();
        $this->createTransactionCodes();
        $this->createAddresses();
        $this->createCurrencies();
        $this->createTaxCodes();
        $this->createWarehouseBins();
        $this->createProductUomConversions();
        $this->createProductTaxCodes();
        $this->createStockLots();
    }

    public function down(): void
    {
        $this->forge->dropTable('stock_lots', true);
        $this->forge->dropTable('product_tax_codes', true);
        $this->forge->dropTable('product_uom_conversions', true);
        $this->forge->dropTable('warehouse_bins', true);
        $this->forge->dropTable('tax_codes', true);
        $this->forge->dropTable('currencies', true);
        $this->forge->dropTable('addresses', true);
        $this->forge->dropTable('transaction_codes', true);
        $this->forge->dropTable('departments', true);
        $this->forge->dropTable('countries', true);
    }

    private function createCountries(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'iso2'       => ['type' => 'CHAR', 'constraint' => 2],
            'iso3'       => ['type' => 'CHAR', 'constraint' => 3],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'phone_code' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'is_active'  => ['type' => 'BOOLEAN', 'default' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('iso2');
        $this->forge->addUniqueKey('iso3');
        $this->forge->addKey('name');
        $this->forge->createTable('countries', true);
    }

    private function createDepartments(): void
    {
        $this->forge->addField($this->tenantFields([
            'code'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'   => ['type' => 'VARCHAR', 'constraint' => 120],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addKey(['company_id', 'status']);
        $this->addTenantAuditForeignKeys();
        $this->forge->createTable('departments', true);
    }

    private function createTransactionCodes(): void
    {
        $this->forge->addField($this->tenantFields([
            'branch_id'     => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'module'        => ['type' => 'VARCHAR', 'constraint' => 30],
            'code'          => ['type' => 'VARCHAR', 'constraint' => 40],
            'prefix'        => ['type' => 'VARCHAR', 'constraint' => 30],
            'next_number'   => ['type' => 'BIGINT', 'unsigned' => true, 'default' => 1],
            'number_length' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 6],
            'reset_rule'    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'never'],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'branch_id', 'code']);
        $this->forge->addKey(['company_id', 'module', 'status']);
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('transaction_codes', true);
    }

    private function createAddresses(): void
    {
        $this->forge->addField($this->tenantFields([
            'code'          => ['type' => 'VARCHAR', 'constraint' => 40],
            'label'         => ['type' => 'VARCHAR', 'constraint' => 120],
            'address_line1' => ['type' => 'TEXT'],
            'country_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'village_id'    => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'postal_code'   => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addKey(['company_id', 'status']);
        $this->forge->addKey('village_id');
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey('country_id', 'countries', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('village_id', 'villages', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('addresses', true);
    }

    private function createCurrencies(): void
    {
        $this->forge->addField($this->tenantFields([
            'code'    => ['type' => 'CHAR', 'constraint' => 3],
            'name'    => ['type' => 'VARCHAR', 'constraint' => 60],
            'is_base' => ['type' => 'BOOLEAN', 'default' => false],
            'status'  => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addKey(['company_id', 'status']);
        $this->addTenantAuditForeignKeys();
        $this->forge->createTable('currencies', true);
    }

    private function createTaxCodes(): void
    {
        $this->forge->addField($this->tenantFields([
            'code'     => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'     => ['type' => 'VARCHAR', 'constraint' => 80],
            'tax_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'both'],
            'rate'     => ['type' => 'DECIMAL', 'constraint' => '9,6'],
            'status'   => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addKey(['company_id', 'status']);
        $this->addTenantAuditForeignKeys();
        $this->forge->createTable('tax_codes', true);
    }

    private function createWarehouseBins(): void
    {
        $this->forge->addField($this->tenantFields([
            'branch_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'warehouse_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'code'         => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 80],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'warehouse_id', 'code']);
        $this->forge->addKey(['company_id', 'branch_id', 'status']);
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('warehouse_bins', true);
    }

    private function createProductUomConversions(): void
    {
        $this->forge->addField($this->tenantFields([
            'product_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'from_uom_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'to_uom_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'factor'      => ['type' => 'DECIMAL', 'constraint' => '18,6'],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'product_id', 'from_uom_id', 'to_uom_id']);
        $this->forge->addKey(['company_id', 'status']);
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('from_uom_id', 'units_of_measure', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('to_uom_id', 'units_of_measure', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('product_uom_conversions', true);
    }

    private function createProductTaxCodes(): void
    {
        $this->forge->addField($this->tenantFields([
            'product_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'tax_code_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'usage_type'  => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'sales'],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'product_id', 'tax_code_id', 'usage_type']);
        $this->forge->addKey(['company_id', 'status']);
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('tax_code_id', 'tax_codes', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('product_tax_codes', true);
    }

    private function createStockLots(): void
    {
        $this->forge->addField($this->tenantFields([
            'product_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'lot_no'      => ['type' => 'VARCHAR', 'constraint' => 60],
            'expiry_date' => ['type' => 'DATE', 'null' => true],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'product_id', 'lot_no']);
        $this->forge->addKey(['company_id', 'expiry_date', 'status']);
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('stock_lots', true);
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

    private function addTenantAuditForeignKeys(): void
    {
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
    }
}
