<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    // retorna os dados do perfil logado
    public function show(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    // atualiza o perfil exigindo a senha atual
   public function update(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
            'password' => 'nullable|string|min:8',
            'current_password' => 'required|string',
        ]);

        if (! Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['A senha atual está incorreta.'],
            ]);
        }

        $request->user()->email = $request->email;
        
        if ($request->filled('password')) {
            $request->user()->password = Hash::make($request->password);
        }

        $request->user()->save();

        return response()->json($request->user());
    }

    // exclui a conta exigindo a senha atual
    public function destroy(Request $request): Response
    {
        $request->validate([
            'current_password' => 'required|string',
        ]);

        if (! Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['A senha atual está incorreta.'],
            ]);
        }

        $request->user()->delete();

        return response()->noContent();
    }
}