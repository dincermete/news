<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SeoAnalysisServiceType: string implements HasLabel
{
    case Seo = 'seo';
    case Geo = 'geo';
    case Backlink = 'backlink';
    case WebDesign = 'web_design';
    case GoogleAds = 'google_ads';
    case SocialMedia = 'social_media';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return match ($this) {
            self::Seo => 'SEO',
            self::Geo => 'GEO',
            self::Backlink => 'Backlink',
            self::WebDesign => 'Web Tasarım',
            self::GoogleAds => 'Google Ads',
            self::SocialMedia => 'Sosyal Medya',
            self::Unknown => 'Bilmiyorum',
        };
    }
}
