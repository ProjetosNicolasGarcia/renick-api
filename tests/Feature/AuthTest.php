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
            'email' => 'novo@cliente.com',
            'password' => 'SenhaForte@123',
            'password_confirmation' => 'SenhaForte@123',
        ];

        // simula a chamada ao endpoint de cadastro
        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                     'user' => ['id', 'email'],
                 ]);

        $this->assertDatabaseHas('users', ['email' => 'novo@cliente.com']);
    }

    public function test_authenticates_user_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'cliente@teste.com',
            'password' => bcrypt('SenhaForte@123'),
        ]);

        $payload = [
            'email' => 'cliente@teste.com',
            'password' => 'SenhaForte@123',
        ];

        // simula a chamada ao endpoint de login
        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token', 'token_type']);
    }
}