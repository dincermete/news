<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum WalletBalanceType: string implements HasLabel
{
    case Main = 'main';
    case Bonus = 'bonus';
    case SpinPrize = 'spin_prize';
    case AffiliateCommission = 'affiliate_commission';

    public function getLabel(): string
    {
        return match ($this) {
            self::Main => 'Ana bakiye',
            self::Bonus => 'Bonus',
            self::SpinPrize => 'Çark ödülü',
            self::AffiliateCommission => 'Affiliate komisyon',
        };
    }

    /**
     * @return list<self>
     */
    public static function debitPriority(): array
    {
        return [
            self::Bonus,
            self::SpinPrize,
            self::AffiliateCommission,
            self::Main,
        ];
    }
}
