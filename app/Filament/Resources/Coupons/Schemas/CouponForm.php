<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\CouponType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kod')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->extraInputAttributes(['style' => 'text-transform: uppercase']),
                Select::make('type')
                    ->label('Tip')
                    ->options(CouponType::class)
                    ->required()
                    ->live(),
                TextInput::make('value')
                    ->label('Değer')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01),
                TextInput::make('min_cart_amount')
                    ->label('Min sepet tutarı')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->prefix('₺'),
                TextInput::make('usage_limit')
                    ->label('Kullanım limiti')
                    ->numeric()
                    ->minValue(1)
                    ->integer(),
                TextInput::make('used_count')
                    ->label('Kullanım sayısı')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(),
                DateTimePicker::make('valid_from')->label('Başlangıç')->seconds(false),
                DateTimePicker::make('valid_until')->label('Bitiş')->seconds(false),
                Toggle::make('is_active')->label('Aktif')->default(true),
            ]);
    }
}
