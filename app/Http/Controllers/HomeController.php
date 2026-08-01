<?php

namespace App\Http\Controllers;

use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Models\BacklinkPackage;
use App\Models\FaqEntry;
use App\Models\PromotionalListing;
use App\Models\SeoPackage;
use App\Models\Site;
use App\Models\SiteBundle;
use App\Models\SiteCategory;
use App\Services\PublicStatsService;
use App\Services\SeoMetaService;
use App\Support\CatalogQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public const CACHE_KEY = 'home.sections.v2';

    public const PRODUCTS_CACHE_KEY = 'home.products.v2';

    public const CACHE_TTL_SECONDS = 300;

    public function __invoke(SeoMetaService $seo, PublicStatsService $stats): View
    {
        /** @var array{newest: list<array<string, mixed>>, discounted: list<array<string, mixed>>, best_sellers: list<array<string, mixed>>} $sections */
        $sections = Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            fn (): array => [
                'newest' => $this->toRows(
                    CatalogQuery::activeSitesWithListing(PromotionalListingType::SiteArticle)
                        ->orderByDesc('sites.created_at')
                        ->orderByDesc('sites.id')
                        ->limit(6)
                        ->get(),
                ),
                'discounted' => $this->toRows(
                    CatalogQuery::activeSitesWithListing(PromotionalListingType::SiteArticle)
                        ->whereNotNull('promotional_listings.discount_price')
                        ->whereColumn('promotional_listings.discount_price', '<', 'promotional_listings.price')
                        ->orderByRaw('(promotional_listings.price - promotional_listings.discount_price) / promotional_listings.price desc')
                        ->limit(6)
                        ->get(),
                ),
                'best_sellers' => $this->toRows(
                    CatalogQuery::activeSitesWithListing(PromotionalListingType::SiteArticle)
                        ->withCount('orders')
                        ->orderByDesc('orders_count')
                        ->orderBy('sites.id')
                        ->limit(6)
                        ->get(),
                ),
            ],
        );

        $categories = SiteCategory::query()
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'slug']);

        $faqs = FaqEntry::query()
            ->active()
            ->orderBy('id')
            ->limit(8)
            ->get(['id', 'question_topic', 'answer']);

        $popularSites = CatalogQuery::activeSitesWithListing(PromotionalListingType::SiteArticle)
            ->withCount('favorites')
            ->orderByDesc('favorites_count')
            ->orderByDesc('sites.da_value')
            ->orderBy('sites.id')
            ->limit(5)
            ->get()
            ->each(fn (Site $site) => $site->normalizeJoinedPricingAttributes());

        $newestSites = CatalogQuery::activeSitesWithListing(PromotionalListingType::SiteArticle)
            ->orderByDesc('sites.created_at')
            ->orderByDesc('sites.id')
            ->limit(5)
            ->get()
            ->each(fn (Site $site) => $site->normalizeJoinedPricingAttributes());

        $pressReleaseSites = CatalogQuery::activeSitesWithListing(PromotionalListingType::PressRelease)
            ->orderBy('promotional_listings.price')
            ->orderBy('sites.id')
            ->limit(5)
            ->get()
            ->each(fn (Site $site) => $site->normalizeJoinedPricingAttributes());

        $bestSellerSites = CatalogQuery::activeSitesWithListing(PromotionalListingType::SiteArticle)
            ->withCount('orders')
            ->orderByDesc('orders_count')
            ->orderBy('sites.id')
            ->limit(5)
            ->get()
            ->each(fn (Site $site) => $site->normalizeJoinedPricingAttributes());

        $featuredBundles = SiteBundle::query()
            ->where('status', SiteStatus::Active)
            ->withCount('sites')
            ->with(['sites' => fn ($sites) => $sites->orderBy('domain')->with('category')])
            ->orderBy('price')
            ->limit(8)
            ->get();

        $featuredBacklinkPackages = BacklinkPackage::query()
            ->where('status', SiteStatus::Active)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('price')
            ->limit(8)
            ->get();

        return view('home', [
            'meta' => $seo->forRoute('home'),
            'stats' => $stats->all(),
            'sections' => $sections,
            'categories' => $categories,
            'faqs' => $faqs,
            'productPrices' => $this->productPrices(),
            'popularSites' => $popularSites,
            'newestSites' => $newestSites,
            'pressReleaseSites' => $pressReleaseSites,
            'bestSellerSites' => $bestSellerSites,
            'featuredBundles' => $featuredBundles,
            'featuredBacklinkPackages' => $featuredBacklinkPackages,
            'favoritedSiteIds' => auth()->user()?->favorites()->pluck('site_id')->all() ?? [],
        ]);
    }

    /**
     * Real minimum prices per product line, for the homepage product grid.
     * Keeps the homepage honest about what's actually purchasable and at what price.
     *
     * @return array{site_article: ?float, press_release: ?float, bundle: ?float, seo_package: ?float, backlink_package: ?float}
     */
    protected function productPrices(): array
    {
        return Cache::remember(
            self::PRODUCTS_CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            fn (): array => [
                'site_article' => (float) (PromotionalListing::query()
                    ->activeForSale()
                    ->ofType(PromotionalListingType::SiteArticle)
                    ->selectRaw('MIN(COALESCE(discount_price, price)) as min_price')
                    ->value('min_price') ?? 0) ?: null,
                'press_release' => (float) (PromotionalListing::query()
                    ->activeForSale()
                    ->ofType(PromotionalListingType::PressRelease)
                    ->min('price') ?? 0) ?: null,
                'bundle' => (float) (SiteBundle::query()
                    ->where('status', SiteStatus::Active)
                    ->min('price') ?? 0) ?: null,
                'seo_package' => (float) (SeoPackage::query()
                    ->where('status', SiteStatus::Active)
                    ->min('monthly_price') ?? 0) ?: null,
                'backlink_package' => (float) (BacklinkPackage::query()
                    ->where('status', SiteStatus::Active)
                    ->min('price') ?? 0) ?: null,
            ],
        );
    }

    /**
     * Cache-safe scalar rows for home ranking lists (no Eloquent graphs in cache).
     *
     * @param  Collection<int, Site>  $sites
     * @return list<array{domain: string, price: float, discount_price: float|null, currency: string, site_id: int}>
     */
    protected function toRows($sites): array
    {
        return $sites
            ->each(fn (Site $site) => $site->normalizeJoinedPricingAttributes())
            ->map(fn (Site $site): array => [
                'site_id' => $site->id,
                'domain' => $site->domain,
                'price' => (float) $site->price,
                'discount_price' => $site->discount_price !== null ? (float) $site->discount_price : null,
                'currency' => $site->currency instanceof \BackedEnum
                    ? $site->currency->value
                    : (string) $site->currency,
            ])
            ->values()
            ->all();
    }
}
