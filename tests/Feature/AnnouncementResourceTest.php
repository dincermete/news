<?php

namespace Tests\Feature;

use App\Enums\NotificationAudience;
use App\Filament\Resources\Announcements\Pages\CreateAnnouncement;
use App\Filament\Resources\Announcements\Pages\ListAnnouncements;
use App\Models\Announcement;
use App\Models\User;
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

    public function test_admin_can_create_announcement(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(CreateAnnouncement::class)
            ->fillForm([
                'title' => 'Bakım duyurusu',
                'body' => 'Sistem bakımda olacak.',
                'audience' => NotificationAudience::LoggedInOnly->value,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addWeek(),
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas(Announcement::class, [
            'title' => 'Bakım duyurusu',
            'audience' => NotificationAudience::LoggedInOnly->value,
            'is_active' => 1,
        ]);
    }
}
