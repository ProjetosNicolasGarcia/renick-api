<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartService
{
    public function handleCart(Request $request): Cart
    {
        // 1. Carrinho Exclusivo do Usuário Autenticado
        if ($user = $request->user('sanctum')) {
            $cart = Cart::where('user_id', $user->id)->first();
            
            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => $user->id,
                    'session_id' => (string) Str::uuid()
                ]);
            }
            return $cart;
        }

        // 2. Carrinho Exclusivo do Visitante (Anônimo)
        $sessionId = $request->header('X-Cart-Session-Id');
        
        if ($sessionId) {
            // Proteção: Impede estritamente que um visitante "sequestre" o carrinho de uma conta
            $cart = Cart::where('session_id', $sessionId)
                        ->whereNull('user_id')
                        ->first();
                        
            if ($cart) {
                return $cart;
            }
        }

        return Cart::create([
            'session_id' => (string) Str::uuid(),
            'user_id' => null
        ]);
    }

    public function getCartData(Request $request): array
    {
        $cart = $this->handleCart($request);
        return $this->formatCart($cart);
    }

    public function addItem(Request $request, int $variantId, int $quantity): array
    {
        $cart = $this->handleCart($request);
        
        $item = $cart->items()->where('product_variant_id', $variantId)->first();

        if ($item) {
            $item->quantity += $quantity;
            $item->save();
        } else {
            $cart->items()->create([
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
            ]);
        }

        return $this->formatCart($cart);
    }

    public function removeItem(Request $request, int $itemId): array
    {
        $cart = $this->handleCart($request);
        $cart->items()->where('id', $itemId)->delete();
        
        return $this->formatCart($cart);
    }

    private function formatCart(Cart $cart): array
    {
        $items = $cart->items()->with(['variant.product.images'])->get();
        $formattedItems = [];
        $subtotal = 0;
        $totalDiscount = 0;

        foreach ($items as $item) {
            $variant = $item->variant;
            if (!$variant) continue;

            $product = $variant->product;
            if (!$product) continue;

            $originalPrice = $variant->price ?? 0;
            $price = $variant->promo_price ?? $variant->promotional_price ?? $originalPrice;
            
            $totalPrice = $price * $item->quantity;
            $subtotal += $totalPrice;

            if ($originalPrice > $price) {
                $totalDiscount += ($originalPrice - $price) * $item->quantity;
            }

            $imageUrl = null;
            if ($product->images && $product->images->isNotEmpty()) {
                $colorSlug = Str::slug($variant->color_name);
                $variantImage = $product->images->firstWhere('color_slug', $colorSlug);
                
                if ($variantImage) {
                    $imageUrl = $variantImage->url ?? $variantImage->image_url ?? null;
                } else {
                    $firstImage = $product->images->first();
                    $imageUrl = $firstImage->url ?? $firstImage->image_url ?? null;
                }
            }

            $formattedItems[] = [
                'id' => $item->id,
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'product_name' => $product->name,
                'image_url' => $imageUrl,
                'color' => $variant->color_name ?? '',
                'size' => $variant->size ?? '',
                'unit_price' => (float) $price,
                'original_price' => (float) $originalPrice,
                'quantity' => $item->quantity,
                'subtotal' => (float) $totalPrice,
            ];
        }

        return [
            'items' => $formattedItems,
            'items_count' => $items->sum('quantity'),
            'subtotal' => round($subtotal, 2),
            'discount' => round($totalDiscount, 2),
            'coupon' => null,
            'total' => round($subtotal, 2),
            // Defesa: Só devolve ID de sessão se for carrinho anônimo. Impede cache acidental no Front.
            'session_id' => $cart->user_id ? null : $cart->session_id 
        ];
    }
}