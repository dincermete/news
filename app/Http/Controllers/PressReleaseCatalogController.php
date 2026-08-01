<?php

namespace App\Http\Controllers;

use App\Enums\PromotionalListingType;
use App\Models\SiteCategory;
use App\Services\SeoMetaService;
use App\Support\CatalogQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PressReleaseCatalogController extends Controller
{
    public const PER_PAGE = 24;

    public function __invoke(Request $request, SeoMetaService $seo): View
    {
        $q = trim((string) $request->query('q', ''));
        $kategori = $request->query('kategori');
        $kategori = is_string($kategori) && $kategori !== '' ? $kategori : null;
        $sort = (string) $request->query('sort', 'price_asc');

        $query = CatalogQuery::activeSitesWithListing(PromotionalListingType::PressRelease);

        if ($q !== '') {
            $escaped = addcslashes($q, '%_\\');
            $query->where('sites.domain', 'like', "%{$escaped}%");
        }

        if ($kategori !== null) {
            $query->whereHas('category', fn ($category) => $category->where('slug', $kategori));
        }

        match ($sort) {
            'price_desc' => $query->orderByDesc('promotional_listings.price')->orderBy('sites.id'),
            'newest' => $query->orderByDesc('sites.created_at')->orderByDesc('sites.id'),
            default => $query->orderBy('promotional_listings.price')->orderBy('sites.id'),
        };

        $sites = $query->paginate(self::PER_PAGE)->withQueryString();
        $sites->getCollection()->each(fn ($site) => $site->normalizeJoinedPricingAttributes());

        $categories = SiteCategory::query()->orderBy('name')->get(['id', 'name', 'slug']);

        return view('press-release.index', [
            'sites' => $sites,
            'categories' => $categories,
            'q' => $q !== '' ? $q : null,
            'kategori' => $kategori,
            'sort' => $sort,
            'meta' => $seo->forRoute('press-release.index', 'Basın Bülteni | '.site_setting('site_name')),
            'favoritedSiteIds' => auth()->user()?->favorites()->pluck('site_id')->all() ?? [],
        ]);
    }
}
