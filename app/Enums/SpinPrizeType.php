<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SpinPrizeType: string implements HasLabel
{
    case Balance = 'balance';
    case None = 'none';

    public function getLabel(): string
    {
        return match ($this) {
            self::Balance => 'Bakiye',
            self::None => 'Boş',
        };
    }
}
