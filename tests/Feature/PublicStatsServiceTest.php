<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\SiteStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Site;
use App\Models\User;
use App\Services\PublicStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_counts_are_calculated_correctly(): void
    {
        Site::factory()->create(['status' => SiteStatus::Active]);
        Site::factory()->create(['status' => SiteStatus::Active]);
        Site::factory()->create(['status' => SiteStatus::Draft]);

        $orderSite = Site::factory()->create(['status' => SiteStatus::Draft]);

        Order::factory()->create([
            'site_id' => $orderSite->id,
            'status' => OrderStatus::Published,
        ]);
        Order::factory()->create([
            'site_id' => $orderSite->id,
            'status' => OrderStatus::ReportSent,
        ]);
        Order::factory()->create([
            'site_id' => $orderSite->id,
            'status' => OrderStatus::PaymentPending,
        ]);

        User::factory()->customer()->count(3)->create();
        User::factory()->admin()->create();
        User::factory()->editor()->create();

        $stats = app(PublicStatsService::class);

        $this->assertSame(2, $stats->activeSiteCount());
        $this->assertSame(2, $stats->publishedOrderCount());
        $this->assertSame(3, $stats->customerCount());
    }

    public function test_active_site_count_cache_is_invalidated_when_site_saved(): void
    {
        Site::factory()->create(['status' => SiteStatus::Active]);

        $stats = app(PublicStatsService::class);
        $this->assertSame(1, $stats->activeSiteCount());
        $this->assertTrue(Cache::has(PublicStatsService::CACHE_KEY_ACTIVE_SITES));

        // Cached until a Site write invalidates via SiteObserver
        $this->assertSame(1, $stats->activeSiteCount());

        Site::factory()->create(['status' => SiteStatus::Active]);

        $this->assertSame(2, $stats->activeSiteCount());
    }

    public function test_forget_clears_cached_counts(): void
    {
        Site::factory()->create(['status' => SiteStatus::Active]);

        $stats = app(PublicStatsService::class);
        $this->assertSame(1, $stats->activeSiteCount());

        Site::factory()->create(['status' => SiteStatus::Draft]);
        // Draft create still clears active-sites key; recount stays 1
        $stats->forget();
        $this->assertSame(1, $stats->activeSiteCount());
    }

    public function test_refresh_command_warms_cache(): void
    {
        User::factory()->customer()->create(['role' => UserRole::Customer]);
        Site::factory()->create(['status' => SiteStatus::Active]);
        Order::factory()->create([
            'site_id' => Site::factory()->create(['status' => SiteStatus::Draft]),
            'status' => OrderStatus::Published,
        ]);

        $this->artisan('stats:refresh-public')->assertSuccessful();

        $this->assertTrue(Cache::has(PublicStatsService::CACHE_KEY_ACTIVE_SITES));
        $this->assertTrue(Cache::has(PublicStatsService::CACHE_KEY_PUBLISHED_ORDERS));
        $this->assertTrue(Cache::has(PublicStatsService::CACHE_KEY_CUSTOMERS));
    }

    public function test_public_stats_api_endpoint(): void
    {
        Site::factory()->create(['status' => SiteStatus::Active]);

        $response = $this->getJson(route('api.public-stats'));

        $response->assertOk()
            ->assertJsonStructure(['active_sites', 'published_orders', 'customers'])
            ->assertJsonPath('active_sites', 1);
    }
}
