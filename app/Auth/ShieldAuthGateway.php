<?php

declare(strict_types=1);

namespace App\Auth;

final class ShieldAuthGateway implements AuthGatewayInterface
{
    public function userId(): ?int
    {
        $id = auth()->id();

        return $id === null ? null : (int) $id;
    }

    public function isLoggedIn(): bool
    {
        return auth()->loggedIn();
    }

    public function hasPlatformPermission(string $permission): bool
    {
        return auth()->user()?->can($permission) ?? false;
    }
}
