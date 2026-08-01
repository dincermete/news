<?php

use App\Models\Site;
use App\Models\SiteSetting;
use App\Services\ProductPublicUrl;

if (! function_exists('site_setting')) {
    function site_setting(?string $key = null, mixed $default = null): mixed
    {
        $settings = SiteSetting::current();

        if ($key === null) {
            return $settings;
        }

        return match ($key) {
            'site_name' => $settings->siteName(),
            'site_domain' => $settings->siteDomain(),
            default => data_get($settings, $key, $default),
        };
    }
}

if (! function_exists('storefront_site_url')) {
    function storefront_site_url(Site $site): string
    {
        return app(ProductPublicUrl::class)->urlForSite($site);
    }
}

if (! function_exists('publisher_site_url')) {
    function publisher_site_url(Site $site): string
    {
        $domain = trim((string) $site->domain);

        if ($domain === '') {
            return '#';
        }

        if (preg_match('#^https?://#i', $domain) === 1) {
            return $domain;
        }

        return 'https://'.$domain;
    }
}
