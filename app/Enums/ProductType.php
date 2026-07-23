<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductType: string implements HasLabel
{
    case SiteArticle = 'site_article';
    case FooterLink = 'footer_link';
    case Bundle = 'bundle';
    case Story = 'story';

    public function getLabel(): string
    {
        return match ($this) {
            self::SiteArticle => 'Site yazısı',
            self::FooterLink => 'Footer link',
            self::Bundle => 'Paket',
            self::Story => 'Story',
        };
    }
}
