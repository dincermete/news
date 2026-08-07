<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderFulfillmentTrack: string implements HasColor, HasLabel
{
    case Content = 'content';
    case Service = 'service';
    case Instant = 'instant';

    public function getLabel(): string
    {
        return match ($this) {
            self::Content => 'İçerik',
            self::Service => 'Hizmet',
            self::Instant => 'Anında',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Content => 'primary',
            self::Service => 'info',
            self::Instant => 'success',
        };
    }

    public static function forProductType(ProductType $productType): self
    {
        return match ($productType) {
            ProductType::Balance => self::Instant,
            ProductType::SeoPackage, ProductType::BacklinkPackage => self::Service,
            default => self::Content,
        };
    }
}
