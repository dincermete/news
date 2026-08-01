<?php

namespace App\Filament\Resources\SiteReviews\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteReviewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Yorum')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('site.domain')->label('Site'),
                            IconEntry::make('is_approved')->label('Onaylı')->boolean(),
                            TextEntry::make('name')->label('Ad soyad'),
                            TextEntry::make('email')->label('E-posta'),
                            TextEntry::make('phone')->label('Telefon'),
                            TextEntry::make('message')->label('Mesaj')->columnSpanFull(),
                            TextEntry::make('approved_at')->label('Onay tarihi')->dateTime()->placeholder('—'),
                            TextEntry::make('created_at')->label('Gönderim')->dateTime(),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
