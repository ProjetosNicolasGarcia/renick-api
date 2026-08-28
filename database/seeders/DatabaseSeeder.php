<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categorias Limpas (Apenas o Tipo da Roupa)
        $subNames = ['Camisetas', 'Camisas', 'Casacos', 'Calças', 'Bermudas', 'Conjuntos'];
        $categories = [];
        
        foreach ($subNames as $name) {
            $categories[Str::slug($name)] = Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }

        $colInverno = Collection::firstOrCreate(['slug' => 'inverno'], ['name' => 'Inverno 2026', 'image_url' => '/collections/inverno.png']);

        Banner::insert([
            ['image_url' => '/banners/seu-banner-principal.jpg', 'image_url_mobile' => '/banners/seu-banner-principal-mobile.jpg', 'link_url' => '/products?collection=inverno'],
            ['image_url' => '/banners/banner-2.jpg', 'image_url_mobile' => '/banners/banner-2-mobile.jpg', 'link_url' => '/products?gender=masculino'],
            ['image_url' => '/banners/banner-3.jpg', 'image_url_mobile' => '/banners/banner-3-mobile.jpg', 'link_url' => '/products?gender=feminino'],
        ]);

        // 2. Produtos Focados: 1 para cada Categoria por Gênero (Pulando Conjuntos)
        $gendersToSeed = ['masculino', 'feminino', 'bebes'];
        
        foreach ($gendersToSeed as $gender) {
            foreach ($categories as $slug => $cat) {
                if ($slug === 'conjuntos') continue; // Deixa conjuntos vazio para teste

                $prodName = $cat->name . ' ' . ucfirst($gender);
                
                // Transforma a Camiseta do Masculino em Unissex para teste cruzado
                if ($gender === 'masculino' && $slug === 'camisetas') {
                    $prodName = 'Camiseta Unissex Básica';
                    $productGender = 'unissex';
                } else {
                    $productGender = $gender;
                }

                $prod = Product::create([
                    'category_id' => $cat->id,
                    'collection_id' => ($slug === 'casacos') ? $colInverno->id : null,
                    'gender' => $productGender,
                    'name' => $prodName,
                    'slug' => Str::slug($prodName . '-' . uniqid()),
                    'description' => "Produto criado para testar a listagem de {$cat->name} no gênero {$gender}.",
                    'is_active' => true,
                ]);

                ProductImage::create([
                    'product_id' => $prod->id,
                    'image_url' => '/products/sua-foto-de-produto-azul.png',
                    'color_slug' => 'padrao',
                    'sort_order' => 0,
                ]);

                // Atribuição estruturada de tamanhos com base na subcategoria
                $sizesToAssign = match($slug) {
                    'camisetas' => ['1', '10'],
                    'camisas'   => ['2', '12'],
                    'casacos'   => ['3', '14'],
                    'calcas'    => ['4', '16'],
                    'bermudas'  => ['6', '8'],
                    default     => ['M'],
                };

                // Cria uma variante para cada tamanho do array acima
                foreach ($sizesToAssign as $size) {
                    ProductVariant::create([
                        'product_id' => $prod->id,
                        'sku' => strtoupper("SKU-{$gender}-{$slug}-{$size}"),
                        'size' => $size,
                        'color_name' => 'Padrão',
                        'color_hex' => '#1E45FB',
                        'price' => rand(80, 150) + 0.99,
                        'promo_price' => rand(0, 1) ? rand(50, 79) + 0.99 : null,
                        'stock_quantity' => 15,
                        'weight_kg' => 0.3,
                        'length_cm' => 20,
                        'width_cm' => 15,
                        'height_cm' => 5,
                    ]);
                }
            }
        }
    }
}