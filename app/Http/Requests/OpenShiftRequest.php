<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpenShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opening_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'opening_cash.required' => 'يرجى إدخال مبلغ العهدة النقدية لافتتاح الوردية.',
            'opening_cash.numeric' => 'مبلغ العهدة يجب أن يكون رقماً صحيحاً أو عشرياً.',
            'opening_cash.min' => 'مبلغ العهدة الافتتاحية لا يمكن أن يكون سالباً.',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 500 حرف.',
        ];
    }

    public function attributes(): array
    {
        return [
            'opening_cash' => 'العهدة الافتتاحية',
            'notes' => 'الملاحظات',
        ];
    }
}

