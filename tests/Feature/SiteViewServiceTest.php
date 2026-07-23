<?php

namespace Tests\Feature;

use App\Models\LiveSession;
use App\Models\Site;
use App\Models\SiteView;
use App\Services\SiteViewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteViewServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_increments_today_and_total_counts(): void
    {
        $site = Site::factory()->create();
        $service = app(SiteViewService::class);

        $service->record($site);
        $service->record($site);

        $this->assertSame(2, $service->todayCount($site));
        $this->assertSame(2, $service->totalCount($site));
        $this->assertSame(2, SiteView::query()->where('site_id', $site->id)->count());
    }

    public function test_record_links_live_session_when_token_exists(): void
    {
        $site = Site::factory()->create();
        $session = LiveSession::factory()->create(['session_token' => 'view-token']);

        app(SiteViewService::class)->record($site, 'view-token');

        $this->assertDatabaseHas(SiteView::class, [
            'site_id' => $site->id,
            'live_session_id' => $session->id,
        ]);
    }

    public function test_counts_are_cached_and_invalidated_on_record(): void
    {
        $site = Site::factory()->create();
        $service = app(SiteViewService::class);

        SiteView::factory()->count(3)->create([
            'site_id' => $site->id,
            'viewed_at' => now(),
        ]);

        $this->assertSame(3, $service->totalCount($site));

        // Cached value should be served without seeing a new factory row until forget
        SiteView::factory()->create([
            'site_id' => $site->id,
            'viewed_at' => now(),
        ]);

        $this->assertSame(3, $service->totalCount($site));

        $service->record($site);

        $this->assertFalse(Cache::has('site:'.$site->id.':views:total'));
        $this->assertSame(5, $service->totalCount($site));
        $this->assertSame(5, $service->todayCount($site));
    }

    public function test_site_view_route_records_view(): void
    {
        $site = Site::factory()->create();

        $response = $this->postJson(route('site.view', $site), [
            'session_token' => null,
        ]);

        $response->assertOk()->assertJsonPath('ok', true);

        $this->assertSame(1, SiteView::query()->where('site_id', $site->id)->count());
    }
}
