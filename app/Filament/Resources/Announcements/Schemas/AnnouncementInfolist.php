<?php

namespace App\Filament\Resources\Announcements\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AnnouncementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')->label('Başlık'),
                TextEntry::make('body')->label('İçerik')->columnSpanFull(),
                TextEntry::make('audience')->label('Kitle')->badge(),
                IconEntry::make('is_active')->label('Aktif')->boolean(),
                TextEntry::make('starts_at')->label('Başlangıç')->dateTime()->placeholder('—'),
                TextEntry::make('ends_at')->label('Bitiş')->dateTime()->placeholder('—'),
            ]);
    }
}
