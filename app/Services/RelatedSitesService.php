<?php

namespace App\Services;

use App\Enums\PromotionalListingType;
use App\Models\Site;
use App\Support\CatalogQuery;
use Illuminate\Support\Collection;

class RelatedSitesService
{
    /**
     * Same-category active sites with sale listings, excluding the current one, ordered by DA desc.
     *
     * @return Collection<int, Site>
     */
    public function forSite(Site $site, int $limit = 4): Collection
    {
        return CatalogQuery::activeSitesWithListing(PromotionalListingType::SiteArticle)
            ->where('sites.site_category_id', $site->site_category_id)
            ->where('sites.id', '!=', $site->id)
            ->orderByDesc('sites.da_value')
            ->orderBy('sites.id')
            ->limit($limit)
            ->get()
            ->each(fn (Site $related) => $related->normalizeJoinedPricingAttributes());
    }
}
