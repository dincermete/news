<?php

namespace App\Filament\Resources\ArticleWordPackages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ArticleWordPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('word_count')
                    ->label('Kelime sayısı')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->integer()
                    ->unique(ignoreRecord: true),
                TextInput::make('price')
                    ->label('Fiyat')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01)
                    ->prefix('₺'),
                TextInput::make('sort_order')
                    ->label('Sıra')
                    ->numeric()
                    ->default(0)
                    ->integer(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
