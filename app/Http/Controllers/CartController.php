<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartItemRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $service) {}

    public function show(Request $request): JsonResponse
    {
        $data = $this->service->getCartData($request);
        return $this->formatResponse($data);
    }

    public function store(AddCartItemRequest $request): JsonResponse
    {
        $data = $this->service->addItem($request, $request->variant_id, $request->quantity);
        return $this->formatResponse($data);
    }

    public function destroy(Request $request, int $itemId): JsonResponse
    {
        $data = $this->service->removeItem($request, $itemId);
        return $this->formatResponse($data);
    }

    private function formatResponse(array $data): JsonResponse
    {
        $response = response()->json($data);
        
        if (!empty($data['session_id'])) {
            $response->header('X-Cart-Session-Id', $data['session_id']);
        }
        
        return $response;
    }
}