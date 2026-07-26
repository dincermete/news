<?php

namespace App\Filament\Resources\LegalDocuments\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LegalDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sözleşme')
                    ->schema([
                        TextInput::make('title')
                            ->label('Başlık')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state, string $operation): void {
                                if ($operation !== 'create') {
                                    return;
                                }

                                if (filled($get('slug'))) {
                                    return;
                                }

                                $set('slug', Str::slug((string) $state));
                            }),
                        TextInput::make('slug')
                            ->label('URL yolu')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Örn: kvkk, gizlilik — /{slug} olarak yayınlanır.'),
                        Toggle::make('is_active')
                            ->label('Yayında')
                            ->default(true)
                            ->required(),
                        RichEditor::make('content')
                            ->label('Sözleşme metni')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('SEO')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta başlık')
                            ->maxLength(255),
                        TextInput::make('meta_keywords')
                            ->label('Meta keywords')
                            ->maxLength(255),
                        Textarea::make('meta_description')
                            ->label('Meta açıklama')
                            ->rows(3)
                            ->maxLength(512)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsed(),
            ]);
    }
}
