<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Filament\Resources\SiteReviews\Pages\ListSiteReviews;
use App\Models\Site;
use App\Models\SiteReview;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_review_without_login(): void
    {
        $site = Site::factory()->create(['status' => SiteStatus::Active]);

        $this->post(route('sites.review', $site), [
            'name' => 'Ayşe Yılmaz',
            'email' => 'ayse@example.com',
            'phone' => '05551234567',
            'message' => 'Site çok hızlı yayınladı, teşekkürler.',
        ])->assertRedirect();

        $this->assertDatabaseHas(SiteReview::class, [
            'site_id' => $site->id,
            'name' => 'Ayşe Yılmaz',
            'email' => 'ayse@example.com',
            'phone' => '05551234567',
            'is_approved' => false,
        ]);
    }

    public function test_unapproved_reviews_are_hidden_on_show_page(): void
    {
        $site = Site::factory()->create([
            'domain' => 'review-hide.test',
            'status' => SiteStatus::Active,
        ]);

        SiteReview::factory()->create([
            'site_id' => $site->id,
            'name' => 'Gizli Yorumcu',
            'message' => 'Bu yorum henüz onaylanmadı ve görünmemeli.',
            'is_approved' => false,
        ]);

        $approved = SiteReview::factory()->approved()->create([
            'site_id' => $site->id,
            'name' => 'Onaylı Yorumcu',
            'message' => 'Bu onaylı yorum detayda görünmeli.',
        ]);

        $response = $this->get(route('sites.show', $site->domain));

        $response->assertOk();
        $response->assertSee('Onaylı Yorumcu', false);
        $response->assertSee('Bu onaylı yorum detayda görünmeli.', false);
        $response->assertDontSee('Gizli Yorumcu', false);
        $response->assertSee('Siteye Git', false);
        $response->assertSee('Hemen Satın Al', false);
        $response->assertSee('Sepete Ekle', false);
        $this->assertTrue($approved->is_approved);
    }

    public function test_admin_can_approve_review_from_filament(): void
    {
        $admin = User::factory()->admin()->create();
        $site = Site::factory()->create(['status' => SiteStatus::Active]);
        $review = SiteReview::factory()->create([
            'site_id' => $site->id,
            'is_approved' => false,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListSiteReviews::class)
            ->callAction(TestAction::make('approve')->table($review))
            ->assertNotified();

        $review->refresh();

        $this->assertTrue($review->is_approved);
        $this->assertSame($admin->id, $review->approved_by);
        $this->assertNotNull($review->approved_at);
    }

    public function test_show_page_renders_delivery_details_from_listing(): void
    {
        $site = Site::factory()->create([
            'domain' => 'delivery-rich.test',
            'status' => SiteStatus::Active,
        ]);

        $listing = $site->articleListing;
        $listing?->update([
            'delivery_details' => '<p>Teslimat 48 saat içinde yapılır.</p>',
            'cta_whatsapp_color' => '#25D366',
        ]);

        $response = $this->get(route('sites.show', $site->domain));

        $response->assertOk();
        $response->assertSee('Teslimat Detayları', false);
        $response->assertSee('Teslimat 48 saat içinde yapılır.', false);
        $response->assertSee('bg-accent-600', false);
        $response->assertSee('Hemen Satın Al', false);
    }

    public function test_show_page_uses_site_setting_default_delivery_when_listing_empty(): void
    {
        SiteSetting::current()->update([
            'default_delivery_details' => '<p>Varsayılan teslimat metni burada.</p>',
        ]);

        $site = Site::factory()->create([
            'domain' => 'delivery-default.test',
            'status' => SiteStatus::Active,
        ]);

        $response = $this->get(route('sites.show', $site->domain));

        $response->assertOk();
        $response->assertSee('Teslimat Detayları', false);
        $response->assertSee('Varsayılan teslimat metni burada.', false);
    }

    public function test_delivery_tab_is_hidden_when_no_delivery_content(): void
    {
        SiteSetting::current()->update([
            'default_delivery_details' => null,
        ]);

        $site = Site::factory()->create([
            'domain' => 'delivery-hidden.test',
            'status' => SiteStatus::Active,
        ]);

        $response = $this->get(route('sites.show', $site->domain));

        $response->assertOk();
        $response->assertDontSee('Teslimat Detayları', false);
        $response->assertDontSee('Panelden teslimat metni eklenmedi', false);
    }
}
