<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateFoundationMasterTables extends Migration
{
    public function up(): void
    {
        $this->createProvinces();
        $this->createRegencies();
        $this->createDistricts();
        $this->createVillages();
        $this->createCompanies();
        $this->createBranches();
    }

    public function down(): void
    {
        $this->forge->dropTable('branches', true);
        $this->forge->dropTable('companies', true);
        $this->forge->dropTable('villages', true);
        $this->forge->dropTable('districts', true);
        $this->forge->dropTable('regencies', true);
        $this->forge->dropTable('provinces', true);
    }

    private function createProvinces(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'code'           => ['type' => 'VARCHAR', 'constraint' => 10],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 100],
            'source_version' => ['type' => 'VARCHAR', 'constraint' => 40],
            'is_active'      => ['type' => 'BOOLEAN', 'default' => true],
            'created_at'     => ['type' => 'DATETIME'],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('name');
        $this->forge->createTable('provinces', true);
    }

    private function createRegencies(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'province_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'code'           => ['type' => 'VARCHAR', 'constraint' => 10],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 120],
            'type'           => ['type' => 'VARCHAR', 'constraint' => 20],
            'source_version' => ['type' => 'VARCHAR', 'constraint' => 40],
            'is_active'      => ['type' => 'BOOLEAN', 'default' => true],
            'created_at'     => ['type' => 'DATETIME'],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey(['province_id', 'name']);
        $this->forge->addForeignKey('province_id', 'provinces', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('regencies', true);
    }

    private function createDistricts(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'regency_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'code'           => ['type' => 'VARCHAR', 'constraint' => 15],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 120],
            'source_version' => ['type' => 'VARCHAR', 'constraint' => 40],
            'is_active'      => ['type' => 'BOOLEAN', 'default' => true],
            'created_at'     => ['type' => 'DATETIME'],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey(['regency_id', 'name']);
        $this->forge->addForeignKey('regency_id', 'regencies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('districts', true);
    }

    private function createVillages(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'district_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'code'           => ['type' => 'VARCHAR', 'constraint' => 20],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 120],
            'type'           => ['type' => 'VARCHAR', 'constraint' => 20],
            'postal_code'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'source_version' => ['type' => 'VARCHAR', 'constraint' => 40],
            'is_active'      => ['type' => 'BOOLEAN', 'default' => true],
            'created_at'     => ['type' => 'DATETIME'],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey(['district_id', 'name']);
        $this->forge->addForeignKey('district_id', 'districts', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('villages', true);
    }

    private function createCompanies(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'code'          => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 150],
            'tax_no'        => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'address'       => ['type' => 'TEXT', 'null' => true],
            'village_id'    => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'postal_code'   => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'base_currency' => ['type' => 'CHAR', 'constraint' => 3, 'default' => 'IDR'],
            'timezone'      => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Asia/Jakarta'],
            'branding_json' => ['type' => 'TEXT', 'null' => true],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at'    => ['type' => 'DATETIME'],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_by'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('status');
        $this->forge->addKey('village_id');
        $this->forge->addForeignKey('village_id', 'villages', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('companies', true);
    }

    private function createBranches(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'code'           => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 150],
            'address'        => ['type' => 'TEXT', 'null' => true],
            'village_id'     => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'postal_code'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'is_head_office' => ['type' => 'BOOLEAN', 'default' => false],
            'status'         => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at'     => ['type' => 'DATETIME'],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'     => ['type' => 'DATETIME', 'null' => true],
            'created_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addKey(['company_id', 'status']);
        $this->forge->addKey('village_id');
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('village_id', 'villages', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('branches', true);
    }
}
