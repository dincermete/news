<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Site;
use App\Models\SiteQuestion;
use App\Services\CatalogCache;
use App\Services\RelatedSitesService;
use App\Services\SeoMetaService;
use App\Services\SiteViewService;
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
        RelatedSitesService $relatedSites,
    ): View {
        $site = $cache->findActiveSiteByDomain($slug);

        if ($site === null) {
            throw new NotFoundHttpException;
        }

        // Refresh count without N+1 on list pages; single aggregate for this detail.
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

        return view('sites.show', [
            'site' => $site,
            'meta' => $seo->forSite($site),
            'viewsToday' => $siteViews->todayCount($site),
            'viewsTotal' => $siteViews->totalCount($site),
            'favoritesCount' => (int) $site->favorites_count,
            'isFavorited' => $isFavorited,
            'relatedSites' => $relatedSites->forSite($site),
            'questions' => $questions,
        ]);
    }
}
