<?php

namespace App\Services;

use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    public function sendResetLink(array $data): void
    {
        $status = Password::sendResetLink($data);

        // clausula guarda para falha de envio
        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    public function resetPassword(array $data): void
    {
        $status = Password::reset(
            $data,
            function ($user, $password) {
                // o cast hashed do model lidara com a criptografia
                $user->forceFill([
                    'password' => $password,
                ])->save();
            }
        );

        // clausula guarda para token invalido ou expirado
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }
}