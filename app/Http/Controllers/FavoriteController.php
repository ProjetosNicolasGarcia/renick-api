<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddFavoriteRequest;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FavoriteController extends Controller
{
    public function __construct(private FavoriteService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->service->getFavorites($request->user()->id));
    }

    public function store(AddFavoriteRequest $request): JsonResponse
    {
        try {
            $this->service->addFavorite($request->user()->id, $request->product_id);
            return response()->json(['message' => 'adicionado aos favoritos com sucesso.'], 201);
        } catch (ConflictHttpException $e) {
            return response()->json(['code' => 'CONFLICT', 'message' => $e->getMessage()], 409);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->service->removeFavorite($request->user()->id, $id);
            // CORREÇÃO: Retorno compatível com JsonResponse
            return response()->json(null, 204); 
        } catch (ModelNotFoundException $e) {
            return response()->json(['code' => 'NOT_FOUND', 'message' => $e->getMessage()], 404);
        }
    }
}