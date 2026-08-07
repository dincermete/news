<?php

namespace App\Observers;

use App\Models\Site;
use App\Services\CatalogCache;
use App\Services\ProvinceStatsService;
use App\Services\PublicStatsService;

class SiteObserver
{
    public function __construct(
        private CatalogCache $catalogCache,
        private PublicStatsService $publicStats,
        private ProvinceStatsService $provinceStats,
    ) {}

    public function saved(Site $site): void
    {
        $this->invalidate($site);
    }

    public function deleted(Site $site): void
    {
        $this->invalidate($site);
    }

    protected function invalidate(Site $site): void
    {
        $this->catalogCache->forgetSite($site);
        $this->publicStats->forgetActiveSites();
        $this->provinceStats->forgetHub();

        $site->provinces()
            ->get(['provinces.id'])
            ->each(fn ($province) => $this->provinceStats->forget($province));
    }
}
