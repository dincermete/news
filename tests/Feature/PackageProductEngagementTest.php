<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Models\BacklinkPackage;
use App\Models\SeoPackage;
use App\Models\SeoPackageDurationOption;
use App\Models\SiteQuestion;
use App\Models\SiteReview;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageProductEngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_seo_package_show_renders_product_shell_and_tabs(): void
    {
        SiteSetting::current()->update([
            'default_delivery_details' => '<p>SEO teslimatı planlanır.</p>',
        ]);

        $package = SeoPackage::factory()->create([
            'name' => 'SEO Pro',
            'slug' => 'seo-pro',
            'status' => SiteStatus::Active,
            'description' => 'Kapsamlı SEO paketi açıklaması',
            'keyword_count' => 25,
            'monthly_price' => 4500,
        ]);

        SeoPackageDurationOption::factory()->create([
            'months' => 3,
            'is_active' => true,
        ]);

        $this->get(route('seo-packages.show', $package))
            ->assertOk()
            ->assertSee('SEO Pro', false)
            ->assertSee('Sepete Ekle', false)
            ->assertSee('Hemen Satın Al', false)
            ->assertSee('Açıklamalar', false)
            ->assertSee('Teslimat Detayları', false)
            ->assertSee('Kullanıcı Yorumları', false)
            ->assertSee('Kullanıcı Soruları &amp; Yanıtları', false)
            ->assertSee('SEO teslimatı planlanır.', false);
    }

    public function test_guest_can_submit_seo_package_review_and_question(): void
    {
        $package = SeoPackage::factory()->create([
            'status' => SiteStatus::Active,
        ]);

        $this->post(route('seo-packages.review', $package), [
            'name' => 'Ayşe Yılmaz',
            'email' => 'ayse@example.com',
            'phone' => '05551234567',
            'message' => 'SEO paketi çok verimli oldu, teşekkürler.',
        ])->assertRedirect();

        $this->assertDatabaseHas(SiteReview::class, [
            'seo_package_id' => $package->id,
            'site_id' => null,
            'name' => 'Ayşe Yılmaz',
            'is_approved' => false,
        ]);

        $this->post(route('seo-packages.question', $package), [
            'guest_email' => 'soru@example.com',
            'question' => 'Kaç anahtar kelime takip ediliyor?',
        ])->assertRedirect();

        $this->assertDatabaseHas(SiteQuestion::class, [
            'seo_package_id' => $package->id,
            'site_id' => null,
            'guest_email' => 'soru@example.com',
        ]);
    }

    public function test_approved_seo_reviews_and_answered_questions_appear_on_show(): void
    {
        $package = SeoPackage::factory()->create([
            'name' => 'SEO Yayın',
            'slug' => 'seo-yayin',
            'status' => SiteStatus::Active,
        ]);

        SiteReview::factory()->forSeoPackage($package)->create([
            'name' => 'Gizli',
            'message' => 'Gizli yorum görünmemeli.',
            'is_approved' => false,
        ]);

        SiteReview::factory()->forSeoPackage($package)->approved()->create([
            'name' => 'Onaylı SEO',
            'message' => 'Onaylı SEO yorumu görünmeli.',
        ]);

        SiteQuestion::factory()->forSeoPackage($package)->guest()->answered()->create([
            'question' => 'Raporlama sıklığı nedir?',
            'answer' => 'Aylık rapor sunuyoruz.',
        ]);

        $this->get(route('seo-packages.show', $package))
            ->assertOk()
            ->assertSee('Onaylı SEO', false)
            ->assertSee('Onaylı SEO yorumu görünmeli.', false)
            ->assertDontSee('Gizli yorum görünmemeli.', false)
            ->assertSee('Raporlama sıklığı nedir?', false)
            ->assertSee('Aylık rapor sunuyoruz.', false);
    }

    public function test_backlink_package_show_renders_product_shell_and_tabs(): void
    {
        SiteSetting::current()->update([
            'default_delivery_details' => '<p>Backlink teslimatı 7 gündür.</p>',
        ]);

        $package = BacklinkPackage::factory()->create([
            'name' => 'Backlink Max',
            'slug' => 'backlink-max',
            'status' => SiteStatus::Active,
            'description' => 'Güçlü backlink paketi',
            'competition_label' => 'Yüksek rekabet',
            'price' => 8900,
        ]);

        $this->get(route('backlink-packages.show', $package))
            ->assertOk()
            ->assertSee('Backlink Max', false)
            ->assertSee('Sepete Ekle', false)
            ->assertSee('Hemen Satın Al', false)
            ->assertSee('Açıklamalar', false)
            ->assertSee('Teslimat Detayları', false)
            ->assertSee('Kullanıcı Yorumları', false)
            ->assertSee('Kullanıcı Soruları &amp; Yanıtları', false)
            ->assertSee('Backlink teslimatı 7 gündür.', false);
    }

    public function test_guest_can_submit_backlink_package_review_and_question(): void
    {
        $package = BacklinkPackage::factory()->create([
            'status' => SiteStatus::Active,
        ]);

        $this->post(route('backlink-packages.review', $package), [
            'name' => 'Mehmet Demir',
            'email' => 'mehmet@example.com',
            'phone' => '05559876543',
            'message' => 'Backlink kalitesi çok iyiydi.',
        ])->assertRedirect();

        $this->assertDatabaseHas(SiteReview::class, [
            'backlink_package_id' => $package->id,
            'site_id' => null,
            'name' => 'Mehmet Demir',
            'is_approved' => false,
        ]);

        $this->post(route('backlink-packages.question', $package), [
            'guest_email' => 'bl@example.com',
            'question' => 'Linkler dofollow mu geliyor?',
        ])->assertRedirect();

        $this->assertDatabaseHas(SiteQuestion::class, [
            'backlink_package_id' => $package->id,
            'site_id' => null,
            'guest_email' => 'bl@example.com',
        ]);
    }
}
