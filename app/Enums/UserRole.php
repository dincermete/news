<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Customer = 'customer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Editor => 'Editör',
            self::Customer => 'Müşteri',
        };
    }

    public function isStaff(): bool
    {
        return $this === self::Admin || $this === self::Editor;
    }
}
