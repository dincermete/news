<?php

namespace App\Filament\Resources\InstagramStoryPrices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstagramStoryPricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('instagramAccount.handle')->label('Instagram hesabı')->searchable()->sortable(),
                TextColumn::make('format')->label('Format')->badge()->sortable(),
                TextColumn::make('price')->label('Fiyat')->money('TRY')->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean()->sortable(),
            ])
            ->defaultSort('instagramAccount.handle')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
