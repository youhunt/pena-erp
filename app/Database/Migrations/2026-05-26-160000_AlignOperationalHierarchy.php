<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class AlignOperationalHierarchy extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('branch_id', 'departments')) {
            $this->forge->addColumn('departments', [
                'branch_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'company_id'],
            ]);
        }

        if (! $this->db->fieldExists('department_id', 'warehouses')) {
            $this->forge->addColumn('warehouses', [
                'department_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'branch_id'],
            ]);
        }

        $this->backfillExistingHierarchy();

        $departments = $this->db->prefixTable('departments');
        $warehouses = $this->db->prefixTable('warehouses');
        $branches = $this->db->prefixTable('branches');
        $this->db->query("CREATE INDEX departments_company_branch_status ON {$departments} (company_id, branch_id, status)");
        $this->db->query("CREATE INDEX warehouses_hierarchy ON {$warehouses} (company_id, branch_id, department_id)");

        if ($this->db->DBDriver === 'MySQLi') {
            $this->db->query("ALTER TABLE {$departments} ADD CONSTRAINT departments_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES {$branches}(id) ON DELETE RESTRICT ON UPDATE CASCADE");
            $this->db->query("ALTER TABLE {$warehouses} ADD CONSTRAINT warehouses_department_id_foreign FOREIGN KEY (department_id) REFERENCES {$departments}(id) ON DELETE RESTRICT ON UPDATE CASCADE");
        }
    }

    public function down(): void
    {
        if ($this->db->DBDriver === 'MySQLi') {
            $this->db->query('ALTER TABLE ' . $this->db->prefixTable('warehouses') . ' DROP FOREIGN KEY warehouses_department_id_foreign');
            $this->db->query('ALTER TABLE ' . $this->db->prefixTable('departments') . ' DROP FOREIGN KEY departments_branch_id_foreign');
        }

        $this->forge->dropColumn('warehouses', 'department_id');
        $this->forge->dropColumn('departments', 'branch_id');
    }

    private function backfillExistingHierarchy(): void
    {
        $departments = $this->db->table('departments')
            ->where('branch_id', null)
            ->get()
            ->getResultArray();

        foreach ($departments as $department) {
            $branch = $this->db->table('branches')
                ->where('company_id', $department['company_id'])
                ->where('deleted_at', null)
                ->orderBy('is_head_office', 'DESC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getFirstRow('array');

            if ($branch !== null) {
                $this->db->table('departments')->where('id', $department['id'])->update(['branch_id' => $branch['id']]);
            }
        }

        $warehouses = $this->db->table('warehouses')
            ->where('department_id', null)
            ->get()
            ->getResultArray();

        foreach ($warehouses as $warehouse) {
            $department = $this->db->table('departments')
                ->where([
                    'company_id' => $warehouse['company_id'],
                    'branch_id'  => $warehouse['branch_id'],
                ])
                ->where('deleted_at', null)
                ->orderBy('id', 'ASC')
                ->get()
                ->getFirstRow('array');

            if ($department !== null) {
                $this->db->table('warehouses')->where('id', $warehouse['id'])->update(['department_id' => $department['id']]);
            }
        }
    }
}
