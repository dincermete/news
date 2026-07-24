<?php

namespace App\Filament\Resources\SeoPackageDurationOptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeoPackageDurationOptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Ad')->searchable()->sortable(),
                TextColumn::make('months')->label('Ay')->sortable(),
                TextColumn::make('price_multiplier')->label('Çarpan')->sortable(),
                TextColumn::make('bonus_label')->label('Bonus')->placeholder('—'),
                IconColumn::make('is_active')->label('Aktif')->boolean()->sortable(),
                TextColumn::make('sort_order')->label('Sıra')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
