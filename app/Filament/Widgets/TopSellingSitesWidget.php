<?php

namespace App\Filament\Widgets;

use App\Services\SalesReportService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class TopSellingSitesWidget extends TableWidget
{
    protected static ?string $heading = 'En Çok Satan Siteler';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => collect(app(SalesReportService::class)->topSellingSites(10))
                ->keyBy('site_id'))
            ->columns([
                TextColumn::make('domain')
                    ->label('Domain'),
                TextColumn::make('orders_count')
                    ->label('Sipariş')
                    ->alignEnd(),
                TextColumn::make('revenue')
                    ->label('Gelir')
                    ->alignEnd()
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2, ',', '.').' ₺'),
            ])
            ->paginated(false);
    }
}
