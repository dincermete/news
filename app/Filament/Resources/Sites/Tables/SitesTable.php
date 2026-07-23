<?php

namespace App\Filament\Resources\Sites\Tables;

use App\Enums\SiteStatus;
use App\Models\Site;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('domain')
                    ->label('Domain')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('da_value')
                    ->label('DA')
                    ->sortable()
                    ->numeric(decimalPlaces: 0),
                TextColumn::make('pa_value')
                    ->label('PA')
                    ->sortable()
                    ->numeric(decimalPlaces: 0),
                TextColumn::make('spam_score_value')
                    ->label('Spam Score')
                    ->sortable()
                    ->numeric(decimalPlaces: 0)
                    ->toggleable(),
                TextColumn::make('semrush_authority_score_value')
                    ->label('Semrush AS')
                    ->sortable()
                    ->numeric(decimalPlaces: 0)
                    ->toggleable(),
                TextColumn::make('organic_traffic_value')
                    ->label('Organic Traffic')
                    ->sortable()
                    ->numeric(decimalPlaces: 0)
                    ->toggleable(),
                TextColumn::make('backlinks_value')
                    ->label('Backlinks')
                    ->sortable()
                    ->numeric(decimalPlaces: 0)
                    ->toggleable(),
                TextColumn::make('price')
                    ->label('Fiyat')
                    ->sortable()
                    ->money(fn (Site $record): string => $record->currency?->value ?? 'USD'),
                TextColumn::make('discount_price')
                    ->label('İndirimli')
                    ->sortable()
                    ->money(fn (Site $record): string => $record->currency?->value ?? 'USD')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_dofollow')
                    ->label('Dofollow')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_news_approved')
                    ->label('News')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Güncellenme')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('domain')
            ->filters([
                SelectFilter::make('site_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_dofollow')
                    ->label('Dofollow / Nofollow')
                    ->trueLabel('Dofollow')
                    ->falseLabel('Nofollow')
                    ->placeholder('Tümü'),
                TernaryFilter::make('is_news_approved')
                    ->label('News onaylı')
                    ->trueLabel('Onaylı')
                    ->falseLabel('Onaysız')
                    ->placeholder('Tümü'),
                Filter::make('price_range')
                    ->label('Fiyat aralığı')
                    ->schema([
                        TextInput::make('price_min')
                            ->label('Min fiyat')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('price_max')
                            ->label('Max fiyat')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['price_min'] ?? null),
                                fn (Builder $query): Builder => $query->where('price', '>=', $data['price_min']),
                            )
                            ->when(
                                filled($data['price_max'] ?? null),
                                fn (Builder $query): Builder => $query->where('price', '<=', $data['price_max']),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['price_min'] ?? null)) {
                            $indicators[] = 'Min fiyat: ' . $data['price_min'];
                        }

                        if (filled($data['price_max'] ?? null)) {
                            $indicators[] = 'Max fiyat: ' . $data['price_max'];
                        }

                        return $indicators;
                    }),
                Filter::make('da_range')
                    ->label('DA aralığı')
                    ->schema([
                        TextInput::make('da_min')
                            ->label('Min DA')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('da_max')
                            ->label('Max DA')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['da_min'] ?? null),
                                fn (Builder $query): Builder => $query->where('da_value', '>=', $data['da_min']),
                            )
                            ->when(
                                filled($data['da_max'] ?? null),
                                fn (Builder $query): Builder => $query->where('da_value', '<=', $data['da_max']),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['da_min'] ?? null)) {
                            $indicators[] = 'Min DA: ' . $data['da_min'];
                        }

                        if (filled($data['da_max'] ?? null)) {
                            $indicators[] = 'Max DA: ' . $data['da_max'];
                        }

                        return $indicators;
                    }),
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(SiteStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('updatePrices')
                        ->label('Toplu fiyat güncelle')
                        ->icon(Heroicon::CurrencyDollar)
                        ->schema([
                            Select::make('mode')
                                ->label('Güncelleme tipi')
                                ->options([
                                    'percentage_increase' => 'Yüzde artış',
                                    'percentage_decrease' => 'Yüzde azalış',
                                    'fixed' => 'Sabit yeni değer',
                                ])
                                ->required()
                                ->live(),
                            TextInput::make('value')
                                ->label(fn (Get $get): string => match ($get('mode')) {
                                    'fixed' => 'Yeni fiyat',
                                    default => 'Yüzde (%)',
                                })
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->step(0.01),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $mode = $data['mode'];
                            $value = (float) $data['value'];

                            DB::transaction(function () use ($records, $mode, $value): void {
                                foreach ($records as $record) {
                                    /** @var Site $record */
                                    $newPrice = match ($mode) {
                                        'percentage_increase' => (float) $record->price * (1 + ($value / 100)),
                                        'percentage_decrease' => (float) $record->price * (1 - ($value / 100)),
                                        'fixed' => $value,
                                        default => (float) $record->price,
                                    };

                                    $record->update([
                                        'price' => max(0, round($newPrice, 2)),
                                    ]);
                                }
                            });

                            Notification::make()
                                ->title('Fiyatlar güncellendi')
                                ->body($records->count() . ' site için fiyat güncellendi.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('activate')
                        ->label('Aktif et')
                        ->icon(Heroicon::CheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => SiteStatus::Active]);

                            Notification::make()
                                ->title('Siteler aktifleştirildi')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label('Pasife al')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => SiteStatus::Inactive]);

                            Notification::make()
                                ->title('Siteler pasife alındı')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
