<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LandingPageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_banners(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            Banner::create([
                'image_url' => 'https://cdn.loja.com/banners/banner' . $i . '.jpg',
                'link_url' => '/collections/colecao-' . $i,
            ]);
        }

        $response = $this->getJson('/api/banners');
        
        $response->assertStatus(200)->assertJsonCount(3);
    }

    public function test_can_filter_products_by_sale_and_limit_per_page(): void
    {
        $category = Category::create([
            'name' => 'Categoria Geral',
            'slug' => 'categoria-geral',
        ]);

        for ($i = 1; $i <= 5; $i++) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Produto Promocional ' . $i,
                'slug' => Str::slug('Produto Promocional ' . $i),
                'description' => 'Descrição do produto.',
            ]);

            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'SKU-PROMO-' . $i,
                'size' => 'M',
                'color_name' => 'Azul',
                'color_hex' => '#0000FF',
                'price' => 199.99,
                'promo_price' => 50.00,
                'stock_quantity' => 10,
                'weight_kg' => 0.500,
                'length_cm' => 20,
                'width_cm' => 20,
                'height_cm' => 5,
            ]);
        }

        for ($i = 6; $i <= 10; $i++) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Produto Normal ' . $i,
                'slug' => Str::slug('Produto Normal ' . $i),
                'description' => 'Descrição do produto.',
            ]);

            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'SKU-NORM-' . $i,
                'size' => 'M',
                'color_name' => 'Vermelho',
                'color_hex' => '#FF0000',
                'price' => 100.00,
                'promo_price' => null,
                'stock_quantity' => 10,
                'weight_kg' => 0.500,
                'length_cm' => 20,
                'width_cm' => 20,
                'height_cm' => 5,
            ]);
        }

        $response = $this->getJson('/api/products?is_sale=true&per_page=4');

        $response->assertStatus(200)
                 ->assertJsonCount(4, 'data')
                 ->assertJsonPath('data.0.variants.0.promo_price', "50.00");
    }
}