<?php

namespace App\Http\Controllers;

use App\Enums\PromotionalListingType;
use App\Models\Province;
use App\Models\Site;
use App\Services\ProvinceStatsService;
use App\Services\SeoMetaService;
use App\Support\CatalogQuery;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProvinceSitesController extends Controller
{
    public function __invoke(
        string $slug,
        ProvinceStatsService $statsService,
        SeoMetaService $seo,
    ): View {
        $province = Province::query()->where('slug', $slug)->firstOrFail();
        $stats = $statsService->forProvince($province);

        $sites = CatalogQuery::activeSitesWithListing(PromotionalListingType::SiteArticle)
            ->whereHas('provinces', fn ($q) => $q->where('provinces.id', $province->id))
            ->orderByDesc('sites.da_value')
            ->orderBy('sites.id')
            ->get()
            ->each(fn ($site) => $site->normalizeJoinedPricingAttributes());

        $meta = $seo->forProvince($province, $stats);

        return view('provinces.show', [
            'province' => $province,
            'sites' => $sites,
            'stats' => $stats,
            'meta' => $meta,
            'favoritedSiteIds' => auth()->user()?->favorites()->pluck('site_id')->all() ?? [],
            'jsonLd' => $this->jsonLd($province, $sites, $stats, $meta),
        ]);
    }

    /**
     * @param  Collection<int, Site>  $sites
     * @param  array<string, mixed>  $stats
     * @param  array<string, mixed>  $meta
     * @return list<array<string, mixed>>
     */
    protected function jsonLd(Province $province, $sites, array $stats, array $meta): array
    {
        $schemas = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Ana Sayfa',
                        'item' => url('/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Tüm Siteler',
                        'item' => route('sites.index'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $province->name.' Tanıtım Yazısı Siteleri',
                        'item' => $province->url(),
                    ],
                ],
            ],
        ];

        if ($sites->isNotEmpty()) {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => $province->name.' Tanıtım Yazısı Siteleri',
                'numberOfItems' => $sites->count(),
                'itemListElement' => $sites->values()->map(fn ($site, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $site->listing_name ?? $site->domain,
                    'url' => storefront_site_url($site),
                ])->all(),
            ];
        }

        if (! empty($stats['faqs'])) {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => collect($stats['faqs'])->map(fn (array $faq): array => [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['answer'],
                    ],
                ])->all(),
            ];
        }

        return $schemas;
    }
}
