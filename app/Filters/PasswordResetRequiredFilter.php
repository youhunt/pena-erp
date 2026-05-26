<?php

declare(strict_types=1);

namespace App\Filters;

use App\Services\UserSessionSecurityService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

final class PasswordResetRequiredFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userId = auth()->id();

        if ($userId !== null && (new UserSessionSecurityService())->requiresPasswordReset((int) $userId)) {
            return redirect()->to(site_url('account/security/password'))->with('error', 'Anda wajib mengganti password sementara sebelum melanjutkan.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
