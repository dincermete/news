<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Filament\Widgets\CategoryPerformanceWidget;
use App\Filament\Widgets\PublicStatsOverview;
use App\Filament\Widgets\RevenueChartWidget;
use App\Models\Payment;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_public_stats_overview_renders_cached_counts(): void
    {
        Site::factory()->count(2)->create(['status' => SiteStatus::Active]);
        User::factory()->customer()->count(3)->create();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(PublicStatsOverview::class)
            ->assertSuccessful()
            ->assertSee('Aktif Siteler')
            ->assertSee('2')
            ->assertSee('Müşteriler')
            ->assertSee('3');
    }

    public function test_revenue_chart_filter_changes_description(): void
    {
        Payment::factory()->paid()->create([
            'amount' => 100,
            'paid_at' => now()->subDay(),
        ]);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(RevenueChartWidget::class)
            ->assertSuccessful()
            ->assertSee('Son 30 gün')
            ->set('filter', '7')
            ->assertSee('Son 7 gün');
    }

    public function test_category_performance_shows_empty_state_without_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(CategoryPerformanceWidget::class)
            ->assertSuccessful()
            ->assertSee('Kategori verisi yok');
    }
}
