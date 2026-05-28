<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreatePosSaleTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField($this->tenantFields([
            'shift_id'      => ['type' => 'BIGINT', 'unsigned' => true],
            'register_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'customer_id'   => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'currency_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'receipt_no'    => ['type' => 'VARCHAR', 'constraint' => 50],
            'sold_at'       => ['type' => 'DATETIME'],
            'subtotal'      => ['type' => 'DECIMAL', 'constraint' => '19,4'],
            'tax_amount'    => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'total_amount'  => ['type' => 'DECIMAL', 'constraint' => '19,4'],
            'paid_amount'   => ['type' => 'DECIMAL', 'constraint' => '19,4'],
            'change_amount' => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'paid'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'receipt_no'], 'uq_pos_sale_receipt');
        $this->forge->addKey(['company_id', 'shift_id', 'sold_at']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('shift_id', 'pos_shifts', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('register_id', 'pos_registers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('currency_id', 'currencies', 'id', 'CASCADE', 'RESTRICT');
        $this->addAuditForeignKeys();
        $this->forge->createTable('pos_sales', true);

        $this->forge->addField($this->tenantFields([
            'pos_sale_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'product_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'uom_id'      => ['type' => 'BIGINT', 'unsigned' => true],
            'tax_code_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'qty'         => ['type' => 'DECIMAL', 'constraint' => '18,4'],
            'unit_price'  => ['type' => 'DECIMAL', 'constraint' => '19,4'],
            'tax_rate'    => ['type' => 'DECIMAL', 'constraint' => '9,6', 'default' => '0.000000'],
            'tax_amount'  => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'line_total'  => ['type' => 'DECIMAL', 'constraint' => '19,4'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'pos_sale_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('pos_sale_id', 'pos_sales', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('uom_id', 'units_of_measure', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('tax_code_id', 'tax_codes', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('pos_sale_items', true);

        $this->forge->addField($this->tenantFields([
            'pos_sale_id'       => ['type' => 'BIGINT', 'unsigned' => true],
            'payment_method_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'amount'            => ['type' => 'DECIMAL', 'constraint' => '19,4'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'pos_sale_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('pos_sale_id', 'pos_sales', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('payment_method_id', 'pos_payment_methods', 'id', 'CASCADE', 'RESTRICT');
        $this->addAuditForeignKeys();
        $this->forge->createTable('pos_sale_payments', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('pos_sale_payments', true);
        $this->forge->dropTable('pos_sale_items', true);
        $this->forge->dropTable('pos_sales', true);
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

    private function addAuditForeignKeys(): void
    {
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL');
    }
}
