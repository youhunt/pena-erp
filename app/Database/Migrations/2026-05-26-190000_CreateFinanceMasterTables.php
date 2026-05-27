<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateFinanceMasterTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'parent_id'      => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'account_code'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'account_name'   => ['type' => 'VARCHAR', 'constraint' => 120],
            'account_type'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'normal_balance' => ['type' => 'CHAR', 'constraint' => 1],
            'is_postable'    => ['type' => 'BOOLEAN', 'default' => true],
            'status'         => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at'     => ['type' => 'DATETIME'],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'     => ['type' => 'DATETIME', 'null' => true],
            'created_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'account_code'], 'uq_coa_company_code');
        $this->forge->addKey(['company_id', 'account_type', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('parent_id', 'chart_of_accounts', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('chart_of_accounts', true);

        $this->forge->addField([
            'id'                    => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'            => ['type' => 'BIGINT', 'unsigned' => true],
            'branch_id'             => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'account_id'            => ['type' => 'BIGINT', 'unsigned' => true],
            'currency_id'           => ['type' => 'BIGINT', 'unsigned' => true],
            'code'                  => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'                  => ['type' => 'VARCHAR', 'constraint' => 120],
            'account_type'          => ['type' => 'VARCHAR', 'constraint' => 20],
            'bank_name'             => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'account_number_masked' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'status'                => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at'            => ['type' => 'DATETIME'],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'            => ['type' => 'DATETIME', 'null' => true],
            'created_by'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code'], 'uq_cash_bank_company_code');
        $this->forge->addKey(['company_id', 'branch_id', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('account_id', 'chart_of_accounts', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('currency_id', 'currencies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('cash_bank_accounts', true);

        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'currency_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'rate_date'    => ['type' => 'DATE'],
            'rate_type'    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'middle'],
            'rate_to_base' => ['type' => 'DECIMAL', 'constraint' => '19,8'],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at'   => ['type' => 'DATETIME'],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
            'created_by'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'currency_id', 'rate_date', 'rate_type'], 'uq_exchange_rate_date');
        $this->forge->addKey(['company_id', 'rate_date', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('currency_id', 'currencies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('exchange_rates', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('exchange_rates', true);
        $this->forge->dropTable('cash_bank_accounts', true);
        $this->forge->dropTable('chart_of_accounts', true);
    }
}
