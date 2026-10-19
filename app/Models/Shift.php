<?php

namespace App\Models;

use App\Enums\ShiftStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'user_id',
        'opened_at',
        'closed_at',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'difference',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_cash' => 'decimal:2',
            'closing_cash' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'difference' => 'decimal:2',
        ];
    }

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // --- Scopes ---

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // --- Accessors & Financial Totals ---

    public function getIsOpenAttribute(): bool
    {
        return $this->status === 'open';
    }

    public function getIsClosedAttribute(): bool
    {
        return $this->status === 'closed';
    }

    public function getStatusEnumAttribute(): ?ShiftStatus
    {
        return ShiftStatus::tryFrom($this->status);
    }

    public function getStatusLabelArabicAttribute(): string
    {
        return $this->status_enum?->label() ?? ($this->status === 'open' ? 'مفتوحة' : 'مغلقة');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status_enum?->badgeClass() ?? ($this->status === 'open' ? 'badge-success' : 'badge-secondary');
    }

    /**
     * Cash sales for completed sales in this shift.
     */
    public function getCashSalesTotalAttribute(): float
    {
        return (float) ($this->sales()
            ->where('status', 'completed')
            ->where('payment_method', 'cash')
            ->sum('total') ?? 0);
    }

    /**
     * Vodafone Cash sales (electronic, excluded from physical cash drawer).
     */
    public function getVodafoneCashSalesTotalAttribute(): float
    {
        return (float) ($this->sales()
            ->where('status', 'completed')
            ->where('payment_method', 'vodafone_cash')
            ->sum('total') ?? 0);
    }

    /**
     * InstaPay sales (electronic, excluded from physical cash drawer).
     */
    public function getInstapaySalesTotalAttribute(): float
    {
        return (float) ($this->sales()
            ->where('status', 'completed')
            ->where('payment_method', 'instapay')
            ->sum('total') ?? 0);
    }

    /**
     * Total sales revenue across all payment methods.
     */
    public function getTotalSalesAttribute(): float
    {
        return (float) ($this->sales()
            ->where('status', 'completed')
            ->sum('total') ?? 0);
    }

    /**
     * Cash refunds: approved returns where refund was paid in cash.
     */
    public function getCashReturnsTotalAttribute(): float
    {
        return (float) ($this->returns()
            ->where('status', 'approved')
            ->where(function ($q) {
                $q->where('refund_method', 'cash')
                    ->orWhere(function ($q2) {
                        $q2->whereNull('refund_method')
                            ->whereHas('sale', fn($s) => $s->where('payment_method', 'cash'));
                    });
            })
            ->sum('total_amount') ?? 0);
    }

    /**
     * Total returns across all payment methods.
     */
    public function getTotalReturnsAttribute(): float
    {
        return (float) ($this->returns()
            ->where('status', 'approved')
            ->sum('total_amount') ?? 0);
    }

    /**
     * Active cash expenses paid out of the shift drawer.
     */
    public function getCashExpensesTotalAttribute(): float
    {
        return (float) ($this->expenses()
            ->where('status', 'active')
            ->where('payment_method', 'cash')
            ->sum('amount') ?? 0);
    }

    /**
     * Active non-cash expenses (paid via wallet/bank, excluded from cash drawer).
     */
    public function getNonCashExpensesTotalAttribute(): float
    {
        return (float) ($this->expenses()
            ->where('status', 'active')
            ->where('payment_method', '!=', 'cash')
            ->sum('amount') ?? 0);
    }

    /**
     * Total active expenses across all methods.
     */
    public function getTotalExpensesAttribute(): float
    {
        return (float) ($this->expenses()
            ->where('status', 'active')
            ->sum('amount') ?? 0);
    }

    /**
     * Expected physical cash:
     * Opening cash + Cash sales - Cash refunds - Cash expenses.
     */
    public function getCalculatedExpectedCashAttribute(): float
    {
        $opening = (float) $this->opening_cash;
        $cashSales = $this->cash_sales_total;
        $cashReturns = $this->cash_returns_total;
        $cashExpenses = $this->cash_expenses_total;

        return round($opening + $cashSales - $cashReturns - $cashExpenses, 2);
    }

    /**
     * Difference between actual closing cash and expected physical cash:
     * closing_cash - expected_cash.
     */
    public function getCalculatedDifferenceAttribute(): ?float
    {
        if ($this->closing_cash === null) {
            return null;
        }

        $expected = $this->expected_cash !== null ? (float) $this->expected_cash : $this->calculated_expected_cash;

        return round((float) $this->closing_cash - $expected, 2);
    }

    public function getDifferenceStatusAttribute(): ?string
    {
        $diff = $this->difference ?? $this->calculated_difference;
        if ($diff === null) {
            return null;
        }

        if ($diff > 0.001) {
            return 'surplus';
        }

        if ($diff < -0.001) {
            return 'shortage';
        }

        return 'balanced';
    }

    public function getDifferenceLabelArabicAttribute(): string
    {
        return match ($this->difference_status) {
            'surplus' => 'زيادة في الدرج',
            'shortage' => 'عجز في الدرج',
            'balanced' => 'مطابق تماماً',
            default => 'غير محدد',
        };
    }

    public function getDifferenceBadgeClassAttribute(): string
    {
        return match ($this->difference_status) {
            'surplus' => 'badge-success',
            'shortage' => 'badge-danger',
            'balanced' => 'badge-primary',
            default => 'badge-secondary',
        };
    }
}
