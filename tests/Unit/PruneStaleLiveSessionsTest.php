<?php

namespace Tests\Unit;

use App\Console\Commands\PruneStaleLiveSessions;
use App\Models\LiveSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneStaleLiveSessionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_deletes_sessions_older_than_two_minutes(): void
    {
        $fresh = LiveSession::factory()->create([
            'last_seen_at' => now()->subMinute(),
        ]);
        $stale = LiveSession::factory()->stale()->create();

        $this->artisan(PruneStaleLiveSessions::class)
            ->assertSuccessful();

        $this->assertDatabaseHas(LiveSession::class, ['id' => $fresh->id]);
        $this->assertDatabaseMissing(LiveSession::class, ['id' => $stale->id]);
    }
}
