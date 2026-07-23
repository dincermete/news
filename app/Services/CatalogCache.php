<?php

namespace App\Services;

use App\Models\FooterLink;
use App\Models\Label;
use App\Models\Page;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Support\CatalogQuery;
use App\Support\SiteCatalogFilters;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Fragment / data cache for high-traffic public catalog pages.
 *
 * Stores JSON-friendly arrays only (not Eloquent models). The database
 * cache driver corrupts PHP-serialized Eloquent graphs into
 * __PHP_Incomplete_Class — arrays hydrate cleanly on read.
 */
class CatalogCache
{
    public const TTL_SECONDS = 600;

    public const KEY_SITE_LIST_PREFIX = 'catalog.sites.list.v2.';

    public const KEY_SITE_DETAIL_PREFIX = 'catalog.sites.detail.v2.';

    public const KEY_SITE_LIST_VERSION = 'catalog.sites.list_version';

    public const KEY_FOOTER_LINKS = 'catalog.footer_links.v2';

    public const KEY_PAGE_PREFIX = 'catalog.pages.v2.';

    /**
     * @return LengthAwarePaginator<int, Site>
     */
    public function rememberCatalogPage(SiteCatalogFilters $filters): LengthAwarePaginator
    {
        /** @var array{total: int, per_page: int, current_page: int, items: list<array<string, mixed>>} $payload */
        $payload = Cache::remember(
            $this->siteListCacheKey($filters->fingerprint()),
            self::TTL_SECONDS,
            function () use ($filters): array {
                $paginator = CatalogQuery::catalog($filters)
                    ->paginate(
                        perPage: SiteCatalogFilters::PER_PAGE,
                        page: $filters->page,
                    );

                return [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'items' => $paginator->getCollection()
                        ->map(fn (Site $site): array => $this->siteToCacheArray($site))
                        ->values()
                        ->all(),
                ];
            },
        );

        $sites = $this->hydrateSites($payload['items']);

        return new LengthAwarePaginator(
            $sites,
            $payload['total'],
            $payload['per_page'],
            $payload['current_page'],
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ],
        );
    }

    /**
     * @deprecated Prefer rememberCatalogPage(); kept for generic callbacks that already return arrays.
     *
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function rememberSiteList(string $fingerprint, \Closure $callback): mixed
    {
        return Cache::remember(
            $this->siteListCacheKey($fingerprint),
            self::TTL_SECONDS,
            $callback,
        );
    }

    /**
     * @return Collection<int, FooterLink>
     */
    public function footerLinks(): Collection
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = Cache::remember(
            self::KEY_FOOTER_LINKS,
            self::TTL_SECONDS,
            fn (): array => FooterLink::query()
                ->active()
                ->ordered()
                ->get(['id', 'label', 'url', 'group', 'sort_order'])
                ->toArray(),
        );

        return FooterLink::hydrate($rows);
    }

    public function forgetSite(Site $site): void
    {
        Cache::forget(self::KEY_SITE_DETAIL_PREFIX.$site->domain);
        $this->forgetSiteLists();
    }

    public function forgetSiteLists(): void
    {
        Cache::forever(self::KEY_SITE_LIST_VERSION, (string) time());
    }

    public function siteListVersion(): string
    {
        return (string) Cache::get(self::KEY_SITE_LIST_VERSION, '0');
    }

    public function siteListCacheKey(string $fingerprint): string
    {
        return self::KEY_SITE_LIST_PREFIX.$this->siteListVersion().'.'.$fingerprint;
    }

    public function forgetFooterLinks(): void
    {
        Cache::forget(self::KEY_FOOTER_LINKS);
        Cache::forget('catalog.footer_links'); // legacy corrupt Eloquent entries
    }

    public function forgetPage(Page $page): void
    {
        Cache::forget(self::KEY_PAGE_PREFIX.$page->slug);
        Cache::forget('catalog.pages.'.$page->slug);
    }

    public function rememberPage(string $slug, \Closure $callback): mixed
    {
        /** @var array<string, mixed>|null $row */
        $row = Cache::remember(
            self::KEY_PAGE_PREFIX.$slug,
            self::TTL_SECONDS,
            function () use ($callback): ?array {
                $page = $callback();

                return $page instanceof Page ? $page->toArray() : null;
            },
        );

        return $row === null ? null : Page::hydrate([$row])->first();
    }

    public function findActiveSiteByDomain(string $domain): ?Site
    {
        /** @var array<string, mixed>|null $row */
        $row = Cache::remember(
            self::KEY_SITE_DETAIL_PREFIX.$domain,
            self::TTL_SECONDS,
            function () use ($domain): ?array {
                $site = CatalogQuery::activeSites()
                    ->where('domain', $domain)
                    ->first();

                return $site === null ? null : $this->siteToCacheArray($site);
            },
        );

        if ($row === null) {
            return null;
        }

        return $this->hydrateSites([$row])->first();
    }

    /**
     * @return array<string, mixed>
     */
    protected function siteToCacheArray(Site $site): array
    {
        $site->loadMissing(CatalogQuery::DEFAULT_WITH);

        return [
            ...$site->attributesToArray(),
            'category' => $site->category?->attributesToArray(),
            'labels' => $site->labels
                ->map(fn (Label $label): array => $label->attributesToArray())
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return EloquentCollection<int, Site>
     */
    protected function hydrateSites(array $items): EloquentCollection
    {
        $sites = Site::hydrate(
            array_map(function (array $item): array {
                unset($item['category'], $item['labels']);

                return $item;
            }, $items),
        );

        foreach ($sites as $index => $site) {
            $item = $items[$index] ?? [];

            $site->setRelation(
                'category',
                isset($item['category']) && is_array($item['category'])
                    ? SiteCategory::hydrate([$item['category']])->first()
                    : null,
            );

            $site->setRelation(
                'labels',
                Label::hydrate(
                    isset($item['labels']) && is_array($item['labels'])
                        ? $item['labels']
                        : [],
                ),
            );
        }

        return $sites;
    }
}
