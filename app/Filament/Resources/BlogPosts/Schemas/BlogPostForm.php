<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use App\Enums\SiteStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Yazı')
                    ->schema([
                        TextInput::make('title')
                            ->label('Başlık')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                                if ($operation !== 'create') {
                                    return;
                                }

                                $set('slug', Str::slug((string) $state));
                            })
                            ->columnSpan(1),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('URL: /blog/{slug}')
                            ->columnSpan(1),
                        Select::make('blog_category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Ad')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique('blog_categories', 'slug'),
                            ])
                            ->columnSpan(1),
                        Select::make('user_id')
                            ->label('Yazar')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->id())
                            ->columnSpan(1),
                        Select::make('status')
                            ->label('Durum')
                            ->options(SiteStatus::class)
                            ->required()
                            ->default(SiteStatus::Draft)
                            ->live()
                            ->columnSpan(1),
                        DateTimePicker::make('published_at')
                            ->label('Yayın tarihi')
                            ->seconds(false)
                            ->native(false)
                            ->default(now())
                            ->required(fn (Get $get): bool => $get('status') === SiteStatus::Active || $get('status') === SiteStatus::Active->value)
                            ->helperText('Aktif yazılar bu tarihten itibaren önyüzde görünür.')
                            ->columnSpan(1),
                        Toggle::make('is_featured')
                            ->label('Öne çıkan')
                            ->columnSpan(1),
                        Select::make('tags')
                            ->label('Etiketler')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Ad')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique('blog_tags', 'slug'),
                            ])
                            ->columnSpan(1),
                        Textarea::make('excerpt')
                            ->label('Özet')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->label('İçerik')
                            ->required()
                            ->columnSpanFull(),
                        FileUpload::make('featured_image')
                            ->label('Kapak görseli')
                            ->image()
                            ->disk('public')
                            ->directory('blog/featured')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(4096)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('SEO')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta başlık')
                            ->maxLength(255)
                            ->helperText('Boşsa yazı başlığı kullanılır.'),
                        TextInput::make('meta_keywords')
                            ->label('Meta keywords')
                            ->maxLength(255)
                            ->helperText('Virgülle ayırın'),
                        Textarea::make('meta_description')
                            ->label('Meta açıklama')
                            ->rows(3)
                            ->maxLength(512)
                            ->columnSpanFull()
                            ->helperText('Boşsa özet veya içerik özeti kullanılır.'),
                        FileUpload::make('og_image')
                            ->label('Sosyal paylaşım görseli (OG)')
                            ->image()
                            ->disk('public')
                            ->directory('blog/og')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(4096)
                            ->helperText('Boşsa kapak görseli veya site varsayılanı kullanılır.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
