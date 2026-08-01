<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\SeoPackage;
use App\Models\SeoPackageDurationOption;
use App\Services\ProductPublicUrl;
use App\Services\SeoMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SeoPackageShowController extends Controller
{
    public function __invoke(string $slug, SeoMetaService $seo, ProductPublicUrl $publicUrls): View|RedirectResponse
    {
        $package = SeoPackage::query()
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

    public function showPackage(SeoPackage $package, SeoMetaService $seo): View
    {
        $durationOptions = SeoPackageDurationOption::query()
            ->where('is_active', true)
            ->orderBy('months')
            ->get();

        $related = SeoPackage::query()
            ->where('status', SiteStatus::Active)
            ->whereKeyNot($package->id)
            ->orderBy('sort_order')
            ->orderBy('monthly_price')
            ->limit(4)
            ->get();

        return view('seo-packages.show', [
            'package' => $package,
            'durationOptions' => $durationOptions,
            'relatedPackages' => $related,
            'meta' => $seo->forSeoPackage($package),
        ]);
    }
}
