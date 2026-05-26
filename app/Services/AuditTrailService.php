<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

final class AuditTrailService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= Database::connect();
    }

    /**
     * @param array<string, mixed>      $after
     * @param array<string, mixed>|null $before
     */
    public function record(
        string $eventType,
        string $entityType,
        ?int $entityId,
        ?int $companyId,
        ?int $branchId,
        ?int $actorId,
        array $after = [],
        ?array $before = null,
    ): void {
        $now        = date('Y-m-d H:i:s');
        $beforeJson = $before === null ? null : json_encode($before, JSON_THROW_ON_ERROR);

        $this->db->table('audit_logs')->insert([
            'company_id'  => $companyId,
            'branch_id'   => $branchId,
            'user_id'     => $actorId,
            'event_type'  => $eventType,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'before_hash' => $beforeJson === null ? null : hash('sha256', $beforeJson),
            'after_json'  => $after === [] ? null : json_encode($after, JSON_THROW_ON_ERROR),
            'occurred_at' => $now,
            'created_at'  => $now,
        ]);
    }
}
