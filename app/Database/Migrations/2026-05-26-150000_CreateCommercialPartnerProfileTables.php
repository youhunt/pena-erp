<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateCommercialPartnerProfileTables extends Migration
{
    public function up(): void
    {
        $this->createCustomerProfiles();
        $this->createSupplierProfiles();
    }

    public function down(): void
    {
        $this->forge->dropTable('supplier_profiles', true);
        $this->forge->dropTable('customer_profiles', true);
    }

    private function createCustomerProfiles(): void
    {
        $this->forge->addField($this->profileFields([
            'customer_id'          => ['type' => 'BIGINT', 'unsigned' => true],
            'account_manager_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'quantity_limit'       => ['type' => 'DECIMAL', 'constraint' => '19,4', 'null' => true],
            'limit_days'           => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'customer_id']);
        $this->forge->addKey(['company_id', 'default_tax_code_id']);
        $this->addCommonForeignKeys();
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('customer_profiles', true);
    }

    private function createSupplierProfiles(): void
    {
        $this->forge->addField($this->profileFields([
            'supplier_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'buyer_name'    => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'amount_limit'  => ['type' => 'DECIMAL', 'constraint' => '19,4', 'null' => true],
            'quantity_limit' => ['type' => 'DECIMAL', 'constraint' => '19,4', 'null' => true],
            'limit_days'    => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'supplier_id']);
        $this->forge->addKey(['company_id', 'default_tax_code_id']);
        $this->addCommonForeignKeys();
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('supplier_profiles', true);
    }

    /**
     * @param array<string, array<string, mixed>> $specific
     *
     * @return array<string, array<string, mixed>>
     */
    private function profileFields(array $specific): array
    {
        return [
            'id'                   => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'           => ['type' => 'BIGINT', 'unsigned' => true],
            ...$specific,
            'reference_name'       => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'contact_name'         => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'description'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'default_tax_code_id'  => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'default_warehouse_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'status'               => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at'           => ['type' => 'DATETIME'],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'           => ['type' => 'DATETIME', 'null' => true],
            'created_by'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ];
    }

    private function addCommonForeignKeys(): void
    {
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('default_tax_code_id', 'tax_codes', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('default_warehouse_id', 'warehouses', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
    }
}
