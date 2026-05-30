<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class ActivateAssignedTenantRoles extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $rows = $this->db->table('user_roles ur')
            ->distinct()
            ->select('ur.company_id, ur.role_id')
            ->join('users u', 'u.id = ur.user_id')
            ->join('user_company_memberships cm', 'cm.company_id = ur.company_id AND cm.user_id = ur.user_id', 'left')
            ->where('u.active', 1)
            ->groupStart()
                ->where('cm.status', 'active')
                ->orWhere('cm.status IS NULL', null, false)
            ->groupEnd()
            ->groupStart()
                ->where('ur.effective_from <=', date('Y-m-d'))
                ->orWhere('ur.effective_from', null)
            ->groupEnd()
            ->groupStart()
                ->where('ur.effective_to >=', date('Y-m-d'))
                ->orWhere('ur.effective_to', null)
            ->groupEnd()
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $this->db->table('roles')
                ->where('company_id', (int) $row['company_id'])
                ->where('id', (int) $row['role_id'])
                ->update([
                    'status'     => 'active',
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        // Non-destructive: do not deactivate roles automatically.
    }
}
