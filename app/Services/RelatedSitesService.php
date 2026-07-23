<?php

namespace App\Services;

use App\Models\Site;
use App\Support\CatalogQuery;
use Illuminate\Support\Collection;

class RelatedSitesService
{
    /**
     * Same-category active sites, excluding the current one, ordered by DA desc.
     *
     * @return Collection<int, Site>
     */
    public function forSite(Site $site, int $limit = 4): Collection
    {
        return CatalogQuery::activeSites()
            ->where('site_category_id', $site->site_category_id)
            ->whereKeyNot($site->id)
            ->orderByDesc('da_value')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }
}
