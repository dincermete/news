<?php

namespace App\Filament\Resources\FaqEntries\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FaqEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('question_topic')
                    ->label('Konu / soru')
                    ->required()
                    ->maxLength(255),
                Textarea::make('answer')
                    ->label('Cevap')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                TextInput::make('category')
                    ->label('Kategori')
                    ->maxLength(100),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
