<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\FaqEntry;
use App\Models\SiteBundle;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SiteBundleCatalogController extends Controller
{
    public function index(Request $request, SeoMetaService $seo): View
    {
        $q = trim((string) $request->query('q', ''));

        $query = SiteBundle::query()
            ->withCount('sites')
            ->with(['sites' => fn ($sites) => $sites->orderBy('domain')])
            ->where('status', SiteStatus::Active);

        if ($q !== '') {
            $escaped = addcslashes($q, '%_\\');
            $query->where('name', 'like', "%{$escaped}%");
        }

        $bundles = $query->orderBy('price')->get();

        return view('bundles.index', [
            'bundles' => $bundles,
            'q' => $q !== '' ? $q : null,
            'meta' => $seo->forRoute('bundles.index', 'Tanıtım Paketleri | '.site_setting('site_name')),
        ]);
    }

    public function show(string $slug, SeoMetaService $seo): View
    {
        $bundle = SiteBundle::query()
            ->where('slug', $slug)
            ->where('status', SiteStatus::Active)
            ->withCount('sites')
            ->with(['sites' => fn ($sites) => $sites->orderBy('domain')->with('category')])
            ->first();

        if ($bundle === null) {
            throw new NotFoundHttpException;
        }

        $faqs = FaqEntry::query()
            ->active()
            ->where('category', 'bundle-'.$bundle->slug)
            ->orderBy('id')
            ->get();

        $relatedBundles = SiteBundle::query()
            ->where('status', SiteStatus::Active)
            ->where('id', '!=', $bundle->id)
            ->withCount('sites')
            ->with(['sites' => fn ($sites) => $sites->orderBy('domain')])
            ->orderBy('price')
            ->limit(4)
            ->get();

        $brand = site_setting('site_name');

        return view('bundles.show', [
            'bundle' => $bundle,
            'faqs' => $faqs,
            'relatedBundles' => $relatedBundles,
            'meta' => [
                'title' => $bundle->name.' | '.$brand,
                'description' => $bundle->description
                    ?: $bundle->sites_count.' siteyi içeren '.$bundle->name.' tanıtım paketi.',
                'keywords' => null,
                'og_image' => null,
                'og_url' => route('bundles.show', $bundle->slug),
            ],
        ]);
    }
}
