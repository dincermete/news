<?php

namespace App\Filament\Resources\SupportTickets\Tables;

use App\Enums\SupportTicketStatus;
use App\Models\SupportTicket;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupportTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('subject')->label('Konu')->searchable()->limit(40),
                TextColumn::make('user.name')->label('Kullanıcı')->placeholder('Misafir')->searchable(),
                TextColumn::make('status')->label('Durum')->badge()->sortable(),
                TextColumn::make('source')->label('Kaynak')->badge(),
                TextColumn::make('chatbot_conversation_id')
                    ->label('Konuşma')
                    ->formatStateUsing(fn ($state): string => filled($state) ? '#'.$state : '—'),
                TextColumn::make('created_at')->label('Oluşturulma')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Durum')->options(SupportTicketStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('markInProgress')
                    ->label('İşleme Al')
                    ->visible(fn (SupportTicket $record): bool => $record->status === SupportTicketStatus::Open)
                    ->action(function (SupportTicket $record): void {
                        $record->forceFill(['status' => SupportTicketStatus::InProgress])->save();
                        Notification::make()->title('Ticket işleme alındı')->success()->send();
                    }),
                Action::make('markClosed')
                    ->label('Kapat')
                    ->visible(fn (SupportTicket $record): bool => $record->status !== SupportTicketStatus::Closed)
                    ->requiresConfirmation()
                    ->action(function (SupportTicket $record): void {
                        $record->forceFill(['status' => SupportTicketStatus::Closed])->save();
                        Notification::make()->title('Ticket kapatıldı')->success()->send();
                    }),
            ]);
    }
}
