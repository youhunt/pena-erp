<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateCommercialMasterTables extends Migration
{
    public function up(): void
    {
        $this->createCustomerTerms();
        $this->createSupplierTerms();
        $this->createCustomers();
        $this->createSuppliers();
        $this->createCustomerAddresses();
        $this->createSupplierAddresses();
        $this->createCustomerPromotions();
        $this->createSupplierPromotions();
    }

    public function down(): void
    {
        $this->forge->dropTable('supplier_promotions', true);
        $this->forge->dropTable('customer_promotions', true);
        $this->forge->dropTable('supplier_addresses', true);
        $this->forge->dropTable('customer_addresses', true);
        $this->forge->dropTable('suppliers', true);
        $this->forge->dropTable('customers', true);
        $this->forge->dropTable('supplier_terms', true);
        $this->forge->dropTable('customer_terms', true);
    }

    private function createCustomerTerms(): void
    {
        $this->createTermsTable('customer_terms');
    }

    private function createSupplierTerms(): void
    {
        $this->createTermsTable('supplier_terms');
    }

    private function createTermsTable(string $table): void
    {
        $this->forge->addField($this->tenantFields([
            'code'          => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'due_days'      => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'discount_days' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'discount_rate' => ['type' => 'DECIMAL', 'constraint' => '9,6', 'default' => 0],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addKey(['company_id', 'status']);
        $this->addTenantAuditForeignKeys();
        $this->forge->createTable($table, true);
    }

    private function createCustomers(): void
    {
        $this->forge->addField($this->tenantFields([
            'code'            => ['type' => 'VARCHAR', 'constraint' => 40],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 180],
            'tax_no'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'email'           => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'phone'           => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'currency_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'default_term_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'credit_limit'    => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => 0],
            'status'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addKey(['company_id', 'tax_no']);
        $this->forge->addKey(['company_id', 'name']);
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey('currency_id', 'currencies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('default_term_id', 'customer_terms', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('customers', true);
    }

    private function createSuppliers(): void
    {
        $this->forge->addField($this->tenantFields([
            'code'            => ['type' => 'VARCHAR', 'constraint' => 40],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 180],
            'tax_no'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'email'           => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'phone'           => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'currency_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'default_term_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'status'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addKey(['company_id', 'tax_no']);
        $this->forge->addKey(['company_id', 'name']);
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey('currency_id', 'currencies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('default_term_id', 'supplier_terms', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('suppliers', true);
    }

    private function createCustomerAddresses(): void
    {
        $this->createPartnerAddressTable('customer_addresses', 'customer_id', 'customers');
    }

    private function createSupplierAddresses(): void
    {
        $this->createPartnerAddressTable('supplier_addresses', 'supplier_id', 'suppliers');
    }

    private function createPartnerAddressTable(string $table, string $partnerField, string $partnerTable): void
    {
        $this->forge->addField($this->tenantFields([
            $partnerField  => ['type' => 'BIGINT', 'unsigned' => true],
            'address_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'address_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'billing'],
            'is_default'   => ['type' => 'BOOLEAN', 'default' => false],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', $partnerField, 'address_id', 'address_type']);
        $this->forge->addKey(['company_id', $partnerField, 'status']);
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey($partnerField, $partnerTable, 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('address_id', 'addresses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable($table, true);
    }

    private function createCustomerPromotions(): void
    {
        $this->createPromotionTable('customer_promotions', 'customer_id', 'customers');
    }

    private function createSupplierPromotions(): void
    {
        $this->createPromotionTable('supplier_promotions', 'supplier_id', 'suppliers');
    }

    private function createPromotionTable(string $table, string $partnerField, string $partnerTable): void
    {
        $this->forge->addField($this->tenantFields([
            $partnerField    => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'code'           => ['type' => 'VARCHAR', 'constraint' => 40],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 120],
            'discount_type'  => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'percentage'],
            'discount_value' => ['type' => 'DECIMAL', 'constraint' => '19,4'],
            'starts_on'      => ['type' => 'DATE'],
            'ends_on'        => ['type' => 'DATE'],
            'status'         => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addKey(['company_id', 'starts_on', 'ends_on', 'status']);
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey($partnerField, $partnerTable, 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable($table, true);
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
