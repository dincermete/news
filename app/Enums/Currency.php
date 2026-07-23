<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Currency: string implements HasLabel
{
    case Usd = 'USD';
    case Try = 'TRY';

    public function getLabel(): string
    {
        return match ($this) {
            self::Usd => 'USD',
            self::Try => 'TRY',
        };
    }
}
