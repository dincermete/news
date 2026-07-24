<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductType: string implements HasLabel
{
    case SiteArticle = 'site_article';
    case PressRelease = 'press_release';
    case FooterLink = 'footer_link';
    case Bundle = 'bundle';
    case Story = 'story';
    case SeoPackage = 'seo_package';
    case BacklinkPackage = 'backlink_package';
    case Balance = 'balance';

    public function getLabel(): string
    {
        return match ($this) {
            self::SiteArticle => 'Site yazısı',
            self::PressRelease => 'Basın Bülteni',
            self::FooterLink => 'Footer link',
            self::Bundle => 'Paket',
            self::Story => 'Story',
            self::SeoPackage => 'SEO Paketi',
            self::BacklinkPackage => 'Backlink Paketi',
            self::Balance => 'Bakiye Paketi',
        };
    }
}
