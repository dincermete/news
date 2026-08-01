<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\SiteStatus;
use App\Filament\Resources\SiteBundles\Pages\CreateSiteBundle;
use App\Filament\Resources\SiteBundles\Pages\EditSiteBundle;
use App\Models\Site;
use App\Models\SiteBundle;
use App\Models\SiteCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteBundleAppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_bundle_icon_and_background_colors(): void
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
                'name' => 'Renkli Paket',
                'description' => 'İkon ve renkli arka plan',
                'icon' => 'heroicon-o-rocket-launch',
                'bg_palette' => 'violet',
                'bg_color_from' => '#7c3aed',
                'bg_color_to' => '#c4b5fd',
                'price' => 750,
                'currency' => Currency::Try->value,
                'status' => SiteStatus::Active->value,
                'sites' => $sites->pluck('id')->all(),
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $bundle = SiteBundle::query()->where('name', 'Renkli Paket')->first();

        $this->assertNotNull($bundle);
        $this->assertSame('heroicon-o-rocket-launch', $bundle->icon);
        $this->assertSame('#7c3aed', $bundle->bg_color_from);
        $this->assertSame('#c4b5fd', $bundle->bg_color_to);
        $this->assertSame('heroicon-o-rocket-launch', $bundle->resolvedIcon());
        $this->assertStringContainsString('#7c3aed', $bundle->iconBadgeStyle());
        $this->assertStringContainsString('#c4b5fd', $bundle->cardBackgroundStyle());
    }

    public function test_bundle_catalog_renders_selected_icon_and_colors(): void
    {
        $bundle = SiteBundle::factory()->create([
            'name' => 'Görsel Paket',
            'slug' => 'gorsel-paket',
            'icon' => 'heroicon-o-gift',
            'bg_color_from' => '#0d9488',
            'bg_color_to' => '#5eead4',
            'status' => SiteStatus::Active,
        ]);

        $this->get(route('bundles.index'))
            ->assertOk()
            ->assertSee('Görsel Paket')
            ->assertSee('#0d9488', false)
            ->assertSee('#5eead4', false);

        $this->get(route('bundles.show', $bundle))
            ->assertOk()
            ->assertSee('Görsel Paket')
            ->assertSee('#0d9488', false);
    }

    public function test_admin_can_update_bundle_appearance_on_edit(): void
    {
        $admin = User::factory()->admin()->create();
        $bundle = SiteBundle::factory()->create([
            'icon' => 'heroicon-o-cube',
            'bg_color_from' => '#ef4444',
            'bg_color_to' => '#f97316',
        ]);

        $this->actingAs($admin);

        Livewire::test(EditSiteBundle::class, ['record' => $bundle->getRouteKey()])
            ->fillForm([
                'icon' => 'heroicon-o-megaphone',
                'bg_palette' => 'sky',
                'bg_color_from' => '#0284c7',
                'bg_color_to' => '#7dd3fc',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $bundle->refresh();

        $this->assertSame('heroicon-o-megaphone', $bundle->icon);
        $this->assertSame('#0284c7', $bundle->bg_color_from);
        $this->assertSame('#7dd3fc', $bundle->bg_color_to);
    }
}
