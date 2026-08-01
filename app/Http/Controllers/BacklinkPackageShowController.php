<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\BacklinkPackage;
use App\Services\ProductPublicUrl;
use App\Services\SeoMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BacklinkPackageShowController extends Controller
{
    public function __invoke(string $slug, SeoMetaService $seo, ProductPublicUrl $publicUrls): View|RedirectResponse
    {
        $package = BacklinkPackage::query()
            ->where('slug', $slug)
            ->where('status', SiteStatus::Active)
            ->first();

        if ($package === null) {
            throw new NotFoundHttpException;
        }

        $canonical = $publicUrls->redirectTargetIfCanonicalDiffers($package);
        if ($canonical !== null) {
            return redirect()->to($canonical, 301);
        }

        return $this->showPackage($package, $seo);
    }

    public function showPackage(BacklinkPackage $package, SeoMetaService $seo): View
    {
        $related = BacklinkPackage::query()
            ->where('status', SiteStatus::Active)
            ->whereKeyNot($package->id)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->limit(4)
            ->get();

        return view('backlink-packages.show', [
            'package' => $package,
            'relatedPackages' => $related,
            'meta' => $seo->forBacklinkPackage($package),
        ]);
    }
}
