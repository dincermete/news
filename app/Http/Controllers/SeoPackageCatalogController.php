<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\SeoPackage;
use App\Models\SeoPackageDurationOption;
use App\Services\SeoMetaService;
use Illuminate\View\View;

class SeoPackageCatalogController extends Controller
{
    public function __invoke(SeoMetaService $seo): View
    {
        $packages = SeoPackage::query()
            ->where('status', SiteStatus::Active)
            ->orderBy('sort_order')
            ->orderBy('monthly_price')
            ->get();

        $durationOptions = SeoPackageDurationOption::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('months')
            ->get();

        return view('seo-packages.index', [
            'packages' => $packages,
            'durationOptions' => $durationOptions,
            'meta' => [
                ...$seo->forDefault(),
                'title' => 'SEO Paketleri | '.config('app.name'),
                'description' => 'Google ve yapay zeka aramalarında görünürlük için SEO, GEO ve AEO\'yu tek pakette birleştiren hazır SEO paketleri.',
            ],
        ]);
    }
}
