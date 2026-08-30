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

        // Mapeamento de cores para distribuir nos produtos base
        $colorMap = [
            'masculino' => ['name' => 'Azul', 'hex' => '#1E45FB'],
            'feminino'  => ['name' => 'Rosa', 'hex' => '#FF69B4'],
            'bebes'     => ['name' => 'Verde', 'hex' => '#CDF22B'],
        ];

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

                $colorData = $colorMap[$gender];

                ProductImage::create([
                    'product_id' => $prod->id,
                    'image_url' => '/products/sua-foto-de-produto-' . Str::slug($colorData['name']) . '.png',
                    'color_slug' => Str::slug($colorData['name']),
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

                // Cria uma variante para cada tamanho do array acima, usando a cor do gênero
                foreach ($sizesToAssign as $size) {
                    ProductVariant::create([
                        'product_id' => $prod->id,
                        'sku' => strtoupper("SKU-{$gender}-{$slug}-{$size}"),
                        'size' => $size,
                        'color_name' => $colorData['name'],
                        'color_hex' => $colorData['hex'],
                        'price' => rand(80, 150) + 0.99,
                        'promo_price' => rand(0, 1) ? rand(50, 79) + 0.99 : null, // Ofertas aleatórias
                        'stock_quantity' => 15,
                        'weight_kg' => 0.3,
                        'length_cm' => 20,
                        'width_cm' => 15,
                        'height_cm' => 5,
                    ]);
                }
            }
        }

        // =========================================================================
        // 3. PRODUTO ESPECÍFICO DE MÚLTIPLAS CORES (Para testar o filtro de cor)
        // =========================================================================
        $multiColorProduct = Product::create([
            'category_id' => $categories['casacos']->id,
            'collection_id' => null,
            'gender' => 'unissex',
            'name' => 'Casaco Teste Múltiplas Cores',
            'slug' => 'casaco-teste-multiplas-cores',
            'description' => 'Produto criado especificamente para testar o filtro com múltiplas cores associadas ao mesmo item.',
            'is_active' => true,
        ]);

        $multiColors = [
            ['name' => 'Preto', 'hex' => '#0A0A0A'],
            ['name' => 'Branco', 'hex' => '#FAFAFA'],
            ['name' => 'Vermelho', 'hex' => '#D22A31'],
        ];

        foreach ($multiColors as $idx => $color) {
            ProductImage::create([
                'product_id' => $multiColorProduct->id,
                'image_url' => '/products/sua-foto-de-produto-' . Str::slug($color['name']) . '.png',
                'color_slug' => Str::slug($color['name']),
                'sort_order' => 0,
            ]);

            ProductVariant::create([
                'product_id' => $multiColorProduct->id,
                'sku' => "SKU-MULTI-" . strtoupper(Str::slug($color['name'])),
                'size' => 'M',
                'color_name' => $color['name'],
                'color_hex' => $color['hex'],
                // Valores fixos para garantir estabilidade visual no teste
                'price' => 199.99, 
                'promo_price' => null,
                'stock_quantity' => 10,
                'weight_kg' => 0.5,
                'length_cm' => 30,
                'width_cm' => 20,
                'height_cm' => 10,
            ]);
        }
    }
}