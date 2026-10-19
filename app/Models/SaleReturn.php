<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleReturn extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'return_number',
        'sale_id',
        'user_id',
        'shift_id',
        'reason',
        'cancellation_reason',
        'total_amount',
        'refund_method',
        'status',
        'cancelled_at',
        'cancelled_by',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'subject_id')->where('subject_type', self::class);
    }

    // --- Scopes ---

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    // --- Accessors & Helpers ---

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'معتمد / مكتمل',
            'pending' => 'قيد الانتظار',
            'rejected' => 'مرفوض',
            'cancelled' => 'ملغى',
            default => $this->status ?? '-',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'badge-success',
            'pending' => 'badge-warning',
            'rejected' => 'badge-danger',
            'cancelled' => 'badge-secondary',
            default => 'badge-light',
        };
    }

    public function getRefundMethodLabelAttribute(): string
    {
        return match ($this->refund_method) {
            'cash' => 'نقداً (كاش)',
            'vodafone_cash' => 'فودافون كاش',
            'instapay' => 'انستا باي',
            default => $this->refund_method ?? 'نقداً (افتراضي)',
        };
    }

    public function getRefundMethodBadgeClassAttribute(): string
    {
        return match ($this->refund_method) {
            'cash' => 'badge-success',
            'vodafone_cash' => 'badge-danger',
            'instapay' => 'badge-primary',
            default => 'badge-secondary',
        };
    }
}
