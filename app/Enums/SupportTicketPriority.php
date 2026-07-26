<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SupportTicketPriority: string implements HasColor, HasLabel
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';

    public function getLabel(): string
    {
        return match ($this) {
            self::Low => 'Düşük',
            self::Normal => 'Normal',
            self::High => 'Yüksek',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Low => 'gray',
            self::Normal => 'info',
            self::High => 'danger',
        };
    }
}
