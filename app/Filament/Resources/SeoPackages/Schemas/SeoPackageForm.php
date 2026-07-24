<?php

namespace App\Filament\Resources\SeoPackages\Schemas;

use App\Enums\Currency;
use App\Enums\SiteStatus;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class SeoPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Ad')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),
                    TextInput::make('keyword_count')
                        ->label('Anahtar kelime sayısı')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->columnSpan(1),
                    Textarea::make('description')
                        ->label('Açıklama')
                        ->rows(2)
                        ->columnSpanFull(),
                    TextInput::make('monthly_price')
                        ->label('Aylık fiyat')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->step(0.01)
                        ->columnSpan(1),
                    Select::make('currency')
                        ->label('Para birimi')
                        ->options(Currency::class)
                        ->required()
                        ->default(Currency::Try)
                        ->columnSpan(1),
                    Select::make('status')
                        ->label('Durum')
                        ->options(SiteStatus::class)
                        ->required()
                        ->default(SiteStatus::Draft)
                        ->columnSpan(1),
                    TextInput::make('sort_order')
                        ->label('Sıra')
                        ->numeric()
                        ->default(0)
                        ->columnSpan(1),
                    Toggle::make('is_featured')
                        ->label('Öne çıkan (en çok tercih edilen)')
                        ->inline(false),
                ]),
                Repeater::make('features')
                    ->label('Özellik listesi')
                    ->simple(
                        TextInput::make('feature')->label('Özellik')->required(),
                    )
                    ->addActionLabel('Özellik ekle')
                    ->columnSpanFull(),
            ]);
    }
}
