<?php

namespace App\Events;

use App\Models\LiveSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveSessionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public LiveSession $liveSession) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.live-sessions'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'LiveSessionUpdated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->liveSession->id,
            'session_token' => $this->liveSession->session_token,
            'user_id' => $this->liveSession->user_id,
            'current_url' => $this->liveSession->current_url,
            'last_seen_at' => $this->liveSession->last_seen_at?->toIso8601String(),
            'active_count' => LiveSession::query()
                ->where('last_seen_at', '>=', now()->subMinutes(2))
                ->count(),
        ];
    }
}
