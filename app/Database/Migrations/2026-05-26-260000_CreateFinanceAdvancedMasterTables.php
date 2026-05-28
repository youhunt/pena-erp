<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateFinanceAdvancedMasterTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField($this->tenantFields([
            'currency_id'                  => ['type' => 'BIGINT', 'unsigned' => true],
            'retained_earnings_account_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'code'                         => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'                         => ['type' => 'VARCHAR', 'constraint' => 120],
            'book_type'                    => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'primary'],
            'status'                       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code'], 'uq_gl_book_company_code');
        $this->forge->addKey(['company_id', 'book_type', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('currency_id', 'currencies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('retained_earnings_account_id', 'chart_of_accounts', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('gl_books', true);

        $this->forge->addField($this->tenantFields([
            'code'        => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 120],
            'column_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'actual'],
            'sequence_no' => ['type' => 'INT', 'unsigned' => true, 'default' => 10],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code'], 'uq_gl_column_company_code');
        $this->forge->addKey(['company_id', 'column_type', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->addAuditForeignKeys();
        $this->forge->createTable('gl_columns', true);

        $this->forge->addField($this->tenantFields([
            'code'             => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'             => ['type' => 'VARCHAR', 'constraint' => 120],
            'valuation_method' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'standard'],
            'status'           => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code'], 'uq_cost_type_company_code');
        $this->forge->addKey(['company_id', 'valuation_method', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->addAuditForeignKeys();
        $this->forge->createTable('cost_types', true);

        $this->forge->addField($this->tenantFields([
            'product_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'cost_type_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'currency_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'unit_cost'      => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'effective_from' => ['type' => 'DATE'],
            'status'         => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'product_id', 'cost_type_id', 'currency_id', 'effective_from'], 'uq_item_cost_effective');
        $this->forge->addKey(['company_id', 'product_id', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('cost_type_id', 'cost_types', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('currency_id', 'currencies', 'id', 'CASCADE', 'RESTRICT');
        $this->addAuditForeignKeys();
        $this->forge->createTable('item_costs', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('item_costs', true);
        $this->forge->dropTable('cost_types', true);
        $this->forge->dropTable('gl_columns', true);
        $this->forge->dropTable('gl_books', true);
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
