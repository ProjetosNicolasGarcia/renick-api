<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_verifies_two_factor_code_and_authenticates_user(): void
    {
        $user = User::factory()->create([
            'email' => 'cliente@teste.com',
        ]);

        // Simula o código guardado no cache pelo AuthService
        Cache::put('2fa_code_cliente@teste.com', '123456', now()->addMinutes(10));

        $payload = [
            'email' => 'cliente@teste.com',
            'code' => '123456',
        ];

        $response = $this->postJson('/api/auth/2fa/verify', $payload);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                     'user' => ['id', 'email'],
                 ]);
    }
}