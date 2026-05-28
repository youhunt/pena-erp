<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateFiscalPeriodCloseTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField($this->tenantFields([
            'year'      => ['type' => 'SMALLINT', 'unsigned' => true],
            'period'    => ['type' => 'TINYINT', 'unsigned' => true],
            'starts_on' => ['type' => 'DATE'],
            'ends_on'   => ['type' => 'DATE'],
            'status'    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'open'],
            'locked_at' => ['type' => 'DATETIME', 'null' => true],
            'locked_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'year', 'period'], 'uq_fiscal_period_company_period');
        $this->forge->addKey(['company_id', 'status', 'starts_on']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('locked_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('fiscal_periods', true);

        $this->forge->addField($this->tenantFields([
            'fiscal_period_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'module_code'      => ['type' => 'VARCHAR', 'constraint' => 30],
            'status'           => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'open'],
            'closed_at'        => ['type' => 'DATETIME', 'null' => true],
            'closed_by'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'reopened_at'      => ['type' => 'DATETIME', 'null' => true],
            'reopened_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'fiscal_period_id', 'module_code'], 'uq_module_period_close');
        $this->forge->addKey(['company_id', 'module_code', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('fiscal_period_id', 'fiscal_periods', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('closed_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('reopened_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('module_period_closes', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('module_period_closes', true);
        $this->forge->dropTable('fiscal_periods', true);
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
