<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function registerUser(array $data): array
    {
        // criacao do usuario isolada
        $user = User::create([
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        return $this->createAuthResponse($user);
    }

    public function authenticateUser(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        // clausula guarda para falha de autenticacao
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        return $this->createAuthResponse($user);
    }

    private function createAuthResponse(User $user): array
    {
        // gera o personal access token respeitando o prefixo do sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration') * 60,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ];
    }
}