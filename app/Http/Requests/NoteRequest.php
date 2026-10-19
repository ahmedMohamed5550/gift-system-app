<?php

namespace App\Http\Requests;

use App\Models\Note;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title_type' => ['nullable', 'string', Rule::in(Note::PREDEFINED_TITLES)],
            'custom_title' => ['nullable', 'string', 'max:255', 'required_if:title_type,أخرى'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'due_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', Rule::in([Note::STATUS_OPEN, Note::STATUS_COMPLETED, Note::STATUS_CANCELLED])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $resolved = $this->getResolvedTitle();
            if (empty($resolved)) {
                $validator->errors()->add('title_type', 'يرجى اختيار عنوان للملاحظة من القائمة أو كتابة عنوان مخصص عند اختيار "أخرى".');
            }
        });
    }

    /**
     * Resolves the final note title from title_type and custom_title or direct title input.
     */
    public function getResolvedTitle(): string
    {
        if ($this->filled('title_type')) {
            $type = (string) $this->input('title_type');
            if ($type === 'أخرى') {
                return trim((string) $this->input('custom_title'));
            }
            return $type;
        }

        return trim((string) $this->input('title'));
    }

    public function messages(): array
    {
        return [
            'title_type.in' => 'نوع العنوان المحدد غير صالح.',
            'custom_title.required_if' => 'يرجى كتابة العنوان المخصص عند اختيار "أخرى".',
            'custom_title.max' => 'العنوان المخصص يجب ألا يتجاوز 255 حرفاً.',
            'content.required' => 'يرجى كتابة نص وتفاصيل الملاحظة.',
            'amount.numeric' => 'المبلغ يجب أن يكون قيمة رقمية صحيحة.',
            'amount.min' => 'المبلغ لا يمكن أن يكون سالباً.',
            'amount.max' => 'المبلغ المدخل كبير جداً.',
            'due_at.date' => 'تاريخ ووقت المتابعة غير صحيح.',
            'status.in' => 'حالة الملاحظة المحددة غير صالحة.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title_type' => 'نوع الملاحظة',
            'custom_title' => 'العنوان المخصص',
            'title' => 'عنوان الملاحظة',
            'content' => 'نص الملاحظة',
            'amount' => 'المبلغ المرتبط',
            'due_at' => 'موعد المتابعة',
            'status' => 'حالة الملاحظة',
        ];
    }
}

