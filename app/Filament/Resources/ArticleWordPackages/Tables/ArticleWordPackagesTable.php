<?php

namespace App\Filament\Resources\ArticleWordPackages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArticleWordPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('word_count')->label('Kelime')->sortable(),
                TextColumn::make('price')->label('Fiyat')->money('TRY')->sortable(),
                TextColumn::make('sort_order')->label('Sıra')->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
