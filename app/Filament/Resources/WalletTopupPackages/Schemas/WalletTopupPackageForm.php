<?php

namespace App\Filament\Resources\WalletTopupPackages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WalletTopupPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount')
                    ->label('Tutar')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01)
                    ->prefix('₺'),
                TextInput::make('spin_credits')
                    ->label('Çark kredisi')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->integer(),
                TextInput::make('sort_order')
                    ->label('Sıra')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->integer(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
