<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Services\SalesReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_revenue_between_sums_paid_payments(): void
    {
        Payment::factory()->paid()->create([
            'amount' => 100,
            'paid_at' => now()->subDays(2),
        ]);
        Payment::factory()->paid()->create([
            'amount' => 50.5,
            'paid_at' => now()->subDay(),
        ]);
        Payment::factory()->create([
            'status' => PaymentStatus::Pending,
            'amount' => 999,
            'paid_at' => null,
        ]);
        Payment::factory()->paid()->create([
            'amount' => 20,
            'paid_at' => now()->subDays(40),
        ]);

        $revenue = app(SalesReportService::class)->revenueBetween(
            now()->subDays(7),
            now(),
        );

        $this->assertSame(150.5, $revenue);
    }

    public function test_top_selling_sites_orders_by_count(): void
    {
        $siteA = Site::factory()->create(['domain' => 'a.test']);
        $siteB = Site::factory()->create(['domain' => 'b.test']);

        Order::factory()->count(3)->create(['site_id' => $siteA->id, 'price' => 100]);
        Order::factory()->count(1)->create(['site_id' => $siteB->id, 'price' => 200]);

        $top = app(SalesReportService::class)->topSellingSites(10);

        $this->assertSame('a.test', $top[0]['domain']);
        $this->assertSame(3, $top[0]['orders_count']);
        $this->assertSame(300.0, $top[0]['revenue']);
        $this->assertSame('b.test', $top[1]['domain']);
    }

    public function test_category_performance_groups_by_category(): void
    {
        $news = SiteCategory::factory()->create(['name' => 'Haber']);
        $tech = SiteCategory::factory()->create(['name' => 'Teknoloji']);

        $newsSite = Site::factory()->create(['site_category_id' => $news->id]);
        $techSite = Site::factory()->create(['site_category_id' => $tech->id]);

        Order::factory()->count(2)->create(['site_id' => $newsSite->id, 'price' => 100]);
        Order::factory()->create(['site_id' => $techSite->id, 'price' => 250]);

        $performance = app(SalesReportService::class)->categoryPerformance();

        $byName = collect($performance)->keyBy('category');

        $this->assertSame(2, $byName['Haber']['orders_count']);
        $this->assertSame(200.0, $byName['Haber']['revenue']);
        $this->assertSame(1, $byName['Teknoloji']['orders_count']);
        $this->assertSame(250.0, $byName['Teknoloji']['revenue']);
    }

    public function test_daily_revenue_series_fills_missing_days(): void
    {
        Payment::factory()->paid()->create([
            'amount' => 80,
            'paid_at' => now()->subDay()->setTime(12, 0),
        ]);

        $series = app(SalesReportService::class)->dailyRevenueSeries(
            now()->subDays(2)->startOfDay(),
            now()->endOfDay(),
        );

        $this->assertCount(3, $series['labels']);
        $this->assertCount(3, $series['data']);
        $this->assertContains(80.0, $series['data']);
    }
}
