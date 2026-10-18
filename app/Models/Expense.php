<?php

namespace App\Models;

use App\Enums\ExpenseStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'shift_id',
        'category',
        'amount',
        'payment_method',
        'status',
        'description',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // --- Scopes ---

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeCash(Builder $query): Builder
    {
        return $query->where('payment_method', 'cash');
    }

    public function scopeForShift(Builder $query, int $shiftId): Builder
    {
        return $query->where('shift_id', $shiftId);
    }

    // --- Accessors & Helpers ---

    public function getIsCashAttribute(): bool
    {
        return $this->payment_method === 'cash';
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getPaymentMethodEnumAttribute(): ?PaymentMethod
    {
        return PaymentMethod::tryFrom($this->payment_method);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return $this->payment_method_enum?->label() ?? match ($this->payment_method) {
            'cash' => 'نقداً (كاش)',
            'vodafone_cash' => 'فودافون كاش',
            'instapay' => 'انستا باي',
            default => $this->payment_method ?: 'نقداً',
        };
    }

    public function getPaymentMethodBadgeClassAttribute(): string
    {
        return $this->payment_method_enum?->badgeClass() ?? match ($this->payment_method) {
            'cash' => 'badge-success',
            'vodafone_cash' => 'badge-danger',
            'instapay' => 'badge-primary',
            default => 'badge-secondary',
        };
    }

    public function getStatusEnumAttribute(): ?ExpenseStatus
    {
        return ExpenseStatus::tryFrom($this->status);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status_enum?->label() ?? ($this->status === 'cancelled' ? 'ملغي' : 'معتمد');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status_enum?->badgeClass() ?? ($this->status === 'cancelled' ? 'badge-danger' : 'badge-success');
    }
}
