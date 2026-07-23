<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Services\CatalogCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceBaselineTest extends TestCase
{
    use RefreshDatabase;

    public const MAX_QUERIES_SITE_LIST = 15;

    public const MAX_QUERIES_SITE_DETAIL = 15;

    public const MAX_QUERIES_FILTERED_CATALOG = 15;

    public function test_site_list_stays_under_query_budget_with_eager_loading(): void
    {
        Site::factory()->count(15)->create([
            'status' => SiteStatus::Active,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->get(route('sites.index'));

        $response->assertOk();
        $this->assertLessThanOrEqual(
            self::MAX_QUERIES_SITE_LIST,
            count(DB::getQueryLog()),
            'Site listesi sorgu bütçesini aştı — N+1 veya eksik cache olabilir.',
        );
    }

    public function test_filtered_catalog_stays_under_query_budget(): void
    {
        Site::factory()->count(20)->create([
            'status' => SiteStatus::Active,
            'price' => 150,
            'da_value' => 25,
            'is_dofollow' => true,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->get(route('sites.index', [
            'fiyat_min' => 50,
            'fiyat_max' => 500,
            'da_min' => 10,
            'sort' => 'da_desc',
            'dofollow' => 1,
        ]));

        $response->assertOk();
        $this->assertLessThanOrEqual(
            self::MAX_QUERIES_FILTERED_CATALOG,
            count(DB::getQueryLog()),
            'Filtrelenmiş katalog sorgu bütçesini aştı.',
        );
    }

    public function test_site_detail_stays_under_query_budget(): void
    {
        $category = SiteCategory::factory()->create();
        $site = Site::factory()->create([
            'domain' => 'perf-detail.test',
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'da_value' => 30,
        ]);
        Site::factory()->count(3)->create([
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'da_value' => 40,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->get(route('sites.show', $site->domain));

        $response->assertOk();
        $this->assertLessThanOrEqual(
            self::MAX_QUERIES_SITE_DETAIL,
            count(DB::getQueryLog()),
            'Site detayı sorgu bütçesini aştı.',
        );
    }

    public function test_site_update_invalidates_detail_cache(): void
    {
        $site = Site::factory()->create([
            'domain' => 'cache-bust.test',
            'status' => SiteStatus::Active,
            'description' => 'Eski açıklama',
        ]);

        $cache = app(CatalogCache::class);

        $cached = $cache->findActiveSiteByDomain('cache-bust.test');
        $this->assertSame('Eski açıklama', $cached?->description);

        $site->update(['description' => 'Yeni açıklama']);

        $fresh = $cache->findActiveSiteByDomain('cache-bust.test');
        $this->assertSame('Yeni açıklama', $fresh?->description);
    }

    public function test_second_site_list_hit_uses_cache(): void
    {
        Site::factory()->count(5)->create([
            'status' => SiteStatus::Active,
        ]);

        $this->get(route('sites.index'))->assertOk();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get(route('sites.index'))->assertOk();

        // Cached list + footer composer (also cached) should be very cheap.
        $this->assertLessThanOrEqual(3, count(DB::getQueryLog()));
    }
}
