<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticates_or_creates_user_with_valid_google_token(): void
    {
        // Intercepta a chamada HTTP para a API do Google e retorna dados fictícios
        Http::fake([
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                'email' => 'google@teste.com',
                'given_name' => 'Teste',
                'family_name' => 'Google',
            ], 200),
        ]);

        $response = $this->postJson('/api/auth/google', [
            'id_token' => 'fake_valid_token',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                     'user' => ['id', 'email'],
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'google@teste.com',
        ]);
    }
}