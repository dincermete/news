<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Filament\Resources\PromotionalListings\Pages\CreatePromotionalListing;
use App\Filament\Resources\PromotionalListings\Pages\ListPromotionalListings;
use App\Filament\Resources\Sites\Pages\CreateSite;
use App\Models\PromotionalListing;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PromotionalListingResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_promotional_listings(): void
    {
        $admin = User::factory()->admin()->create();
        $listings = PromotionalListing::factory()->count(2)->create([
            'status' => SiteStatus::Active,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListPromotionalListings::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($listings);
    }

    public function test_admin_can_create_promotional_listing(): void
    {
        $admin = User::factory()->admin()->create();
        $site = Site::factory()->create(['status' => SiteStatus::Active]);

        // Default factory listings may already occupy site_article / footer_link.
        $site->promotionalListings()->delete();

        $this->actingAs($admin);

        Livewire::test(CreatePromotionalListing::class)
            ->fillForm([
                'site_id' => $site->id,
                'type' => PromotionalListingType::SiteArticle->value,
                'name' => 'Tanıtım Yazısı Ürünü',
                'price' => 250,
                'discount_price' => 200,
                'currency' => Currency::Try->value,
                'status' => SiteStatus::Active->value,
                'short_description' => 'Tanıtım yazısı',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas(PromotionalListing::class, [
            'site_id' => $site->id,
            'type' => PromotionalListingType::SiteArticle->value,
            'name' => 'Tanıtım Yazısı Ürünü',
            'price' => 250,
            'discount_price' => 200,
            'status' => SiteStatus::Active->value,
        ]);
    }

    public function test_site_form_does_not_expose_sale_price_fields(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(CreateSite::class)
            ->assertSuccessful()
            ->assertFormFieldDoesNotExist('price')
            ->assertFormFieldDoesNotExist('discount_price')
            ->assertFormFieldDoesNotExist('press_release_price')
            ->assertFormFieldDoesNotExist('currency');
    }
}
