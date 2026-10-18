<?php

namespace App\Enums;

enum ExpenseStatus: string
{
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'معتمد / نشط',
            self::CANCELLED => 'ملغي',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'badge-success',
            self::CANCELLED => 'badge-danger',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}

