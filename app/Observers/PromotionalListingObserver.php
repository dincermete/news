<?php

namespace App\Observers;

use App\Models\PromotionalListing;
use App\Services\CatalogCache;
use App\Services\PublicStatsService;

class PromotionalListingObserver
{
    public function __construct(
        private CatalogCache $catalogCache,
        private PublicStatsService $publicStats,
    ) {}

    public function saved(PromotionalListing $listing): void
    {
        $this->invalidate($listing);
    }

    public function deleted(PromotionalListing $listing): void
    {
        $this->invalidate($listing);
    }

    protected function invalidate(PromotionalListing $listing): void
    {
        $listing->loadMissing('site');

        if ($listing->site !== null) {
            $this->catalogCache->forgetSite($listing->site);
        } else {
            $this->catalogCache->forgetSiteLists();
        }

        $this->publicStats->forgetActiveSites();
        cache()->forget('home.sections.v2');
        cache()->forget('home.products.v2');
    }
}
