<?php

namespace App\Filament\Resources\UserNotifications\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserNotificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')->label('Kullanıcı'),
                TextEntry::make('title')->label('Başlık'),
                TextEntry::make('body')->label('İçerik')->columnSpanFull(),
                TextEntry::make('read_at')->label('Okunma')->dateTime()->placeholder('Okunmadı'),
                TextEntry::make('created_at')->label('Oluşturulma')->dateTime(),
            ]);
    }
}
