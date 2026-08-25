<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService
    ) {}

    public function sendResetLink(ForgotPasswordRequest $request): JsonResponse
    {
        $this->passwordResetService->sendResetLink($request->validated());

        return response()->json(['message' => 'Solicitação processada com sucesso.'], 200);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $this->passwordResetService->resetPassword($request->validated());

        return response()->json(['message' => 'Operação realizada com sucesso.'], 200);
    }

    public function validateToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        // utiliza o broker do laravel para checar se o token bate com o hash no banco
        if (! $user || ! Password::broker()->tokenExists($user, $request->token)) {
            return response()->json(['message' => 'O token de redefinição é inválido ou expirou.'], 400);
        }

        return response()->json(['message' => 'Token válido.'], 200);
    }
}