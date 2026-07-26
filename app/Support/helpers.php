<?php

use App\Models\SiteSetting;

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
