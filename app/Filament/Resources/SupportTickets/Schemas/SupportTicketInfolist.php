<?php

namespace App\Filament\Resources\SupportTickets\Schemas;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\SupportTicket;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupportTicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Talep')
                    ->schema([
                        TextEntry::make('id')->label('#'),
                        TextEntry::make('subject')->label('Konu')->columnSpan(2),
                        TextEntry::make('status')->label('Durum')->badge(),
                        TextEntry::make('priority')->label('Öncelik')->badge(),
                        TextEntry::make('source')->label('Kaynak')->badge(),
                        TextEntry::make('created_at')->label('Oluşturulma')->dateTime(),
                        TextEntry::make('last_replied_at')->label('Son yanıt')->dateTime()->placeholder('—'),
                        TextEntry::make('closed_at')->label('Kapanış')->dateTime()->placeholder('—'),
                    ])
                    ->columns(3),
                Section::make('Müşteri')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Ad')
                            ->placeholder('Misafir')
                            ->url(fn (SupportTicket $record): ?string => $record->user_id
                                ? CustomerResource::getUrl('view', ['record' => $record->user_id])
                                : null),
                        TextEntry::make('user.email')
                            ->label('E-posta')
                            ->placeholder('—')
                            ->copyable(),
                        TextEntry::make('user.phone')
                            ->label('Telefon')
                            ->placeholder('—'),
                        TextEntry::make('assignee.name')
                            ->label('Atanan')
                            ->placeholder('Atanmamış'),
                        TextEntry::make('chatbot_conversation_id')
                            ->label('Chatbot')
                            ->formatStateUsing(fn ($state): string => filled($state) ? '#'.$state : '—'),
                    ])
                    ->columns(3),
            ]);
    }
}
