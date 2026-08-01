<?php

namespace Tests\Feature;

use App\Enums\NotificationAudience;
use App\Filament\Resources\Announcements\Pages\ListAnnouncements;
use App\Models\Announcement;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AnnouncementResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_announcements(): void
    {
        $admin = User::factory()->admin()->create();
        $announcements = Announcement::factory()->count(2)->create();

        $this->actingAs($admin);

        Livewire::test(ListAnnouncements::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($announcements);
    }

    public function test_admin_can_create_announcement_from_modal(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(ListAnnouncements::class)
            ->callAction('create', [
                'title' => 'Bakım duyurusu',
                'body' => 'Sistem bakımda olacak.',
                'audience' => NotificationAudience::LoggedInOnly->value,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addWeek(),
            ])
            ->assertHasNoActionErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Announcement::class, [
            'title' => 'Bakım duyurusu',
            'audience' => NotificationAudience::LoggedInOnly->value,
            'is_active' => 1,
        ]);
    }

    public function test_admin_can_edit_announcement_from_modal(): void
    {
        $admin = User::factory()->admin()->create();
        $announcement = Announcement::factory()->create([
            'title' => 'Eski başlık',
            'audience' => NotificationAudience::All,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListAnnouncements::class)
            ->callAction(TestAction::make('edit')->table($announcement), [
                'title' => 'Yeni başlık',
                'body' => $announcement->body,
                'audience' => NotificationAudience::All->value,
                'is_active' => true,
            ])
            ->assertHasNoActionErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Announcement::class, [
            'id' => $announcement->id,
            'title' => 'Yeni başlık',
        ]);
    }

    public function test_currently_visible_includes_active_window_and_null_starts(): void
    {
        $visibleNow = Announcement::factory()->create([
            'title' => 'Hemen yayın',
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => now()->addDay(),
            'audience' => NotificationAudience::All,
        ]);

        $future = Announcement::factory()->create([
            'title' => 'Gelecek',
            'is_active' => true,
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addDay(),
            'audience' => NotificationAudience::All,
        ]);

        $expired = Announcement::factory()->create([
            'title' => 'Süresi dolmuş',
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->subMinute(),
            'audience' => NotificationAudience::All,
        ]);

        $ids = Announcement::query()->currentlyVisible()->pluck('id');

        $this->assertTrue($ids->contains($visibleNow->id));
        $this->assertFalse($ids->contains($future->id));
        $this->assertFalse($ids->contains($expired->id));
    }

    public function test_guest_home_shows_public_announcement_in_bell(): void
    {
        Announcement::factory()->create([
            'title' => 'Önyüz duyurusu',
            'body' => 'Zilde görünmeli',
            'audience' => NotificationAudience::All,
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Önyüz duyurusu')
            ->assertSee('notificationBell', false);
    }
}
