<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_OPEN = 'open';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Standard predefined note titles.
     */
    public const PREDEFINED_TITLES = [
        'فلوس متأجلة',
        'طلب أو أوردر معلق',
        'نواقص أو طلب بضاعة',
        'متابعة عميل',
        'ملاحظات الوردية',
        'مشكلة أو تنبيه',
        'أخرى',
    ];

    /**
     * Mass assignable attributes.
     * Note: user_id is explicitly omitted to prevent creator spoofing via request input.
     */
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'amount',
        'due_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * The employee who created the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if note has an open status.
     */
    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Check if note has a completed status.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if note has a cancelled status.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Determine if note follow-up is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->isOpen() && $this->due_at && $this->due_at->isPast();
    }

    /**
     * Determine if note follow-up is due today.
     */
    public function isDueToday(): bool
    {
        return $this->due_at && $this->due_at->isToday();
    }

    /**
     * Localized status in Arabic.
     */
    public function getStatusArabicAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'مفتوحة / قيد المتابعة',
            self::STATUS_COMPLETED => 'مكتملة',
            self::STATUS_CANCELLED => 'ملغاة',
            default => $this->status,
        };
    }

    /**
     * Bootstrap badge class for status display.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'badge-warning',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-secondary',
            default => 'badge-light',
        };
    }

    /**
     * Scope for open notes.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope for completed notes.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for cancelled notes.
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope notes according to user authorization rules:
     * Employees strictly see only their own notes; owners see all notes or filter by employee.
     */
    public function scopeForUser(Builder $query, User $user, ?int $filterUserId = null): Builder
    {
        if (! $user->isOwner()) {
            return $query->where('user_id', $user->id);
        }

        if ($filterUserId) {
            return $query->where('user_id', $filterUserId);
        }

        return $query;
    }

    /**
     * Scope query with search and filters.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // 1. Text Search (title, content, amount)
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");

                if (is_numeric($search)) {
                    $q->orWhere('amount', $search);
                }
            });
        }

        // 2. Title Filter
        if (! empty($filters['title'])) {
            $query->where('title', $filters['title']);
        }

        // 3. Status Filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // 4. Creation Date Range
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // 5. Due Date / Follow-up Filters
        if (! empty($filters['due_status'])) {
            match ($filters['due_status']) {
                'overdue' => $query->where('status', self::STATUS_OPEN)
                                   ->whereNotNull('due_at')
                                   ->where('due_at', '<', now()),
                'today' => $query->whereNotNull('due_at')
                                 ->whereDate('due_at', today()),
                'upcoming' => $query->whereNotNull('due_at')
                                    ->where('due_at', '>', now()),
                'has_due' => $query->whereNotNull('due_at'),
                'no_due' => $query->whereNull('due_at'),
                default => null,
            };
        }

        if (! empty($filters['due_from'])) {
            $query->whereDate('due_at', '>=', $filters['due_from']);
        }
        if (! empty($filters['due_to'])) {
            $query->whereDate('due_at', '<=', $filters['due_to']);
        }

        return $query;
    }
}
