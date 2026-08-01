<?php

namespace App\Http\Controllers;

use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Models\Favorite;
use App\Models\PromotionalListing;
use App\Models\Site;
use App\Models\SiteQuestion;
use App\Models\SiteReview;
use App\Services\CatalogCache;
use App\Services\ProductCrossSellService;
use App\Services\ProductPublicUrl;
use App\Services\SeoMetaService;
use App\Services\SiteViewService;
use App\Services\WhatsAppRedirectService;
use App\Support\CatalogQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SiteShowController extends Controller
{
    public function __invoke(
        Request $request,
        string $slug,
        CatalogCache $cache,
        SeoMetaService $seo,
        SiteViewService $siteViews,
        ProductCrossSellService $crossSell,
        ProductPublicUrl $publicUrls,
    ): View|RedirectResponse {
        $site = $cache->findActiveSiteByDomain($slug);

        if ($site === null) {
            throw new NotFoundHttpException;
        }

        $listing = $site->articleListing()
            ->where('status', SiteStatus::Active)
            ->first();

        if ($listing instanceof PromotionalListing) {
            $canonical = $publicUrls->redirectTargetIfCanonicalDiffers($listing);
            if ($canonical !== null) {
                return redirect()->to($canonical, 301);
            }
        }

        return $this->showSite($request, $site, $listing, $seo, $siteViews, $crossSell);
    }

    public function showSite(
        Request $request,
        Site $site,
        ?PromotionalListing $listing,
        SeoMetaService $seo,
        ?SiteViewService $siteViews = null,
        ?ProductCrossSellService $crossSell = null,
        ?WhatsAppRedirectService $whatsApp = null,
    ): View {
        $siteViews ??= app(SiteViewService::class);
        $crossSell ??= app(ProductCrossSellService::class);
        $whatsApp ??= app(WhatsAppRedirectService::class);

        if ($listing instanceof PromotionalListing) {
            $site->applyListingPricing($listing);
        }

        $site->loadCount('favorites');
        $siteViews->record($site, $request->input('session_token'));

        $isFavorited = false;
        if ($request->user() !== null) {
            $isFavorited = Favorite::query()
                ->where('site_id', $site->id)
                ->where('user_id', $request->user()->id)
                ->exists();
        }

        $questions = SiteQuestion::query()
            ->publicAnswered()
            ->where('site_id', $site->id)
            ->latest('answered_at')
            ->limit(20)
            ->get(['id', 'question', 'answer', 'answered_at', 'guest_email', 'user_id']);

        $reviews = SiteReview::query()
            ->approved()
            ->where('site_id', $site->id)
            ->latest('approved_at')
            ->limit(50)
            ->get(['id', 'name', 'message', 'approved_at', 'created_at']);

        $whatsappUrl = null;
        try {
            $whatsappUrl = $whatsApp->buildLink(
                "Merhaba, {$site->domain} ürünü hakkında sipariş vermek istiyorum."
            );
        } catch (\RuntimeException) {
            $whatsappUrl = null;
        }

        $bestSellers = CatalogQuery::activeSitesWithListing(PromotionalListingType::SiteArticle)
            ->where('sites.id', '!=', $site->id)
            ->withCount('orders')
            ->orderByDesc('orders_count')
            ->orderBy('sites.id')
            ->limit(6)
            ->get()
            ->each(fn ($bestSeller) => $bestSeller->normalizeJoinedPricingAttributes());

        $relatedSites = $crossSell->relatedSitesFor($listing, $site);
        $recommendedSites = $crossSell->recommendedSitesFor(
            $listing,
            $site,
            excludeSiteIds: $relatedSites->pluck('id')->all(),
        );

        return view('sites.show', [
            'site' => $site,
            'listing' => $listing,
            'meta' => $seo->forSite($site, $listing),
            'viewsToday' => $siteViews->todayCount($site),
            'viewsTotal' => $siteViews->totalCount($site),
            'favoritesCount' => (int) $site->favorites_count,
            'isFavorited' => $isFavorited,
            'relatedSites' => $relatedSites,
            'recommendedSites' => $recommendedSites,
            'questions' => $questions,
            'reviews' => $reviews,
            'whatsappUrl' => $whatsappUrl,
            'bestSellers' => $bestSellers,
            'favoritedSiteIds' => auth()->user()?->favorites()->pluck('site_id')->all() ?? [],
        ]);
    }
}
