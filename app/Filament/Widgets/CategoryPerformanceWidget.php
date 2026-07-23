<?php

namespace App\Filament\Widgets;

use App\Services\SalesReportService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\ChartWidget;

class CategoryPerformanceWidget extends ChartWidget
{
    protected ?string $heading = 'Kategori Performansı';

    protected ?string $description = 'Kategori bazında sipariş adedi';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = null;

    protected ?string $emptyStateHeading = 'Kategori verisi yok';

    protected ?string $emptyStateDescription = 'Henüz kategorize edilmiş sipariş bulunmuyor.';

    protected string|\BackedEnum|null $emptyStateIcon = Heroicon::OutlinedChartPie;

    protected function getData(): array
    {
        $rows = app(SalesReportService::class)->categoryPerformance();

        $colors = [
            '#f59e0b', '#3b82f6', '#10b981', '#8b5cf6', '#ef4444',
            '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#64748b',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Sipariş sayısı',
                    'data' => array_column($rows, 'orders_count'),
                    'backgroundColor' => array_slice($colors, 0, max(count($rows), 1)),
                ],
            ],
            'labels' => array_column($rows, 'category'),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function isEmpty(): bool
    {
        $data = $this->getCachedData();

        return empty($data['datasets'][0]['data'] ?? []);
    }
}
