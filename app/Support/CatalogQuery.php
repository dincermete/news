<?php

namespace App\Support;

use App\Enums\SiteStatus;
use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;

/**
 * Public catalog query helpers.
 *
 * Code standard (Faz 11b/11c): never lazy-load relationships on list/detail
 * pages. Always start from CatalogQuery::activeSites() (or an equivalent
 * ->with([...]) chain) so category/labels are eager-loaded in one go.
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
     * @return Builder<Site>
     */
    public static function activeSites(): Builder
    {
        return Site::query()
            ->with(self::DEFAULT_WITH)
            ->where('status', SiteStatus::Active);
    }

    /**
     * Filtered + sorted active sites for /siteler.
     *
     * @return Builder<Site>
     */
    public static function catalog(SiteCatalogFilters $filters): Builder
    {
        return $filters->apply(self::activeSites());
    }
}
