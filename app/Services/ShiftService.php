<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShiftService
{
    /**
     * Get the current active open shift for a given user.
     */
    public function getActiveShift(User|int $user): ?Shift
    {
        $userId = $user instanceof User ? $user->id : $user;

        return Shift::where('user_id', $userId)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();
    }

    /**
     * Check if a user currently has an open shift.
     */
    public function hasActiveShift(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return Shift::where('user_id', $userId)
            ->where('status', 'open')
            ->exists();
    }

    /**
     * Open a new shift for the user with initial cash drawer balance.
     * Enforces the business rule that prevents duplicate open shifts.
     *
     * @throws ValidationException
     */
    public function openShift(User|int $user, float $openingCash, ?string $notes = null): Shift
    {
        $userId = $user instanceof User ? $user->id : $user;

        if ($openingCash < 0) {
            throw ValidationException::withMessages([
                'opening_cash' => ['رصيد افتتاح الوردية لا يمكن أن يكون سالباً.'],
            ]);
        }

        return DB::transaction(function () use ($userId, $openingCash, $notes) {
            // Check for existing open shift with row locking on the user record to prevent race conditions
            $existing = Shift::where('user_id', $userId)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'shift' => ['يوجد وردية عمل مفتوحة بالفعل لهذا المستخدم (رقم #' . $existing->id . '). يرجى إغلاقها أولاً قبل بدء وردية جديدة.'],
                ]);
            }

            $shift = Shift::create([
                'user_id' => $userId,
                'opened_at' => now(),
                'opening_cash' => round($openingCash, 2),
                'status' => 'open',
                'notes' => $notes,
            ]);

            \App\Services\ActivityLogService::log(
                action: 'shift_opened',
                subject: $shift,
                description: "تم فتح وردية عمل جديدة رقم #{$shift->id} بعهدة نقدية أولية قدرها {$shift->opening_cash} ج.م.",
                newValues: [
                    'shift_id' => $shift->id,
                    'opening_cash' => (float) $shift->opening_cash,
                    'opened_at' => $shift->opened_at->toDateTimeString(),
                    'notes' => $shift->notes,
                ],
                userId: $userId
            );

            return $shift;
        });
    }

    /**
     * Reconcile cash and close an open shift with row locking and audit verification.
     *
     * Expected physical cash = Opening cash + Cash sales - Cash refunds - Cash expenses.
     *
     * @throws ValidationException
     */
    public function closeShift(Shift|int $shift, float $actualClosingCash, ?string $notes = null): Shift
    {
        $shiftId = $shift instanceof Shift ? $shift->id : $shift;

        if ($actualClosingCash < 0) {
            throw ValidationException::withMessages([
                'closing_cash' => ['المبلغ الفعلي المتبقي بالدرج لا يمكن أن يكون سالباً.'],
            ]);
        }

        return DB::transaction(function () use ($shiftId, $actualClosingCash, $notes) {
            /** @var Shift $lockedShift */
            $lockedShift = Shift::where('id', $shiftId)->lockForUpdate()->firstOrFail();

            // Prevent invalid state transitions
            if ($lockedShift->is_closed) {
                throw ValidationException::withMessages([
                    'status' => ['هذه الوردية مغلقة بالفعل ولا يمكن إعادة إغلاقها.'],
                ]);
            }

            // Calculate exact expected physical cash based on all recorded transactions
            $expectedCash = $lockedShift->calculated_expected_cash;
            $difference = round($actualClosingCash - $expectedCash, 2);

            $mergedNotes = $lockedShift->notes;
            if ($notes) {
                $mergedNotes = $mergedNotes
                    ? ($mergedNotes . " | ملاحظات الإغلاق: " . $notes)
                    : $notes;
            }

            $oldValues = [
                'status' => 'open',
                'opening_cash' => (float) $lockedShift->opening_cash,
            ];

            $lockedShift->update([
                'closed_at' => now(),
                'closing_cash' => round($actualClosingCash, 2),
                'expected_cash' => $expectedCash,
                'difference' => $difference,
                'status' => 'closed',
                'notes' => $mergedNotes,
            ]);

            \App\Services\ActivityLogService::log(
                action: 'shift_closed',
                subject: $lockedShift,
                description: "تم إغلاق الوردية رقم #{$lockedShift->id}: النقدية الفعلية {$lockedShift->closing_cash} ج.م، المتوقعة {$lockedShift->expected_cash} ج.م، الفارق: {$lockedShift->difference} ج.م.",
                oldValues: $oldValues,
                newValues: [
                    'status' => 'closed',
                    'closing_cash' => (float) $lockedShift->closing_cash,
                    'expected_cash' => (float) $lockedShift->expected_cash,
                    'difference' => (float) $lockedShift->difference,
                    'closed_at' => $lockedShift->closed_at->toDateTimeString(),
                ],
                userId: auth()->id() ?? $lockedShift->user_id
            );

            return $lockedShift;
        });
    }

    /**
     * Get a comprehensive financial reconciliation report for a shift.
     */
    public function getShiftFinancialSummary(Shift $shift): array
    {
        return [
            'shift_id' => $shift->id,
            'user' => $shift->user,
            'opened_at' => $shift->opened_at,
            'closed_at' => $shift->closed_at,
            'status' => $shift->status,
            'opening_cash' => (float) $shift->opening_cash,

            // Sales breakdown
            'sales' => [
                'cash' => $shift->cash_sales_total,
                'vodafone_cash' => $shift->vodafone_cash_sales_total,
                'instapay' => $shift->instapay_sales_total,
                'total' => $shift->total_sales,
                'count' => $shift->sales()->where('status', 'completed')->count(),
            ],

            // Returns breakdown
            'returns' => [
                'cash' => $shift->cash_returns_total,
                'total' => $shift->total_returns,
                'count' => $shift->returns()->where('status', 'approved')->count(),
            ],

            // Expenses breakdown
            'expenses' => [
                'cash' => $shift->cash_expenses_total,
                'non_cash' => $shift->non_cash_expenses_total,
                'total' => $shift->total_expenses,
                'count' => $shift->expenses()->where('status', 'active')->count(),
            ],

            // Cash Drawer Reconciliation
            'reconciliation' => [
                'opening_cash' => (float) $shift->opening_cash,
                'cash_sales' => $shift->cash_sales_total,
                'cash_returns' => $shift->cash_returns_total,
                'cash_expenses' => $shift->cash_expenses_total,
                'expected_cash' => $shift->expected_cash !== null ? (float) $shift->expected_cash : $shift->calculated_expected_cash,
                'actual_closing_cash' => $shift->closing_cash !== null ? (float) $shift->closing_cash : null,
                'difference' => $shift->difference !== null ? (float) $shift->difference : $shift->calculated_difference,
                'difference_status' => $shift->difference_status,
            ],
        ];
    }
}

