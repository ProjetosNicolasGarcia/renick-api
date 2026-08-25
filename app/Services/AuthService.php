<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    private const CODE_EXPIRATION_MINUTES = 10;

    public function registerUser(array $data): array
    {
        $user = User::create([
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        return $this->generateTwoFactorPayload($user);
    }

    public function authenticateUser(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        return $this->generateTwoFactorPayload($user);
    }

    private function generateTwoFactorPayload(User $user): array
    {
        // gera codigo aleatorio de 6 digitos
        $code = (string) random_int(100000, 999999);

        // armazena no cache por 10 minutos usando chave unica por email
        Cache::put("2fa_code_{$user->email}", $code, now()->addMinutes(self::CODE_EXPIRATION_MINUTES));

        // dispara o email via mailpit
        $user->notify(new TwoFactorCodeNotification($code));

        // cria token temporario para o fluxo
        $tempToken = $user->createToken('temp_2fa', ['2fa:verify'])->plainTextToken;

        return [
            'requires_2fa' => true,
            'temp_token' => $tempToken,
            'message' => 'Código de verificação enviado para o seu e-mail.',
        ];
    }

    public function verifyTwoFactor(array $data): array
    {
        $user = User::where('email', $data['email'])->first();
        $cachedCode = Cache::get("2fa_code_{$data['email']}");

        // valida existencia do usuario e correspondencia do codigo gravado no cache
        if (! $user || ! $cachedCode || $cachedCode !== $data['code']) {
            throw ValidationException::withMessages([
                'code' => ['O código fornecido é inválido ou expirou.'],
            ]);
        }

        // remove o codigo do cache apos utilizacao
        Cache::forget("2fa_code_{$data['email']}");

        // revoga tokens temporarios
        $user->tokens()->where('name', 'temp_2fa')->delete();

        // gera o token final de sessao
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

public function authenticateWithGoogle(array $data): array
    {
        $token = $data['id_token'];
        $client = app(\Google_Client::class);
        $client->setClientId(env('GOOGLE_CLIENT_ID'));

        // identifica se o token recebido possui o formato jwt de 3 segmentos
        $isJwt = substr_count($token, '.') === 2;

        if ($isJwt) {
            $payload = $client->verifyIdToken($token);
        } else {
            // consulta os dados do usuario diretamente via api do google com o access token
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if ($response->successful()) {
                $googleUser = $response->json();
                $payload = [
                    'email' => $googleUser['email'] ?? null,
                    'given_name' => $googleUser['given_name'] ?? null,
                    'family_name' => $googleUser['family_name'] ?? null,
                ];
            } else {
                $payload = false;
            }
        }

        // early return se o token for invalido ou a consulta falhar
        if (! $payload || empty($payload['email'])) {
            throw ValidationException::withMessages([
                'id_token' => ['O token do Google é inválido ou expirou.'],
            ]);
        }

        // busca o usuario pelo email ou cria um novo registro
        $user = User::firstOrCreate(
            ['email' => $payload['email']],
            [
                'first_name' => $payload['given_name'] ?? null,
                'last_name' => $payload['family_name'] ?? null,
                'password' => \Illuminate\Support\Str::random(32),
            ]
        );

        $authToken = $user->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $authToken,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration') * 60,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
            'is_new' => $user->wasRecentlyCreated,
        ];
    }

}