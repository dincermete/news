<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AffiliateCommissionStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Beklemede',
            self::Approved => 'Onaylandı',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
        };
    }
}
