<?php

namespace App\Filament\Widgets;

use App\Services\SalesReportService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Gelir Grafiği';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $color = 'warning';

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = null;

    public ?string $filter = '30';

    protected ?string $emptyStateHeading = 'Gelir verisi yok';

    protected ?string $emptyStateDescription = 'Seçilen dönemde ödenmiş ödeme bulunmuyor.';

    protected string|\BackedEnum|null $emptyStateIcon = Heroicon::OutlinedChartBar;

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Son 7 gün',
            '30' => 'Son 30 gün',
            '90' => 'Son 90 gün',
        ];
    }

    public function getDescription(): ?string
    {
        return match ($this->resolvedFilterDays()) {
            7 => 'Son 7 gün, günlük ödenen tutarlar',
            90 => 'Son 90 gün, günlük ödenen tutarlar',
            default => 'Son 30 gün, günlük ödenen tutarlar',
        };
    }

    protected function getData(): array
    {
        $days = $this->resolvedFilterDays();

        $series = app(SalesReportService::class)->dailyRevenueSeries(
            now()->subDays($days - 1)->startOfDay(),
            now()->endOfDay(),
        );

        return [
            'datasets' => [
                [
                    'label' => 'Gelir (₺)',
                    'data' => $series['data'],
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function isEmpty(): bool
    {
        $data = $this->getCachedData();
        $points = $data['datasets'][0]['data'] ?? [];

        if ($points === []) {
            return true;
        }

        return collect($points)->sum() <= 0;
    }

    protected function resolvedFilterDays(): int
    {
        $allowed = array_keys($this->getFilters() ?? []);

        if (! in_array($this->filter, $allowed, true)) {
            return 30;
        }

        return (int) $this->filter;
    }
}
