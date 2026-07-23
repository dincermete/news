<?php

namespace App\Filament\Widgets;

use App\Models\LiveSession;
use Filament\Widgets\Widget;

class LiveVisitorsWidget extends Widget
{
    protected string $view = 'filament.widgets.live-visitors';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public int $activeCount = 0;

    /**
     * @var list<array{id: int, current_url: string, user: ?string, last_seen_at: string}>
     */
    public array $sessions = [];

    public function mount(): void
    {
        $this->refreshSessions();
    }

    /**
     * @return array<string, string>
     */
    protected function getListeners(): array
    {
        return [
            'echo-private:admin.live-sessions,.LiveSessionUpdated' => 'refreshSessions',
        ];
    }

    public function refreshSessions(): void
    {
        $cutoff = now()->subMinutes(2);

        $sessions = LiveSession::query()
            ->with('user:id,name')
            ->where('last_seen_at', '>=', $cutoff)
            ->orderByDesc('last_seen_at')
            ->limit(25)
            ->get();

        $this->activeCount = $sessions->count();
        $this->sessions = $sessions
            ->map(fn (LiveSession $session): array => [
                'id' => $session->id,
                'current_url' => $session->current_url,
                'user' => $session->user?->name,
                'last_seen_at' => $session->last_seen_at?->diffForHumans() ?? '—',
            ])
            ->all();
    }
}
