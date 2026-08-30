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

    public function test_can_fetch_filter_attributes()
    {
        $response = $this->getJson('/api/attributes');
        $response->assertStatus(200)
                 ->assertJsonStructure(['genders', 'types', 'sizes', 'colors', 'price_range']);
    }

    public function test_can_filter_products_by_color_and_price()
    {
        $category = Category::create(['name' => 'Cat', 'slug' => 'cat']);
        $p1 = Product::create(['category_id' => $category->id, 'name' => 'Prod 1', 'slug' => 'p1', 'description' => 'desc']);
        $p2 = Product::create(['category_id' => $category->id, 'name' => 'Prod 2', 'slug' => 'p2', 'description' => 'desc']);

        \App\Models\ProductVariant::create([
            'product_id' => $p1->id, 'sku' => 'SKU1', 'size' => 'M', 
            'color_name' => 'Azul', 'color_hex' => '#0000FF', 'price' => 50, 'stock_quantity' => 10,
            'weight_kg' => 0.5, 'length_cm' => 20, 'width_cm' => 15, 'height_cm' => 5 // Inserções dimensionais exigidas pelo BD
        ]);
        
        \App\Models\ProductVariant::create([
            'product_id' => $p2->id, 'sku' => 'SKU2', 'size' => 'M', 
            'color_name' => 'Vermelho', 'color_hex' => '#FF0000', 'price' => 150, 'stock_quantity' => 10,
            'weight_kg' => 0.5, 'length_cm' => 20, 'width_cm' => 15, 'height_cm' => 5 // Inserções dimensionais exigidas pelo BD
        ]);

        $responseColor = $this->getJson('/api/products?color=azul');
        $responseColor->assertStatus(200)->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Prod 1');

        $responsePrice = $this->getJson('/api/products?min_price=100');
        $responsePrice->assertStatus(200)->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Prod 2');
    }
}