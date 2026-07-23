<?php

namespace Tests\Unit;

use App\Enums\SiteStatus;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Services\BudgetPackageSuggester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetPackageSuggesterTest extends TestCase
{
    use RefreshDatabase;

    public function test_suggest_returns_sites_within_budget(): void
    {
        $category = SiteCategory::factory()->create();

        Site::factory()->create([
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'price' => 100,
            'domain' => 'a.example',
        ]);
        Site::factory()->create([
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'price' => 150,
            'domain' => 'b.example',
        ]);
        Site::factory()->create([
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'price' => 400,
            'domain' => 'c.example',
        ]);

        $result = app(BudgetPackageSuggester::class)->suggest(280);

        $this->assertLessThanOrEqual(280, $result['total']);
        $this->assertNotEmpty($result['sites']);
        $this->assertTrue(collect($result['sites'])->every(fn (Site $site): bool => (float) $site->price <= 280));
    }

    public function test_suggest_filters_by_category(): void
    {
        $catA = SiteCategory::factory()->create(['name' => 'A']);
        $catB = SiteCategory::factory()->create(['name' => 'B']);

        Site::factory()->create([
            'site_category_id' => $catA->id,
            'status' => SiteStatus::Active,
            'price' => 50,
        ]);
        Site::factory()->create([
            'site_category_id' => $catB->id,
            'status' => SiteStatus::Active,
            'price' => 50,
        ]);

        $result = app(BudgetPackageSuggester::class)->suggest(200, $catA->id);

        $this->assertNotEmpty($result['sites']);
        $this->assertTrue(collect($result['sites'])->every(
            fn (Site $site): bool => $site->site_category_id === $catA->id,
        ));
    }

    public function test_suggest_returns_empty_for_zero_budget(): void
    {
        $result = app(BudgetPackageSuggester::class)->suggest(0);

        $this->assertSame([], $result['sites']);
        $this->assertSame(0.0, $result['total']);
    }
}
