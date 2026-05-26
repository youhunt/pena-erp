<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateUserSessionSecurity extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('user_session_security')) {
            return;
        }

        $this->forge->addField([
            'user_id'             => ['type' => 'INT', 'unsigned' => true],
            'security_version'    => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'sessions_revoked_at' => ['type' => 'DATETIME', 'null' => true],
            'last_reason'         => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'created_at'          => ['type' => 'DATETIME'],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_by'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('user_id', true);
        $this->forge->addKey('sessions_revoked_at');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'RESTRICT', 'SET NULL');
        $this->forge->createTable('user_session_security', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('user_session_security', true);
    }
}
