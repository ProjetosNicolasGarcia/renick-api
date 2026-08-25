<?php

namespace App\Http\Controllers;

use App\Http\Requests\TwoFactorVerifyRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class TwoFactorController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function verify(TwoFactorVerifyRequest $request): JsonResponse
    {
        $response = $this->authService->verifyTwoFactor($request->validated());

        return response()->json($response, 200);
    }

    public function send(): JsonResponse
    {
        return response()->json(['message' => 'Código reenviado com sucesso.'], 200);
    }
}