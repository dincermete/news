<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ApiTokenAbility: string implements HasLabel
{
    case ReadCatalog = 'read_catalog';
    case CreateOrder = 'create_order';
    case ReadOrders = 'read_orders';
    case ReadWallet = 'read_wallet';

    public function getLabel(): string
    {
        return match ($this) {
            self::ReadCatalog => 'Katalog okuma',
            self::CreateOrder => 'Sipariş oluşturma',
            self::ReadOrders => 'Sipariş okuma',
            self::ReadWallet => 'Cüzdan okuma',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $ability): array => [$ability->value => $ability->getLabel()])
            ->all();
    }
}
