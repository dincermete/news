<?php

namespace App\Filament\Resources\BankAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BankAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('short_code')->label('Kod')->badge()->placeholder('—'),
                TextColumn::make('name')->label('Banka adı')->searchable()->sortable(),
                TextColumn::make('account_name')->label('Hesap adı')->searchable(),
                TextColumn::make('iban')->label('IBAN')->copyable()->fontFamily('mono'),
                IconColumn::make('is_active')->label('Aktif')->boolean()->sortable(),
                TextColumn::make('sort_order')->label('Sıra')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
