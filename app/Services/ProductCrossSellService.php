<?php

namespace App\Services;

use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Models\PromotionalListing;
use App\Models\Site;
use App\Support\CatalogQuery;
use Illuminate\Support\Collection;

class ProductCrossSellService
{
    /**
     * Sites for the "İlgili Ürünler" table.
     * Manual picks first; falls back to same-category active article listings.
     *
     * @return Collection<int, Site>
     */
    public function relatedSitesFor(?PromotionalListing $listing, Site $site, int $limit = 8): Collection
    {
        if ($listing instanceof PromotionalListing) {
            $manual = $this->sitesFromListings(
                $listing->relatedListings()
                    ->activeForSale()
                    ->where('promotional_listings.id', '!=', $listing->id)
                    ->with(['site.category', 'site.labels'])
                    ->orderByPivot('sort_order')
                    ->orderBy('promotional_listings.id')
                    ->limit($limit)
                    ->get(),
            );

            if ($manual->isNotEmpty()) {
                return $manual;
            }
        }

        return CatalogQuery::activeSitesWithListing(PromotionalListingType::SiteArticle)
            ->where('sites.site_category_id', $site->site_category_id)
            ->where('sites.id', '!=', $site->id)
            ->orderByDesc('sites.da_value')
            ->orderBy('sites.id')
            ->limit($limit)
            ->get()
            ->each(fn (Site $related) => $related->normalizeJoinedPricingAttributes());
    }

    /**
     * Sites for the "Tavsiye Edilen Ürünler" table.
     * Manual picks first; falls back to best-selling active article listings.
     *
     * @param  list<int>  $excludeSiteIds
     * @return Collection<int, Site>
     */
    public function recommendedSitesFor(
        ?PromotionalListing $listing,
        Site $site,
        int $limit = 8,
        array $excludeSiteIds = [],
    ): Collection {
        $excludeSiteIds = array_values(array_unique([
            $site->id,
            ...$excludeSiteIds,
        ]));

        if ($listing instanceof PromotionalListing) {
            $manual = $this->sitesFromListings(
                $listing->recommendedListings()
                    ->activeForSale()
                    ->where('promotional_listings.id', '!=', $listing->id)
                    ->with(['site.category', 'site.labels'])
                    ->orderByPivot('sort_order')
                    ->orderBy('promotional_listings.id')
                    ->limit($limit)
                    ->get(),
            );

            if ($manual->isNotEmpty()) {
                return $manual;
            }
        }

        return CatalogQuery::activeSitesWithListing(PromotionalListingType::SiteArticle)
            ->whereNotIn('sites.id', $excludeSiteIds)
            ->withCount('orders')
            ->orderByDesc('orders_count')
            ->orderByDesc('sites.da_value')
            ->orderBy('sites.id')
            ->limit($limit)
            ->get()
            ->each(fn (Site $recommended) => $recommended->normalizeJoinedPricingAttributes());
    }

    /**
     * @param  Collection<int, PromotionalListing>  $listings
     * @return Collection<int, Site>
     */
    protected function sitesFromListings(Collection $listings): Collection
    {
        return $listings
            ->map(function (PromotionalListing $listing): ?Site {
                $site = $listing->site;

                if ($site === null || $site->status !== SiteStatus::Active) {
                    return null;
                }

                return $site->applyListingPricing($listing);
            })
            ->filter()
            ->unique('id')
            ->values();
    }
}
