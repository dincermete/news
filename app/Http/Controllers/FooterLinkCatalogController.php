<?php

namespace App\Http\Controllers;

use App\Enums\PromotionalListingType;
use App\Models\FooterLinkDurationOption;
use App\Models\SiteCategory;
use App\Services\SeoMetaService;
use App\Support\CatalogQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FooterLinkCatalogController extends Controller
{
    public const PER_PAGE = 24;

    public function __invoke(Request $request, SeoMetaService $seo): View
    {
        $q = trim((string) $request->query('q', ''));
        $kategori = $request->query('kategori');
        $kategori = is_string($kategori) && $kategori !== '' ? $kategori : null;
        $sort = (string) $request->query('sort', 'price_asc');

        $query = CatalogQuery::activeSitesWithListing(PromotionalListingType::FooterLink);

        if ($q !== '') {
            $escaped = addcslashes($q, '%_\\');
            $query->where('sites.domain', 'like', "%{$escaped}%");
        }

        if ($kategori !== null) {
            $query->whereHas('category', fn ($category) => $category->where('slug', $kategori));
        }

        match ($sort) {
            'price_desc' => $query->orderByDesc('promotional_listings.price')->orderBy('sites.id'),
            default => $query->orderBy('promotional_listings.price')->orderBy('sites.id'),
        };

        $sites = $query->paginate(self::PER_PAGE)->withQueryString();
        $sites->getCollection()->each(function ($site): void {
            $site->normalizeJoinedPricingAttributes();
            $site->setAttribute('base_price', (float) ($site->discount_price ?? $site->price));
        });

        $categories = SiteCategory::query()->orderBy('name')->get(['id', 'name', 'slug']);
        $durationOptions = FooterLinkDurationOption::query()
            ->where('is_active', true)
            ->orderBy('months')
            ->get();

        return view('footer-links.index', [
            'sites' => $sites,
            'categories' => $categories,
            'durationOptions' => $durationOptions,
            'q' => $q !== '' ? $q : null,
            'kategori' => $kategori,
            'sort' => $sort,
            'meta' => $seo->forRoute('footer-links.index', 'Footer Link | '.site_setting('site_name')),
            'favoritedSiteIds' => auth()->user()?->favorites()->pluck('site_id')->all() ?? [],
        ]);
    }
}
