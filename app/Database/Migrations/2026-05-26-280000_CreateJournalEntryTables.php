<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateJournalEntryTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField($this->tenantFields([
            'gl_book_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'journal_no'     => ['type' => 'VARCHAR', 'constraint' => 50],
            'journal_date'   => ['type' => 'DATE'],
            'source_type'    => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'manual'],
            'source_id'      => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'description'    => ['type' => 'VARCHAR', 'constraint' => 200],
            'status'         => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'posted_at'      => ['type' => 'DATETIME', 'null' => true],
            'posted_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'reversal_of_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'journal_no'], 'uq_journal_company_no');
        $this->forge->addKey(['company_id', 'journal_date', 'status']);
        $this->forge->addKey(['company_id', 'source_type', 'source_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('gl_book_id', 'gl_books', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('posted_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('reversal_of_id', 'journal_entries', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('journal_entries', true);

        $this->forge->addField($this->tenantFields([
            'journal_entry_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'account_id'       => ['type' => 'BIGINT', 'unsigned' => true],
            'description'      => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'debit'            => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'credit'           => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'partner_type'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'partner_id'       => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'journal_entry_id']);
        $this->forge->addKey(['company_id', 'account_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('journal_entry_id', 'journal_entries', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('account_id', 'chart_of_accounts', 'id', 'CASCADE', 'RESTRICT');
        $this->addAuditForeignKeys();
        $this->forge->createTable('journal_entry_lines', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('journal_entry_lines', true);
        $this->forge->dropTable('journal_entries', true);
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
