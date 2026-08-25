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

    public function googleAuth(\App\Http\Requests\GoogleAuthRequest $request): \Illuminate\Http\JsonResponse
    {
        $response = $this->authService->authenticateWithGoogle($request->validated());
        
        $status = $response['is_new'] ? 201 : 200;
        unset($response['is_new']);

        return response()->json($response, $status);
    }

    public function logout(\Illuminate\Http\Request $request): \Illuminate\Http\Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
    
}