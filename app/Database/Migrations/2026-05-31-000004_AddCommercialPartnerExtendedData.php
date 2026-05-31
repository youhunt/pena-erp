<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class AddCommercialPartnerExtendedData extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('customers') && ! $this->db->fieldExists('extended_data', 'customers')) {
            $this->forge->addColumn('customers', [
                'extended_data' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                    'after' => 'phone',
                ],
            ]);
        }

        if ($this->db->tableExists('suppliers') && ! $this->db->fieldExists('extended_data', 'suppliers')) {
            $this->forge->addColumn('suppliers', [
                'extended_data' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                    'after' => 'phone',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('customers') && $this->db->fieldExists('extended_data', 'customers')) {
            $this->forge->dropColumn('customers', 'extended_data');
        }

        if ($this->db->tableExists('suppliers') && $this->db->fieldExists('extended_data', 'suppliers')) {
            $this->forge->dropColumn('suppliers', 'extended_data');
        }
    }
}
