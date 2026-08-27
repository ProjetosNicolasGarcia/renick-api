<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Product;
use Illuminate\Database\Seeder;

class LandingPageSeeder extends Seeder
{
    public function run(): void
    {
        Banner::create([
            'title' => 'NOVA COLEÇÃO DE INVERNO',
            'image_url' => env('APP_URL') . '/storage/banners/inverno.jpg',
            'link_url' => '/products?collection=inverno'
        ]);

        // Criação de produtos em promoção
        Product::factory()->count(8)->create([
            'price' => 199.99,
            'promotional_price' => 99.99
        ]);

        // Criação de produtos novos
        Product::factory()->count(8)->create([
            'created_at' => now()
        ]);
    }
}