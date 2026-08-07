<?php

namespace App\Filament\Resources\SiteQuestions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteQuestionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detay')
                    ->schema([
                        TextEntry::make('site.domain')
                            ->label('Site')
                            ->placeholder('—')
                            ->visible(fn ($record): bool => filled($record->site_id)),
                        TextEntry::make('bundle.name')
                            ->label('Tanıtım paketi')
                            ->placeholder('—')
                            ->visible(fn ($record): bool => filled($record->site_bundle_id)),
                        TextEntry::make('seoPackage.name')
                            ->label('SEO paketi')
                            ->placeholder('—')
                            ->visible(fn ($record): bool => filled($record->seo_package_id)),
                        TextEntry::make('backlinkPackage.name')
                            ->label('Backlink paketi')
                            ->placeholder('—')
                            ->visible(fn ($record): bool => filled($record->backlink_package_id)),
                        TextEntry::make('user.name')->label('Kullanıcı')->placeholder('Misafir'),
                        TextEntry::make('guest_email')->label('Misafir e-posta')->placeholder('—'),
                        TextEntry::make('question')->label('Soru')->columnSpanFull(),
                        TextEntry::make('answer')->label('Yanıt')->placeholder('Yanıtlanmadı')->columnSpanFull(),
                        TextEntry::make('answeredBy.name')->label('Yanıtlayan')->placeholder('—'),
                        TextEntry::make('answered_at')->label('Yanıt tarihi')->dateTime()->placeholder('—'),
                        IconEntry::make('is_public')->label('Herkese açık')->boolean(),
                        TextEntry::make('created_at')->label('Oluşturulma')->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
