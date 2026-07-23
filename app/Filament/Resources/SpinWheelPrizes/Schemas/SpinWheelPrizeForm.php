<?php

namespace App\Filament\Resources\SpinWheelPrizes\Schemas;

use App\Enums\SpinPrizeType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SpinWheelPrizeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Ad')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Tip')
                    ->options(SpinPrizeType::class)
                    ->required()
                    ->live(),
                TextInput::make('value')
                    ->label('Bakiye tutarı (₺)')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->visible(fn (Get $get): bool => $get('type') === SpinPrizeType::Balance->value
                        || $get('type') === SpinPrizeType::Balance),
                TextInput::make('probability_weight')
                    ->label('Olasılık ağırlığı')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->default(1)
                    ->integer(),
                TextInput::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->minValue(0)
                    ->integer()
                    ->helperText('Boş bırakılırsa sınırsız.'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
