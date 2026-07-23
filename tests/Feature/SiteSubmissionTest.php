<?php

namespace Tests\Feature;

use App\Enums\SiteSubmissionStatus;
use App\Filament\Resources\SiteSubmissions\Pages\ListSiteSubmissions;
use App\Filament\Resources\Sites\SiteResource;
use App\Models\SiteCategory;
use App\Models\SiteSubmission;
use App\Models\User;
use App\Models\UserNotification;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_site_submission(): void
    {
        $user = User::factory()->customer()->create();
        $category = SiteCategory::factory()->create();

        $this->actingAs($user)
            ->post(route('account.site-submissions.store'), [
                'url' => 'https://ornek-site.com',
                'price' => 150.5,
                'site_category_id' => $category->id,
                'age' => 5,
            ])
            ->assertRedirect(route('account.site-submissions'));

        $this->assertDatabaseHas(SiteSubmission::class, [
            'user_id' => $user->id,
            'url' => 'https://ornek-site.com',
            'price' => 150.5,
            'site_category_id' => $category->id,
            'age' => 5,
            'status' => SiteSubmissionStatus::Pending->value,
        ]);

        $this->actingAs($user)
            ->get(route('account.site-submissions'))
            ->assertOk()
            ->assertSee('https://ornek-site.com')
            ->assertSee('Beklemede');
    }

    public function test_admin_can_approve_submission_and_user_is_notified(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $submission = SiteSubmission::factory()->create([
            'user_id' => $customer->id,
            'status' => SiteSubmissionStatus::Pending,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListSiteSubmissions::class)
            ->callAction(TestAction::make('approve')->table($submission), [
                'admin_note' => 'Uygun görünüyor',
            ])
            ->assertNotified();

        $submission->refresh();

        $this->assertSame(SiteSubmissionStatus::Approved, $submission->status);
        $this->assertSame($admin->id, $submission->reviewed_by);
        $this->assertNotNull($submission->reviewed_at);
        $this->assertSame('Uygun görünüyor', $submission->admin_note);

        $this->assertDatabaseHas(UserNotification::class, [
            'user_id' => $customer->id,
            'title' => 'Site başvurunuz onaylandı',
        ]);
    }

    public function test_admin_reject_requires_admin_note(): void
    {
        $admin = User::factory()->admin()->create();
        $submission = SiteSubmission::factory()->create([
            'status' => SiteSubmissionStatus::Pending,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListSiteSubmissions::class)
            ->callAction(TestAction::make('reject')->table($submission), [
                'admin_note' => null,
            ])
            ->assertHasActionErrors(['admin_note']);

        $this->assertSame(SiteSubmissionStatus::Pending, $submission->fresh()->status);
    }

    public function test_admin_can_reject_submission_with_note_and_user_is_notified(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $submission = SiteSubmission::factory()->create([
            'user_id' => $customer->id,
            'status' => SiteSubmissionStatus::Pending,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListSiteSubmissions::class)
            ->callAction(TestAction::make('reject')->table($submission), [
                'admin_note' => 'Metrikler yetersiz',
            ])
            ->assertNotified();

        $submission->refresh();

        $this->assertSame(SiteSubmissionStatus::Rejected, $submission->status);
        $this->assertSame('Metrikler yetersiz', $submission->admin_note);

        $this->assertDatabaseHas(UserNotification::class, [
            'user_id' => $customer->id,
            'title' => 'Site başvurunuz reddedildi',
        ]);
    }

    public function test_convert_to_site_action_builds_prefill_query_string(): void
    {
        $admin = User::factory()->admin()->create();
        $category = SiteCategory::factory()->create();
        $submission = SiteSubmission::factory()->approved($admin)->create([
            'url' => 'https://www.donustur-ornek.com/path',
            'price' => 200,
            'site_category_id' => $category->id,
            'age' => 8,
        ]);

        $expectedQuery = http_build_query($submission->siteCreateQuery());
        $expectedUrl = SiteResource::getUrl('create').'?'.$expectedQuery;

        $this->assertSame('donustur-ornek.com', $submission->domainFromUrl());
        $this->assertStringContainsString('domain=donustur-ornek.com', $expectedUrl);
        $this->assertStringContainsString('price=200', $expectedUrl);
        $this->assertStringContainsString('site_category_id='.$category->id, $expectedUrl);
        $this->assertStringContainsString('age=8', $expectedUrl);

        $this->actingAs($admin);

        Livewire::test(ListSiteSubmissions::class)
            ->assertTableActionHasUrl('convertToSite', $expectedUrl, $submission);
    }
}
