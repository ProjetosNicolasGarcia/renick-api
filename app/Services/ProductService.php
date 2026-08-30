<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;

class ProductService
{
   public function getListedProducts(array $filters): array
    {
        $query = \App\Models\Product::with(['images', 'variants', 'category'])->where('is_active', true);

        if (!empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['gender'])) {
            $genders = array_map('trim', explode(',', strtolower($filters['gender'])));
            $query->where(function($q) use ($genders) {
                $q->whereIn('gender', $genders);
                if (array_intersect(['masculino', 'feminino'], $genders)) {
                    $q->orWhere('gender', 'unissex');
                }
            });
        }

        if (!empty($filters['type'])) {
            $types = array_map('trim', explode(',', strtolower($filters['type'])));
            $query->whereHas('category', fn($c) => $c->whereIn('slug', $types));
        }

        if (!empty($filters['size'])) {
            $sizes = array_map('trim', explode(',', strtoupper($filters['size'])));
            $query->whereHas('variants', fn($v) => $v->whereIn('size', $sizes));
        }

        if (!empty($filters['color'])) {
            $colors = array_map('trim', explode(',', strtolower($filters['color'])));
            $query->whereHas('variants', function ($v) use ($colors) {
                $v->where(function($q) use ($colors) {
                    foreach($colors as $color) {
                        $q->orWhere('color_name', 'like', "%{$color}%");
                    }
                });
            });
        }

        // Filtra pelo valor mínimo efetivo da variante
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $min = (float) $filters['min_price'];
            $variantTable = (new \App\Models\ProductVariant())->getTable();
            $query->whereRaw("(SELECT MIN(COALESCE(NULLIF(promo_price, 0), price)) FROM {$variantTable} WHERE product_id = products.id) >= ?", [$min]);
        }

        // Filtra pelo valor máximo efetivo da variante
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $max = (float) $filters['max_price'];
            $variantTable = (new \App\Models\ProductVariant())->getTable();
            $query->whereRaw("(SELECT MIN(COALESCE(NULLIF(promo_price, 0), price)) FROM {$variantTable} WHERE product_id = products.id) <= ?", [$max]);
        }

        if (!empty($filters['collection'])) {
            $col = strtolower(trim($filters['collection']));
            $query->whereHas('collection', fn($c) => $c->where('slug', $col));
        }

        if (isset($filters['is_sale']) && in_array($filters['is_sale'], ['true', true, 1, '1'], true)) {
            $query->whereHas('variants', fn($v) => $v->whereNotNull('promo_price')->where('promo_price', '>', 0));
        }

        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'best_selling':
                $query->orderBy('id', 'desc');
                break;
            case 'price_asc':
                $query->orderBy(\App\Models\ProductVariant::select('price')->whereColumn('product_id', 'products.id')->orderBy('price', 'asc')->limit(1));
                break;
            case 'price_desc':
                $query->orderBy(\App\Models\ProductVariant::select('price')->whereColumn('product_id', 'products.id')->orderBy('price', 'desc')->limit(1));
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = $filters['per_page'] ?? 12;
        $paginator = $query->paginate($perPage);

        $data = $paginator->map(function ($prod) {
            
            // CORREÇÃO: Puxa sempre a variante mais barata (com ou sem oferta) para a capa
            $cheapestVariant = $prod->variants->sortBy(function ($v) {
                return ($v->promo_price > 0) ? (float) $v->promo_price : (float) $v->price;
            })->first();

            $firstImage = $prod->images->where('sort_order', 0)->first() ?? $prod->images->first();

            return [
                'id' => $prod->id,
                'name' => $prod->name,
                'slug' => $prod->slug,
                'price' => $cheapestVariant ? (float) $cheapestVariant->price : 0.00,
                'promotional_price' => ($cheapestVariant && $cheapestVariant->promo_price > 0) ? (float) $cheapestVariant->promo_price : null,
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
    

    public function getFilterAttributes(): array
    {
        $types = \App\Models\Category::pluck('name')->unique()->values()->toArray();
        $sizes = \App\Models\ProductVariant::pluck('size')->unique()->filter()->values()->toArray();
        sort($sizes);
        $colors = \App\Models\ProductVariant::select('color_name as name', 'color_hex as hex')->distinct()->get()->toArray();
        $minPrice = \App\Models\ProductVariant::min('promo_price') ?? \App\Models\ProductVariant::min('price') ?? 0;
        $maxPrice = \App\Models\ProductVariant::max('price') ?? 500;

        return [
            'genders' => ['Masculino', 'Feminino', 'Bebês', 'Unissex'],
            'types' => $types,
            'sizes' => $sizes,
            'colors' => $colors,
            'price_range' => [
                'min' => (float) $minPrice,
                'max' => (float) $maxPrice,
            ]
        ];
    }

    public function getProductDetails(int $id): Product
    {
        $product = Product::with(['images' => function($q) {
            $q->orderBy('sort_order', 'asc');
        }, 'variants'])->findOrFail($id);

        $saleVariant = $product->variants->firstWhere(function ($v) {
            return !is_null($v->promo_price) && $v->promo_price > 0;
        });
        $firstVariant = $saleVariant ?? $product->variants->first();
        
        $product->price = $firstVariant ? (float) $firstVariant->price : 0.00;
        $product->promotional_price = ($firstVariant && $firstVariant->promo_price > 0) ? (float) $firstVariant->promo_price : null;
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
            $saleVariant = $prod->variants->firstWhere(function ($v) {
                return !is_null($v->promo_price) && $v->promo_price > 0;
            });
            $firstVariant = $saleVariant ?? $prod->variants->first();
            $firstImage = $prod->images->where('sort_order', 0)->first() ?? $prod->images->first();
            
            $prod->price = $firstVariant ? (float) $firstVariant->price : 0.00;
            $prod->promotional_price = ($firstVariant && $firstVariant->promo_price > 0) ? (float) $firstVariant->promo_price : null;
            $prod->image_url = $firstImage ? $firstImage->image_url : null;
            return $prod;
        })->toArray();
    }
}