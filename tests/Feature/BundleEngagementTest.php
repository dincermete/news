<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Models\SiteBundle;
use App\Models\SiteQuestion;
use App\Models\SiteReview;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BundleEngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_bundle_show_page_renders_engagement_tabs(): void
    {
        SiteSetting::current()->update([
            'default_delivery_details' => '<p>Paket teslimatı 24 saat içinde yapılır.</p>',
        ]);

        $bundle = SiteBundle::factory()->create([
            'name' => 'Engagement Paket',
            'slug' => 'engagement-paket',
            'status' => SiteStatus::Active,
        ]);

        $this->get(route('bundles.show', $bundle))
            ->assertOk()
            ->assertSee('Açıklamalar', false)
            ->assertSee('Teslimat Detayları', false)
            ->assertSee('Kullanıcı Yorumları', false)
            ->assertSee('Kullanıcı Soruları &amp; Yanıtları', false)
            ->assertSee('Paket teslimatı 24 saat içinde yapılır.', false);
    }

    public function test_guest_can_submit_bundle_review(): void
    {
        $bundle = SiteBundle::factory()->create([
            'status' => SiteStatus::Active,
        ]);

        $this->post(route('bundles.review', $bundle), [
            'name' => 'Ayşe Yılmaz',
            'email' => 'ayse@example.com',
            'phone' => '05551234567',
            'message' => 'Paket çok hızlı yayınlandı, teşekkürler.',
        ])->assertRedirect();

        $this->assertDatabaseHas(SiteReview::class, [
            'site_id' => null,
            'site_bundle_id' => $bundle->id,
            'name' => 'Ayşe Yılmaz',
            'email' => 'ayse@example.com',
            'is_approved' => false,
        ]);
    }

    public function test_unapproved_bundle_reviews_are_hidden_on_show_page(): void
    {
        $bundle = SiteBundle::factory()->create([
            'name' => 'Gizli Yorum Paketi',
            'slug' => 'gizli-yorum-paketi',
            'status' => SiteStatus::Active,
        ]);

        SiteReview::factory()->forBundle($bundle)->create([
            'name' => 'Gizli Yorumcu',
            'message' => 'Bu yorum henüz onaylanmadı ve görünmemeli.',
            'is_approved' => false,
        ]);

        SiteReview::factory()->forBundle($bundle)->approved()->create([
            'name' => 'Onaylı Yorumcu',
            'message' => 'Bu onaylı yorum pakette görünmeli.',
        ]);

        $this->get(route('bundles.show', $bundle))
            ->assertOk()
            ->assertSee('Onaylı Yorumcu', false)
            ->assertSee('Bu onaylı yorum pakette görünmeli.', false)
            ->assertDontSee('Gizli Yorumcu', false);
    }

    public function test_guest_can_submit_bundle_question(): void
    {
        $bundle = SiteBundle::factory()->create([
            'status' => SiteStatus::Active,
        ]);

        $this->post(route('bundles.question', $bundle), [
            'guest_email' => 'soru@example.com',
            'question' => 'Paket kaç site içeriyor tam olarak?',
        ])->assertRedirect();

        $this->assertDatabaseHas(SiteQuestion::class, [
            'site_id' => null,
            'site_bundle_id' => $bundle->id,
            'guest_email' => 'soru@example.com',
            'question' => 'Paket kaç site içeriyor tam olarak?',
        ]);
    }

    public function test_public_answered_bundle_questions_appear_on_show_page(): void
    {
        $bundle = SiteBundle::factory()->create([
            'name' => 'Soru Paketi',
            'slug' => 'soru-paketi',
            'status' => SiteStatus::Active,
        ]);

        SiteQuestion::factory()->forBundle($bundle)->guest('gizli@example.com')->create([
            'question' => 'Yanıtlanmamış soru görünmemeli?',
        ]);

        SiteQuestion::factory()->forBundle($bundle)->guest('acik@example.com')->answered()->create([
            'question' => 'Paket teslimatı ne kadar sürer?',
            'answer' => 'Sipariş sonrası 24 saat içinde yayınlanır.',
        ]);

        $this->get(route('bundles.show', $bundle))
            ->assertOk()
            ->assertSee('Paket teslimatı ne kadar sürer?', false)
            ->assertSee('Sipariş sonrası 24 saat içinde yayınlanır.', false)
            ->assertDontSee('Yanıtlanmamış soru görünmemeli?', false);
    }
}
