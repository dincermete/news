<?php

namespace App\Filament\Resources\OrderGroups\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('user.name')->label('Müşteri')->searchable()->sortable(),
                TextColumn::make('subtotal')->label('Ara toplam')->money(fn ($record) => $record->currency?->value ?? 'TRY')->sortable(),
                TextColumn::make('discount_tier_amount')->label('Kademe indirimi')->money(fn ($record) => $record->currency?->value ?? 'TRY'),
                TextColumn::make('coupon_discount_amount')->label('Kupon')->money(fn ($record) => $record->currency?->value ?? 'TRY'),
                TextColumn::make('total')->label('Toplam')->money(fn ($record) => $record->currency?->value ?? 'TRY')->sortable(),
                TextColumn::make('orders_count')->counts('orders')->label('Sipariş'),
                TextColumn::make('created_at')->label('Tarih')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([ViewAction::make()])
            ->toolbarActions([]);
    }
}
