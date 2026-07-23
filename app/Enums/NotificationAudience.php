<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NotificationAudience: string implements HasLabel
{
    case All = 'all';
    case LoggedInOnly = 'logged_in_only';

    public function getLabel(): string
    {
        return match ($this) {
            self::All => 'Herkes',
            self::LoggedInOnly => 'Sadece giriş yapanlar',
        };
    }
}
