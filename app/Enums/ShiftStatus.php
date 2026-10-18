<?php

namespace App\Enums;

enum ShiftStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'مفتوحة',
            self::CLOSED => 'مغلقة',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::OPEN => 'badge-success',
            self::CLOSED => 'badge-secondary',
        };
    }
}

