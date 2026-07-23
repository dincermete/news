<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_mark_notification_read(): void
    {
        $notification = UserNotification::factory()->create();

        $this->patchJson(route('notifications.read', $notification))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = User::factory()->customer()->create();
        $notification = UserNotification::factory()->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);

        $this->actingAs($user)
            ->patchJson(route('notifications.read', $notification))
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'unread_count' => 0,
            ]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification(): void
    {
        $user = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();
        $notification = UserNotification::factory()->create([
            'user_id' => $other->id,
        ]);

        $this->actingAs($user)
            ->patchJson(route('notifications.read', $notification))
            ->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_header_shows_unread_badge_for_authenticated_user(): void
    {
        $user = User::factory()->customer()->create();
        UserNotification::factory()->count(2)->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('aria-label="Bildirimler"', false)
            ->assertSee('notificationBell', false);
    }
}
