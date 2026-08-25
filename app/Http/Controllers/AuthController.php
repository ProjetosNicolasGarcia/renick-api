<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

   public function register(RegisterUserRequest $request): JsonResponse
    {
        $response = $this->authService->registerUser($request->validated());
        // retorna 202 indicando que o fluxo precisa do 2FA
        return response()->json($response, 202); 
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $response = $this->authService->authenticateUser($request->validated());
        // retorna 202 indicando que o fluxo precisa do 2FA
        return response()->json($response, 202);
    }
}