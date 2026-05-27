<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreatePosMasterTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'          => ['type' => 'BIGINT', 'unsigned' => true],
            'branch_id'           => ['type' => 'BIGINT', 'unsigned' => true],
            'department_id'       => ['type' => 'BIGINT', 'unsigned' => true],
            'warehouse_id'        => ['type' => 'BIGINT', 'unsigned' => true],
            'default_customer_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'currency_id'         => ['type' => 'BIGINT', 'unsigned' => true],
            'transaction_code_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'code'                => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'                => ['type' => 'VARCHAR', 'constraint' => 120],
            'device_label'        => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'status'              => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at'          => ['type' => 'DATETIME'],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'          => ['type' => 'DATETIME', 'null' => true],
            'created_by'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addKey(['company_id', 'branch_id', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('department_id', 'departments', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('default_customer_id', 'customers', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('currency_id', 'currencies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('transaction_code_id', 'transaction_codes', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('pos_registers', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('pos_registers', true);
    }
}
