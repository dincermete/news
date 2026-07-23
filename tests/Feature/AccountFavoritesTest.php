<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Models\Favorite;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountFavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function test_favorites_page_shows_empty_state(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user)
            ->get(route('account.favorites'))
            ->assertOk()
            ->assertSee('Henüz favori ürününüz yok')
            ->assertSee('Siteleri İncele');
    }

    public function test_favorites_page_lists_user_favorites(): void
    {
        $user = User::factory()->customer()->create();
        $site = Site::factory()->create([
            'status' => SiteStatus::Active,
            'domain' => 'favori-ornek.com',
        ]);

        Favorite::factory()->create([
            'user_id' => $user->id,
            'site_id' => $site->id,
        ]);

        $this->actingAs($user)
            ->get(route('account.favorites'))
            ->assertOk()
            ->assertSee('favori-ornek.com');
    }

    public function test_user_can_remove_favorite_from_account(): void
    {
        $user = User::factory()->customer()->create();
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'site_id' => Site::factory()->create(['status' => SiteStatus::Active]),
        ]);

        $this->actingAs($user)
            ->delete(route('account.favorites.destroy', $favorite))
            ->assertRedirect(route('account.favorites'));

        $this->assertDatabaseMissing(Favorite::class, ['id' => $favorite->id]);
    }
}
