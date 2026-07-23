<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kod')->searchable()->sortable(),
                TextColumn::make('type')->label('Tip')->badge()->sortable(),
                TextColumn::make('value')->label('Değer')->sortable(),
                TextColumn::make('used_count')->label('Kullanım')->sortable(),
                TextColumn::make('usage_limit')->label('Limit')->placeholder('∞')->sortable(),
                TextColumn::make('valid_until')->label('Bitiş')->dateTime()->placeholder('—')->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean()->sortable(),
            ])
            ->defaultSort('code')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
