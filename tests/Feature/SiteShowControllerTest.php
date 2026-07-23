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
use App\Services\RelatedSitesService;
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

        $results = app(RelatedSitesService::class)->forSite($site);

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
            15,
            count(DB::getQueryLog()),
            'Site detay sayfası sorgu bütçesini aştı.',
        );
    }
}
