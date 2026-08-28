<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:100',
            'gender' => 'nullable|in:masculino,feminino,bebes,unissex',
            'type' => 'nullable|string|max:100',
            'collection' => 'nullable|string',
            'size' => 'nullable|string|max:50',
            'is_sale' => 'nullable|in:true,false,1,0',
            'sort' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}