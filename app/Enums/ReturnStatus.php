<?php

namespace App\Enums;

enum ReturnStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'قيد الانتظار',
            self::APPROVED => 'معتمد / مكتمل',
            self::REJECTED => 'مرفوض',
            self::CANCELLED => 'ملغى',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'badge-warning',
            self::APPROVED => 'badge-success',
            self::REJECTED => 'badge-danger',
            self::CANCELLED => 'badge-secondary',
        };
    }

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }
}

