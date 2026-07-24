<?php

namespace App\Filament\Resources\SeoPackageDurationOptions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SeoPackageDurationOptionForm
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
                    ->default(1)
                    ->helperText('Aylık fiyata ve ay sayısına uygulanır. Uzun dönemde indirim için 1\'den küçük bir değer girin.'),
                TextInput::make('bonus_label')
                    ->label('Bonus etiketi')
                    ->maxLength(255)
                    ->helperText('Örn. "1 AY HEDİYE". Boş bırakılabilir.'),
                TextInput::make('sort_order')
                    ->label('Sıra')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
