<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number',
        'user_id',
        'shift_id',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'subject_id')->where('subject_type', self::class);
    }

    // --- Helper Methods & Accessors ---

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'نقداً (كاش)',
            'vodafone_cash' => 'فودافون كاش',
            'instapay' => 'انستا باي',
            default => $this->payment_method ?? '-',
        };
    }

    public function getPaymentMethodBadgeClassAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'badge-success',
            'vodafone_cash' => 'badge-danger',
            'instapay' => 'badge-primary',
            default => 'badge-secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'completed' ? 'مكتملة' : 'ملغاة';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status === 'completed' ? 'badge-success' : 'badge-danger';
    }
}
