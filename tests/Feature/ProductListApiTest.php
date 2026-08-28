<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductListApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_filter_products_by_search_query_and_gender()
    {
        $category = Category::create(['name' => 'Cat 1', 'slug' => 'cat-1']);
        
        Product::create([
            'category_id' => $category->id, 
            'name' => 'Camisa Polo', 
            'slug' => 'p1', 
            'gender' => 'masculino', 
            'description' => 'descrição de teste'
        ]);
        
        Product::create([
            'category_id' => $category->id, 
            'name' => 'Vestido Floral', 
            'slug' => 'p2', 
            'gender' => 'feminino', 
            'description' => 'descrição de teste'
        ]);

        $responseQuery = $this->getJson('/api/products?q=Polo');
        $responseQuery->assertStatus(200)
                      ->assertJsonCount(1, 'data')
                      ->assertJsonPath('data.0.name', 'Camisa Polo');

        $responseGender = $this->getJson('/api/products?gender=feminino');
        $responseGender->assertStatus(200)
                       ->assertJsonCount(1, 'data')
                       ->assertJsonPath('data.0.name', 'Vestido Floral');
    }

    public function test_can_filter_products_by_collection()
    {
        $category = Category::create(['name' => 'Cat', 'slug' => 'cat']);
        $collection = Collection::create(['name' => 'Inverno', 'slug' => 'inverno', 'image_url' => '/img.jpg']);
        
        Product::create([
            'category_id' => $category->id, 
            'collection_id' => $collection->id, 
            'name' => 'Casaco', 
            'slug' => 'c1', 
            'description' => 'descrição de teste'
        ]);
        
        Product::create([
            'category_id' => $category->id, 
            'name' => 'Bermuda', 
            'slug' => 'b1', 
            'description' => 'descrição de teste'
        ]);

        $response = $this->getJson('/api/products?collection=inverno');
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.name', 'Casaco');
    }
}