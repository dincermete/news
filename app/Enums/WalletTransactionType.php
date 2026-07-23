<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum WalletTransactionType: string implements HasColor, HasLabel
{
    case Credit = 'credit';
    case Debit = 'debit';

    public function getLabel(): string
    {
        return match ($this) {
            self::Credit => 'Yükleme',
            self::Debit => 'Harcama',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Credit => 'success',
            self::Debit => 'danger',
        };
    }
}
