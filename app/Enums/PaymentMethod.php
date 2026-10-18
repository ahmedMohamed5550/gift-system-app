<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case VODAFONE_CASH = 'vodafone_cash';
    case INSTAPAY = 'instapay';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'نقداً (كاش)',
            self::VODAFONE_CASH => 'فودافون كاش',
            self::INSTAPAY => 'انستا باي (InstaPay)',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::CASH => 'badge-success',
            self::VODAFONE_CASH => 'badge-danger',
            self::INSTAPAY => 'badge-primary',
        };
    }

    public function isCash(): bool
    {
        return $this === self::CASH;
    }
}

