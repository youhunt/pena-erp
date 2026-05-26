<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Session\SessionInterface;
use Config\Database;

final class TenantContextService
{
    private const COMPANY_KEY = 'tenant_company_id';
    private const BRANCH_KEY = 'tenant_branch_id';

    public function __construct(
        private ?BaseConnection $db = null,
        private ?SessionInterface $session = null,
        private ?AuditTrailService $audit = null,
    ) {
        $this->db ??= Database::connect();
        $this->session ??= service('session');
        $this->audit ??= new AuditTrailService($this->db);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function availableContexts(int $userId): array
    {
        $branchMembershipTable = $this->db->prefixTable('user_branch_memberships');

        return $this->db->table('user_company_memberships m')
            ->select('c.id AS company_id, c.code AS company_code, c.name AS company_name, b.id AS branch_id, b.code AS branch_code, b.name AS branch_name, m.is_default')
            ->join('companies c', "c.id = m.company_id AND c.deleted_at IS NULL AND c.status = 'active'")
            ->join('user_branch_memberships bm', "bm.company_id = m.company_id AND bm.user_id = m.user_id AND bm.status = 'active' AND bm.can_switch = 1", 'left')
            ->join('branches b', "b.id = bm.branch_id AND b.deleted_at IS NULL AND b.status = 'active'", 'left')
            ->where('m.user_id', $userId)
            ->where('m.status', 'active')
            ->where("(b.id IS NOT NULL OR NOT EXISTS (SELECT 1 FROM {$branchMembershipTable} scoped_bm WHERE scoped_bm.company_id = m.company_id AND scoped_bm.user_id = m.user_id))", null, false)
            ->orderBy('m.is_default', 'DESC')
            ->orderBy('c.name', 'ASC')
            ->orderBy('b.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function current(int $userId): ?array
    {
        $companyId = (int) ($this->session->get(self::COMPANY_KEY) ?? 0);
        $branchId = $this->session->get(self::BRANCH_KEY);

        if ($companyId > 0) {
            $context = $this->findContext($userId, $companyId, $branchId === null ? null : (int) $branchId);

            if ($context !== null) {
                return $context;
            }
        }

        $contexts = $this->availableContexts($userId);

        if ($contexts === []) {
            $this->clear();

            return null;
        }

        return $this->store($contexts[0]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function activate(int $userId, int $companyId, ?int $branchId = null): ?array
    {
        $context = $this->findContext($userId, $companyId, $branchId);

        if ($context === null) {
            return null;
        }

        $changed = (int) ($this->session->get(self::COMPANY_KEY) ?? 0) !== (int) $context['company_id']
            || ($this->session->get(self::BRANCH_KEY) === null ? null : (int) $this->session->get(self::BRANCH_KEY))
                !== ($context['branch_id'] === null ? null : (int) $context['branch_id']);
        $stored = $this->store($context);

        if ($changed) {
            $this->audit->record('TENANT_CONTEXT_CHANGED', 'company', (int) $context['company_id'], (int) $context['company_id'], $context['branch_id'] === null ? null : (int) $context['branch_id'], $userId, [
                'branch_id' => $context['branch_id'] === null ? null : (int) $context['branch_id'],
            ]);
        }

        return $stored;
    }

    private function findContext(int $userId, int $companyId, ?int $branchId): ?array
    {
        $contexts = array_values(array_filter(
            $this->availableContexts($userId),
            static fn (array $context): bool => (int) $context['company_id'] === $companyId,
        ));

        if ($contexts === []) {
            return null;
        }

        if ($branchId === null) {
            return $contexts[0];
        }

        foreach ($contexts as $context) {
            if ($context['branch_id'] !== null && (int) $context['branch_id'] === $branchId) {
                return $context;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    private function store(array $context): array
    {
        $this->session->set([
            self::COMPANY_KEY => (int) $context['company_id'],
            self::BRANCH_KEY  => $context['branch_id'] === null ? null : (int) $context['branch_id'],
        ]);

        return $context;
    }

    private function clear(): void
    {
        $this->session->remove([self::COMPANY_KEY, self::BRANCH_KEY]);
    }
}
