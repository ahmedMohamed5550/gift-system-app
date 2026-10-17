<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'subcategory_id' => ['required', 'exists:subcategories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'purchase_price' => ['required', 'numeric', 'gte:0'],
            'selling_price' => ['required', 'numeric', 'gte:0'],
            'stock_quantity' => ['required', 'numeric', 'gte:0'],
            'minimum_stock' => ['required', 'numeric', 'gte:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}

