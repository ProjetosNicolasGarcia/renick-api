<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

class ProductService
{
    public function getListedProducts(array $filters): array
    {
        $query = Product::with(['images', 'variants', 'category'])->where('is_active', true);

        if (!empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro de Gênero: Traz o sexo específico + produtos unissex automaticamente
        if (!empty($filters['gender'])) {
            $gender = strtolower(trim($filters['gender']));
            if (in_array($gender, ['masculino', 'feminino'])) {
                $query->whereIn('gender', [$gender, 'unissex']);
            } else {
                $query->where('gender', $gender);
            }
        }

        if (!empty($filters['size'])) {
            $sizes = array_map('trim', explode(',', $filters['size']));
            $query->whereHas('variants', function ($v) use ($sizes) {
                $v->whereIn('size', $sizes);
            });
        }

        // Filtro Exato de Categoria (Pronto para o Admin)
        if (!empty($filters['type'])) {
            $type = strtolower(trim($filters['type']));
            $query->whereHas('category', fn($c) => $c->where('slug', $type));
        }

        if (!empty($filters['collection'])) {
            $col = strtolower(trim($filters['collection']));
            $query->whereHas('collection', fn($c) => $c->where('slug', $col));
        }

        // Promoções dinâmicas: verifica se alguma variante possui promo_price
        if (isset($filters['is_sale']) && in_array($filters['is_sale'], ['true', true, 1, '1'], true)) {
            $query->whereHas('variants', fn($v) => $v->whereNotNull('promo_price'));
        }

        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'best_selling':
                $query->orderBy('id', 'desc');
                break;
            case 'price_asc':
                $query->orderBy(ProductVariant::select('price')->whereColumn('product_id', 'products.id')->orderBy('price', 'asc')->limit(1));
                break;
            case 'price_desc':
                $query->orderBy(ProductVariant::select('price')->whereColumn('product_id', 'products.id')->orderBy('price', 'desc')->limit(1));
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = $filters['per_page'] ?? 12;
        $paginator = $query->paginate($perPage);

        $data = $paginator->map(function ($prod) {
            $firstVariant = $prod->variants->first();
            $firstImage = $prod->images->where('sort_order', 0)->first();

            return [
                'id' => $prod->id,
                'name' => $prod->name,
                'slug' => $prod->slug,
                'price' => $firstVariant ? (float) $firstVariant->price : 0.00,
                'promotional_price' => ($firstVariant && $firstVariant->promo_price) ? (float) $firstVariant->promo_price : null,
                'image_url' => $firstImage ? $firstImage->image_url : null,
            ];
        });

        return [
            'data' => $data->toArray(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ]
        ];
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
        
        $product->images_list = $product->images->map(function ($img) {
            return [
                'url' => $img->image_url,
                'color_slug' => $img->color_slug
            ];
        })->toArray();
        
        $product->variants->transform(function ($variant) use ($product) {
            $colorSlug = \Illuminate\Support\Str::slug($variant->color_name);
            $variantImage = $product->images->where('color_slug', $colorSlug)->where('sort_order', 0)->first();
            $variant->image_url = $variantImage ? $variantImage->image_url : null;
            return $variant;
        });

        $product->rating_summary = ['average' => 5.0, 'total_reviews' => 2];
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