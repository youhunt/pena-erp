<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreatePosShiftTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'      => ['type' => 'BIGINT', 'unsigned' => true],
            'register_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'cashier_user_id' => ['type' => 'INT', 'unsigned' => true],
            'opened_at'       => ['type' => 'DATETIME'],
            'opening_cash'    => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'closed_at'       => ['type' => 'DATETIME', 'null' => true],
            'closing_cash'    => ['type' => 'DECIMAL', 'constraint' => '19,4', 'null' => true],
            'status'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'open'],
            'created_at'      => ['type' => 'DATETIME'],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
            'created_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'register_id', 'status']);
        $this->forge->addKey(['company_id', 'cashier_user_id', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('register_id', 'pos_registers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('cashier_user_id', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('pos_shifts', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('pos_shifts', true);
    }
}
