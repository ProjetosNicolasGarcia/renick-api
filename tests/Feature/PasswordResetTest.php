<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_password_reset_link(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Solicitação processada com sucesso.']);
    }

    public function test_resets_password_successfully(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NovaSenha@123',
            'password_confirmation' => 'NovaSenha@123',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Operação realizada com sucesso.']);
    }
}