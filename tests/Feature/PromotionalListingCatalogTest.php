<?php

namespace Tests\Feature;

use App\Enums\CartStatus;
use App\Enums\Currency;
use App\Enums\ProductType;
use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Models\Cart;
use App\Models\FooterLinkDurationOption;
use App\Models\PromotionalListing;
use App\Models\Site;
use App\Models\User;
use App\Services\CartService;
use App\Support\CatalogQuery;
use App\Support\SiteCatalogFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PromotionalListingCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_only_includes_active_article_listings(): void
    {
        $visible = Site::factory()->create([
            'domain' => 'visible-listing.test',
            'status' => SiteStatus::Active,
            'price' => 100,
        ]);
        $hiddenSite = Site::factory()->create([
            'domain' => 'inactive-listing.test',
            'status' => SiteStatus::Active,
            'price' => 100,
        ]);
        $hiddenSite->articleListing()->update(['status' => SiteStatus::Inactive]);

        Site::factory()->create([
            'domain' => 'draft-site.test',
            'status' => SiteStatus::Draft,
            'price' => 100,
        ]);

        $sites = CatalogQuery::catalog(
            SiteCatalogFilters::fromRequest(Request::create('/siteler', 'GET'))
        )->get();

        $this->assertTrue($sites->contains('domain', 'visible-listing.test'));
        $this->assertFalse($sites->contains('domain', 'inactive-listing.test'));
        $this->assertFalse($sites->contains('domain', 'draft-site.test'));
        $this->assertSame(100.0, (float) $sites->firstWhere('id', $visible->id)->price);
    }

    public function test_cart_uses_listing_price_for_site_article(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);
        $site = Site::factory()->create([
            'status' => SiteStatus::Active,
            'price' => 80,
            'discount_price' => 50,
            'currency' => Currency::Try,
        ]);

        $item = app(CartService::class)->addSiteArticle($cart, $site);

        $this->assertSame(ProductType::SiteArticle, $item->product_type);
        $this->assertSame('50.00', $item->price);
    }

    public function test_footer_cart_uses_absolute_listing_price_without_multiplier(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);
        $site = Site::factory()->create([
            'status' => SiteStatus::Active,
            'price' => 100,
            'currency' => Currency::Try,
        ]);
        $option = FooterLinkDurationOption::factory()->create([
            'is_active' => true,
            'price_multiplier' => 3,
            'flat_price' => null,
        ]);

        $item = app(CartService::class)->addFooterLink($cart, $site, $option);

        $this->assertSame('100.00', $item->price);
    }

    public function test_press_release_requires_active_listing(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);
        $site = Site::factory()->create([
            'status' => SiteStatus::Active,
            'price' => 100,
        ]);

        $this->expectException(ValidationException::class);

        app(CartService::class)->addPressRelease($cart, $site);
    }

    public function test_factory_creates_typed_listings(): void
    {
        $listing = PromotionalListing::factory()->pressRelease()->active()->create([
            'price' => 175,
        ]);

        $this->assertSame(PromotionalListingType::PressRelease, $listing->type);
        $this->assertSame(175.0, $listing->effectivePrice());
    }
}
