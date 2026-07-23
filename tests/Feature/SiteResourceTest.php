<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Sites\Pages\CreateSite;
use App\Filament\Resources\Sites\Pages\ListSites;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_sites(): void
    {
        $admin = User::factory()->admin()->create();
        $category = SiteCategory::factory()->create();
        $sites = Site::factory()->count(3)->create([
            'site_category_id' => $category->id,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListSites::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($sites);
    }

    public function test_admin_can_create_a_site(): void
    {
        $admin = User::factory()->admin()->create();
        $category = SiteCategory::factory()->create();

        $this->actingAs($admin);

        Livewire::test(CreateSite::class)
            ->fillForm([
                'domain' => 'example-test.com',
                'site_category_id' => $category->id,
                'description' => 'Test site',
                'age' => 5,
                'is_dofollow' => true,
                'is_news_approved' => false,
                'status' => SiteStatus::Active->value,
                'price' => 100,
                'discount_price' => 80,
                'currency' => 'USD',
                'da_value' => 40,
                'da_source' => 'manual',
                'pa_value' => 35,
                'pa_source' => 'manual',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas(Site::class, [
            'domain' => 'example-test.com',
            'site_category_id' => $category->id,
            'price' => 100,
            'status' => SiteStatus::Active->value,
        ]);
    }

    public function test_editor_does_not_see_admin_only_tabs(): void
    {
        $editor = User::factory()->editor()->create([
            'role' => UserRole::Editor,
        ]);

        $this->actingAs($editor);

        Livewire::test(CreateSite::class)
            ->assertSuccessful()
            ->assertFormFieldIsHidden('internal_notes')
            ->assertFormFieldIsHidden('site_owner_name');
    }

    public function test_bulk_activate_updates_selected_sites(): void
    {
        $admin = User::factory()->admin()->create();
        $category = SiteCategory::factory()->create();
        $sites = Site::factory()->count(2)->create([
            'site_category_id' => $category->id,
            'status' => SiteStatus::Draft,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListSites::class)
            ->callTableBulkAction('activate', $sites)
            ->assertNotified();

        foreach ($sites as $site) {
            $this->assertDatabaseHas(Site::class, [
                'id' => $site->id,
                'status' => SiteStatus::Active->value,
            ]);
        }
    }
}
