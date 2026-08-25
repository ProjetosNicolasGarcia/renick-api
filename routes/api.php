<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

#Rotas de autenticação
Route::post('/users', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleAuth']);

// rotas de recuperacao de senha
Route::post('/auth/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::get('/auth/reset-password/validate', [PasswordResetController::class, 'validateToken']);
Route::post('/auth/reset-password', [PasswordResetController::class, 'reset']);

// rotas de 2fa 
Route::post('/auth/2fa/send', [TwoFactorController::class, 'send']);
Route::post('/auth/2fa/verify', [TwoFactorController::class, 'verify']);
