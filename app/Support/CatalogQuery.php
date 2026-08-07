<?php

namespace App\Support;

use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Models\PromotionalListing;
use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Public catalog query helpers.
 *
 * Code standard (Faz 11b/11c): never lazy-load relationships on list/detail
 * pages. Always start from CatalogQuery::activeSites() / activeListings()
 * (or an equivalent ->with([...]) chain) so category/labels are eager-loaded.
 */
class CatalogQuery
{
    /**
     * Default eager loads for public site listings and detail pages.
     *
     * @var list<string>
     */
    public const DEFAULT_WITH = [
        'category',
        'labels',
    ];

    /**
     * @var list<string>
     */
    public const LISTING_WITH = [
        'site.category',
        'site.labels',
    ];

    /**
     * @return Builder<Site>
     */
    public static function activeSites(): Builder
    {
        return Site::query()
            ->with(self::DEFAULT_WITH)
            ->where('status', SiteStatus::Active);
    }

    /**
     * Active promotional listings (sale SKUs) with their source site.
     *
     * @return Builder<PromotionalListing>
     */
    public static function activeListings(?PromotionalListingType $type = null): Builder
    {
        return PromotionalListing::query()
            ->with(self::LISTING_WITH)
            ->activeForSale()
            ->when(
                $type !== null,
                fn (Builder $query): Builder => $query->ofType($type),
            );
    }

    /**
     * Active sites that have an active listing of the given type, with sale
     * price columns selected from promotional_listings for storefront display.
     *
     * @return Builder<Site>
     */
    public static function activeSitesWithListing(PromotionalListingType $type): Builder
    {
        return Site::query()
            ->select([
                'sites.*',
                'promotional_listings.price as price',
                'promotional_listings.discount_price as discount_price',
                'promotional_listings.currency as currency',
                'promotional_listings.id as promotional_listing_id',
                'promotional_listings.public_path as listing_public_path',
                'promotional_listings.name as listing_name',
                'promotional_listings.estimated_delivery as estimated_delivery',
                'promotional_listings.reference_content_url as reference_content_url',
                'promotional_listings.reference_content_label as reference_content_label',
                'promotional_listings.reference_content_image_paths as reference_content_image_paths',
            ])
            ->join('promotional_listings', function ($join) use ($type): void {
                $join->on('promotional_listings.site_id', '=', 'sites.id')
                    ->where('promotional_listings.type', '=', $type->value)
                    ->where('promotional_listings.status', '=', SiteStatus::Active->value);
            })
            ->with(self::DEFAULT_WITH)
            ->where('sites.status', SiteStatus::Active);
    }

    /**
     * Filtered + sorted active site_article listings for /siteler.
     *
     * @return Builder<Site>
     */
    public static function catalog(SiteCatalogFilters $filters): Builder
    {
        return $filters->apply(self::activeSitesWithListing(PromotionalListingType::SiteArticle));
    }

    /**
     * @param  Collection<int, PromotionalListing>  $listings
     * @return Collection<int, Site>
     */
    public static function sitesFromListings(Collection $listings): Collection
    {
        return $listings
            ->map(function (PromotionalListing $listing): ?Site {
                $site = $listing->site;

                if ($site === null) {
                    return null;
                }

                return $site->applyListingPricing($listing);
            })
            ->filter()
            ->values();
    }
}
