<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    /**
     * Recommended common expense categories for gift shop POS.
     */
    public const DEFAULT_CATEGORIES = [
        'أدوات تغليف وشرائط هدايا',
        'بوفيه وضيافة ومشروبات',
        'نظافة ومستلزمات عامة',
        'صيانة وكهرباء',
        'إيجار وفواتير ومرافق',
        'نقل وشحن بضائع',
        'مطبوعات وأكياس ورقية',
        'مصروفات نثرية متنوعة',
    ];

    /**
     * Record a new business expense.
     * Automatically links to the user's active shift if present and not explicitly provided.
     */
    public function recordExpense(
        int $userId,
        string $category,
        float $amount,
        string $paymentMethod = 'cash',
        ?string $description = null,
        ?int $shiftId = null
    ): Expense {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['مبلغ المصروف يجب أن يكون أكبر من الصفر.'],
            ]);
        }

        // If shift_id was not explicitly specified, attach to current open shift for this user
        if ($shiftId === null) {
            $activeShift = Shift::where('user_id', $userId)->where('status', 'open')->first();
            if ($activeShift) {
                $shiftId = $activeShift->id;
            }
        }

        return DB::transaction(function () use ($userId, $category, $amount, $paymentMethod, $description, $shiftId) {
            $expense = Expense::create([
                'user_id' => $userId,
                'shift_id' => $shiftId,
                'category' => trim($category),
                'amount' => round($amount, 2),
                'payment_method' => $paymentMethod,
                'status' => 'active',
                'description' => $description,
            ]);

            \App\Services\ActivityLogService::log(
                action: 'expense_created',
                subject: $expense,
                description: "تم تسجيل مصروف جديد بقيمة {$expense->amount} ج.م في بند \"{$expense->category}\" بطريقة دفع " . ($expense->payment_method_arabic ?? $expense->payment_method) . ".",
                newValues: [
                    'category' => $expense->category,
                    'amount' => (float) $expense->amount,
                    'payment_method' => $expense->payment_method,
                    'shift_id' => $expense->shift_id,
                    'description' => $expense->description,
                ],
                userId: $userId
            );

            return $expense;
        });
    }

    /**
     * Cancel an expense while preserving complete financial history and audit trail.
     *
     * @throws ValidationException
     */
    public function cancelExpense(Expense $expense, string $reason, int $cancelledByUserId): Expense
    {
        if ($expense->is_cancelled) {
            throw ValidationException::withMessages([
                'status' => ['هذا المصروف ملغي بالفعل.'],
            ]);
        }

        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => ['يرجى توضيح سبب إلغاء المصروف لتوثيق السجل المالي.'],
            ]);
        }

        return DB::transaction(function () use ($expense, $reason, $cancelledByUserId) {
            $oldValues = [
                'status' => 'active',
                'amount' => (float) $expense->amount,
                'category' => $expense->category,
                'payment_method' => $expense->payment_method,
            ];

            $expense->update([
                'status' => 'cancelled',
                'cancellation_reason' => trim($reason),
                'cancelled_at' => now(),
                'cancelled_by' => $cancelledByUserId,
            ]);

            \App\Services\ActivityLogService::log(
                action: 'expense_cancelled',
                subject: $expense,
                description: "تم إلغاء المصروف رقم #{$expense->id} بقيمة {$expense->amount} ج.م (السبب: {$reason}).",
                oldValues: $oldValues,
                newValues: [
                    'status' => 'cancelled',
                    'cancellation_reason' => trim($reason),
                    'cancelled_by' => $cancelledByUserId,
                ],
                userId: $cancelledByUserId
            );

            return $expense;
        });
    }

    /**
     * Update an active expense.
     *
     * @throws ValidationException
     */
    public function updateExpense(Expense $expense, array $data): Expense
    {
        if ($expense->is_cancelled) {
            throw ValidationException::withMessages([
                'status' => ['لا يمكن تعديل مصروف تم إلغاؤه.'],
            ]);
        }

        if (isset($data['amount']) && (float) $data['amount'] <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['مبلغ المصروف يجب أن يكون أكبر من الصفر.'],
            ]);
        }

        return DB::transaction(function () use ($expense, $data) {
            $oldValues = [
                'category' => $expense->category,
                'amount' => (float) $expense->amount,
                'payment_method' => $expense->payment_method,
                'description' => $expense->description,
            ];

            $expense->update($data);

            $newValues = [
                'category' => $expense->category,
                'amount' => (float) $expense->amount,
                'payment_method' => $expense->payment_method,
                'description' => $expense->description,
            ];

            \App\Services\ActivityLogService::log(
                action: 'expense_updated',
                subject: $expense,
                description: "تم تحديث بيانات المصروف رقم #{$expense->id} بقيمة {$expense->amount} ج.م في بند \"{$expense->category}\".",
                oldValues: $oldValues,
                newValues: $newValues
            );

            return $expense;
        });
    }

    /**
     * Return list of suggested expense categories.
     */
    public function getCategories(): array
    {
        $existing = Expense::distinct()->pluck('category')->filter()->toArray();
        $merged = array_unique(array_merge(self::DEFAULT_CATEGORIES, $existing));
        sort($merged);

        return $merged;
    }
}

