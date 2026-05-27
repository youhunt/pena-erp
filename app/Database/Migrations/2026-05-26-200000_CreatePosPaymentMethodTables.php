<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreatePosPaymentMethodTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                   => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'           => ['type' => 'BIGINT', 'unsigned' => true],
            'register_id'          => ['type' => 'BIGINT', 'unsigned' => true],
            'cash_bank_account_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'code'                 => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'                 => ['type' => 'VARCHAR', 'constraint' => 120],
            'payment_type'         => ['type' => 'VARCHAR', 'constraint' => 30],
            'is_default'           => ['type' => 'BOOLEAN', 'default' => false],
            'sort_order'           => ['type' => 'INT', 'unsigned' => true, 'default' => 10],
            'status'               => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at'           => ['type' => 'DATETIME'],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'           => ['type' => 'DATETIME', 'null' => true],
            'created_by'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'register_id', 'code'], 'uq_pos_payment_register_code');
        $this->forge->addKey(['company_id', 'register_id', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('register_id', 'pos_registers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('cash_bank_account_id', 'cash_bank_accounts', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('pos_payment_methods', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('pos_payment_methods', true);
    }
}
