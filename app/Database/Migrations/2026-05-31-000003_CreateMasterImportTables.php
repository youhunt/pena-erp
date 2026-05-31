<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateMasterImportTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'branch_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'import_type' => ['type' => 'VARCHAR', 'constraint' => 80],
            'original_filename' => ['type' => 'VARCHAR', 'constraint' => 255],
            'stored_path' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'file_hash' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'uploaded'],
            'total_rows' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'valid_rows' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'error_rows' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'imported_rows' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'started_at' => ['type' => 'DATETIME', 'null' => true],
            'finished_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'import_type', 'status']);
        $this->forge->addKey(['company_id', 'created_at']);
        $this->forge->addKey('created_by');
        $this->forge->createTable('master_import_batches', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'batch_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'row_number' => ['type' => 'INT', 'unsigned' => true],
            'row_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'pending'],
            'raw_data' => ['type' => 'LONGTEXT'],
            'mapped_data' => ['type' => 'LONGTEXT', 'null' => true],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'target_table' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'target_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'batch_id']);
        $this->forge->addKey(['batch_id', 'row_status']);
        $this->forge->addKey(['target_table', 'target_id']);
        $this->forge->createTable('master_import_rows', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('master_import_rows', true);
        $this->forge->dropTable('master_import_batches', true);
    }
}
