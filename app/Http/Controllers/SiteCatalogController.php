<?php

namespace App\Http\Controllers;

use App\Models\SiteCategory;
use App\Services\CatalogCache;
use App\Services\PublicStatsService;
use App\Services\SeoMetaService;
use App\Support\SiteCatalogFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteCatalogController extends Controller
{
    public function __invoke(
        Request $request,
        CatalogCache $cache,
        SeoMetaService $seo,
        PublicStatsService $stats,
        ?string $kategori = null,
    ): View|RedirectResponse {
        if ($request->routeIs('sites.index')) {
            $legacy = $request->query('kategori');

            if (is_string($legacy) && $legacy !== '') {
                return redirect()->route(
                    'sites.category',
                    ['kategori' => $legacy] + collect($request->query())->except('kategori')->all(),
                    301,
                );
            }
        }

        $activeCategory = null;

        if (filled($kategori)) {
            $activeCategory = SiteCategory::query()
                ->where('slug', $kategori)
                ->first(['id', 'name', 'slug', 'description']);

            if ($activeCategory === null) {
                abort(404);
            }
        }

        $filters = SiteCatalogFilters::fromRequest($request, $activeCategory?->slug);

        $sites = $cache->rememberCatalogPage($filters);
        $sites->appends($filters->toQueryParameters());

        $categories = SiteCategory::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $brand = site_setting('site_name');

        return view('sites.index', [
            'sites' => $sites,
            'filters' => $filters,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'activeSiteCount' => $stats->activeSiteCount(),
            'meta' => $activeCategory instanceof SiteCategory
                ? $seo->forSiteCategory($activeCategory)
                : $seo->forRoute('sites.index', 'Markanıza uygun siteler | '.$brand),
            'favoritedSiteIds' => auth()->user()?->favorites()->pluck('site_id')->all() ?? [],
        ]);
    }
}
