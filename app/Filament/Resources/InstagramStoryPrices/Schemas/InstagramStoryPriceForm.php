<?php

namespace App\Filament\Resources\InstagramStoryPrices\Schemas;

use App\Enums\Currency;
use App\Enums\StoryFormat;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class InstagramStoryPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('instagram_account_id')
                    ->label('Instagram hesabı')
                    ->relationship('instagramAccount', 'handle')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('format')
                    ->label('Format')
                    ->options(StoryFormat::class)
                    ->required(),
                TextInput::make('price')
                    ->label('Fiyat')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01),
                Select::make('currency')
                    ->label('Para birimi')
                    ->options(Currency::class)
                    ->required()
                    ->default(Currency::Try),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
