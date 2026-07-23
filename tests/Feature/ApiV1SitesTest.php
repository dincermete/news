<?php

namespace Tests\Feature;

use App\Enums\ApiTokenAbility;
use App\Enums\SiteStatus;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1SitesTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/sites')->assertUnauthorized();
    }

    public function test_requires_read_catalog_ability(): void
    {
        $user = User::factory()->customer()->create();
        $token = $user->createToken('limited', [ApiTokenAbility::ReadWallet->value])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/sites')
            ->assertForbidden();
    }

    public function test_lists_only_active_sites_with_filters(): void
    {
        $category = SiteCategory::factory()->create();
        $otherCategory = SiteCategory::factory()->create();

        Site::factory()->create([
            'status' => SiteStatus::Active,
            'site_category_id' => $category->id,
            'price' => 100,
            'da_value' => 40,
            'domain' => 'match.test',
        ]);
        Site::factory()->create([
            'status' => SiteStatus::Active,
            'site_category_id' => $otherCategory->id,
            'price' => 500,
            'da_value' => 10,
            'domain' => 'other.test',
        ]);
        Site::factory()->create([
            'status' => SiteStatus::Draft,
            'site_category_id' => $category->id,
            'price' => 50,
            'da_value' => 90,
            'domain' => 'draft.test',
        ]);

        $user = User::factory()->customer()->create();
        $token = $user->createToken('catalog', [ApiTokenAbility::ReadCatalog->value])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/sites?'.http_build_query([
                'category_id' => $category->id,
                'min_price' => 50,
                'max_price' => 200,
                'min_da' => 30,
                'max_da' => 50,
            ]))
            ->assertOk();

        $domains = collect($response->json('data'))->pluck('domain');

        $this->assertTrue($domains->contains('match.test'));
        $this->assertFalse($domains->contains('other.test'));
        $this->assertFalse($domains->contains('draft.test'));
    }

    public function test_show_returns_active_site(): void
    {
        $site = Site::factory()->create(['status' => SiteStatus::Active, 'domain' => 'detail.test']);
        $user = User::factory()->customer()->create();
        $token = $user->createToken('catalog', [ApiTokenAbility::ReadCatalog->value])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/sites/'.$site->id)
            ->assertOk()
            ->assertJsonPath('data.domain', 'detail.test');
    }
}
