<?php

declare(strict_types=1);

namespace App\Auth;

use App\Services\AuditTrailService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Shield\Models\UserModel;
use Config\Database;
use RuntimeException;

final class ShieldUserProvisioningService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= Database::connect();
    }

    /**
     * @param array{username: string, email: string, password: string} $data
     */
    public function provision(array $data, int $actorId): int
    {
        /** @var UserModel $users */
        $users = model(UserModel::class);

        $user = $users->createNewUser([
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'active'   => true,
        ]);
        $users->save($user);
        $userId = (int) $users->getInsertID();
        $saved  = $users->findById($userId);

        if ($saved === null) {
            throw new RuntimeException('User Shield tidak dapat dimuat setelah provisioning.');
        }

        // A new identity has no platform privilege; tenant access is assigned separately.
        (new AuditTrailService($this->db))->record('USER_PROVISIONED', 'user', $userId, null, null, $actorId, [
            'username' => $data['username'],
            'email'    => $data['email'],
            'provider' => 'shield',
        ]);

        return $userId;
    }

    public function setActive(int $userId, bool $active, int $actorId): bool
    {
        /** @var UserModel $users */
        $users = model(UserModel::class);
        $user  = $users->findById($userId);

        if ($user === null) {
            return false;
        }

        $before = ['active' => (bool) $user->active];
        $users->update($userId, ['active' => $active]);
        (new AuditTrailService($this->db))->record('USER_STATUS_UPDATED', 'user', $userId, null, null, $actorId, [
            'active' => $active,
        ], $before);

        return true;
    }

    public function setTemporaryPassword(int $userId, string $password, int $actorId): bool
    {
        /** @var UserModel $users */
        $users = model(UserModel::class);
        $user  = $users->findById($userId);

        if ($user === null) {
            return false;
        }

        $user->password = $password;
        $users->save($user);
        (new AuditTrailService($this->db))->record('USER_PASSWORD_REPLACED', 'user', $userId, null, null, $actorId, [
            'provider' => 'shield',
            'mode'     => 'temporary_password',
        ]);

        return true;
    }
}
