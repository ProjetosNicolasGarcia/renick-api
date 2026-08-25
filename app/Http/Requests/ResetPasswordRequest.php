<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // define as regras rigorosas para a nova senha
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => [
                'required', 
                'confirmed', 
                Password::min(8)->mixedCase()->numbers()->symbols(),
                // Regra customizada para impedir a reutilizacao da senha
                function (string $attribute, mixed $value, \Closure $fail) {
                    $user = User::where('email', $this->email)->first();
                    
                    if ($user && Hash::check($value, $user->password)) {
                        $fail('A nova senha deve ser diferente da anterior.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'O campo senha é obrigatório.',
            'password.confirmed' => 'A confirmação da senha não corresponde.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.mixed' => 'A senha deve conter pelo menos uma letra maiúscula e uma minúscula.',
            'password.numbers' => 'A senha deve conter pelo menos um número.',
            'password.symbols' => 'A senha deve conter pelo menos um símbolo especial.',
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'Forneça um email válido.',
            'token.required' => 'O token de redefinição é inválido ou expirou.',
        ];
    }

}