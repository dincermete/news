<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\SiteStatus;
use App\Filament\Resources\PromotionalListings\Pages\CreatePromotionalListing;
use App\Models\PromotionalListing;
use App\Models\SeoPackage;
use App\Models\Site;
use App\Models\SiteBundle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductSeoRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_vanity_path_renders_listing_with_custom_meta(): void
    {
        $site = Site::factory()->withoutListings()->create([
            'domain' => 'hurriyet.com.tr',
            'status' => SiteStatus::Active,
        ]);
        PromotionalListing::factory()->article()->for($site)->create([
            'price' => 100,
            'currency' => Currency::Try,
            'status' => SiteStatus::Active,
            'public_path' => 'hurriyetcom-tr-tanitimyazisi',
            'meta_title' => 'Hürriyet Tanıtım Yazısı',
            'meta_description' => 'Özel meta açıklama',
        ]);

        $this->get('/hurriyetcom-tr-tanitimyazisi')
            ->assertOk()
            ->assertSee('Hürriyet Tanıtım Yazısı', false)
            ->assertSee('Özel meta açıklama', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee(url('/hurriyetcom-tr-tanitimyazisi'), false);
    }

    public function test_technical_site_url_redirects_to_public_path(): void
    {
        $site = Site::factory()->withoutListings()->create([
            'domain' => 'hurriyet.com.tr',
            'status' => SiteStatus::Active,
        ]);
        PromotionalListing::factory()->article()->for($site)->create([
            'price' => 100,
            'status' => SiteStatus::Active,
            'public_path' => 'hurriyetcom-tr-tanitimyazisi',
        ]);

        $this->get(route('sites.show', 'hurriyet.com.tr'))
            ->assertRedirect(url('/hurriyetcom-tr-tanitimyazisi'));
    }

    public function test_technical_bundle_url_redirects_when_public_path_set(): void
    {
        $bundle = SiteBundle::factory()->create([
            'slug' => 'buyuk-paket',
            'public_path' => 'buyuk-paket-seo',
            'status' => SiteStatus::Active,
        ]);

        $this->get(route('bundles.show', $bundle->slug))
            ->assertRedirect(url('/buyuk-paket-seo'));

        $this->get('/buyuk-paket-seo')
            ->assertOk()
            ->assertSee($bundle->name, false);
    }

    public function test_seo_package_technical_and_vanity_urls(): void
    {
        $package = SeoPackage::factory()->create([
            'name' => 'Growth SEO',
            'slug' => 'growth-seo',
            'public_path' => 'growth-seo-paketi',
            'status' => SiteStatus::Active,
            'meta_title' => 'Growth SEO Meta',
        ]);

        $this->get(route('seo-packages.show', 'growth-seo'))
            ->assertRedirect(url('/growth-seo-paketi'));

        $this->get('/growth-seo-paketi')
            ->assertOk()
            ->assertSee('Growth SEO Meta', false);
    }

    public function test_site_without_public_path_stays_on_technical_url(): void
    {
        $site = Site::factory()->create([
            'domain' => 'plain.test',
            'status' => SiteStatus::Active,
            'price' => 50,
        ]);

        $this->get(route('sites.show', 'plain.test'))
            ->assertOk()
            ->assertSee('plain.test', false);
    }

    public function test_filament_listing_form_exposes_seo_fields(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(CreatePromotionalListing::class)
            ->assertSuccessful()
            ->assertFormFieldExists('public_path')
            ->assertFormFieldExists('meta_title')
            ->assertFormFieldExists('meta_description');
    }
}
