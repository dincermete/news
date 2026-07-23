<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ChatbotMessageRole: string implements HasLabel
{
    case User = 'user';
    case Assistant = 'assistant';
    case System = 'system';

    public function getLabel(): string
    {
        return match ($this) {
            self::User => 'Kullanıcı',
            self::Assistant => 'Asistan',
            self::System => 'Sistem',
        };
    }
}
