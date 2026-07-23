<?php

namespace App\Filament\Resources\SupportTickets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SupportTicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')->label('#'),
                TextEntry::make('subject')->label('Konu'),
                TextEntry::make('user.name')->label('Kullanıcı')->placeholder('Misafir'),
                TextEntry::make('status')->label('Durum')->badge(),
                TextEntry::make('source')->label('Kaynak')->badge(),
                TextEntry::make('chatbot_conversation_id')
                    ->label('Chatbot konuşması')
                    ->formatStateUsing(fn ($state): string => filled($state) ? '#'.$state : '—'),
                TextEntry::make('body')->label('İçerik')->columnSpanFull()->placeholder('—'),
                TextEntry::make('created_at')->label('Oluşturulma')->dateTime(),
            ]);
    }
}
