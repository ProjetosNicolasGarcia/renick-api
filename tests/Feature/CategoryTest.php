<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories_with_subcategories(): void
    {
        $parent = Category::create([
            'name' => 'Masculino',
            'slug' => Str::slug('Masculino'),
            'parent_id' => null,
        ]);

        $child = Category::create([
            'name' => 'Camisetas',
            'slug' => Str::slug('Masculino Camisetas'),
            'parent_id' => $parent->id,
        ]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJsonCount(1)
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'name',
                         'slug',
                         'subcategories' => [
                             '*' => ['id', 'name', 'slug']
                         ]
                     ]
                 ])
                 ->assertJsonPath('0.name', 'Masculino')
                 ->assertJsonPath('0.slug', 'masculino')
                 ->assertJsonPath('0.subcategories.0.name', 'Camisetas')
                 ->assertJsonPath('0.subcategories.0.slug', 'masculino-camisetas');
    }
}