<?php

namespace App\Http\Controllers;

use App\Models\FaqEntry;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Services\PublicStatsService;
use App\Services\SeoMetaService;
use App\Support\CatalogQuery;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public const CACHE_KEY = 'home.sections.v1';

    public const CACHE_TTL_SECONDS = 300;

    public function __invoke(SeoMetaService $seo, PublicStatsService $stats): View
    {
        /** @var array{newest: list<array<string, mixed>>, discounted: list<array<string, mixed>>, best_sellers: list<array<string, mixed>>} $sections */
        $sections = Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            fn (): array => [
                'newest' => $this->toRows(
                    CatalogQuery::activeSites()
                        ->orderByDesc('created_at')
                        ->orderByDesc('id')
                        ->limit(6)
                        ->get(),
                ),
                'discounted' => $this->toRows(
                    CatalogQuery::activeSites()
                        ->whereNotNull('discount_price')
                        ->whereColumn('discount_price', '<', 'price')
                        ->orderByRaw('(price - discount_price) / price desc')
                        ->limit(6)
                        ->get(),
                ),
                'best_sellers' => $this->toRows(
                    CatalogQuery::activeSites()
                        ->withCount('orders')
                        ->orderByDesc('orders_count')
                        ->orderBy('id')
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

        return view('home', [
            'meta' => $seo->forDefault(),
            'stats' => $stats->all(),
            'sections' => $sections,
            'categories' => $categories,
            'faqs' => $faqs,
        ]);
    }

    /**
     * Cache-safe scalar rows for home ranking lists (no Eloquent graphs in cache).
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Site>  $sites
     * @return list<array{domain: string, price: float, discount_price: float|null, currency: string, site_id: int}>
     */
    protected function toRows($sites): array
    {
        return $sites
            ->map(fn (Site $site): array => [
                'site_id' => $site->id,
                'domain' => $site->domain,
                'price' => (float) $site->price,
                'discount_price' => $site->discount_price !== null ? (float) $site->discount_price : null,
                'currency' => $site->currency?->value ?? (string) $site->currency,
            ])
            ->values()
            ->all();
    }
}
