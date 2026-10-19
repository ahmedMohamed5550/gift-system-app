<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'يرجى كتابة سبب إلغاء إشعار المرتجع لتوثيق السجل المالي والمخزني.',
            'reason.min' => 'سبب الإلغاء يجب أن يحتوي على 3 أحرف على الأقل.',
            'reason.max' => 'سبب الإلغاء يجب ألا يتجاوز 500 حرف.',
        ];
    }

    public function attributes(): array
    {
        return [
            'reason' => 'سبب الإلغاء',
        ];
    }
}

