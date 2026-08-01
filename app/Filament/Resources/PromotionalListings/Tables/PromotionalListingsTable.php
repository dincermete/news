<?php

namespace App\Filament\Resources\PromotionalListings\Tables;

use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PromotionalListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.domain')
                    ->label('Site')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Fiyat')
                    ->money(fn ($record) => $record->currency?->value ?? 'TRY')
                    ->sortable(),
                TextColumn::make('discount_price')
                    ->label('İndirimli')
                    ->money(fn ($record) => $record->currency?->value ?? 'TRY')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('currency')
                    ->label('PB')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Güncellenme')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tip')
                    ->options(PromotionalListingType::class),
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(SiteStatus::class),
                SelectFilter::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'domain')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
