<?php

namespace App\Filament\Resources\SpinWheelSpins\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SpinWheelSpinsTable
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
                TextColumn::make('prize.name')
                    ->label('Ödül')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('prize.type')
                    ->label('Tip')
                    ->badge(),
                TextColumn::make('prize.value')
                    ->label('Değer')
                    ->money('TRY')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Kullanıcı')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('spin_wheel_prize_id')
                    ->label('Ödül')
                    ->relationship('prize', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
