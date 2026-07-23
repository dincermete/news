<?php

namespace App\Console\Commands;

use App\Services\PublicStatsService;
use Illuminate\Console\Command;

class RefreshPublicStats extends Command
{
    protected $signature = 'stats:refresh-public';

    protected $description = 'Refresh cached public marketplace statistics';

    public function handle(PublicStatsService $stats): int
    {
        $values = $stats->refresh();

        $this->info(sprintf(
            'Public stats refreshed — sites: %d, published orders: %d, customers: %d',
            $values['active_sites'],
            $values['published_orders'],
            $values['customers'],
        ));

        return self::SUCCESS;
    }
}
