<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function getListedProducts(array $filters): LengthAwarePaginator
    {
        $query = Product::with(['images', 'variants']);

        if (isset($filters['is_sale']) && filter_var($filters['is_sale'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas('variants', function ($q) {
                $q->whereNotNull('promo_price');
            });
        }

        if (isset($filters['sort'])) {
            if ($filters['sort'] === 'newest') {
                $query->orderBy('created_at', 'desc');
            } elseif ($filters['sort'] === 'best_selling') {
                // fallback provisório para evitar erro 500 até a criação da model OrderItem
                $query->orderBy('id', 'desc');
            } elseif ($filters['sort'] === 'price_asc') {
                $query->orderBy(ProductVariant::select('price')->whereColumn('product_id', 'products.id')->orderBy('price', 'asc')->limit(1));
            } elseif ($filters['sort'] === 'price_desc') {
                $query->orderBy(ProductVariant::select('price')->whereColumn('product_id', 'products.id')->orderBy('price', 'desc')->limit(1));
            }
        }

        $perPage = $filters['per_page'] ?? 12;
        $paginator = $query->paginate($perPage);

        // mapeia e extrai o preco da variante para a raiz do objeto product
        $paginator->getCollection()->transform(function ($product) {
            $firstVariant = $product->variants->first();
            
            $product->price = $firstVariant ? (float) $firstVariant->price : 0.00;
            $product->promotional_price = $firstVariant && $firstVariant->promo_price ? (float) $firstVariant->promo_price : null;
            
            $firstImage = $product->images->first();
            $product->image_url = $firstImage ? $firstImage->image_url : null;
            
            return $product;
        });

        return $paginator;
    }

    public function getProductDetails(int $id): Product
    {
        $product = Product::with(['images' => function($q) {
            $q->orderBy('sort_order', 'asc');
        }, 'variants'])->findOrFail($id);

        $firstVariant = $product->variants->first();
        
        $product->price = $firstVariant ? (float) $firstVariant->price : 0.00;
        $product->promotional_price = ($firstVariant && $firstVariant->promo_price) ? (float) $firstVariant->promo_price : null;
        $product->installment_info = '5% OFF no Pix ou no cartão em até 3x sem juros';
        
        // Adaptação de Contrato (Ponto 3 e 4): Retornamos objetos para o Front-end filtrar o carrossel por cor
        $product->images_list = $product->images->map(function ($img) {
            return [
                'url' => $img->image_url,
                'color_slug' => $img->color_slug
            ];
        })->toArray();
        
        $product->variants->transform(function ($variant) use ($product) {
            $colorSlug = \Illuminate\Support\Str::slug($variant->color_name);
            
            $variantImage = $product->images
                ->where('color_slug', $colorSlug)
                ->where('sort_order', 0)
                ->first();

            $variant->image_url = $variantImage ? $variantImage->image_url : null;
            return $variant;
        });

        $product->rating_summary = [
            'average' => 5.0,
            'total_reviews' => 2
        ];

        return $product;
    }

    public function getRelatedProducts(int $id): array
    {
        $product = Product::findOrFail($id);
        $limit = 8;

        $related = Product::with(['images', 'variants'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $id)
            ->limit($limit)
            ->get();

        if ($related->count() < $limit) {
            $missing = $limit - $related->count();
            $others = Product::with(['images', 'variants'])
                ->where('category_id', '!=', $product->category_id)
                ->where('id', '!=', $id)
                ->limit($missing)
                ->get();
            
            $related = $related->merge($others);
        }

        return $related->map(function ($prod) {
            $firstVariant = $prod->variants->first();
            $firstImage = $prod->images->where('sort_order', 0)->first();

            $prod->price = $firstVariant ? (float) $firstVariant->price : 0.00;
            $prod->promotional_price = ($firstVariant && $firstVariant->promo_price) ? (float) $firstVariant->promo_price : null;
            $prod->image_url = $firstImage ? $firstImage->image_url : null;

            return $prod;
        })->toArray();
    }
}