<?php

namespace App\Filament\Resources\Sites\Tables;

use App\Enums\SiteStatus;
use App\Filament\Actions\BulkActionGroup;
use App\Models\Site;
use App\Services\SeoMetaService;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('public')
                    ->height(24)
                    ->width(107)
                    ->extraImgAttributes(['class' => 'object-contain'])
                    ->defaultImageUrl(fn (Site $record): string => app(SeoMetaService::class)->faviconUrl($record->domain)),
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
                TextColumn::make('ahrefs_dr_value')
                    ->label('Ahrefs DR')
                    ->sortable()
                    ->numeric(decimalPlaces: 0)
                    ->toggleable(),
                TextColumn::make('ahrefs_keywords_value')
                    ->label('Ahrefs Kelime')
                    ->sortable()
                    ->numeric(decimalPlaces: 0)
                    ->toggleable(),
                TextColumn::make('monthly_traffic_value')
                    ->label('Aylık Trafik')
                    ->sortable()
                    ->numeric(decimalPlaces: 0)
                    ->toggleable(),
                TextColumn::make('backlinks_value')
                    ->label('Backlinks')
                    ->sortable()
                    ->numeric(decimalPlaces: 0)
                    ->toggleable(),
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
                IconColumn::make('is_google_indexed')
                    ->label('Google Index')
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
                            $indicators[] = 'Min DA: '.$data['da_min'];
                        }

                        if (filled($data['da_max'] ?? null)) {
                            $indicators[] = 'Max DA: '.$data['da_max'];
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
