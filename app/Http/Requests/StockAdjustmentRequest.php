<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'adjustment_mode' => ['required', 'string', 'in:relative,absolute'],
            'direction' => ['required_if:adjustment_mode,relative', 'nullable', 'string', 'in:add,subtract'],
            'quantity' => ['required_if:adjustment_mode,relative', 'nullable', 'numeric', 'min:0.001'],
            'counted_quantity' => ['required_if:adjustment_mode,absolute', 'nullable', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'يرجى اختيار المنتج المطلوب تسوية رصيده.',
            'product_id.exists' => 'المنتج المحدد غير موجود بالنظام.',
            'adjustment_mode.required' => 'يرجى تحديد نمط التسوية الجردية.',
            'direction.required_if' => 'يرجى تحديد اتجاه التسوية (إضافة أو خصم).',
            'quantity.required_if' => 'يرجى إدخال كمية التسوية المطلوبة.',
            'quantity.min' => 'يجب أن تكون كمية التسوية أكبر من الصفر.',
            'counted_quantity.required_if' => 'يرجى إدخال الرصيد الفعلي بعد الجرد.',
            'counted_quantity.min' => 'الرصيد الفعلي لا يمكن أن يكون سالباً.',
            'reason.required' => 'يرجى كتابة سبب التسوية الجردية لتوثيق العملية.',
            'reason.min' => 'سبب التسوية يجب أن يحتوي على 3 أحرف على الأقل.',
            'reason.max' => 'سبب التسوية يجب ألا يتجاوز 500 حرف.',
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'المنتج',
            'adjustment_mode' => 'نوع التسوية',
            'direction' => 'اتجاه التسوية',
            'quantity' => 'كمية التسوية',
            'counted_quantity' => 'الرصيد الفعلي',
            'reason' => 'سبب التسوية',
        ];
    }
}

