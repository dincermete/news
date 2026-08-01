<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Models\PromotionalListing;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Services\ProductCrossSellService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrossSellTest extends TestCase
{
    use RefreshDatabase;

    public function test_related_sites_fall_back_to_same_category_when_manual_empty(): void
    {
        $category = SiteCategory::factory()->create();
        $other = SiteCategory::factory()->create();

        $site = Site::factory()->create([
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'da_value' => 10,
        ]);
        $related = Site::factory()->create([
            'domain' => 'related-high-da.test',
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'da_value' => 80,
        ]);
        Site::factory()->create([
            'domain' => 'other-category.test',
            'site_category_id' => $other->id,
            'status' => SiteStatus::Active,
            'da_value' => 99,
        ]);

        $listing = $site->articleListing;

        $results = app(ProductCrossSellService::class)->relatedSitesFor($listing, $site);

        $this->assertTrue($results->contains('id', $related->id));
        $this->assertFalse($results->contains('domain', 'other-category.test'));
        $this->assertFalse($results->contains('id', $site->id));
    }

    public function test_manual_related_listings_override_category_fallback(): void
    {
        $category = SiteCategory::factory()->create();

        $site = Site::factory()->create([
            'domain' => 'source.test',
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
        ]);
        $sameCategory = Site::factory()->create([
            'domain' => 'same-category.test',
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'da_value' => 90,
        ]);
        $picked = Site::factory()->create([
            'domain' => 'manual-related.test',
            'site_category_id' => SiteCategory::factory()->create()->id,
            'status' => SiteStatus::Active,
            'da_value' => 20,
        ]);

        $listing = $site->articleListing;
        $pickedListing = $picked->articleListing;

        $this->assertInstanceOf(PromotionalListing::class, $listing);
        $this->assertInstanceOf(PromotionalListing::class, $pickedListing);

        $listing->relatedListings()->attach($pickedListing->id, ['sort_order' => 0]);

        $results = app(ProductCrossSellService::class)->relatedSitesFor($listing, $site);

        $this->assertTrue($results->contains('id', $picked->id));
        $this->assertFalse($results->contains('id', $sameCategory->id));
    }

    public function test_recommended_sites_fall_back_to_best_sellers_when_manual_empty(): void
    {
        $category = SiteCategory::factory()->create();
        $otherCategory = SiteCategory::factory()->create();

        $site = Site::factory()->create([
            'domain' => 'rec-source.test',
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'da_value' => 10,
        ]);
        $relatedOnly = Site::factory()->create([
            'domain' => 'related-only.test',
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'da_value' => 95,
        ]);
        $bestSeller = Site::factory()->create([
            'domain' => 'best-seller.test',
            'site_category_id' => $otherCategory->id,
            'status' => SiteStatus::Active,
            'da_value' => 40,
        ]);

        $listing = $site->articleListing;
        $this->assertInstanceOf(PromotionalListing::class, $listing);

        $results = app(ProductCrossSellService::class)->recommendedSitesFor(
            $listing,
            $site,
            excludeSiteIds: [$relatedOnly->id],
        );

        $this->assertTrue($results->contains('id', $bestSeller->id));
        $this->assertFalse($results->contains('id', $site->id));
        $this->assertFalse($results->contains('id', $relatedOnly->id));
    }

    public function test_manual_recommended_listings_override_best_seller_fallback(): void
    {
        $category = SiteCategory::factory()->create();

        $site = Site::factory()->create([
            'domain' => 'rec-source.test',
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
        ]);
        $fallback = Site::factory()->create([
            'domain' => 'fallback-best.test',
            'status' => SiteStatus::Active,
            'da_value' => 99,
        ]);
        $recommended = Site::factory()->create([
            'domain' => 'recommended.test',
            'status' => SiteStatus::Active,
            'da_value' => 20,
        ]);

        $listing = $site->articleListing;
        $recommendedListing = $recommended->articleListing;

        $this->assertInstanceOf(PromotionalListing::class, $listing);
        $this->assertInstanceOf(PromotionalListing::class, $recommendedListing);

        $listing->recommendedListings()->attach($recommendedListing->id, ['sort_order' => 1]);

        $results = app(ProductCrossSellService::class)->recommendedSitesFor($listing, $site);

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $recommended->id));
        $this->assertFalse($results->contains('id', $fallback->id));
    }

    public function test_show_page_renders_related_and_recommended_tables(): void
    {
        $category = SiteCategory::factory()->create();
        $otherCategory = SiteCategory::factory()->create();

        $site = Site::factory()->create([
            'domain' => 'cross-sell-show.test',
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
        ]);
        $related = Site::factory()->create([
            'domain' => 'related-show.test',
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'da_value' => 70,
        ]);
        $recommended = Site::factory()->create([
            'domain' => 'recommended-show.test',
            'site_category_id' => $otherCategory->id,
            'status' => SiteStatus::Active,
            'da_value' => 60,
        ]);

        $response = $this->get(route('sites.show', $site->domain));

        $response->assertOk();
        $response->assertSee('İlgili Ürünler', false);
        $response->assertSee('Tavsiye Edilen Ürünler', false);
        $response->assertSee('related-show.test', false);
        $response->assertSee('recommended-show.test', false);
        $response->assertDontSee('Aynı kategoride diğer siteler', false);
        $this->assertNotNull($related->id);
        $this->assertNotNull($recommended->id);
    }
}
