<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateCommercialOrderTables extends Migration
{
    public function up(): void
    {
        $this->createSalesOrders();
        $this->createSalesOrderItems();
        $this->createPurchaseOrders();
        $this->createPurchaseOrderItems();
    }

    public function down(): void
    {
        $this->forge->dropTable('purchase_order_items', true);
        $this->forge->dropTable('purchase_orders', true);
        $this->forge->dropTable('sales_order_items', true);
        $this->forge->dropTable('sales_orders', true);
    }

    private function createSalesOrders(): void
    {
        $this->forge->addField($this->tenantFields([
            'branch_id'             => ['type' => 'BIGINT', 'unsigned' => true],
            'customer_id'           => ['type' => 'BIGINT', 'unsigned' => true],
            'warehouse_id'          => ['type' => 'BIGINT', 'unsigned' => true],
            'currency_id'           => ['type' => 'BIGINT', 'unsigned' => true],
            'term_id'               => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'transaction_code_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'order_no'              => ['type' => 'VARCHAR', 'constraint' => 50],
            'order_date'            => ['type' => 'DATE'],
            'requested_ship_date'   => ['type' => 'DATE', 'null' => true],
            'customer_po_no'        => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'subtotal'              => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'tax_amount'            => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'total_amount'          => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'status'                => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'document_upload_id'    => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'order_no'], 'uq_sales_order_company_no');
        $this->forge->addKey(['company_id', 'customer_id', 'status']);
        $this->forge->addKey(['company_id', 'order_date']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('currency_id', 'currencies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('term_id', 'customer_terms', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('transaction_code_id', 'transaction_codes', 'id', 'CASCADE', 'RESTRICT');
        $this->addAuditForeignKeys();
        $this->forge->createTable('sales_orders', true);
    }

    private function createSalesOrderItems(): void
    {
        $this->forge->addField($this->tenantFields([
            'sales_order_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'product_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'uom_id'         => ['type' => 'BIGINT', 'unsigned' => true],
            'tax_code_id'    => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'qty'            => ['type' => 'DECIMAL', 'constraint' => '18,4'],
            'reserved_qty'   => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => '0.0000'],
            'delivered_qty'  => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => '0.0000'],
            'unit_price'     => ['type' => 'DECIMAL', 'constraint' => '19,4'],
            'tax_rate'       => ['type' => 'DECIMAL', 'constraint' => '9,6', 'default' => '0.000000'],
            'tax_amount'     => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'line_total'     => ['type' => 'DECIMAL', 'constraint' => '19,4'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'sales_order_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('sales_order_id', 'sales_orders', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('uom_id', 'units_of_measure', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('tax_code_id', 'tax_codes', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('sales_order_items', true);
    }

    private function createPurchaseOrders(): void
    {
        $this->forge->addField($this->tenantFields([
            'branch_id'             => ['type' => 'BIGINT', 'unsigned' => true],
            'supplier_id'           => ['type' => 'BIGINT', 'unsigned' => true],
            'warehouse_id'          => ['type' => 'BIGINT', 'unsigned' => true],
            'currency_id'           => ['type' => 'BIGINT', 'unsigned' => true],
            'term_id'               => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'transaction_code_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'po_no'                 => ['type' => 'VARCHAR', 'constraint' => 50],
            'order_date'            => ['type' => 'DATE'],
            'expected_receipt_date' => ['type' => 'DATE', 'null' => true],
            'supplier_ref_no'       => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'subtotal'              => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'tax_amount'            => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'total_amount'          => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'status'                => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'document_upload_id'    => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'po_no'], 'uq_purchase_order_company_no');
        $this->forge->addKey(['company_id', 'supplier_id', 'status']);
        $this->forge->addKey(['company_id', 'order_date']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('currency_id', 'currencies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('term_id', 'supplier_terms', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('transaction_code_id', 'transaction_codes', 'id', 'CASCADE', 'RESTRICT');
        $this->addAuditForeignKeys();
        $this->forge->createTable('purchase_orders', true);
    }

    private function createPurchaseOrderItems(): void
    {
        $this->forge->addField($this->tenantFields([
            'purchase_order_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'product_id'        => ['type' => 'BIGINT', 'unsigned' => true],
            'uom_id'            => ['type' => 'BIGINT', 'unsigned' => true],
            'tax_code_id'       => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'qty'               => ['type' => 'DECIMAL', 'constraint' => '18,4'],
            'received_qty'      => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => '0.0000'],
            'unit_price'        => ['type' => 'DECIMAL', 'constraint' => '19,4'],
            'tax_rate'          => ['type' => 'DECIMAL', 'constraint' => '9,6', 'default' => '0.000000'],
            'tax_amount'        => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'line_total'        => ['type' => 'DECIMAL', 'constraint' => '19,4'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'purchase_order_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('purchase_order_id', 'purchase_orders', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('uom_id', 'units_of_measure', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('tax_code_id', 'tax_codes', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('purchase_order_items', true);
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
