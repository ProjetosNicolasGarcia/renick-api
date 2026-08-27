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
}