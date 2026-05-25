<?php

declare(strict_types=1);

namespace App\Auth;

interface AuthGatewayInterface
{
    public function userId(): ?int;

    public function isLoggedIn(): bool;

    /**
     * Shield permissions are reserved for platform-level authorization.
     * Tenant ERP authorization will be enforced by the membership policy layer.
     */
    public function hasPlatformPermission(string $permission): bool;
}
