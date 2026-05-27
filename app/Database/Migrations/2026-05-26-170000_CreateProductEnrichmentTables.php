<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateProductEnrichmentTables extends Migration
{
    public function up(): void
    {
        $this->createProductProfiles();
        $this->createProductPrices();
    }

    public function down(): void
    {
        $this->forge->dropTable('product_prices', true);
        $this->forge->dropTable('product_profiles', true);
    }

    private function createProductProfiles(): void
    {
        $this->forge->addField($this->tenantFields([
            'product_id'           => ['type' => 'BIGINT', 'unsigned' => true],
            'alternate_code'       => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'alternate_name'       => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'default_warehouse_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'shelf_life_days'      => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'length_cm'            => ['type' => 'DECIMAL', 'constraint' => '12,3', 'null' => true],
            'width_cm'             => ['type' => 'DECIMAL', 'constraint' => '12,3', 'null' => true],
            'height_cm'            => ['type' => 'DECIMAL', 'constraint' => '12,3', 'null' => true],
            'weight_kg'            => ['type' => 'DECIMAL', 'constraint' => '12,4', 'null' => true],
            'package_uom_id'       => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'units_per_package'    => ['type' => 'DECIMAL', 'constraint' => '18,6', 'null' => true],
            'status'               => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'product_id']);
        $this->forge->addKey(['company_id', 'default_warehouse_id']);
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('default_warehouse_id', 'warehouses', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('package_uom_id', 'units_of_measure', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('product_profiles', true);
    }

    private function createProductPrices(): void
    {
        $this->forge->addField($this->tenantFields([
            'product_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'price_type'     => ['type' => 'VARCHAR', 'constraint' => 20],
            'currency_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'uom_id'         => ['type' => 'BIGINT', 'unsigned' => true],
            'unit_price'     => ['type' => 'DECIMAL', 'constraint' => '19,4'],
            'effective_from' => ['type' => 'DATE'],
            'effective_to'   => ['type' => 'DATE', 'null' => true],
            'status'         => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'product_id', 'price_type', 'currency_id', 'uom_id', 'effective_from'], 'uq_product_price_effective');
        $this->forge->addKey(['company_id', 'price_type', 'status']);
        $this->addTenantAuditForeignKeys();
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('currency_id', 'currencies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('uom_id', 'units_of_measure', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('product_prices', true);
    }

    /**
     * @param array<string, array<string, mixed>> $fields
     *
     * @return array<string, array<string, mixed>>
     */
    private function tenantFields(array $fields): array
    {
        return [
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            ...$fields,
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ];
    }

    private function addTenantAuditForeignKeys(): void
    {
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
    }
}
