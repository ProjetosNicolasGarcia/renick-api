<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // valida formato do email conforme contrato
        return [
            'email' => ['required', 'string', 'email'],
        ];
    }
}