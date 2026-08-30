<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Favorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_favorites()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Cat', 'slug' => 'cat']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Prod', 'slug' => 'p1', 'description' => 'desc']);
        Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $response = $this->actingAs($user)->getJson('/api/me/favorites');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.product.name', 'Prod');
    }

    public function test_user_can_add_favorite()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Cat', 'slug' => 'cat']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Prod', 'slug' => 'p1', 'description' => 'desc']);

        $response = $this->actingAs($user)->postJson('/api/me/favorites', ['product_id' => $product->id]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_user_cannot_add_duplicate_favorite()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Cat', 'slug' => 'cat']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Prod', 'slug' => 'p1', 'description' => 'desc']);
        Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $response = $this->actingAs($user)->postJson('/api/me/favorites', ['product_id' => $product->id]);

        $response->assertStatus(409);
    }

    public function test_user_can_remove_favorite()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Cat', 'slug' => 'cat']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Prod', 'slug' => 'p1', 'description' => 'desc']);
        Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $response = $this->actingAs($user)->deleteJson('/api/me/favorites/' . $product->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_unauthenticated_user_cannot_access_favorites()
    {
        $this->getJson('/api/me/favorites')->assertStatus(401);
    }
}