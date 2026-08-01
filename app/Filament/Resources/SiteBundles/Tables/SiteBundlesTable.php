<?php

namespace App\Filament\Resources\SiteBundles\Tables;

use App\Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteBundlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ad')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('icon')
                    ->label('İkon')
                    ->icon(fn ($state): ?string => filled($state) ? (string) $state : 'heroicon-o-cube')
                    ->color('gray'),
                ColorColumn::make('bg_color_from')
                    ->label('Renk'),
                TextColumn::make('price')
                    ->label('Fiyat')
                    ->money(fn ($record) => $record->currency?->value ?? 'TRY')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->sortable(),
                TextColumn::make('sites_count')
                    ->counts('sites')
                    ->label('Site sayısı')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Güncellenme')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
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
