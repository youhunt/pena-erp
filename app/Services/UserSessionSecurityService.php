<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Session\SessionInterface;
use Config\Database;
use RuntimeException;

final class UserSessionSecurityService
{
    private const VERSION_KEY = 'auth_security_version';

    public function __construct(
        private ?BaseConnection $db = null,
        private ?SessionInterface $session = null,
    ) {
        $this->db ??= Database::connect();
        $this->session ??= service('session');
    }

    public function stampLogin(int $userId): void
    {
        $this->session->set(self::VERSION_KEY, $this->currentVersion($userId));
    }

    public function currentSessionIsValid(int $userId): bool
    {
        $sessionVersion = (int) ($this->session->get(self::VERSION_KEY) ?? 0);

        return $sessionVersion === $this->currentVersion($userId);
    }

    public function requiresPasswordReset(int $userId): bool
    {
        $identity = $this->db->table('auth_identities')
            ->select('force_reset')
            ->where(['user_id' => $userId, 'type' => 'email_password'])
            ->get()
            ->getFirstRow('array');

        return $identity !== null && (bool) $identity['force_reset'];
    }

    public function setPasswordResetRequired(int $userId, bool $required): void
    {
        $this->db->table('auth_identities')
            ->where(['user_id' => $userId, 'type' => 'email_password'])
            ->update(['force_reset' => $required]);
    }

    public function revokeSessions(int $userId, int $actorId, string $reason): int
    {
        $state = $this->db->table('user_session_security')
            ->where('user_id', $userId)
            ->get()
            ->getFirstRow('array');
        $version = $state === null ? 1 : (int) $state['security_version'] + 1;
        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        if ($state === null) {
            $this->db->table('user_session_security')->insert([
                'user_id'             => $userId,
                'security_version'    => $version,
                'sessions_revoked_at' => $now,
                'last_reason'         => $reason,
                'created_at'          => $now,
                'updated_by'          => $actorId,
            ]);
        } else {
            $this->db->table('user_session_security')->where('user_id', $userId)->update([
                'security_version'    => $version,
                'sessions_revoked_at' => $now,
                'last_reason'         => $reason,
                'updated_at'          => $now,
                'updated_by'          => $actorId,
            ]);
        }

        (new AuditTrailService($this->db))->record('USER_SESSIONS_REVOKED', 'user', $userId, null, null, $actorId, [
            'security_version' => $version,
            'reason'           => $reason,
        ]);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Revokasi session user gagal dan transaksi dibatalkan.');
        }

        return $version;
    }

    private function currentVersion(int $userId): int
    {
        $state = $this->db->table('user_session_security')
            ->select('security_version')
            ->where('user_id', $userId)
            ->get()
            ->getFirstRow('array');

        return $state === null ? 0 : (int) $state['security_version'];
    }
}
