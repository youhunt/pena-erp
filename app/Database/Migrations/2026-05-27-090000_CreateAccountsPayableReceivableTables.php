<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateAccountsPayableReceivableTables extends Migration
{
    public function up(): void
    {
        $this->createPurchaseInvoices();
        $this->createPurchaseInvoiceItems();
        $this->createSalesInvoices();
        $this->createSalesInvoiceItems();
        $this->createPayments();
        $this->createPaymentAllocations();
    }

    public function down(): void
    {
        $this->forge->dropTable('payment_allocations', true);
        $this->forge->dropTable('payments', true);
        $this->forge->dropTable('sales_invoice_items', true);
        $this->forge->dropTable('sales_invoices', true);
        $this->forge->dropTable('purchase_invoice_items', true);
        $this->forge->dropTable('purchase_invoices', true);
    }

    private function createPurchaseInvoices(): void
    {
        $this->forge->addField($this->tenantFields([
            'supplier_id'        => ['type' => 'BIGINT', 'unsigned' => true],
            'purchase_order_id'  => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'invoice_no'         => ['type' => 'VARCHAR', 'constraint' => 80],
            'invoice_date'       => ['type' => 'DATE'],
            'due_date'           => ['type' => 'DATE'],
            'currency_id'        => ['type' => 'BIGINT', 'unsigned' => true],
            'subtotal'           => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'tax_amount'         => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'total_amount'       => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'status'             => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'document_upload_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'posted_at'          => ['type' => 'DATETIME', 'null' => true],
            'posted_by'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]));

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'supplier_id', 'invoice_no'], 'uq_purchase_invoice_company_supplier_no');
        $this->forge->addKey(['company_id', 'status', 'due_date']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('currency_id', 'currencies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('posted_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('purchase_invoices', true);
    }

    private function createPurchaseInvoiceItems(): void
    {
        $this->forge->addField($this->tenantFields([
            'purchase_invoice_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'product_id'          => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'description'         => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'quantity'            => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'uom_id'              => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'unit_price'          => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'tax_amount'          => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'line_total'          => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
        ]));

        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'purchase_invoice_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('purchase_invoice_id', 'purchase_invoices', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('uom_id', 'units_of_measure', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('purchase_invoice_items', true);
    }

    private function createSalesInvoices(): void
    {
        $this->forge->addField($this->tenantFields([
            'customer_id'        => ['type' => 'BIGINT', 'unsigned' => true],
            'sales_order_id'     => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'invoice_no'         => ['type' => 'VARCHAR', 'constraint' => 80],
            'invoice_date'       => ['type' => 'DATE'],
            'due_date'           => ['type' => 'DATE'],
            'currency_id'        => ['type' => 'BIGINT', 'unsigned' => true],
            'subtotal'           => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'tax_amount'         => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'total_amount'       => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'status'             => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'document_upload_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'posted_at'          => ['type' => 'DATETIME', 'null' => true],
            'posted_by'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]));

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'customer_id', 'invoice_no'], 'uq_sales_invoice_company_customer_no');
        $this->forge->addKey(['company_id', 'status', 'due_date']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('currency_id', 'currencies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('posted_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('sales_invoices', true);
    }

    private function createSalesInvoiceItems(): void
    {
        $this->forge->addField($this->tenantFields([
            'sales_invoice_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'product_id'       => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'description'      => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'quantity'         => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'uom_id'           => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'unit_price'       => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'tax_amount'       => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'line_total'       => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
        ]));

        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'sales_invoice_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('sales_invoice_id', 'sales_invoices', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('uom_id', 'units_of_measure', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('sales_invoice_items', true);
    }

    private function createPayments(): void
    {
        $this->forge->addField($this->tenantFields([
            'payment_no'       => ['type' => 'VARCHAR', 'constraint' => 50],
            'payment_type'     => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'outgoing'],
            'supplier_id'      => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'customer_id'      => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'payment_date'     => ['type' => 'DATE'],
            'currency_id'      => ['type' => 'BIGINT', 'unsigned' => true],
            'bank_account_id'  => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'amount'           => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'status'           => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'posted_at'        => ['type' => 'DATETIME', 'null' => true],
            'posted_by'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]));

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'payment_no'], 'uq_payment_company_no');
        $this->forge->addKey(['company_id', 'payment_type', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('currency_id', 'currencies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('bank_account_id', 'cash_bank_accounts', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('posted_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->addAuditForeignKeys();
        $this->forge->createTable('payments', true);
    }

    private function createPaymentAllocations(): void
    {
        $this->forge->addField($this->tenantFields([
            'payment_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'document_type'  => ['type' => 'VARCHAR', 'constraint' => 40],
            'document_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'allocated_amount' => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => '0.0000'],
            'description'    => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
        ]));

        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'payment_id']);
        $this->forge->addKey(['company_id', 'document_type', 'document_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('payment_id', 'payments', 'id', 'CASCADE', 'RESTRICT');
        $this->addAuditForeignKeys();
        $this->forge->createTable('payment_allocations', true);
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
