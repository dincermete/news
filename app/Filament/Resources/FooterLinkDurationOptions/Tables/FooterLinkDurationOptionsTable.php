<?php

namespace App\Filament\Resources\FooterLinkDurationOptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FooterLinkDurationOptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Ad')->searchable()->sortable(),
                TextColumn::make('months')->label('Ay')->sortable(),
                TextColumn::make('price_multiplier')->label('Çarpan')->placeholder('—')->sortable(),
                TextColumn::make('flat_price')->label('Sabit fiyat')->money('TRY')->placeholder('—')->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean()->sortable(),
            ])
            ->defaultSort('months')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
