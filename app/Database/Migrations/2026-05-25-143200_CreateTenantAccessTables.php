<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateTenantAccessTables extends Migration
{
    public function up(): void
    {
        $this->createUserCompanyMemberships();
        $this->createUserBranchMemberships();
        $this->createRoles();
        $this->createPermissions();
        $this->createRolePermissions();
        $this->createUserRoles();
    }

    public function down(): void
    {
        $this->forge->dropTable('user_roles', true);
        $this->forge->dropTable('role_permissions', true);
        $this->forge->dropTable('permissions', true);
        $this->forge->dropTable('roles', true);
        $this->forge->dropTable('user_branch_memberships', true);
        $this->forge->dropTable('user_company_memberships', true);
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

    private function createUserCompanyMemberships(): void
    {
        $this->forge->addField(array_merge([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true],
            'is_default' => ['type' => 'BOOLEAN', 'default' => false],
            'status'     => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ], $this->auditFields()));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'user_id']);
        $this->forge->addKey(['user_id', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_company_memberships', true);
    }

    private function createUserBranchMemberships(): void
    {
        $this->forge->addField(array_merge([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true],
            'branch_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'can_switch' => ['type' => 'BOOLEAN', 'default' => true],
            'status'     => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ], $this->auditFields()));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'user_id', 'branch_id']);
        $this->forge->addKey(['user_id', 'status']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_branch_memberships', true);
    }

    private function createRoles(): void
    {
        if ($this->db->tableExists('roles')) {
            if (! $this->db->fieldExists('status', 'roles')) {
                $this->forge->addColumn('roles', [
                    'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
                ]);
            }

            return;
        }

        $this->forge->addField(array_merge([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'code'       => ['type' => 'VARCHAR', 'constraint' => 50],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'is_system'  => ['type' => 'BOOLEAN', 'default' => false],
            'status'     => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
        ], $this->auditFields()));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('roles', true);
    }

    private function createPermissions(): void
    {
        $this->forge->addField(array_merge([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'code'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 120],
            'module'     => ['type' => 'VARCHAR', 'constraint' => 40],
        ], $this->auditFields()));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'code']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('permissions', true);
    }

    private function createRolePermissions(): void
    {
        $this->forge->addField(array_merge([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'role_id'       => ['type' => 'BIGINT', 'unsigned' => true],
            'permission_id' => ['type' => 'BIGINT', 'unsigned' => true],
        ], $this->auditFields()));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'role_id', 'permission_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('role_permissions', true);
    }

    private function createUserRoles(): void
    {
        $this->forge->addField(array_merge([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'company_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'user_id'        => ['type' => 'INT', 'unsigned' => true],
            'role_id'        => ['type' => 'BIGINT', 'unsigned' => true],
            'effective_from' => ['type' => 'DATE', 'null' => true],
            'effective_to'   => ['type' => 'DATE', 'null' => true],
        ], $this->auditFields()));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['company_id', 'user_id', 'role_id']);
        $this->forge->addKey(['company_id', 'user_id']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_roles', true);
    }
}
