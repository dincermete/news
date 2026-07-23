<?php

namespace App\Filament\Resources\FooterLinkDurationOptions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FooterLinkDurationOptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Ad')
                    ->required()
                    ->maxLength(255),
                TextInput::make('months')
                    ->label('Ay')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->integer(),
                TextInput::make('price_multiplier')
                    ->label('Fiyat çarpanı')
                    ->numeric()
                    ->step(0.0001)
                    ->helperText('Site taban fiyatına uygulanır. flat_price doluysa çarpan kullanılmaz.'),
                TextInput::make('flat_price')
                    ->label('Sabit fiyat')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('₺'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
