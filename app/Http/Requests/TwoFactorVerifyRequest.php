<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorVerifyRequest extends FormRequest
{
    private const CODE_LENGTH = 6;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // valida o codigo de seis digitos
        return [
            'email' => ['required', 'string', 'email'],
            'code' => ['required', 'string', 'size:' . self::CODE_LENGTH, 'regex:/^[0-9]+$/'],
        ];
    }
}