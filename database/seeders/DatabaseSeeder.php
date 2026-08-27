<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $catMasc = Category::where('slug', 'masculino')->first();
        $catFem = Category::where('slug', 'feminino')->first();

    Banner::insert([
            [
                'image_url' => '/banners/seu-banner-principal.jpg',
                'image_url_mobile' => '/banners/seu-banner-principal-mobile.jpg',
                'link_url' => '/products?collection=inverno',
            ],
            [
                'image_url' => '/banners/banner-2.jpg',
                'image_url_mobile' => '/banners/banner-2-mobile.jpg',
                'link_url' => '/products?gender=masculino',
            ],
            [
                'image_url' => '/banners/banner-3.jpg',
                'image_url_mobile' => '/banners/banner-3-mobile.jpg',
                'link_url' => '/products?gender=feminino',
            ],
        ]);

        // Cria Produtos em Oferta
        for ($i = 1; $i <= 4; $i++) {
            $product = Product::create([
                'category_id' => $catMasc->id ?? 1,
                'name' => 'Camiseta Listrada Infantil ' . $i,
                'slug' => Str::slug('Camiseta Listrada Infantil ' . $i . '-' . uniqid()),
                'description' => 'Camiseta infantil confortável confeccionada em algodão.',
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => '/products/sua-foto-de-produto.jpg',
            ]);

            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'SKU-OFERTA-' . $i,
                'size' => '12',
                'color_name' => 'Laranja',
                'color_hex' => '#FF7537',
                'price' => 199.99,
                'promo_price' => 99.99,
                'stock_quantity' => 20,
                'weight_kg' => 0.300,
                'length_cm' => 20,
                'width_cm' => 15,
                'height_cm' => 4,
            ]);
        }

        // Cria Produtos em Novidades
        for ($i = 1; $i <= 4; $i++) {
            $product = Product::create([
                'category_id' => $catFem->id ?? 2,
                'name' => 'Casaco Infantil Estampado ' . $i,
                'slug' => Str::slug('Casaco Infantil Estampado ' . $i . '-' . uniqid()),
                'description' => 'Casaco infantil perfeito para dias frios.',
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => '/products/sua-foto-de-produto.jpg',
            ]);

            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'SKU-NOVO-' . $i,
                'size' => '14',
                'color_name' => 'Rosa',
                'color_hex' => '#FF69B4',
                'price' => 159.99,
                'promo_price' => null,
                'stock_quantity' => 15,
                'weight_kg' => 0.450,
                'length_cm' => 25,
                'width_cm' => 20,
                'height_cm' => 5,
            ]);
        }
    }
}