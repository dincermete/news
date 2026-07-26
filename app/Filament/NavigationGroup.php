<?php

namespace App\Filament;

use Filament\Support\Contracts\HasLabel;

enum NavigationGroup: string implements HasLabel
{
    case Orders = 'Siparişler';
    case Payments = 'Ödemeler';
    case Customers = 'Müşteriler';
    case Sites = 'Siteler';
    case Products = 'Ürünler';
    case Campaigns = 'Kampanyalar';
    case Support = 'Destek';
    case Content = 'İçerik';
    case Notifications = 'Bildirimler';
    case System = 'Sistem';

    public function getLabel(): string
    {
        return $this->value;
    }
}
