<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\BacklinkPackage;
use App\Services\SeoMetaService;
use Illuminate\View\View;

class BacklinkPackageCatalogController extends Controller
{
    public function __invoke(SeoMetaService $seo): View
    {
        $packages = BacklinkPackage::query()
            ->where('status', SiteStatus::Active)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return view('backlink-packages.index', [
            'packages' => $packages,
            'meta' => [
                ...$seo->forDefault(),
                'title' => 'Backlink Paketleri | '.config('app.name'),
                'description' => 'Konuyla alakalı, yüksek otoriteli kaynaklardan doğal anchor dağılımıyla kalıcı backlink paketleri.',
            ],
        ]);
    }
}
