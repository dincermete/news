<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Filament\Resources\SiteQuestions\Pages\ListSiteQuestions;
use App\Models\Favorite;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Models\SiteQuestion;
use App\Models\SiteView;
use App\Models\User;
use App\Services\ProductCrossSellService;
use Carbon\CarbonImmutable;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class SiteShowControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_page_renders_active_site(): void
    {
        $site = Site::factory()->create([
            'domain' => 'detail-show.test',
            'status' => SiteStatus::Active,
            'description' => 'Detay açıklaması',
        ]);

        $response = $this->get(route('sites.show', $site->domain));

        $response->assertOk();
        $response->assertSee('detail-show.test');
        $response->assertSee('Detay açıklaması');
        $response->assertSee('Sepete Ekle');
    }

    public function test_site_data_section_only_shows_requested_metrics(): void
    {
        CarbonImmutable::setTestNow('2026-08-05');

        $site = Site::factory()->create([
            'domain' => 'metrics-show.test',
            'status' => SiteStatus::Active,
            'is_news_approved' => true,
            'monthly_traffic_value' => 5_000_000,
            'is_dofollow' => false,
            'da_value' => 88,
            'pa_value' => 62,
            'max_link_count' => 2,
            'opened_at' => '1996-11-01',
        ]);

        $response = $this->get(route('sites.show', $site->domain));

        $response->assertOk();
        $response->assertSeeTextInOrder([
            '88',
            'DA Değeri',
            '5.000.000',
            'Günlük Hit',
            '29 Yıl 9 Ay',
            'Site Yaşı',
            'Site Verileri',
            'Google News',
            'Kayıtlı',
            'Google Index',
            'Link Türü',
            'Nofollow',
            'PA Değeri',
            '62',
            'Link Çıkışı',
            '2',
        ]);
        $response->assertDontSee('Ahrefs DR');
        $response->assertDontSee('Semrush AS');
        $response->assertDontSee('Aylık Trafik');

        CarbonImmutable::setTestNow();
    }

    public function test_product_info_card_renders_above_site_data(): void
    {
        $category = SiteCategory::factory()->create(['name' => 'Haber']);
        $site = Site::factory()->create([
            'domain' => 'product-info.test',
            'status' => SiteStatus::Active,
            'site_category_id' => $category->id,
        ]);

        $site->articleListing?->update([
            'estimated_delivery' => '3 Gün',
            'reference_content_url' => 'https://example.com/ornek-icerik',
        ]);

        $response = $this->get(route('sites.show', $site->domain));

        $response->assertOk();
        $response->assertSeeTextInOrder([
            'Site Verileri',
            'Kategori',
            'Haber',
            'Devamı için tıklayın',
            'Google Analytics',
            'Görsel Yok',
            'Tahmini Teslimat',
            '3 Gün',
            'Toplam Satış',
            'Satış Yok',
            'Yorumlar',
            'Yorum Yok',
            'Referans İçerik',
            'Tıklayın',
        ]);
        $response->assertDontSee('Ürün Bilgileri');
        $response->assertDontSee('0 Görsel');
        $response->assertDontSee('0 Adet');
        $response->assertDontSee('0 Yorum');
        $response->assertSee('https://example.com/ornek-icerik', false);
    }

    public function test_uploaded_images_are_exposed_to_the_lightbox(): void
    {
        $site = Site::factory()->create([
            'domain' => 'lightbox.test',
            'status' => SiteStatus::Active,
            'analytics_image_paths' => ['site-analytics/ga-1.png', 'site-analytics/ga-2.png'],
        ]);

        $site->articleListing?->update([
            'reference_content_image_paths' => ['reference-content/ref-1.png'],
        ]);

        $response = $this->get(route('sites.show', $site->domain));

        $response->assertOk();
        $response->assertSee('2 Görsel');
        $response->assertSee('1 Görsel');
        // Lightbox URLs are embedded as JSON in the Alpine component, so slashes are escaped.
        $response->assertSee('site-analytics', false);
        $response->assertSee('ga-1.png', false);
        $response->assertSee('ga-2.png', false);
        $response->assertSee('reference-content', false);
        $response->assertSee('ref-1.png', false);
    }

    public function test_show_records_a_site_view(): void
    {
        $site = Site::factory()->create([
            'domain' => 'view-count.test',
            'status' => SiteStatus::Active,
        ]);

        $this->assertSame(0, SiteView::query()->where('site_id', $site->id)->count());

        $this->get(route('sites.show', $site->domain))->assertOk();

        $this->assertSame(1, SiteView::query()->where('site_id', $site->id)->count());
    }

    public function test_guest_favorite_redirects_to_account(): void
    {
        $site = Site::factory()->create(['status' => SiteStatus::Active]);

        $response = $this->post(route('sites.favorite', $site));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount(Favorite::class, 0);
    }

    public function test_user_can_toggle_favorite(): void
    {
        $user = User::factory()->customer()->create();
        $site = Site::factory()->create(['status' => SiteStatus::Active]);

        $this->actingAs($user)
            ->from(route('sites.show', $site->domain))
            ->post(route('sites.favorite', $site))
            ->assertRedirect(route('sites.show', $site->domain));

        $this->assertDatabaseHas(Favorite::class, [
            'user_id' => $user->id,
            'site_id' => $site->id,
        ]);

        $this->actingAs($user)
            ->from(route('sites.show', $site->domain))
            ->post(route('sites.favorite', $site))
            ->assertRedirect(route('sites.show', $site->domain));

        $this->assertDatabaseMissing(Favorite::class, [
            'user_id' => $user->id,
            'site_id' => $site->id,
        ]);
    }

    public function test_guest_can_submit_a_question_with_email(): void
    {
        $site = Site::factory()->create([
            'domain' => 'ask-me.test',
            'status' => SiteStatus::Active,
        ]);

        $this->from(route('sites.show', $site->domain))
            ->post(route('sites.question', $site), [
                'guest_email' => 'misafir@example.com',
                'question' => 'Bu sitede dofollow link veriliyor mu?',
            ])
            ->assertRedirect(route('sites.show', $site->domain));

        $this->assertDatabaseHas(SiteQuestion::class, [
            'site_id' => $site->id,
            'guest_email' => 'misafir@example.com',
            'user_id' => null,
        ]);
    }

    public function test_public_answered_questions_appear_on_show_page(): void
    {
        $site = Site::factory()->create([
            'domain' => 'qa-site.test',
            'status' => SiteStatus::Active,
        ]);
        $admin = User::factory()->admin()->create();

        SiteQuestion::factory()->answered($admin)->create([
            'site_id' => $site->id,
            'question' => 'Yayında görünen soru?',
            'answer' => 'Evet, yanıt burada.',
            'is_public' => true,
        ]);
        SiteQuestion::factory()->answered($admin)->hidden()->create([
            'site_id' => $site->id,
            'question' => 'Gizli soru?',
            'answer' => 'Görünmemeli.',
        ]);
        SiteQuestion::factory()->create([
            'site_id' => $site->id,
            'question' => 'Yanıtsız soru görünmemeli?',
        ]);

        $response = $this->get(route('sites.show', $site->domain));

        $response->assertOk();
        $response->assertSee('Yayında görünen soru?');
        $response->assertSee('Evet, yanıt burada.');
        $response->assertDontSee('Gizli soru?');
        $response->assertDontSee('Yanıtsız soru görünmemeli?');
    }

    public function test_admin_can_answer_site_question_from_filament(): void
    {
        $admin = User::factory()->admin()->create();
        $question = SiteQuestion::factory()->create([
            'question' => 'Filament yanıt testi?',
            'answer' => null,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListSiteQuestions::class)
            ->callAction(TestAction::make('answer')->table($question), [
                'answer' => 'Evet, destek ekibi yanıtladı.',
            ])
            ->assertNotified();

        $question->refresh();

        $this->assertSame('Evet, destek ekibi yanıtladı.', $question->answer);
        $this->assertSame($admin->id, $question->answered_by);
        $this->assertNotNull($question->answered_at);
    }

    public function test_related_sites_come_from_same_category(): void
    {
        $category = SiteCategory::factory()->create();
        $other = SiteCategory::factory()->create();

        $site = Site::factory()->create([
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'da_value' => 10,
        ]);
        $related = Site::factory()->create([
            'domain' => 'related-high-da.test',
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'da_value' => 80,
        ]);
        Site::factory()->create([
            'domain' => 'other-category.test',
            'site_category_id' => $other->id,
            'status' => SiteStatus::Active,
            'da_value' => 99,
        ]);

        $results = app(ProductCrossSellService::class)->relatedSitesFor($site->articleListing, $site);

        $this->assertTrue($results->contains('id', $related->id));
        $this->assertFalse($results->contains('domain', 'other-category.test'));
        $this->assertFalse($results->contains('id', $site->id));
    }

    public function test_show_page_query_budget(): void
    {
        $category = SiteCategory::factory()->create();
        $site = Site::factory()->create([
            'domain' => 'perf-show.test',
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'da_value' => 40,
        ]);
        Site::factory()->count(4)->create([
            'site_category_id' => $category->id,
            'status' => SiteStatus::Active,
            'da_value' => 50,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get(route('sites.show', $site->domain))->assertOk();

        $this->assertLessThanOrEqual(
            35,
            count(DB::getQueryLog()),
            'Site detay sayfası sorgu bütçesini aştı.',
        );
    }
}
