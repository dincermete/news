<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PromotionalListingType: string implements HasLabel
{
    case SiteArticle = 'site_article';
    case PressRelease = 'press_release';
    case FooterLink = 'footer_link';

    public function getLabel(): string
    {
        return match ($this) {
            self::SiteArticle => 'Tanıtım yazısı',
            self::PressRelease => 'Basın bülteni',
            self::FooterLink => 'Footer link',
        };
    }

    public function toProductType(): ProductType
    {
        return match ($this) {
            self::SiteArticle => ProductType::SiteArticle,
            self::PressRelease => ProductType::PressRelease,
            self::FooterLink => ProductType::FooterLink,
        };
    }

    public static function fromProductType(ProductType $type): ?self
    {
        return match ($type) {
            ProductType::SiteArticle => self::SiteArticle,
            ProductType::PressRelease => self::PressRelease,
            ProductType::FooterLink => self::FooterLink,
            default => null,
        };
    }
}
