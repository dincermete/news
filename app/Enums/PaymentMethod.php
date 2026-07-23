<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Card = 'card';
    case BankTransfer = 'bank_transfer';
    case Balance = 'balance';

    public function getLabel(): string
    {
        return match ($this) {
            self::Card => 'Kredi kartı',
            self::BankTransfer => 'Havale / EFT',
            self::Balance => 'Cüzdan bakiyesi',
        };
    }
}
