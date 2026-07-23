<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AffiliateCommissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'affiliateCommissions';

    protected static ?string $title = 'Affiliate Komisyonları';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('referredUser.name')->label('Referans kullanıcı')->placeholder('—'),
                TextColumn::make('order_id')->label('Sipariş')->sortable(),
                TextColumn::make('amount')
                    ->label('Tutar')
                    ->money('TRY')
                    ->sortable(),
                TextColumn::make('status')->label('Durum')->badge(),
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
