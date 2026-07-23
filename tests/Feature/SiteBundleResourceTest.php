<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\SiteStatus;
use App\Filament\Resources\SiteBundles\Pages\CreateSiteBundle;
use App\Filament\Resources\SiteBundles\Pages\ListSiteBundles;
use App\Models\Site;
use App\Models\SiteBundle;
use App\Models\SiteCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteBundleResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_site_bundles(): void
    {
        $admin = User::factory()->admin()->create();
        $bundles = SiteBundle::factory()->count(2)->create();

        $this->actingAs($admin);

        Livewire::test(ListSiteBundles::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($bundles);
    }

    public function test_admin_can_create_site_bundle_with_sites(): void
    {
        $admin = User::factory()->admin()->create();
        $category = SiteCategory::factory()->create();
        $sites = Site::factory()->count(2)->create([
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
        ]);

        $this->actingAs($admin);

        Livewire::test(CreateSiteBundle::class)
            ->fillForm([
                'name' => 'Tech Bundle',
                'description' => 'Test paket',
                'price' => 450,
                'currency' => Currency::Try->value,
                'status' => SiteStatus::Active->value,
                'sites' => $sites->pluck('id')->all(),
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $bundle = SiteBundle::query()->where('name', 'Tech Bundle')->first();

        $this->assertNotNull($bundle);
        $this->assertCount(2, $bundle->sites);
    }
}
