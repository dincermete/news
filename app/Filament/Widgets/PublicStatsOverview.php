<?php

namespace App\Filament\Widgets;

use App\Services\PublicStatsService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PublicStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected ?string $heading = 'Genel İstatistikler';

    protected ?string $description = 'Aktif siteler, yayınlanan siparişler ve müşteri sayısı (5 dk önbellek)';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $stats = app(PublicStatsService::class);

        return [
            Stat::make('Aktif Siteler', number_format($stats->activeSiteCount(), 0, ',', '.'))
                ->description('Yayında olan siteler')
                ->descriptionIcon(Heroicon::OutlinedGlobeAlt)
                ->color('success'),
            Stat::make('Yayınlanan Siparişler', number_format($stats->publishedOrderCount(), 0, ',', '.'))
                ->description('Yayınlandı veya rapor gönderildi')
                ->descriptionIcon(Heroicon::OutlinedDocumentCheck)
                ->color('primary'),
            Stat::make('Müşteriler', number_format($stats->customerCount(), 0, ',', '.'))
                ->description('Admin / editör hariç')
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->color('warning'),
        ];
    }
}
