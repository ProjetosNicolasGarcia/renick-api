<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    // retorna categorias principais com suas subcategorias
    public function index(): JsonResponse
    {
        $categories = Category::whereNull('parent_id')
            ->with('subcategories')
            ->get();

        return response()->json($categories);
    }
}