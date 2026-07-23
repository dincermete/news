<?php

namespace App\Filament\Resources\SpinCreditTransactions\Tables;

use App\Enums\SpinCreditTransactionType;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SpinCreditTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Kullanıcı')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Miktar')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Sebep')
                    ->searchable(),
                TextColumn::make('related_payment_id')
                    ->label('Ödeme')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tip')
                    ->options(SpinCreditTransactionType::class),
                SelectFilter::make('user_id')
                    ->label('Kullanıcı')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
