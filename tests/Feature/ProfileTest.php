<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/me');

        $response->assertStatus(200)
                 ->assertJson(['email' => $user->email]);
    }

    public function test_user_can_update_email_with_correct_password(): void
    {
        // Cria um usuario com a senha conhecida para o teste
        $user = User::factory()->create(['password' => Hash::make('Senha@123')]);

        $response = $this->actingAs($user)->patchJson('/api/me', [
            'email' => 'novo@email.com',
            'current_password' => 'Senha@123',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['email' => 'novo@email.com']);
    }

    public function test_user_can_update_password_with_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Senha@123')]);

        $response = $this->actingAs($user)->patchJson('/api/me', [
            'email' => $user->email,
            'password' => 'NovaSenha@123',
            'current_password' => 'Senha@123',
        ]);

        $response->assertStatus(200);

        // Atualiza a instancia do model e verifica se o hash corresponde a nova senha
        $user->refresh();
        $this->assertTrue(Hash::check('NovaSenha@123', $user->password));
    }

    public function test_user_cannot_update_email_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Senha@123')]);

        $response = $this->actingAs($user)->patchJson('/api/me', [
            'email' => 'novo@email.com',
            'current_password' => 'SenhaErrada',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_delete_account(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Senha@123')]);

        $response = $this->actingAs($user)->deleteJson('/api/me', [
            'current_password' => 'Senha@123',
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/auth/logout');

        $response->assertStatus(204);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}