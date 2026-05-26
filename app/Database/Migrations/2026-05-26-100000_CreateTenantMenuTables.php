<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateTenantMenuTables extends Migration
{
    public function up(): void
    {
        $this->createMenus();
        $this->createMenuPermissions();
    }

    public function down(): void
    {
        $this->forge->dropTable('menu_permissions', true);
        $this->forge->dropTable('menus', true);
    }

    private function auditFields(): array
    {
        return [
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ];
    }

    private function createMenus(): void
    {
        if ($this->db->tableExists('menus')) {
            return;
        }

        $this->forge->addField(array_merge([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'parent_id'  => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'code'       => ['type' => 'VARCHAR', 'constraint' => 60],
            'label'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'route'      => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'icon'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'sort_order' => ['type' => 'INT', 'default' => 0],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ], $this->auditFields()));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addKey(['company_id', 'sort_order']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('parent_id', 'menus', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('menus', true);
    }

    private function createMenuPermissions(): void
    {
        if ($this->db->tableExists('menu_permissions')) {
            return;
        }

        $this->forge->addField(array_merge([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'menu_id'       => ['type' => 'BIGINT', 'unsigned' => true],
            'permission_id' => ['type' => 'BIGINT', 'unsigned' => true],
        ], $this->auditFields()));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'menu_id', 'permission_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('menu_id', 'menus', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('menu_permissions', true);
    }
}
