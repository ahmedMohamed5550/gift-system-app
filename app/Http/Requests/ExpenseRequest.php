<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'in:cash,vodafone_cash,instapay'],
            'shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'يرجى تحديد أو إدخال بند المصروف.',
            'category.max' => 'بند المصروف يجب ألا يتجاوز 100 حرف.',
            'amount.required' => 'يرجى إدخال مبلغ المصروف.',
            'amount.numeric' => 'مبلغ المصروف يجب أن يكون رقماً صحيحاً أو عشرياً.',
            'amount.min' => 'مبلغ المصروف يجب أن يكون أكبر من الصفر.',
            'payment_method.required' => 'يرجى اختيار طريقة الدفع (كاش، فودافون كاش، انستا باي).',
            'payment_method.in' => 'طريقة الدفع المختارة غير صالحة.',
            'shift_id.exists' => 'الوردية المحددة غير موجودة بالنظام.',
            'description.max' => 'البيان يجب ألا يتجاوز 1000 حرف.',
        ];
    }

    public function attributes(): array
    {
        return [
            'category' => 'بند المصروف',
            'amount' => 'مبلغ المصروف',
            'payment_method' => 'طريقة الدفع',
            'shift_id' => 'الوردية المرتبطة',
            'description' => 'بيان وتفاصيل المصروف',
        ];
    }
}

