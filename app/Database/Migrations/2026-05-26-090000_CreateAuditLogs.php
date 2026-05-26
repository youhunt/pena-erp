<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateAuditLogs extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('audit_logs')) {
            return;
        }

        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'  => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'branch_id'   => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'event_type'  => ['type' => 'VARCHAR', 'constraint' => 80],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 80],
            'entity_id'   => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'request_id'  => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'ip_address'  => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'before_hash' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'after_json'  => ['type' => 'TEXT', 'null' => true],
            'occurred_at' => ['type' => 'DATETIME'],
            'created_at'  => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'occurred_at']);
        $this->forge->addKey(['entity_type', 'entity_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'RESTRICT', 'SET NULL');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'RESTRICT', 'SET NULL');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'SET NULL');
        $this->forge->createTable('audit_logs', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('audit_logs', true);
    }
}
