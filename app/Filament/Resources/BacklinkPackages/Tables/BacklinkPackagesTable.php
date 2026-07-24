<?php

namespace App\Filament\Resources\BacklinkPackages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BacklinkPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Ad')->searchable()->sortable(),
                TextColumn::make('competition_label')->label('Rekabet')->placeholder('—'),
                TextColumn::make('price')->label('Fiyat')->money(fn ($record) => $record->currency?->value ?? 'TRY')->sortable(),
                TextColumn::make('status')->label('Durum')->badge()->sortable(),
                IconColumn::make('is_featured')->label('Öne çıkan')->boolean()->sortable(),
                TextColumn::make('sort_order')->label('Sıra')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
