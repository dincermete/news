<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\SiteBundle;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteBundleCatalogController extends Controller
{
    public function __invoke(Request $request, SeoMetaService $seo): View
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
            'meta' => [
                ...$seo->forDefault(),
                'title' => 'Tanıtım Paketleri | '.config('app.name'),
                'description' => 'Birden fazla siteyi tek pakette birleştiren hazır tanıtım paketleri.',
            ],
        ]);
    }
}
