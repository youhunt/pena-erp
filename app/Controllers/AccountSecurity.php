<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\ShieldUserProvisioningService;
use App\Services\UserSessionSecurityService;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

final class AccountSecurity extends BaseController
{
    public function password(): string
    {
        return view('account/password', [
            'required' => (new UserSessionSecurityService())->requiresPasswordReset((int) auth()->id()),
        ]);
    }

    public function updatePassword(): RedirectResponse
    {
        $data = [
            'password'         => (string) $this->request->getPost('password'),
            'password_confirm' => (string) $this->request->getPost('password_confirm'),
        ];

        if (! $this->validateData($data, [
            'password'         => 'required|min_length[12]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
        ])) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        try {
            $updated = (new ShieldUserProvisioningService())->setTemporaryPassword((int) auth()->id(), $data['password'], (int) auth()->id(), false);
        } catch (Throwable) {
            return redirect()->back()->with('errors', ['password' => 'Password baru gagal disimpan.']);
        }

        if (! $updated) {
            return redirect()->back()->with('errors', ['user_id' => 'User login tidak ditemukan.']);
        }

        auth('session')->getAuthenticator()->logout();

        return redirect()->to(site_url('login'))->with('message', 'Password berhasil diganti. Silakan login kembali.');
    }
}
