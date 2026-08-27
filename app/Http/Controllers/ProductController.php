<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService) {}

    // lista os produtos com base nos filtros da query string
    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->getListedProducts($request->all());
        return response()->json($products);
    }
}