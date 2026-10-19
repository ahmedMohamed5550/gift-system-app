<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'old_values',
        'new_values',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionArabicAttribute(): string
    {
        return \App\Services\ActivityLogService::ACTION_LABELS[$this->action] ?? $this->action;
    }

    public function getSubjectTypeArabicAttribute(): string
    {
        return match ($this->subject_type) {
            \App\Models\User::class => 'مستخدم',
            \App\Models\Product::class => 'منتج',
            \App\Models\Sale::class => 'فاتورة مبيعات',
            \App\Models\SaleReturn::class => 'مرتجع مبيعات',
            \App\Models\Expense::class => 'مصروف',
            \App\Models\Shift::class => 'وردية عمل',
            \App\Models\StockMovement::class => 'حركة مخزون',
            default => class_basename($this->subject_type ?? ''),
        };
    }

    /**
     * Scope for filtering activity logs.
     */
    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('subject_id', $search);
            });
        }

        return $query;
    }
}
