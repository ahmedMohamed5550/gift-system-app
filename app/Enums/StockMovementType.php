<?php

namespace App\Enums;

enum StockMovementType: string
{
    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case RETURN = 'return';
    case ADJUSTMENT = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE => 'شراء / توريد',
            self::SALE => 'مبيعات',
            self::RETURN => 'مرتجع مبيعات',
            self::ADJUSTMENT => 'تسوية جردية',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PURCHASE => 'badge-success',
            self::SALE => 'badge-info',
            self::RETURN => 'badge-warning',
            self::ADJUSTMENT => 'badge-primary',
        };
    }

    public static function fromReference(?string $reference): ?self
    {
        if (!$reference) {
            return null;
        }

        return match (strtolower($reference)) {
            'purchase' => self::PURCHASE,
            'sale' => self::SALE,
            'return' => self::RETURN,
            'adjustment' => self::ADJUSTMENT,
            default => null,
        };
    }
}

