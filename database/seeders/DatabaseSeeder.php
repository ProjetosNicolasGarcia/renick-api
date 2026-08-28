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

        // ==========================================
        // PRODUTO 1: TESTE COMPLETO (Galeria e Variantes)
        // ==========================================
        $masterProduct = Product::create([
            'category_id' => $catMasc->id ?? 1,
            'name' => 'Camiseta Listrada Infantil',
            'slug' => 'camiseta-listrada-infantil-master',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
        ]);

        // 1. Imagens com color_slug e sort_order (Principal = 0, Secundárias > 0)
        $novasImagens = [
            ['url' => '/products/sua-foto-de-produto-laranja.png', 'color' => 'laranja', 'sort' => 0],
            ['url' => '/products/sua-foto-de-produto-secundaria-laranja.png', 'color' => 'laranja', 'sort' => 1],
            ['url' => '/products/sua-foto-de-produto-amarelo.png', 'color' => 'amarelo', 'sort' => 0],
            ['url' => '/products/sua-foto-de-produto-secundaria-amarelo.png', 'color' => 'amarelo', 'sort' => 1],
            ['url' => '/products/sua-foto-de-produto-azul.png', 'color' => 'azul', 'sort' => 0],
            ['url' => '/products/sua-foto-de-produto-secundaria-azul.png', 'color' => 'azul', 'sort' => 1],
        ];

        foreach ($novasImagens as $img) {
            ProductImage::create([
                'product_id' => $masterProduct->id,
                'color_slug' => $img['color'],
                'image_url' => $img['url'],
                'sort_order' => $img['sort'],
            ]);
        }

       // 2. Variantes atreladas ao Produto Master
        $novasVariantes = [
            ['size' => '1', 'color' => 'Laranja', 'hex' => '#D25A00'],
            ['size' => '2', 'color' => 'Laranja', 'hex' => '#D25A00'],
            ['size' => '1', 'color' => 'Amarelo', 'hex' => '#EED202'],
            ['size' => '2', 'color' => 'Amarelo', 'hex' => '#EED202'],
            ['size' => '1', 'color' => 'Azul', 'hex' => '#1E45FB'],
            ['size' => '2', 'color' => 'Azul', 'hex' => '#1E45FB'],
        ];

        foreach ($novasVariantes as $idx => $v) {
            // Força o estoque para 0 apenas na variante Azul Tamanho 1 para permitir o teste
            $stockQuantity = ($v['color'] === 'Azul' && $v['size'] === '1') ? 0 : 10;

            ProductVariant::create([
                'product_id' => $masterProduct->id,
                'sku' => 'SKU-MASTER-' . $idx,
                'size' => $v['size'],
                'color_name' => $v['color'],
                'color_hex' => $v['hex'],
                'price' => 199.99,
                'promo_price' => 189.99,
                'stock_quantity' => $stockQuantity,
                'weight_kg' => 0.300,
                'length_cm' => 20,
                'width_cm' => 15,
                'height_cm' => 5,
            ]);
        }

        // ==========================================
        // DEMAIS PRODUTOS EM OFERTA (Vitrine Landing Page)
        // ==========================================
        for ($i = 2; $i <= 4; $i++) {
            $product = Product::create([
                'category_id' => $catMasc->id ?? 1,
                'name' => 'Camiseta Listrada Infantil ' . $i,
                'slug' => Str::slug('Camiseta Listrada Infantil ' . $i . '-' . uniqid()),
                'description' => 'Camiseta infantil confortável confeccionada em algodão.',
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => '/products/sua-foto-de-produto-laranja.png',
                'color_slug' => 'laranja',
                'sort_order' => 0,
            ]);

            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'SKU-OFERTA-' . $i,
                'size' => '12',
                'color_name' => 'Laranja',
                'color_hex' => '#D25A00',
                'price' => 199.99,
                'promo_price' => 99.99,
                'stock_quantity' => 20,
                'weight_kg' => 0.300,
                'length_cm' => 20,
                'width_cm' => 15,
                'height_cm' => 4,
            ]);
        }

        // ==========================================
        // DEMAIS PRODUTOS EM NOVIDADES (Vitrine Landing Page)
        // ==========================================
        for ($i = 1; $i <= 4; $i++) {
            $product = Product::create([
                'category_id' => $catFem->id ?? 2,
                'name' => 'Casaco Infantil Estampado ' . $i,
                'slug' => Str::slug('Casaco Infantil Estampado ' . $i . '-' . uniqid()),
                'description' => 'Casaco infantil perfeito para dias frios.',
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => '/products/sua-foto-de-produto-rosa.png',
                'color_slug' => 'rosa',
                'sort_order' => 0,
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