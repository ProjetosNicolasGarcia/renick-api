<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // cria um usuario padrao de teste sem o campo name
        User::factory()->create([
            'email' => 'test@example.com',
        ]);
    }
}