<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_own_addresses(): void
    {
        $user = User::factory()->create();

        // Criado diretamente pelo Eloquent para não depender da classe Factory
        Address::create([
            'user_id' => $user->id,
            'zip_code' => '01311999',
            'street' => 'Avenida Paulista',
            'number' => '118',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'is_default' => true
        ]);

        $response = $this->actingAs($user)->getJson('/api/me/addresses');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    public function test_user_can_create_address(): void
    {
        $user = User::factory()->create();
        
        $payload = [
            'zip_code' => '01311999', // Hífen removido para respeitar o limite atual do banco de dados
            'street' => 'Avenida Paulista',
            'number' => '118',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'is_default' => true
        ];

        $response = $this->actingAs($user)->postJson('/api/me/addresses', $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('zip_code', '01311999');
    }
}