<?php

namespace App\Filament\Resources\SiteReviews\Schemas;

use App\Models\SiteReview;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Yorum')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('site_id')
                                ->label('Site')
                                ->relationship('site', 'domain')
                                ->searchable()
                                ->preload()
                                ->disabled()
                                ->dehydrated()
                                ->visible(fn (?SiteReview $record): bool => filled($record?->site_id))
                                ->columnSpan(1),
                            Select::make('site_bundle_id')
                                ->label('Tanıtım paketi')
                                ->relationship('bundle', 'name')
                                ->searchable()
                                ->preload()
                                ->disabled()
                                ->dehydrated()
                                ->visible(fn (?SiteReview $record): bool => filled($record?->site_bundle_id))
                                ->columnSpan(1),
                            Select::make('seo_package_id')
                                ->label('SEO paketi')
                                ->relationship('seoPackage', 'name')
                                ->searchable()
                                ->preload()
                                ->disabled()
                                ->dehydrated()
                                ->visible(fn (?SiteReview $record): bool => filled($record?->seo_package_id))
                                ->columnSpan(1),
                            Select::make('backlink_package_id')
                                ->label('Backlink paketi')
                                ->relationship('backlinkPackage', 'name')
                                ->searchable()
                                ->preload()
                                ->disabled()
                                ->dehydrated()
                                ->visible(fn (?SiteReview $record): bool => filled($record?->backlink_package_id))
                                ->columnSpan(1),
                            Toggle::make('is_approved')
                                ->label('Onaylı')
                                ->columnSpan(1),
                            TextInput::make('name')
                                ->label('Ad soyad')
                                ->required()
                                ->maxLength(120)
                                ->columnSpan(1),
                            TextInput::make('email')
                                ->label('E-posta')
                                ->email()
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('phone')
                                ->label('Telefon')
                                ->required()
                                ->maxLength(40)
                                ->columnSpan(1),
                            Textarea::make('message')
                                ->label('Mesaj')
                                ->required()
                                ->rows(5)
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
