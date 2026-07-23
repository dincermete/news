<?php

namespace App\Filament\Resources\Carts\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Enums\CartStatus;

class CartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('user.name')->label('Kullanıcı')->placeholder('Misafir')->searchable()->sortable(),
                TextColumn::make('session_token')->label('Oturum')->limit(12)->toggleable(),
                TextColumn::make('status')->label('Durum')->badge()->sortable(),
                TextColumn::make('items_count')->counts('items')->label('Kalem')->sortable(),
                TextColumn::make('updated_at')->label('Güncellenme')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('status')->options(CartStatus::class),
            ])
            ->recordActions([ViewAction::make()])
            ->toolbarActions([]);
    }
}
