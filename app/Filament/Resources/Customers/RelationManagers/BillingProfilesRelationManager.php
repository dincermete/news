<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BillingProfilesRelationManager extends RelationManager
{
    protected static string $relationship = 'billingProfiles';

    protected static ?string $title = 'Fatura Profilleri';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('type')->label('Tip')->badge(),
                TextColumn::make('company_name')->label('Ünvan')->placeholder('—'),
                TextColumn::make('tax_id')->label('VKN/TCKN')->placeholder('—'),
                TextColumn::make('tax_office')->label('Vergi dairesi')->placeholder('—'),
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
