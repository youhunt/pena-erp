<?php

declare(strict_types=1);

namespace App\Filters;

use App\Services\UserSessionSecurityService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

final class SessionSecurityFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        if (! $user->active || ! (new UserSessionSecurityService())->currentSessionIsValid((int) $user->id)) {
            auth('session')->getAuthenticator()->logout();

            return redirect()->route('login')->with('error', 'Session Anda sudah dicabut. Silakan login kembali.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
