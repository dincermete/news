<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sayfa')
                    ->schema([
                        TextInput::make('title')
                            ->label('Başlık')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state, string $operation): void {
                                if ($operation !== 'create' || ($get('is_system') ?? false)) {
                                    return;
                                }

                                $set('slug', Str::slug((string) $state));
                            }),
                        TextInput::make('slug')
                            ->label('Slug / URL yolu')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled(fn (Get $get): bool => (bool) ($get('is_system') ?? false))
                            ->dehydrated()
                            ->helperText('Sistem sayfalarında slug kilitlidir. Özel route’lu sayfalar route_key ile bağlanır.'),
                        TextInput::make('route_key')
                            ->label('Route anahtarı')
                            ->maxLength(120)
                            ->disabled(fn (Get $get): bool => (bool) ($get('is_system') ?? false))
                            ->dehydrated()
                            ->helperText('Örn: home, sites.index — sistem sayfalarında otomatik.'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),
                        Toggle::make('is_system')
                            ->label('Sistem sayfası')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Sistem sayfaları silinemez.'),
                        RichEditor::make('content')
                            ->label('İçerik')
                            ->columnSpanFull()
                            ->helperText('Şablonlu katalog sayfalarında opsiyoneldir; CMS ve hakkımızda/iletişim gövdesinde kullanılır.'),
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
                            ->maxLength(255)
                            ->helperText('Virgülle ayırın'),
                        Textarea::make('meta_description')
                            ->label('Meta açıklama')
                            ->rows(3)
                            ->maxLength(512)
                            ->columnSpanFull(),
                        FileUpload::make('og_image')
                            ->label('Sosyal paylaşım görseli (OG)')
                            ->image()
                            ->disk('public')
                            ->directory('pages/og')
                            ->imageEditor()
                            ->maxSize(4096)
                            ->helperText('Önerilen: 1200×630. Boşsa site varsayılan OG kullanılır.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
