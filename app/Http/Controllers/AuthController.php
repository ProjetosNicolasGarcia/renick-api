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
        // repassa os dados validados para o servico
        $response = $this->authService->registerUser($request->validated());

        return response()->json($response, 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $response = $this->authService->authenticateUser($request->validated());

        return response()->json($response, 200);
    }
}