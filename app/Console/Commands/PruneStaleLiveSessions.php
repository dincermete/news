<?php

namespace App\Console\Commands;

use App\Models\LiveSession;
use Illuminate\Console\Command;

class PruneStaleLiveSessions extends Command
{
    protected $signature = 'live-sessions:prune';

    protected $description = 'Delete live sessions that have not been seen for more than 2 minutes';

    public function handle(): int
    {
        $deleted = LiveSession::query()
            ->where('last_seen_at', '<', now()->subMinutes(2))
            ->delete();

        $this->info("Pruned {$deleted} stale live session(s).");

        return self::SUCCESS;
    }
}
