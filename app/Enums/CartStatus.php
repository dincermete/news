<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CartStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Converted = 'converted';
    case Abandoned = 'abandoned';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Converted => 'Dönüştürüldü',
            self::Abandoned => 'Terk edildi',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Active => 'success',
            self::Converted => 'info',
            self::Abandoned => 'gray',
        };
    }
}
