<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_id' => ['required', 'integer', 'exists:sales,id'],
            'refund_method' => ['required', 'string', 'in:cash,vodafone_cash,instapay'],
            'reason' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'integer', 'exists:sale_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'sale_id.required' => 'معرف الفاتورة الأصلية مطلوب.',
            'sale_id.exists' => 'الفاتورة الأصلية غير موجودة بالنظام.',
            'refund_method.required' => 'طريقة استرداد المبلغ مطلوبة.',
            'refund_method.in' => 'طريقة الاسترداد المحددة غير صالحة.',
            'reason.max' => 'سبب المرتجع لا يجب أن يتجاوز 500 حرف.',
            'items.required' => 'يجب تحديد الأصناف المراد إرجاعها.',
            'items.min' => 'يجب تحديد صنف واحد على الأقل.',
            'items.*.sale_item_id.required' => 'معرف بند الفاتورة مطلوب.',
            'items.*.sale_item_id.exists' => 'أحد البنود المحددة غير موجود في الفاتورة.',
            'items.*.quantity.required' => 'كمية المرتجع مطلوبة.',
            'items.*.quantity.min' => 'كمية المرتجع لا يمكن أن تكون أقل من الصفر.',
        ];
    }
}

