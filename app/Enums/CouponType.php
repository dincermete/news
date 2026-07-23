<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CouponType: string implements HasLabel
{
    case Percentage = 'percentage';
    case FixedAmount = 'fixed_amount';

    public function getLabel(): string
    {
        return match ($this) {
            self::Percentage => 'Yüzde',
            self::FixedAmount => 'Sabit tutar',
        };
    }
}
