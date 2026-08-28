<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService) {}

   public function index(Request $request): JsonResponse
    {
        try {
            // Captura todos os filtros da URL diretamente
            $filters = $request->all();
            $products = $this->productService->getListedProducts($filters);
            
            return response()->json($products);
        } catch (\Throwable $e) {
            // Se algo falhar, o erro exato será devolvido para o front-end
            return response()->json([
                'error' => 'Erro interno no servidor',
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->productService->getProductDetails($id);
            
            return response()->json([
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'promotional_price' => $product->promotional_price,
                'installment_info' => $product->installment_info,
                'images' => $product->images_list,
                'variants' => $product->variants,
                'rating_summary' => $product->rating_summary,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Produto não encontrado', 'message' => $e->getMessage()], 404);
        }
    }

    public function related(int $id): JsonResponse
    {
        try {
            $relatedProducts = $this->productService->getRelatedProducts($id);
            return response()->json(['data' => $relatedProducts]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Falha ao buscar relacionados', 'message' => $e->getMessage()], 500);
        }
    }
}