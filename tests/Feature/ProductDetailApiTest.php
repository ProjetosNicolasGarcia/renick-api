<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_product_details_with_color_grouped_images()
    {
        $category = Category::create(['name' => 'Cat 1', 'slug' => 'cat-1']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Camisa Teste',
            'slug' => 'camisa-teste',
            'description' => 'Descrição',
        ]);

        ProductImage::create(['product_id' => $product->id, 'color_slug' => 'azul', 'image_url' => '/img1.jpg', 'sort_order' => 0]);
        ProductImage::create(['product_id' => $product->id, 'color_slug' => 'azul', 'image_url' => '/img2.jpg', 'sort_order' => 1]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-1',
            'size' => 'M',
            'color_name' => 'Azul',
            'color_hex' => '#0000FF',
            'price' => 100.50,
            'stock_quantity' => 10,
            'weight_kg' => 0.5,
            'length_cm' => 10,
            'width_cm' => 10,
            'height_cm' => 10,
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('name', 'Camisa Teste')
                 ->assertJsonPath('price', 100.50)
                 ->assertJsonCount(2, 'images')
                 ->assertJsonPath('images.0.color_slug', 'azul');
    }
}   