<?php

namespace App\Services;

use App\Models\Favorite;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class FavoriteService
{
    // retorna a listagem formatada conforme o contrato openapi
    public function getFavorites(int $userId): array
    {
        $favorites = Favorite::with(['product.images', 'product.variants'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        $data = $favorites->map(function ($fav) {
            $prod = $fav->product;
            
            $cheapestVariant = $prod->variants->sortBy(function ($v) {
                return ($v->promo_price > 0) ? (float) $v->promo_price : (float) $v->price;
            })->first();

            $firstImage = $prod->images->where('sort_order', 0)->first() ?? $prod->images->first();

            return [
                'id' => $fav->id,
                'product' => [
                    'id' => $prod->id,
                    'name' => $prod->name,
                    'slug' => $prod->slug,
                    'price' => $cheapestVariant ? (float) $cheapestVariant->price : 0.00,
                    'promotional_price' => ($cheapestVariant && $cheapestVariant->promo_price > 0) ? (float) $cheapestVariant->promo_price : null,
                    'image_url' => $firstImage ? $firstImage->image_url : null,
                    'is_favorite' => true,
                ],
                'created_at' => $fav->created_at->toIso8601String(),
            ];
        });

        return ['data' => $data->toArray()];
    }

    // impede duplicidade com early return de exception 409
    public function addFavorite(int $userId, int $productId): void
    {
        $exists = Favorite::where('user_id', $userId)->where('product_id', $productId)->exists();
        
        if ($exists) {
            throw new ConflictHttpException('produto já favoritado.');
        }

        Favorite::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);
    }

    // deleta baseado no identificador do produto
    public function removeFavorite(int $userId, int $productId): void
    {
        $deleted = Favorite::where('user_id', $userId)->where('product_id', $productId)->delete();
        
        if (!$deleted) {
            throw new ModelNotFoundException('favorito não encontrado.');
        }
    }
}