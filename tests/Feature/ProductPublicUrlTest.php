<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\SiteStatus;
use App\Models\Page;
use App\Models\PromotionalListing;
use App\Models\Site;
use App\Models\SiteBundle;
use App\Services\ProductPublicUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductPublicUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_reserved_paths(): void
    {
        $this->expectException(ValidationException::class);

        app(ProductPublicUrl::class)->assertPathAvailable('siteler');
    }

    public function test_rejects_duplicate_paths_across_product_types(): void
    {
        $site = Site::factory()->withoutListings()->create(['status' => SiteStatus::Active]);
        PromotionalListing::factory()->article()->for($site)->create([
            'public_path' => 'hurriyetcom-tr-tanitimyazisi',
            'status' => SiteStatus::Active,
            'price' => 100,
            'currency' => Currency::Try,
        ]);

        $this->expectException(ValidationException::class);

        app(ProductPublicUrl::class)->assertPathAvailable('hurriyetcom-tr-tanitimyazisi');
    }

    public function test_url_for_prefers_public_path(): void
    {
        $bundle = SiteBundle::factory()->create([
            'slug' => 'tech-paket',
            'public_path' => 'tech-paket-kanonik',
            'status' => SiteStatus::Active,
        ]);

        $this->assertSame(
            url('/tech-paket-kanonik'),
            app(ProductPublicUrl::class)->urlFor($bundle),
        );
    }

    public function test_url_for_falls_back_to_technical_route(): void
    {
        $bundle = SiteBundle::factory()->create([
            'slug' => 'tech-paket',
            'public_path' => null,
            'status' => SiteStatus::Active,
        ]);

        $this->assertSame(
            route('bundles.show', 'tech-paket'),
            app(ProductPublicUrl::class)->urlFor($bundle),
        );
    }

    public function test_rejects_page_slug_collision(): void
    {
        Page::factory()->create(['slug' => 'ozel-sayfa', 'is_active' => true]);

        $this->expectException(ValidationException::class);

        app(ProductPublicUrl::class)->assertPathAvailable('ozel-sayfa');
    }
}
