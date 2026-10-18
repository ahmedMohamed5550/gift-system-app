<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'closing_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'closing_cash.required' => 'يرجى إدخال المبلغ الفعلي المتبقي في الدرج عند الإغلاق.',
            'closing_cash.numeric' => 'المبلغ الفعلي يجب أن يكون قيمة رقمية.',
            'closing_cash.min' => 'المبلغ الفعلي لا يمكن أن يكون سالباً.',
            'notes.max' => 'ملاحظات الإغلاق يجب ألا تتجاوز 500 حرف.',
        ];
    }

    public function attributes(): array
    {
        return [
            'closing_cash' => 'المبلغ الفعلي بالدرج',
            'notes' => 'ملاحظات الإغلاق',
        ];
    }
}

