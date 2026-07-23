<?php

namespace App\Filament\Resources\SpinWheelPrizes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SpinWheelPrizesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ad')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->sortable(),
                TextColumn::make('value')
                    ->label('Değer')
                    ->money('TRY')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('probability_weight')
                    ->label('Ağırlık')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Stok')
                    ->placeholder('∞')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
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
