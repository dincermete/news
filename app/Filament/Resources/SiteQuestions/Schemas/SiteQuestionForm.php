<?php

namespace App\Filament\Resources\SiteQuestions\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Soru')
                    ->schema([
                        Textarea::make('question')
                            ->label('Soru')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('answer')
                            ->label('Yanıt')
                            ->rows(5)
                            ->columnSpanFull(),
                        Toggle::make('is_public')
                            ->label('Herkese açık')
                            ->default(true),
                    ]),
            ]);
    }
}
