<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateDocumentProcessingReviewTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'extraction_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'field_path' => ['type' => 'VARCHAR', 'constraint' => 160],
            'field_label' => ['type' => 'VARCHAR', 'constraint' => 120],
            'extracted_value' => ['type' => 'TEXT', 'null' => true],
            'corrected_value' => ['type' => 'TEXT', 'null' => true],
            'confidence_score' => ['type' => 'DECIMAL', 'constraint' => '5,4', 'null' => true],
            'is_required' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_resolved' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'extraction_id']);
        $this->forge->addKey('field_path');
        $this->forge->createTable('document_extraction_fields', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'extraction_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'line_no' => ['type' => 'INT', 'unsigned' => true],
            'product_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'sku_hint' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255],
            'qty' => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => 0],
            'uom_text' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'uom_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'unit_price' => ['type' => 'DECIMAL', 'constraint' => '18,4', 'null' => true],
            'tax_amount' => ['type' => 'DECIMAL', 'constraint' => '18,4', 'null' => true],
            'line_total' => ['type' => 'DECIMAL', 'constraint' => '18,4', 'null' => true],
            'confidence_score' => ['type' => 'DECIMAL', 'constraint' => '5,4', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'extraction_id']);
        $this->forge->addKey('product_id');
        $this->forge->createTable('document_extraction_items', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'document_upload_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'extraction_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'rule_code' => ['type' => 'VARCHAR', 'constraint' => 80],
            'severity' => ['type' => 'VARCHAR', 'constraint' => 30],
            'message' => ['type' => 'TEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'open'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'resolved_at' => ['type' => 'DATETIME', 'null' => true],
            'resolved_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'document_upload_id']);
        $this->forge->addKey(['company_id', 'rule_code', 'status']);
        $this->forge->createTable('document_validation_logs', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'document_upload_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'extraction_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'target_module' => ['type' => 'VARCHAR', 'constraint' => 50],
            'target_table' => ['type' => 'VARCHAR', 'constraint' => 80],
            'target_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'conversion_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft_created'],
            'created_by' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'target_module', 'target_table', 'target_id']);
        $this->forge->addKey('document_upload_id');
        $this->forge->createTable('document_conversion_links', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('document_conversion_links', true);
        $this->forge->dropTable('document_validation_logs', true);
        $this->forge->dropTable('document_extraction_items', true);
        $this->forge->dropTable('document_extraction_fields', true);
    }
}
