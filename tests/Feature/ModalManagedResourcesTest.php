<?php

namespace Tests\Feature;

use App\Filament\Resources\DiscountTiers\Pages\ManageDiscountTiers;
use App\Filament\Resources\Labels\Pages\ManageLabels;
use App\Filament\Resources\SiteCategories\Pages\ManageSiteCategories;
use App\Filament\Resources\SpinWheelPrizes\Pages\ManageSpinWheelPrizes;
use App\Filament\Resources\WalletTopupPackages\Pages\ManageWalletTopupPackages;
use App\Models\DiscountTier;
use App\Models\Label;
use App\Models\SiteCategory;
use App\Models\SpinWheelPrize;
use App\Models\User;
use App\Models\WalletTopupPackage;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ModalManagedResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_categories_are_managed_via_modals(): void
    {
        $admin = User::factory()->admin()->create();
        $category = SiteCategory::factory()->create();

        $this->actingAs($admin);

        Livewire::test(ManageSiteCategories::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$category])
            ->assertActionExists('create')
            ->callAction('create', [
                'name' => 'Yeni Kategori',
                'slug' => 'yeni-kategori',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(SiteCategory::class, [
            'slug' => 'yeni-kategori',
        ]);

        Livewire::test(ManageSiteCategories::class)
            ->callAction(TestAction::make('edit')->table($category), [
                'name' => 'Guncel Kategori',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(SiteCategory::class, [
            'id' => $category->id,
            'name' => 'Guncel Kategori',
        ]);
    }

    public function test_labels_discount_tiers_wallet_packages_and_prizes_use_manage_pages(): void
    {
        $admin = User::factory()->admin()->create();
        Label::factory()->create();
        DiscountTier::factory()->create();
        WalletTopupPackage::factory()->create();
        SpinWheelPrize::factory()->none()->create();

        $this->actingAs($admin);

        Livewire::test(ManageLabels::class)->assertSuccessful()->assertActionExists('create');
        Livewire::test(ManageDiscountTiers::class)->assertSuccessful()->assertActionExists('create');
        Livewire::test(ManageWalletTopupPackages::class)->assertSuccessful()->assertActionExists('create');
        Livewire::test(ManageSpinWheelPrizes::class)->assertSuccessful()->assertActionExists('create');
    }

    public function test_legacy_create_and_edit_routes_are_removed(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        $this->get('/admin/site-categories/create')->assertNotFound();
        $this->get('/admin/labels/create')->assertNotFound();
        $this->get('/admin/discount-tiers/create')->assertNotFound();
        $this->get('/admin/wallet-topup-packages/create')->assertNotFound();
        $this->get('/admin/spin-wheel-prizes/create')->assertNotFound();
    }
}
