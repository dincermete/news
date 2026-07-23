<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SupportTicketStatus: string implements HasColor, HasLabel
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Open => 'Açık',
            self::InProgress => 'İşlemde',
            self::Closed => 'Kapalı',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Open => 'warning',
            self::InProgress => 'info',
            self::Closed => 'gray',
        };
    }
}
