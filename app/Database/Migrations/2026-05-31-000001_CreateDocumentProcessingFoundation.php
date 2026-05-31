<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateDocumentProcessingFoundation extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'branch_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'uploaded_by' => ['type' => 'INT', 'unsigned' => true],
            'original_filename' => ['type' => 'VARCHAR', 'constraint' => 255],
            'stored_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 120],
            'file_size' => ['type' => 'BIGINT', 'unsigned' => true],
            'sha256_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'document_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'source_direction' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'uploaded'],
            'confidence_score' => ['type' => 'DECIMAL', 'constraint' => '5,4', 'null' => true],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'status']);
        $this->forge->addKey(['company_id', 'sha256_hash']);
        $this->forge->addKey(['company_id', 'created_at']);
        $this->forge->addKey('uploaded_by');
        $this->forge->createTable('document_uploads', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'document_upload_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'stage' => ['type' => 'VARCHAR', 'constraint' => 50],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'queued'],
            'attempt_no' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'payload_text' => ['type' => 'TEXT', 'null' => true],
            'result_text' => ['type' => 'TEXT', 'null' => true],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'status', 'stage']);
        $this->forge->addKey(['document_upload_id', 'stage']);
        $this->forge->createTable('document_processing_jobs', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'document_upload_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'engine' => ['type' => 'VARCHAR', 'constraint' => 80],
            'language' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'ind+eng'],
            'raw_text' => ['type' => 'LONGTEXT'],
            'avg_confidence' => ['type' => 'DECIMAL', 'constraint' => '5,4', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'document_upload_id']);
        $this->forge->createTable('document_ocr_results', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'document_upload_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'ocr_result_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'provider' => ['type' => 'VARCHAR', 'constraint' => 80],
            'detected_document_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'unknown'],
            'extracted_text' => ['type' => 'LONGTEXT'],
            'confidence_score' => ['type' => 'DECIMAL', 'constraint' => '5,4', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'proposed'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'document_upload_id']);
        $this->forge->createTable('document_ai_extractions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('document_ai_extractions', true);
        $this->forge->dropTable('document_ocr_results', true);
        $this->forge->dropTable('document_processing_jobs', true);
        $this->forge->dropTable('document_uploads', true);
    }
}
