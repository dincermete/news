<?php

namespace App\Services;

use App\Models\LiveSession;
use App\Models\Site;
use App\Models\SiteView;
use Illuminate\Support\Facades\Cache;

class SiteViewService
{
    public const CACHE_TTL_SECONDS = 60;

    public function record(Site $site, ?string $sessionToken = null): void
    {
        $liveSessionId = null;

        if (filled($sessionToken)) {
            $liveSessionId = LiveSession::query()
                ->where('session_token', $sessionToken)
                ->value('id');
        }

        SiteView::query()->create([
            'site_id' => $site->id,
            'live_session_id' => $liveSessionId,
            'viewed_at' => now(),
        ]);

        $this->forgetCounts($site);
    }

    public function todayCount(Site $site): int
    {
        return (int) Cache::remember(
            $this->todayCacheKey($site),
            self::CACHE_TTL_SECONDS,
            fn (): int => SiteView::query()
                ->where('site_id', $site->id)
                ->whereDate('viewed_at', today())
                ->count(),
        );
    }

    public function totalCount(Site $site): int
    {
        return (int) Cache::remember(
            $this->totalCacheKey($site),
            self::CACHE_TTL_SECONDS,
            fn (): int => SiteView::query()
                ->where('site_id', $site->id)
                ->count(),
        );
    }

    public function forgetCounts(Site $site): void
    {
        Cache::forget($this->todayCacheKey($site));
        Cache::forget($this->totalCacheKey($site));
    }

    protected function todayCacheKey(Site $site): string
    {
        return 'site:'.$site->id.':views:today:'.today()->toDateString();
    }

    protected function totalCacheKey(Site $site): string
    {
        return 'site:'.$site->id.':views:total';
    }
}
