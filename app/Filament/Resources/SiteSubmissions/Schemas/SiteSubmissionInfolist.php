<?php

namespace App\Filament\Resources\SiteSubmissions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteSubmissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Başvuru')
                    ->schema([
                        TextEntry::make('user.name')->label('Kullanıcı'),
                        TextEntry::make('url')->label('URL')->url(fn ($state) => $state),
                        TextEntry::make('price')->label('Fiyat')->money('TRY'),
                        TextEntry::make('category.name')->label('Kategori')->placeholder('—'),
                        TextEntry::make('age')->label('Yaş')->placeholder('—'),
                        TextEntry::make('status')->label('Durum')->badge(),
                        TextEntry::make('admin_note')->label('Admin notu')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('reviewer.name')->label('İnceleyen')->placeholder('—'),
                        TextEntry::make('reviewed_at')->label('İnceleme zamanı')->dateTime()->placeholder('—'),
                        TextEntry::make('created_at')->label('Başvuru tarihi')->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
