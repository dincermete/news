<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Models\Label;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Services\CatalogCache;
use App\Support\SiteCatalogFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SiteCatalogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_active_sites(): void
    {
        $active = Site::factory()->create([
            'domain' => 'active-list.test',
            'status' => SiteStatus::Active,
        ]);
        Site::factory()->create([
            'domain' => 'draft-list.test',
            'status' => SiteStatus::Draft,
        ]);

        $response = $this->get(route('sites.index'));

        $response->assertOk();
        $response->assertSee('active-list.test');
        $response->assertDontSee('draft-list.test');
        $response->assertViewHas('sites', fn ($sites) => $sites->contains('id', $active->id));
    }

    public function test_filters_by_category_slug(): void
    {
        $haber = SiteCategory::factory()->create(['name' => 'Haber', 'slug' => 'haber']);
        $blog = SiteCategory::factory()->create(['name' => 'Blog', 'slug' => 'blog']);

        Site::factory()->create([
            'domain' => 'haber-site.test',
            'site_category_id' => $haber->id,
            'status' => SiteStatus::Active,
        ]);
        Site::factory()->create([
            'domain' => 'blog-site.test',
            'site_category_id' => $blog->id,
            'status' => SiteStatus::Active,
        ]);

        $response = $this->get(route('sites.index', ['kategori' => 'haber']));

        $response->assertOk();
        $response->assertSee('haber-site.test');
        $response->assertDontSee('blog-site.test');
    }

    public function test_filters_by_domain_search_query(): void
    {
        Site::factory()->create([
            'domain' => 'habergazetesi.test',
            'status' => SiteStatus::Active,
        ]);
        Site::factory()->create([
            'domain' => 'finansblogu.test',
            'status' => SiteStatus::Active,
        ]);

        $response = $this->get(route('sites.index', ['q' => 'haber']));

        $response->assertOk();
        $response->assertSee('habergazetesi.test');
        $response->assertDontSee('finansblogu.test');
    }

    public function test_domain_search_escapes_like_wildcards(): void
    {
        Site::factory()->create([
            'domain' => 'yuzde100haber.test',
            'status' => SiteStatus::Active,
        ]);

        $response = $this->get(route('sites.index', ['q' => '%']));

        $response->assertOk();
        $response->assertDontSee('yuzde100haber.test');
    }

    public function test_filters_by_price_and_da_range(): void
    {
        Site::factory()->create([
            'domain' => 'in-range.test',
            'status' => SiteStatus::Active,
            'price' => 100,
            'da_value' => 30,
        ]);
        Site::factory()->create([
            'domain' => 'price-high.test',
            'status' => SiteStatus::Active,
            'price' => 900,
            'da_value' => 30,
        ]);
        Site::factory()->create([
            'domain' => 'da-low.test',
            'status' => SiteStatus::Active,
            'price' => 100,
            'da_value' => 5,
        ]);

        $response = $this->get(route('sites.index', [
            'fiyat_min' => 50,
            'fiyat_max' => 500,
            'da_min' => 20,
            'da_max' => 40,
        ]));

        $response->assertOk();
        $response->assertSee('in-range.test');
        $response->assertDontSee('price-high.test');
        $response->assertDontSee('da-low.test');
    }

    public function test_filters_dofollow_and_news_approved(): void
    {
        Site::factory()->create([
            'domain' => 'dofollow-news.test',
            'status' => SiteStatus::Active,
            'is_dofollow' => true,
            'is_news_approved' => true,
        ]);
        Site::factory()->create([
            'domain' => 'nofollow.test',
            'status' => SiteStatus::Active,
            'is_dofollow' => false,
            'is_news_approved' => true,
        ]);

        $response = $this->get(route('sites.index', [
            'dofollow' => 1,
            'news' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('dofollow-news.test');
        $response->assertDontSee('nofollow.test');
    }

    public function test_sorts_by_price_ascending(): void
    {
        Site::factory()->create([
            'domain' => 'expensive.test',
            'status' => SiteStatus::Active,
            'price' => 300,
        ]);
        Site::factory()->create([
            'domain' => 'cheap.test',
            'status' => SiteStatus::Active,
            'price' => 50,
        ]);

        $response = $this->get(route('sites.index', ['sort' => 'price_asc']));

        $response->assertOk();
        $response->assertSeeInOrder(['cheap.test', 'expensive.test']);
    }

    public function test_sorts_by_da_descending(): void
    {
        Site::factory()->create([
            'domain' => 'low-da.test',
            'status' => SiteStatus::Active,
            'da_value' => 10,
        ]);
        Site::factory()->create([
            'domain' => 'high-da.test',
            'status' => SiteStatus::Active,
            'da_value' => 80,
        ]);

        $response = $this->get(route('sites.index', ['sort' => 'da_desc']));

        $response->assertOk();
        $response->assertSeeInOrder(['high-da.test', 'low-da.test']);
    }

    public function test_pagination_uses_twenty_four_per_page(): void
    {
        Site::factory()->count(25)->create([
            'status' => SiteStatus::Active,
        ]);

        $page1 = $this->get(route('sites.index'));
        $page1->assertOk();
        $page1->assertViewHas('sites', fn ($sites) => $sites->count() === 24 && $sites->total() === 25);

        $page2 = $this->get(route('sites.index', ['page' => 2]));
        $page2->assertOk();
        $page2->assertViewHas('sites', fn ($sites) => $sites->count() === 1 && $sites->currentPage() === 2);
    }

    public function test_pagination_preserves_filter_query_string(): void
    {
        Site::factory()->count(25)->create([
            'status' => SiteStatus::Active,
            'price' => 100,
        ]);

        $response = $this->get(route('sites.index', [
            'fiyat_min' => 50,
            'sort' => 'price_desc',
            'page' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('fiyat_min=50', false);
        $response->assertSee('sort=price_desc', false);
    }

    public function test_catalog_list_is_cached_under_fingerprint_key(): void
    {
        Site::factory()->create([
            'status' => SiteStatus::Active,
            'price' => 120,
        ]);

        $filters = SiteCatalogFilters::fromRequest(
            Request::create('/siteler', 'GET', [
                'fiyat_min' => 50,
                'sort' => 'price_asc',
            ]),
        );

        $cache = app(CatalogCache::class);
        $key = $cache->siteListCacheKey($filters->fingerprint());

        $this->assertFalse(Cache::has($key));

        $this->get(route('sites.index', [
            'fiyat_min' => 50,
            'sort' => 'price_asc',
        ]))->assertOk();

        $this->assertTrue(Cache::has($key));
    }

    public function test_different_filters_use_different_cache_keys(): void
    {
        $a = SiteCatalogFilters::fromRequest(Request::create('/siteler', 'GET', ['fiyat_min' => 10]));
        $b = SiteCatalogFilters::fromRequest(Request::create('/siteler', 'GET', ['fiyat_min' => 99]));

        $this->assertNotSame($a->fingerprint(), $b->fingerprint());
    }

    public function test_catalog_query_budget_with_labels_stays_under_threshold(): void
    {
        $label = Label::factory()->create(['name' => 'Premium']);

        $sites = Site::factory()->count(12)->create([
            'status' => SiteStatus::Active,
        ]);

        foreach ($sites as $site) {
            $site->labels()->attach($label);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->get(route('sites.index', [
            'fiyat_min' => 1,
            'sort' => 'price_asc',
        ]));

        $response->assertOk();
        $this->assertLessThanOrEqual(
            15,
            count(DB::getQueryLog()),
            'Filtrelenmiş site kataloğu sorgu bütçesini aştı.',
        );
    }
}
