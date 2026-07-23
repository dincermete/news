<?php

namespace Tests\Feature;

use App\Events\LiveSessionUpdated;
use App\Models\LiveSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LiveHeartbeatTest extends TestCase
{
    use RefreshDatabase;

    public function test_heartbeat_creates_live_session_and_broadcasts_event(): void
    {
        Event::fake([LiveSessionUpdated::class]);

        $response = $this->postJson(route('live.heartbeat'), [
            'session_token' => 'token-abc',
            'current_url' => 'https://example.test/sites/1',
        ]);

        $response->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas(LiveSession::class, [
            'session_token' => 'token-abc',
            'current_url' => 'https://example.test/sites/1',
            'user_id' => null,
        ]);

        Event::assertDispatched(LiveSessionUpdated::class, function (LiveSessionUpdated $event): bool {
            return $event->liveSession->session_token === 'token-abc'
                && $event->liveSession->current_url === 'https://example.test/sites/1';
        });
    }

    public function test_heartbeat_updates_existing_session_and_attaches_auth_user(): void
    {
        Event::fake([LiveSessionUpdated::class]);

        $user = User::factory()->create();
        $session = LiveSession::factory()->create([
            'session_token' => 'token-xyz',
            'current_url' => 'https://example.test/old',
            'user_id' => null,
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route('live.heartbeat'), [
            'session_token' => 'token-xyz',
            'current_url' => 'https://example.test/new',
        ]);

        $response->assertOk();

        $this->assertSame(1, LiveSession::query()->where('session_token', 'token-xyz')->count());
        $this->assertDatabaseHas(LiveSession::class, [
            'id' => $session->id,
            'session_token' => 'token-xyz',
            'current_url' => 'https://example.test/new',
            'user_id' => $user->id,
        ]);

        Event::assertDispatched(LiveSessionUpdated::class);
    }
}
