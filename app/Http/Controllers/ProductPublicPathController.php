<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\BacklinkPackage;
use App\Models\PromotionalListing;
use App\Models\SeoPackage;
use App\Models\SiteBundle;
use App\Services\CatalogCache;
use App\Services\ProductPublicUrl;
use App\Services\SeoMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductPublicPathController extends Controller
{
    public function __invoke(
        Request $request,
        string $publicPath,
        ProductPublicUrl $publicUrls,
        SeoMetaService $seo,
        CatalogCache $cache,
        SiteShowController $siteShow,
        SiteBundleCatalogController $bundles,
        SeoPackageShowController $seoPackages,
        BacklinkPackageShowController $backlinkPackages,
    ): View|RedirectResponse {
        $product = $publicUrls->resolve($publicPath);

        if ($product instanceof PromotionalListing) {
            $product->loadMissing('site');

            if ($product->status !== SiteStatus::Active || $product->site === null || $product->site->status !== SiteStatus::Active) {
                throw new NotFoundHttpException;
            }

            return $siteShow->showSite($request, $product->site, $product, $seo);
        }

        if ($product instanceof SiteBundle) {
            if ($product->status !== SiteStatus::Active) {
                throw new NotFoundHttpException;
            }

            return $bundles->showBundle($product, $seo);
        }

        if ($product instanceof SeoPackage) {
            if ($product->status !== SiteStatus::Active) {
                throw new NotFoundHttpException;
            }

            return $seoPackages->showPackage($product, $seo);
        }

        if ($product instanceof BacklinkPackage) {
            if ($product->status !== SiteStatus::Active) {
                throw new NotFoundHttpException;
            }

            return $backlinkPackages->showPackage($product, $seo);
        }

        return app(PageController::class)($publicPath, $cache, $seo);
    }
}
