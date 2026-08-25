<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_new_user_and_returns_token(): void
    {
        $payload = [
            'email' => 'novo@teste.com',
            'password' => 'Senha@123',
            'password_confirmation' => 'Senha@123',
        ];

        $response = $this->postJson('/api/users', $payload);

        // Atualizado para 202 e estrutura de 2FA
        $response->assertStatus(202)
                 ->assertJsonStructure([
                     'requires_2fa',
                     'temp_token',
                     'message',
                 ]);
    }

    public function test_authenticates_user_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'cliente@teste.com',
            'password' => bcrypt('Senha@123'),
        ]);

        $payload = [
            'email' => 'cliente@teste.com',
            'password' => 'Senha@123',
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        // Atualizado para 202 e estrutura de 2FA
        $response->assertStatus(202)
                 ->assertJsonStructure([
                     'requires_2fa',
                     'temp_token',
                     'message',
                 ]);
    }
}