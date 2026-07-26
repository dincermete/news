<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Site;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeoMetaService
{
    /**
     * @return array{title: string, description: string, keywords: string|null, og_image: string|null, og_url: string|null}
     */
    public function forSite(Site $site): array
    {
        $brand = site_setting('site_name');
        $title = $site->domain.' | '.$brand;
        $description = filled($site->description)
            ? Str::limit(strip_tags($site->description), 160)
            : $site->domain.' sitesinde backlink ve yazı paketi fırsatları.';

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => null,
            'og_image' => $this->faviconUrl($site->domain),
            'og_url' => url('/site/'.$site->slug),
        ];
    }

    /**
     * @return array{title: string, description: string, keywords: string|null, og_image: string|null, og_url: string|null}
     */
    public function forPage(Page $page): array
    {
        $brand = site_setting('site_name');
        $settings = SiteSetting::current();

        $ogUrl = url('/'.$page->slug);
        if (filled($page->route_key) && Route::has($page->route_key)) {
            $ogUrl = route($page->route_key);
        }

        return [
            'title' => $page->meta_title ?: ($page->title.' | '.$brand),
            'description' => $page->meta_description
                ?: (Str::limit(strip_tags((string) $page->content), 160)
                    ?: (string) ($settings->meta_description ?: 'Kaliteli backlink, yazı ve medya paketleri — '.$brand)),
            'keywords' => $page->meta_keywords,
            'og_image' => $this->resolveOgImage($page->og_image, $settings),
            'og_url' => $ogUrl,
        ];
    }

    /**
     * @return array{title: string, description: string, keywords: string|null, og_image: string|null, og_url: string|null}
     */
    public function forRoute(string $routeKey, ?string $fallbackTitle = null, ?string $fallbackDescription = null): array
    {
        $page = Page::findByRouteKey($routeKey);

        if ($page !== null) {
            return $this->forPage($page);
        }

        $defaults = $this->forDefault();

        return [
            'title' => $fallbackTitle ?: $defaults['title'],
            'description' => $fallbackDescription ?: $defaults['description'],
            'keywords' => $defaults['keywords'],
            'og_image' => $defaults['og_image'],
            'og_url' => Route::has($routeKey) ? route($routeKey) : url()->current(),
        ];
    }

    /**
     * @return array{title: string, description: string, keywords: string|null, og_image: string|null, og_url: string|null}
     */
    public function forDefault(): array
    {
        $settings = SiteSetting::current();
        $brand = $settings->siteName();

        return [
            'title' => $brand,
            'description' => (string) ($settings->meta_description
                ?: 'Kaliteli backlink, yazı ve medya paketleri — '.$brand),
            'keywords' => null,
            'og_image' => $settings->ogImageUrl(),
            'og_url' => url('/'),
        ];
    }

    public function faviconUrl(string $domain): string
    {
        return 'https://www.google.com/s2/favicons?domain='.urlencode($domain).'&sz=128';
    }

    private function resolveOgImage(?string $pageOgPath, SiteSetting $settings): ?string
    {
        if (filled($pageOgPath)) {
            return Storage::disk('public')->url($pageOgPath);
        }

        return $settings->ogImageUrl();
    }
}
