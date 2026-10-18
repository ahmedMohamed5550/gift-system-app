<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'string', 'in:cash,vodafone_cash,instapay'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'يرجى اختيار طريقة الدفع (كاش، فودافون كاش، انستا باي).',
            'payment_method.in' => 'طريقة الدفع المحددة غير صالحة.',
            'discount.numeric' => 'قيمة الخصم يجب أن تكون رقماً.',
            'discount.min' => 'قيمة الخصم لا يمكن أن تكون أقل من الصفر.',
            'items.required' => 'يجب إضافة منتج واحد على الأقل للفاتورة.',
            'items.min' => 'يجب إضافة منتج واحد على الأقل للفاتورة.',
            'items.*.product_id.required' => 'معرف المنتج مطلوب.',
            'items.*.product_id.exists' => 'أحد المنتجات المحددة غير موجود بالنظام.',
            'items.*.quantity.required' => 'كمية المنتج مطلوبة.',
            'items.*.quantity.gt' => 'يجب أن تكون كمية كل منتج أكبر من الصفر.',
        ];
    }
}

