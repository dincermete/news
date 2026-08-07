<?php

namespace App\Services;

use App\Enums\SiteStatus;
use App\Models\BacklinkPackage;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Page;
use App\Models\PromotionalListing;
use App\Models\Province;
use App\Models\SeoPackage;
use App\Models\Site;
use App\Models\SiteBundle;
use App\Models\SiteCategory;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeoMetaService
{
    public function __construct(
        protected ProductPublicUrl $publicUrls,
    ) {}

    /**
     * @return array{title: string, description: string, keywords: string|null, og_image: string|null, og_url: string|null}
     */
    public function forSite(Site $site, ?PromotionalListing $listing = null): array
    {
        $listing ??= $site->relationLoaded('articleListing')
            ? $site->articleListing
            : $site->articleListing()->where('status', SiteStatus::Active)->first();

        if ($listing instanceof PromotionalListing) {
            return $this->forPromotionalListing($listing);
        }

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
            'og_url' => route('sites.show', $site->domain),
        ];
    }

    /**
     * @return array{title: string, description: string, keywords: string|null, og_image: string|null, og_url: string|null}
     */
    public function forPromotionalListing(PromotionalListing $listing): array
    {
        $listing->loadMissing('site');
        $site = $listing->site;
        $brand = site_setting('site_name');
        $domain = $site?->domain ?? 'Site';

        $title = filled($listing->meta_title)
            ? $listing->meta_title
            : (filled($listing->name) ? $listing->name.' | '.$brand : $domain.' | '.$brand);

        $description = filled($listing->meta_description)
            ? $listing->meta_description
            : (filled($listing->short_description)
                ? Str::limit(strip_tags($listing->short_description), 160)
                : (filled($listing->description)
                    ? Str::limit(strip_tags($listing->description), 160)
                    : (filled($site?->description)
                        ? Str::limit(strip_tags((string) $site->description), 160)
                        : $domain.' — '.$this->publicUrls->listingTypeLabel($listing))));

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $listing->meta_keywords,
            'og_image' => $listing->ogImageUrl() ?? ($site ? $this->faviconUrl($site->domain) : null),
            'og_url' => $this->publicUrls->urlFor($listing),
        ];
    }

    /**
     * @return array{title: string, description: string, keywords: string|null, og_image: string|null, og_url: string|null}
     */
    public function forBundle(SiteBundle $bundle): array
    {
        $brand = site_setting('site_name');

        return [
            'title' => filled($bundle->meta_title) ? $bundle->meta_title : ($bundle->name.' | '.$brand),
            'description' => filled($bundle->meta_description)
                ? $bundle->meta_description
                : ($bundle->description
                    ?: (($bundle->sites_count ?? $bundle->sites()->count()).' siteyi içeren '.$bundle->name.' tanıtım paketi.')),
            'keywords' => $bundle->meta_keywords,
            'og_image' => $bundle->ogImageUrl() ?? SiteSetting::current()->ogImageUrl(),
            'og_url' => $this->publicUrls->urlFor($bundle),
        ];
    }

    /**
     * @return array{title: string, description: string, keywords: string|null, og_image: string|null, og_url: string|null}
     */
    public function forSeoPackage(SeoPackage $package): array
    {
        $brand = site_setting('site_name');

        return [
            'title' => filled($package->meta_title) ? $package->meta_title : ($package->name.' | '.$brand),
            'description' => filled($package->meta_description)
                ? $package->meta_description
                : Str::limit(strip_tags((string) ($package->description ?: $package->name.' SEO paketi')), 160),
            'keywords' => $package->meta_keywords,
            'og_image' => $package->ogImageUrl() ?? SiteSetting::current()->ogImageUrl(),
            'og_url' => $this->publicUrls->urlFor($package),
        ];
    }

    /**
     * @return array{title: string, description: string, keywords: string|null, og_image: string|null, og_url: string|null}
     */
    public function forBacklinkPackage(BacklinkPackage $package): array
    {
        $brand = site_setting('site_name');

        return [
            'title' => filled($package->meta_title) ? $package->meta_title : ($package->name.' | '.$brand),
            'description' => filled($package->meta_description)
                ? $package->meta_description
                : Str::limit(strip_tags((string) ($package->description ?: $package->name.' backlink paketi')), 160),
            'keywords' => $package->meta_keywords,
            'og_image' => $package->ogImageUrl() ?? SiteSetting::current()->ogImageUrl(),
            'og_url' => $this->publicUrls->urlFor($package),
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
     * @return array{title: string, description: string, keywords: string|null, og_image: string|null, og_url: string|null, og_type?: string, published_time?: string|null, modified_time?: string|null, author?: string|null}
     */
    public function forBlogPost(BlogPost $post): array
    {
        $brand = site_setting('site_name');
        $settings = SiteSetting::current();

        $description = $post->meta_description
            ?: ($post->excerpt
                ?: Str::limit(strip_tags((string) $post->content), 160));

        return [
            'title' => $post->meta_title ?: ($post->title.' | Blog | '.$brand),
            'description' => (string) ($description ?: $settings->meta_description),
            'keywords' => $post->meta_keywords,
            'og_image' => $post->ogImageUrl() ?: $settings->ogImageUrl(),
            'og_url' => $post->url(),
            'og_type' => 'article',
            'published_time' => $post->published_at?->toAtomString(),
            'modified_time' => $post->updated_at?->toAtomString(),
            'author' => $post->author?->name,
        ];
    }

    /**
     * @return array{title: string, description: string, keywords: string|null, og_image: string|null, og_url: string|null}
     */
    public function forBlogCategory(BlogCategory $category): array
    {
        $brand = site_setting('site_name');
        $settings = SiteSetting::current();

        return [
            'title' => $category->name.' | Blog | '.$brand,
            'description' => filled($category->description)
                ? Str::limit(strip_tags($category->description), 160)
                : $category->name.' kategorisindeki blog yazıları — '.$brand,
            'keywords' => $category->name,
            'og_image' => $settings->ogImageUrl(),
            'og_url' => route('blog.category', $category),
        ];
    }

    /**
     * @return array{title: string, description: string, keywords: string|null, og_image: string|null, og_url: string|null}
     */
    public function forSiteCategory(SiteCategory $category): array
    {
        $brand = site_setting('site_name');
        $settings = SiteSetting::current();

        return [
            'title' => $category->name.' Siteleri | '.$brand,
            'description' => filled($category->description)
                ? Str::limit(strip_tags($category->description), 160)
                : $category->name.' kategorisindeki tanıtım yazısı siteleri — DA/PA, fiyat ve dofollow bilgileriyle karşılaştırın.',
            'keywords' => $category->name.' siteleri, '.$category->name.' tanıtım yazısı',
            'og_image' => $settings->ogImageUrl(),
            'og_url' => route('sites.category', ['kategori' => $category->slug]),
        ];
    }

    /**
     * @return array{title: string, description: string, keywords: string|null, og_image: string|null, og_url: string|null}
     */
    public function forBlogTag(BlogTag $tag): array
    {
        $brand = site_setting('site_name');
        $settings = SiteSetting::current();

        return [
            'title' => '#'.$tag->name.' | Blog | '.$brand,
            'description' => $tag->name.' etiketli blog yazıları — '.$brand,
            'keywords' => $tag->name,
            'og_image' => $settings->ogImageUrl(),
            'og_url' => route('blog.tag', $tag),
        ];
    }

    /**
     * @param  array{
     *     sites_count?: int,
     *     top_categories?: list<array{name: string, slug: string, count: int}>,
     *     da_min?: float|null,
     *     da_max?: float|null,
     *     summary?: string
     * }  $stats
     * @return array{title: string, description: string, keywords: string|null, og_image: string|null, og_url: string|null, robots?: string}
     */
    public function forProvince(Province $province, array $stats = []): array
    {
        $brand = site_setting('site_name');
        $count = (int) ($stats['sites_count'] ?? 0);
        $top = collect($stats['top_categories'] ?? [])->pluck('name')->filter()->values();

        $title = match (true) {
            $count >= Province::INDEX_MIN_SITES => "{$province->name} Tanıtım Yazısı Siteleri — {$count} Site Listelendi | {$brand}",
            $count >= 1 => "{$province->name} Yayın Siteleri ve Backlink Fırsatları | {$brand}",
            default => "{$province->name} Tanıtım Yazısı Siteleri Yakında | {$brand}",
        };

        if (filled($stats['summary'] ?? null)) {
            $description = Str::limit((string) $stats['summary'], 160);
        } elseif ($count > 0 && $top->isNotEmpty()) {
            $description = "{$province->name} için tanıtım yazısı siteleri; öne çıkan kategoriler: ".$top->implode(', ').'. 14 SEO metriği ve fiyatlarla karşılaştırın.';
        } else {
            $description = "{$province->name_locative} tanıtım yazısı ve backlink verilebilen yayın siteleri. Benzer illerdeki siteleri inceleyin veya kataloğa göz atın.";
        }

        $keywords = collect([
            $province->name.' tanıtım yazısı',
            $province->name.' backlink',
            $province->name.' yayın siteleri',
            ...$top->all(),
        ])->filter()->implode(', ');

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'og_image' => SiteSetting::current()->ogImageUrl(),
            'og_url' => $province->url(),
            'robots' => $province->robotsDirective($count),
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
