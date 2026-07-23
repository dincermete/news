<?php

namespace App\Filament\Resources\DiscountTiers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DiscountTiersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('min_cart_amount')->label('Min tutar')->money('TRY')->sortable(),
                TextColumn::make('discount_percentage')->label('İndirim %')->sortable()->suffix('%'),
                TextColumn::make('sort_order')->label('Sıra')->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean()->sortable(),
            ])
            ->defaultSort('min_cart_amount')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
