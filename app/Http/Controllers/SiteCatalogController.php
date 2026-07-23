<?php

namespace App\Http\Controllers;

use App\Models\SiteCategory;
use App\Services\CatalogCache;
use App\Services\PublicStatsService;
use App\Services\SeoMetaService;
use App\Support\SiteCatalogFilters;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteCatalogController extends Controller
{
    public function __invoke(
        Request $request,
        CatalogCache $cache,
        SeoMetaService $seo,
        PublicStatsService $stats,
    ): View {
        $filters = SiteCatalogFilters::fromRequest($request);

        $sites = $cache->rememberCatalogPage($filters);
        $sites->appends($filters->toQueryParameters());

        $categories = SiteCategory::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return view('sites.index', [
            'sites' => $sites,
            'filters' => $filters,
            'categories' => $categories,
            'activeSiteCount' => $stats->activeSiteCount(),
            'meta' => [
                ...$seo->forDefault(),
                'title' => 'Siteler | '.config('app.name'),
                'description' => 'Backlink ve yazı paketleri için aktif site kataloğu. Kategori, fiyat ve DA ile filtreleyin.',
            ],
        ]);
    }
}
