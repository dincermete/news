<?php

namespace App\Filament\Resources\FakeOrderNotificationTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FakeOrderNotificationTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('message_template')
                    ->label('Şablon')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('display_interval_seconds')
                    ->label('Aralık (sn)')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Sıra')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
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
